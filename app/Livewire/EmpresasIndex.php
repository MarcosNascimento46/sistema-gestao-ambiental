<?php

namespace App\Livewire;

use App\Models\Empresa;
use App\Models\Requerente;
use Livewire\Component;
use Livewire\WithPagination;

class EmpresasIndex extends Component
{
    use WithPagination;

    public $search = '';

    public function render()
    {
        $requerentes = Requerente::all();
        $search = trim($this->search);
        $digits = preg_replace('/\D/', '', $search);

        $empresasQuery = Empresa::query();

        if ($search !== '') {
            $empresasQuery->where('nome', 'ilike', '%' . $search . '%');

            if ($digits !== '') {
                $empresasQuery->orWhereRaw(
                    "regexp_replace(cpf_cnpj, '[^0-9]', '', 'g') like ?",
                    ['%' . $digits . '%']
                );
            }
        }

        $empresas = $empresasQuery->orderBy('nome')->paginate(20);
        return view('livewire.empresas-index', ['empresas' => $empresas, 'requerentes' => $requerentes]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}
