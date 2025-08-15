<nav class="bg-white shadow-lg border-b border-gray-200" x-data="{ mobileMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Logo y nombre -->
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                        <span class="text-white text-xl font-bold">T</span>
                    </div>
                    <span class="ml-3 text-xl font-bold text-gray-900">TodoList</span>
                </div>
            </div>

            <!-- Navegación desktop -->
            <div class="hidden md:flex items-center space-x-8">
                @auth
                    <a href="{{ route('home') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        <i class="fas fa-home mr-2"></i>Inicio
                    </a>
                    <a href="{{ route('calendar') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        <i class="fas fa-calendar mr-2"></i>Calendario
                    </a>
                    <a href="{{ route('reservations.index') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        <i class="fas fa-list mr-2"></i>Reservas
                    </a>
                    <a href="{{ route('profile') }}" class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors duration-200">
                        <i class="fas fa-user mr-2"></i>Perfil
                    </a>
                @endauth
            </div>

            <!-- Botones de autenticación -->
            <div class="flex items-center space-x-4">
                @auth
                    <!-- Usuario autenticado -->
                    <div class="relative" x-data="{ profileMenuOpen: false }">
                        <button @click="profileMenuOpen = !profileMenuOpen" class="flex items-center space-x-2 text-gray-700 hover:text-blue-600 transition-colors duration-200">
                            @if(auth()->user()->avatar)
                                <img class="w-8 h-8 rounded-full" src="{{ auth()->user()->avatar }}" alt="Avatar">
                            @else
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-semibold">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                </div>
                            @endif
                            <span class="hidden sm:block text-sm font-medium">{{ auth()->user()->name }}</span>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        
                        <!-- Menú desplegable -->
                        <div x-show="profileMenuOpen" @click.away="profileMenuOpen = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                            <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user mr-2"></i>Mi Perfil
                            </a>
                            <a href="{{ route('google.auth') }}?email={{ Auth::user()->email }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-calendar-alt mr-2"></i>Sincronizar Google
                            </a>
                            <hr class="my-1">
                            <form method="POST" action="{{ route('auth.logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <!-- Usuario no autenticado -->
                    <a href="{{ route('login') }}" class="btn-primary">
                        <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                    </a>
                @endauth
            </div>

            <!-- Botón menú móvil -->
            <div class="md:hidden flex items-center">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-700 hover:text-blue-600 p-2">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Menú móvil -->
    <div x-show="mobileMenuOpen" x-transition class="md:hidden">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-white border-t border-gray-200">
            @auth
                <a href="{{ route('home') }}" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md text-base font-medium">
                    <i class="fas fa-home mr-2"></i>Inicio
                </a>
                <a href="{{ route('calendar') }}" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md text-base font-medium">
                    <i class="fas fa-calendar mr-2"></i>Calendario
                </a>
                <a href="{{ route('reservations.index') }}" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md text-base font-medium">
                    <i class="fas fa-list mr-2"></i>Reservas
                </a>
                <a href="{{ route('profile') }}" class="block px-3 py-2 text-gray-700 hover:text-blue-600 hover:bg-gray-50 rounded-md text-base font-medium">
                    <i class="fas fa-user mr-2"></i>Perfil
                </a>
            @endauth
        </div>
    </div>
</nav>
