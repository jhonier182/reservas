<footer class="bg-white border-t border-gray-200 mt-auto">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Información de la empresa -->
            <div>
                <div class="flex items-center mb-4">
                    <div class="w-8 h-8 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
                        <span class="text-white text-lg font-bold">T</span>
                    </div>
                    <span class="ml-2 text-lg font-bold text-gray-900">Reservas</span>
                </div>
                <p class="text-gray-600 text-sm">
                    Sistema de gestión de reservas y calendario para nuestra empresa Belt. 
                    Organiza tu tiempo de manera eficiente.
                </p>
            </div>

            <!-- Enlaces rápidos -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase mb-4">
                    Enlaces Rápidos
                </h3>
                <ul class="space-y-2">
                    @auth
                        <li>
                            <a href="{{ route('home') }}" class="text-gray-600 hover:text-blue-600 text-sm transition-colors duration-200">
                                <i class="fas fa-home mr-2"></i>Inicio
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('calendar') }}" class="text-gray-600 hover:text-blue-600 text-sm transition-colors duration-200">
                                <i class="fas fa-calendar mr-2"></i>Calendario
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('reservations.index') }}" class="text-gray-600 hover:text-blue-600 text-sm transition-colors duration-200">
                                <i class="fas fa-list mr-2"></i>Reservas
                            </a>
                        </li>
                    @else
                        <li>
                            <a href="{{ route('login') }}" class="text-gray-600 hover:text-blue-600 text-sm transition-colors duration-200">
                                <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>

            <!-- Información de contacto -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 tracking-wider uppercase mb-4">
                    Contacto
                </h3>
                <div class="space-y-2 text-sm text-gray-600">
                    <p>
                        <i class="fas fa-building mr-2"></i>
                        Belt Colombia
                    </p>
                    <p>
                        <i class="fas fa-envelope mr-2"></i>
                        info@beltcolombia.com
                    </p>
                    <p>
                        <i class="fas fa-phone mr-2"></i>
                        +57 (1) 123-4567
                    </p>
                </div>
            </div>
        </div>

        <!-- Línea divisoria y copyright -->
        <div class="mt-8 pt-8 border-t border-gray-200">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-500 text-sm">
                    © {{ date('Y') }} Belt Colombia. Todos los derechos reservados.
                </p>
                
            </div>
        </div>
    </div>
</footer>
