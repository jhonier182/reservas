<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TodoList - Belt Colombia')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        .glass {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .btn-primary {
            @apply bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold py-3 px-6 rounded-2xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl;
        }
        
        .btn-secondary {
            @apply bg-white text-gray-700 font-semibold py-3 px-6 rounded-2xl border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 transition-all duration-300 transform hover:scale-105 shadow-md hover:shadow-lg;
        }
        
        .form-input {
            @apply w-full px-4 py-3 border-2 border-gray-200 rounded-2xl focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all duration-300 text-gray-700 placeholder-gray-400;
        }
        
        .form-label {
            @apply block text-sm font-semibold text-gray-700 mb-2;
        }
        
        .form-checkbox {
            @apply h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navbar -->
    @include('partials.navbar')
    
    <!-- Contenido principal -->
    <main class="min-h-screen">
        @yield('content')
    </main>
    
    <!-- Footer -->
    @include('partials.footer')
    
    <!-- Scripts -->
    @stack('scripts')
    
    <!-- Mensajes Flash -->
    @if(session('success'))
        <div id="success-message" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in-up">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                {{ session('success') }}
            </div>
        </div>
        <script>
            setTimeout(() => {
                document.getElementById('success-message').style.display = 'none';
            }, 5000);
        </script>
    @endif
    
    @if(session('error'))
        <div id="error-message" class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-fade-in-up">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                {{ session('error') }}
            </div>
        </div>
        <script>
            setTimeout(() => {
                document.getElementById('error-message').style.display = 'none';
            }, 5000);
        </script>
    @endif
</body>
</html>
