<x-app-layout>
    @section('content')
    <div class="container-fluid" style="padding-top: 3rem; padding-bottom: 6rem; padding-left: 10px; padding-right: 20px">
        <div class="form-row justify-content-center">
            <div class="col-md-12">
                <div class="form-row">
                    <div class="col-md-12">
                        <h4 class="card-title">Solicitar uma Aração</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card card-borda-esquerda" style="width: 100%;">
                    <div class="card-body">
                    <form id="create-aracao" method="POST" action="{{route('aracao.store')}}">
                            @csrf
                            <div class="form-row">
                                <div class="col-md-6 form-group">
                                    <label for="solicitante">{{ __('Solicitante') }}</label>
                                    <input id="solicitante" class="form-control @error('solicitante') is-invalid @enderror" type="string" name="solicitante" value="{{old('solicitante')}}" autofocus autocomplete="solicitante" placeholder="Digite o nome do solicitante...">
                                    @error('solicitante')
                                        <div id="validationServer03Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="cultura">{{ __('Cultura') }}</label>
                                    <input id="cultura" class="form-control @error('cultura') is-invalid @enderror" type="string" name="cultura" value="{{old('cultura')}}" autocomplete="cultura" placeholder="Digite o nome da cultura...">
                                    @error('cultura')
                                        <div id="validationServer03Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6 form-group">
                                    <label for="ponto_localizacao">{{ __('Ponto de Referência') }}</label>
                                    <input id="ponto_localizacao" class="form-control @error('ponto_localizacao') is-invalid @enderror" type="string" name="ponto_localizacao" value="{{old('ponto_localizacao')}}" autocomplete="ponto_localizacao" placeholder="Digite o ponto de referência...">
                                    @error('ponto_localizacao')
                                        <div id="validationServer03Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="quantidade_ha">{{ __('Área em hectares (ha)') }}</label>
                                    <input id="quantidade_ha" class="form-control @error('quantidade_ha') is-invalid @enderror" type="number" name="quantidade_ha" value="{{old('quantidade_ha')}}" autocomplete="quantidade_ha" placeholder="Ex: 1.5" min="0" step="0.01" inputmode="decimal">
                                    @error('quantidade_ha')
                                        <div id="validationServer03Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-md-6 form-group">
                                    <label for="quantidade_horas">{{ __('Quantidade de Horas') }}</label>
                                    <input id="quantidade_horas" class="form-control @error('quantidade_horas') is-invalid @enderror" type="number" name="quantidade_horas" value="{{old('quantidade_horas')}}" autocomplete="quantidade_horas" placeholder="Ex: 4" min="0" step="1" inputmode="numeric">
                                    @error('quantidade_horas')
                                        <div id="validationServer03Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="beneficiario_id">{{ __('Beneficiário') }}</label>
                                    <select name="beneficiario_id" id="beneficiario_id" class="form-control selectpicker @error('beneficiario_id') is-invalid @enderror" data-live-search="true">
                                        <option value="" selected>-- {{__('Selecione o Beneficiário')}} --</option>
                                        @foreach ($beneficiarios as $beneficiario)
                                            <option @if(old('beneficiario_id') == $beneficiario->id) selected @endif value="{{$beneficiario->id}}">{{$beneficiario->nome}}</option>
                                        @endforeach
                                    </select>
                                    @error('beneficiario_id')
                                        <div id="validationServer03Feedback" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="card-footer">
                        <div class="form-row">
                            <div class="col-md-6"></div>
                            <div class="col-md-6" style="text-align: right">
                                <button type="submit" class="btn btn-success btn-color-dafault submeterFormBotao" form="create-aracao" style="width: 100%">Salvar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- @push ('scripts')
        <script>

        </script>
    @endpush --}}
@endsection
</x-app-layout>
