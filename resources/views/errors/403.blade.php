@extends('layouts.app')

@section('title', 'Acceso Denegado - Sistema Medifarma')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-red-50 to-orange-50 flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <!-- Error Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 text-center">
            <!-- Error Icon -->
            <div class="mb-6">
                <div class="mx-auto w-20 h-20 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-ban text-red-500 text-3xl"></i>
                </div>
            </div>

            <!-- Error Message -->
            <h1 class="text-3xl font-bold text-gray-900 mb-2">403</h1>
            <h2 class="text-xl font-semibold text-red-600 mb-4">Acceso Denegado</h2>
            
            <p class="text-gray-600 mb-6">
                No tienes los permisos necesarios para acceder a esta sección del sistema.
            </p>

            <!-- User Info -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-center">
                    <div class="w-10 h-10 rounded-full bg-red-600 text-white flex items-center justify-center font-bold mr-3">
                        {{ substr(Auth::user()->usuario, 0, 1) }}
                    </div>
                    <div class="text-left">
                        <p class="text-sm font-medium text-gray-900">{{ Auth::user()->usuario }}</p>
                        <p class="text-xs text-gray-500">{{ Auth::user()->getRoleDisplayName() }}</p>
                    </div>
                </div>
            </div>

            <!-- Required Access -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-yellow-500 mt-0.5 mr-2"></i>
                    <div class="text-sm text-yellow-700 text-left">
                        <strong>Acceso requerido:</strong> Solo los usuarios con rol de <strong>Administrador</strong> pueden acceder al módulo de Gestión de Usuarios.
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="space-y-3">
                <button onclick="history.back()" 
                        class="w-full bg-gradient-to-r from-gray-600 to-gray-700 text-white py-3 px-4 rounded-lg hover:from-gray-700 hover:to-gray-800 transition-all duration-200 transform hover:scale-105">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver Atrás
                </button>
                
                <a href="{{ route('market-management.index') }}" 
                   class="w-full bg-gradient-to-r from-red-600 to-blue-600 text-white py-3 px-4 rounded-lg hover:from-red-700 hover:to-blue-700 transition-all duration-200 transform hover:scale-105 inline-block">
                    <i class="fas fa-home mr-2"></i>
                    Ir al Inicio
                </a>
            </div>

            <!-- Contact Admin -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-xs text-gray-500">
                    ¿Necesitas acceso? Contacta al administrador del sistema.
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-gray-500 text-sm">
            <p>&copy; {{ date('Y') }} Medifarma. Sistema de Gestión.</p>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .animate-bounce-slow {
        animation: bounce 2s infinite;
    }
    
    @keyframes bounce {
        0%, 20%, 53%, 80%, 100% {
            transform: translate3d(0,0,0);
        }
        40%, 43% {
            transform: translate3d(0,-30px,0);
        }
        70% {
            transform: translate3d(0,-15px,0);
        }
        90% {
            transform: translate3d(0,-4px,0);
        }
    }
</style>
@endpush
