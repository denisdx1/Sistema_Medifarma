@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8">
        <div class="text-center">
            <!-- Icono de error -->
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-6">
                <i class="fas fa-exclamation-triangle text-red-600 text-2xl"></i>
            </div>
            
            <!-- Título -->
            <h1 class="text-2xl font-bold text-gray-900 mb-4">
                Sesión Expirada
            </h1>
            
            <!-- Mensaje -->
            <p class="text-gray-600 mb-8">
                Su sesión ha expirado por inactividad o el token de seguridad ha caducado. 
                Por favor, inicie sesión nuevamente para continuar.
            </p>
            
            <!-- Botones de acción -->
            <div class="space-y-3">
                <a href="{{ route('login') }}" 
                   class="w-full inline-flex justify-center items-center px-4 py-3 bg-primary border border-transparent rounded-lg font-medium text-white hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200">
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Iniciar Sesión
                </a>
                
                <button onclick="window.location.reload()" 
                        class="w-full inline-flex justify-center items-center px-4 py-3 border border-gray-300 rounded-lg font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200">
                    <i class="fas fa-redo mr-2"></i>
                    Intentar de Nuevo
                </button>
            </div>
            
            <!-- Información adicional -->
            <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                    <div class="text-sm text-blue-700">
                        <strong>Consejo:</strong> Si este problema persiste, intente cerrar y abrir su navegador, 
                        o limpiar las cookies del sitio.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-redirect después de 5 segundos
setTimeout(function() {
    window.location.href = '{{ route("login") }}';
}, 5000);
</script>
@endsection
