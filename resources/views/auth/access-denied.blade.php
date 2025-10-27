@extends('layouts.app')

@section('title', 'Acceso Denegado')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <!-- Icono -->
            <div class="mx-auto h-24 w-24 text-red-500 mb-6">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                    </path>
                </svg>
            </div>
            
            <!-- Título -->
            <h1 class="text-3xl font-bold text-gray-900 mb-4">Acceso Denegado</h1>
            
            <!-- Mensaje -->
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                <h2 class="text-lg font-semibold text-red-800 mb-2">Sistema Temporalmente No Disponible</h2>
                <p class="text-red-700">
                    El sistema está configurado para funcionar solo en períodos específicos. 
                    Actualmente no se encuentra dentro del horario de acceso permitido.
                </p>
            </div>
            
            <!-- Información adicional -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Información Importante</h3>
                <ul class="text-gray-700 text-sm space-y-2">
                    <li class="flex items-start">
                        <span class="text-blue-500 mr-2">•</span>
                        Solo los administradores pueden acceder al sistema fuera de los períodos configurados
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-500 mr-2">•</span>
                        Los períodos de acceso son configurados por el administrador del sistema
                    </li>
                    <li class="flex items-start">
                        <span class="text-blue-500 mr-2">•</span>
                        Si necesita acceso urgente, contacte al administrador
                    </li>
                </ul>
            </div>
            
            <!-- Botones de acción -->
            <div class="space-y-4">
                <button onclick="window.location.reload()" 
                        class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                    🔄 Intentar Nuevamente
                </button>
                
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" 
                            class="w-full flex justify-center py-3 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        🚪 Cerrar Sesión
                    </button>
                </form>
            </div>
            
            <!-- Información de contacto -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-600">
                    Si necesita asistencia, contacte al administrador del sistema
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Auto-refresh cada 5 minutos para verificar si el acceso está disponible -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Verificar cada 5 minutos si el acceso está disponible
    setInterval(function() {
        fetch('{{ route("usuarios.estado-acceso") }}')
            .then(response => response.json())
            .then(data => {
                if (data.habilitado) {
                    // Si el acceso está disponible, mostrar notificación y recargar
                    if (confirm('¡El sistema está disponible ahora! ¿Desea acceder?')) {
                        window.location.href = '{{ route("market-management.index") }}';
                    }
                }
            })
            .catch(error => {
                console.log('Error verificando estado del sistema:', error);
            });
    }, 300000); // 5 minutos
});
</script>
@endsection
