<?php

namespace App\Console\Commands;

use DOMDocument;
use DOMXPath;
use App\Models\BoletoAvulso;
use App\Models\WebServiceCaixa\ConsultarBoletoRemessaAvulso;
use App\Models\WebServiceCaixa\Pessoa;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class AtualizarStatusBoletosAvulsos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atualizar:boletosAvulsos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza o status de pagamento dos boletos não vencidos ainda não pagos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // boletos em que o status está indefinido OU (boletos pendentes OU boletos vencidos a menos de 5 dias)
        $boletos = BoletoAvulso::whereNotNull('nosso_numero')
            ->where(function ($qry) {
                $qry->whereNull('status_pagamento')
                    ->orWhere(function ($qry) {
                        // boletos ainda não pagos
                        $qry->where('status_pagamento', 2)
                            ->orWhere(
                                // boletos vencidos a menos de 5 dias
                                [
                                    ['status_pagamento', '=', 3],
                                    ['data_vencimento', '>', now()->subDays(5)],
                                ]
                            );
                    });
            })
            ->lazy()->each(function ($boleto) {
                try {
                    $dados = $this->consultaStatus($boleto);
                    DB::table('boleto_avulsos')
                        ->where('id', $boleto->id)
                        ->update([
                            'status_pagamento' => $dados['status'],
                            'data_pagamento' => $dados['data'],
                            'updated_at' => now(),
                        ]);
                } catch (Throwable $e) {
                    Log::warning('Falha ao consultar boleto avulso na Caixa.', [
                        'boleto_avulso_id' => $boleto->id,
                        'nosso_numero' => $boleto->nosso_numero,
                        'erro' => $e->getMessage(),
                    ]);
                    $this->warn("Boleto avulso {$boleto->id} ignorado: {$e->getMessage()}");
                }
            });
    }

    private function consultaStatus(BoletoAvulso $boleto)
    {
        $beneficiario = new Pessoa();
        $beneficiario->gerarBeneficiario();
        $consulta = new ConsultarBoletoRemessaAvulso();
        $consulta->setAttributes([
            'codigo_beneficiario' => $beneficiario->cod_beneficiario,
            'nosso_numero' => $boleto->nosso_numero,
            'beneficiario' => $beneficiario,
        ]);
        $string = $consulta->gerarRemessa();
        $caminho_arquivo = 'remessas/';
        $documento_nome = 'consultar_boleto_remessa_avulso' . $boleto->id . '.xml';

        $file = fopen(storage_path('') . '/app/' . $caminho_arquivo . $documento_nome, 'w+');
        fwrite($file, $string);
        fclose($file);

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => ConsultarBoletoRemessaAvulso::URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSL_CIPHER_LIST => 'DEFAULT@SECLEVEL=1',
            CURLOPT_POSTFIELDS => file_get_contents(storage_path('') . '/app/' . $caminho_arquivo . $documento_nome),
            CURLOPT_HTTPHEADER => [
                'SoapAction: CONSULTA_BOLETO',
                'Content-Type: text/plain',
            ],
        ]);

        $response = curl_exec($curl);
        $curl_error = curl_error($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($response === false) {
            throw new RuntimeException($curl_error ?: 'Erro desconhecido ao consultar a Caixa.');
        }

        $this->salvarRespostaConsulta($boleto, $response);

        if ($http_code >= 400) {
            throw new RuntimeException("Caixa retornou HTTP {$http_code}.");
        }

        return $this->statusPorRetorno($this->extrairRetornoConsulta($response));
    }

    private function salvarRespostaConsulta(BoletoAvulso $boleto, string $response): void
    {
        delete_file($boleto->resposta_consultar_boleto);

        $caminho_arquivo = 'remessas/';
        $documento_nome = 'resposta_consultar_boleto_remessa_avulso' . $boleto->id . '.xml';

        $file = fopen(storage_path('') . '/app/' . $caminho_arquivo . $documento_nome, 'w+');
        fwrite($file, $response);
        fclose($file);

        DB::table('boleto_avulsos')
            ->where('id', $boleto->id)
            ->update(['resposta_consultar_boleto' => $caminho_arquivo . $documento_nome]);
    }

    private function extrairRetornoConsulta(string $response): string
    {
        try {
            $resultado = (new ConsultarBoletoRemessaAvulso())->xmlToArray($response);
            if (isset($resultado['COD_RETORNO']['DADOS']) && (int) $resultado['COD_RETORNO']['DADOS'] === 0 && isset($resultado['RETORNO'])) {
                return $resultado['RETORNO'];
            }
        } catch (Throwable $e) {
            Log::debug('Parser legado da consulta de boleto avulso falhou; tentando fallback.', [
                'erro' => $e->getMessage(),
            ]);
        }

        return $this->extrairRetornoConsultaPorXpath($response);
    }

    private function extrairRetornoConsultaPorXpath(string $response): string
    {
        $dom_document = new DOMDocument();
        $libxml_previous_state = libxml_use_internal_errors(true);

        try {
            if (! $dom_document->loadXML($response)) {
                $erros = array_map(function ($erro) {
                    return trim($erro->message);
                }, libxml_get_errors());

                throw new RuntimeException('Resposta XML invalida da Caixa: ' . implode(' | ', $erros));
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($libxml_previous_state);
        }

        $xpath = new DOMXPath($dom_document);
        $retornos = $xpath->query('//*[local-name()="RETORNO"]');
        if ($retornos === false || $retornos->length === 0) {
            throw new RuntimeException('Resposta da Caixa sem tag RETORNO.');
        }

        return trim($retornos->item(0)->textContent);
    }

    private function statusPorRetorno(string $retorno): array
    {
        switch ($retorno) {
            case '(0) OPERACAO EFETUADA - SITUACAO DO TITULO = EM ABERTO':
                return ['status' => BoletoAvulso::STATUS_PAGAMENTO_ENUM['nao_pago'], 'data' => null];
            case '(0) OPERACAO EFETUADA - SITUACAO DO TITULO = BAIXA POR DEVOLUCAO':
                return ['status' => BoletoAvulso::STATUS_PAGAMENTO_ENUM['vencido'], 'data' => null];
            case '(0) OPERACAO EFETUADA - SITUACAO DO TITULO = LIQUIDADO':
                return ['status' => BoletoAvulso::STATUS_PAGAMENTO_ENUM['pago'], 'data' => now()];
            default:
                Log::warning('Retorno desconhecido da consulta de boleto avulso.', [
                    'retorno' => $retorno,
                ]);

                return ['status' => BoletoAvulso::STATUS_PAGAMENTO_ENUM['nao_pago'], 'data' => null];
        }
    }
}
