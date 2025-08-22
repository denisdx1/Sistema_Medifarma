<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema Medifarma')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Global theme variables and overrides -->
    <link rel="stylesheet" href="{{ asset('css/global-theme.css') }}">

    @stack('styles')
    <style>
        body {
            font-family: 'Inter', sans-serif;
            */background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .sidebar-collapsed {
            width: 4rem !important;
        }
        
        .sidebar-collapsed .sidebar-text {
            opacity: 0;
            transform: translateX(-20px);
            transition: all 0.3s ease;
        }
        
        .sidebar-collapsed #sidebar-logo {
            opacity: 0;
        }
        
        /* Manejar badges de mercados pendientes */
        .sidebar-collapsed #pending-markets-badge {
            opacity: 0;
            visibility: hidden;
            transform: translateX(-20px);
            transition: all 0.3s ease;
        }
        
        /* Mostrar badge compacto cuando sidebar está contraído */
        #pending-markets-badge-collapsed {
            opacity: 0;
            visibility: hidden;
            transform: scale(0);
            transition: all 0.3s ease;
        }
        
        .sidebar-collapsed #pending-markets-badge-collapsed {
            opacity: 1;
            visibility: visible;
            transform: scale(1);
        }
        
        /* Asegurar que el badge contraído no interfiera con el layout */
        .sidebar-collapsed #pending-markets-badge-collapsed {
            z-index: 10;
        }
        
        .content-expanded {
            margin-left: 4rem !important;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Toast notifications */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 500px;
            border-radius: 8px;
            padding: 16px;
            color: white;
            font-weight: 500;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            transform: translateX(100%);
            transition: transform 0.3s ease-in-out;
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast-success {
            background-color: #10b981;
        }
        
        .toast-error {
            background-color: #ef4444;
        }
        
        .toast-warning {
            background-color: #f59e0b;
        }
        
        .toast-info {
            background-color: var(--info);
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-white-500">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <x-sidebar />
        
        <!-- Main Content -->
        <main id="main-content" class="flex-1 transition-all duration-300 ease-in-out overflow-y-auto">
            <!-- Top Navigation Bar -->
            <header class="bg-white shadow-sm border-b border-gray-200 sticky top-0 z-40">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-800">
                            @yield('page-title', 'Sistema Medifarma')
                        </h1>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <div class="p-6">
                @yield('content')
            </div>
        </main>
    </div>
    
    <!-- Toast Container -->
    <div id="toast-container"></div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Sidebar toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('toggle-sidebar');
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('main-content');
            
            if (toggleBtn && sidebar && mainContent) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('sidebar-collapsed');
                    mainContent.classList.toggle('content-expanded');
                });
            }
        });
        
        // Toast notification function
        function showToast(message, type = 'info', duration = 5000) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        ${type === 'success' ? '<i class="fas fa-check-circle"></i>' : 
                          type === 'error' ? '<i class="fas fa-exclamation-circle"></i>' :
                          type === 'warning' ? '<i class="fas fa-exclamation-triangle"></i>' :
                          '<i class="fas fa-info-circle"></i>'}
                    </div>
                    <div class="ml-3">
                        <p class="text-sm">${message}</p>
                    </div>
                    <div class="ml-auto pl-3">
                        <button type="button" class="inline-flex text-white hover:opacity-75 focus:outline-none" onclick="this.parentElement.parentElement.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
            
            container.appendChild(toast);
            
            // Show toast
            setTimeout(() => toast.classList.add('show'), 100);
            
            // Auto remove
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }
        
        // Show Laravel session messages as toasts
        @if(session('success'))
            showToast('{{ session('success') }}', 'success');
        @endif
        
        @if(session('error'))
            showToast('{{ session('error') }}', 'error');
        @endif
        
        @if(session('warning'))
            showToast('{{ session('warning') }}', 'warning');
        @endif
        
        @if(session('info'))
            showToast('{{ session('info') }}', 'info');
        @endif
        
        // Show validation errors
        @if($errors->any())
            @foreach($errors->all() as $error)
                showToast('{{ $error }}', 'error');
            @endforeach
        @endif
    </script>
    
    @stack('scripts')
</body>
</html>
