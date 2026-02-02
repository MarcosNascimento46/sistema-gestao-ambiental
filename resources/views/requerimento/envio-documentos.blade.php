<x-app-layout>
    @section('content')

    @livewire('enviar-documentos', ['requerimento' => $requerimento])

    @push ('scripts')
        <script>
            window.addEventListener('swal:fire', event => {
                const detail = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                Swal.fire({
                    position: 'bottom-end',
                    icon: detail?.icon ?? 'info',
                    title: detail?.title ?? 'Operacao concluida',
                    showConfirmButton: false,
                    timerProgressBar: true,
                    timer: 3000,
                    toast: true,
                    showCancelButton: false,
                    showConfirmButton: false
                })
            });
            function editar_caminho(string) {
                return string.split("\\")[string.split("\\").length - 1];
            }
            function checar_arquivos() {
                if (window.$ && $.fn && typeof $.fn.modal === 'function') {
                    $("#modalStaticConfirmarEnvio").modal('show');
                    return;
                }

                if (confirm('Tem certeza que deseja enviar estes documentos?')) {
                    document.querySelector('.submeterFormBotao')?.click();
                }
            }
        </script>
    @endpush
    @endsection
</x-app-layout>
