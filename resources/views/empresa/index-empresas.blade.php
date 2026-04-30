<x-app-layout>
    @section('content')
    <div class="container-fluid" style="padding-top: 3rem; padding-bottom: 6rem; padding-left: 10px; padding-right: 20px">
        <div class="form-row justify-content-center">
            <div class="col-md-9">
                <div class="form-row justify-content-between">
                    <div class="col-md-4">
                        <h4 class="card-title">Empresas/Serviços</h4>
                    </div>
                </div>
                <div class="row pb-3">
                    <div class="col-md-7">
                        <form action="{{ route('empresas.listar') }}" method="get">
                            @csrf
                            <div class="form-row">
                                <div class="col-md-8">
                                    <input type="text" class="form-control w-100" name="buscar" placeholder="Busque pelo nome da empresa ou pelo CNPJ/CPF" value="{{ $buscar }}">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn" style="background-color: #00883D; color: white;">Buscar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card card-borda-esquerda" style="width: 100%;">
                    <div class="card-body">
                        <div div class="form-row">
                            @if(session('success'))
                                <div class="col-md-12" style="margin-top: 5px;">
                                    <div class="alert alert-success" role="alert">
                                        <p>{{session('success')}}</p>
                                    </div>
                                </div>
                            @endif
                            @if(session('error'))
                                <div class="col-md-12" style="margin-top: 5px;">
                                    <div class="alert alert-danger" role="alert">
                                        <p>{{session('error')}}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="table-responsive">
                        <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Nome</th>
                                        <th scope="col">CNPJ/CPF</th>
                                        <th scope="col">Empresário</th>
                                        <th scope="col">Grupo</th>
                                        <th scope="col">Opções</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($empresas as $i => $empresa)
                                        <tr>
                                            <th scope="row">{{ ($empresas->currentpage()-1) * $empresas->perpage() + $loop->index + 1 }}</th>
                                            <td>{{$empresa->nome}}</td>
                                            <td>{{$empresa->cpf_cnpj}}</td>
                                            <td>@if($empresa->user->role == 4)Cadastrado no REDSIM @else{{$empresa->user->name}}@endif</td>
                                            <td>{{$empresa->cnaes()->first() ? $empresa->cnaes()->first()->setor->nome : "Sem cnae cadastrado"}}</td>
                                            <td>
                                                <a  href="{{route('empresas.show', $empresa)}}" style="cursor: pointer; margin-left: 2px;"><img class="icon-licenciamento" width="20px;" src="{{asset('img/Visualizar.svg')}}"  alt="Visualizar a empresa" title="Visualizar a empresa"></a>
                                                @can('isSecretario', \App\Models\User::class)
                                                    <a data-toggle="modal" data-target="#modalAlterarRequerente" style="cursor: pointer; margin-left: 2px;"><img class="icon-licenciamento" width="20px;" src="{{asset('img/update-requerente.svg')}}"  alt="Alterar requerente" title="Alterar requerente"></a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                        </table>
                        </div>
                        @if(!empty($empresa))
                            <div class="modal fade" id="modalAlterarRequerente" data-backdrop="static" data-keyboard="false"
                            tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header" style="background-color: var(--primaria);">
                                        <h5 class="modal-title" id="staticBackdropLabel" style="color: white;">Modificar Requerente</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        Essa alteração fará com que todos os requerimentos da empresa sejam transferidos de requerente.
                                        <br>
                                        <br>
                                        <form id="update-requerente" method="POST" action="{{ route('empresas.update.requerente') }}">
                                            @csrf
                                            <input type="hidden" name="empresa_id" value="{{ $empresa->id }}">
                                            <div class="form-row">
                                                <div class="col-md-12 form-group">
                                                    <label for="user_id">{{__('Selecione o requerente')}}</label>
                                                    <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                                        <option value="">-- {{__('Selecione um requerente')}} --</option>
                                                        @foreach ($requerentes as $requerente)
                                                            <option value="{{$requerente->user_id}}">{{$requerente->user->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-success btn-color-dafault" form="update-requerente">Alterar</button>
                                    </div>
                                </div>
                            </div>
                            </div>
                        @endif
                        @if($empresas->first() == null)
                            <div class="col-md-12 text-center" style="font-size: 18px;">
                                {{__('Nenhuma empresa cadastrada')}}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="form-row justify-content-center">
                    <div class="col-md-10">
                        {{$empresas->appends(['buscar' => $buscar])->links()}}
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="col-md-12 shadow-sm p-2 px-3" style="background-color: #ffffff; border-radius: 00.5rem; margin-top: 2.6rem;">
                    <div style="font-size: 21px;" class="tituloModal">
                        Legenda
                    </div>
                    <div class="mt-2 borda-baixo"></div>
                    <ul class="list-group list-unstyled">
                        <li>
                            <div title="Visualizar empresa" class="d-flex align-items-center my-1 pt-0 pb-1">
                                <img class="icon-licenciamento aling-middle" width="20" src="{{asset('img/Visualizar.svg')}}" alt="Visualizar empresa">
                                <div style="font-size: 15px;" class="aling-middle mx-3">
                                    Visualizar empresa
                                </div>
                            </div>
                        </li>
                        @can('isSecretario', \App\Models\User::class)
                            <li>
                                <div title="Alterar requerente" class="d-flex align-items-center my-1 pt-0 pb-1">
                                    <img class="icon-licenciamento aling-middle" width="20" src="{{asset('img/update-requerente.svg')}}" alt="Alterar requerente">
                                    <div style="font-size: 15px;" class="aling-middle mx-3">
                                        Alterar requerente
                                    </div>
                                </div>
                            </li>
                        @endcan
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endsection
</x-app-layout>
