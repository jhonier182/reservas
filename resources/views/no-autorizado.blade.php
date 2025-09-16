@extends('layouts.app')

@section('title', 'Acceso No Autorizado - Reservas')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-red-50 via-pink-50 to-orange-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full text-center">
        <!-- Icono de error -->
        <div class="mx-auto h-24 w-24 flex items-center justify-center mb-8">
            <div class="w-24 h-24 bg-gradient-to-br from-red-500 to-pink-500 rounded-full flex items-center justify-center shadow-2xl">
                <i class="fas fa-exclamation-triangle text-white text-4xl"></i>
            </div>
        </div>

        <!-- Mensaje principal -->
        <h1 class="text-4xl font-bold text-gray-900 mb-4">
            Acceso No Autorizado
        </h1>
        
        <p class="text-xl text-gray-600 mb-8">
            Tu dominio de correo electrónico no está autorizado para acceder a esta aplicación.
        </p>

        <!-- Información sobre dominios permitidos -->
        <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-200 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                Dominios Autorizados
            </h3>
            <div class="space-y-3">
                <div class="flex items-center justify-center space-x-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        @beltcolombia.com
                    </span>
                    <span class="text-gray-500 text-sm">Empleados principales</span>
                </div>
                <div class="flex items-center justify-center space-x-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        @belt.com.co
                    </span>
                    <span class="text-gray-500 text-sm">Sucursales</span>
                </div>
                <div class="flex items-center justify-center space-x-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                        @beltforge.com
                    </span>
                    <span class="text-gray-500 text-sm">Colaboradores</span>
                </div>
            </div>
        </div>

        <!-- Acciones -->
        <div class="space-y-4">
            <a href="{{ route('login') }}" class="btn-primary inline-flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Volver al Login
            </a>
            
            <div class="text-sm text-gray-500">
                Si crees que esto es un error, contacta al administrador del sistema.
            </div>
        </div>

        <!-- Información de contacto -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-500 mb-2">¿Necesitas ayuda?</p>
            <div class="flex justify-center space-x-4 text-sm">
                <a href="mailto:soporte@beltcolombia.com" class="text-blue-600 hover:text-blue-700">
                    <i class="fas fa-envelope mr-1"></i>soporte@beltcolombia.com
                </a>
                <a href="tel:+5711234567" class="text-blue-600 hover:text-blue-700">
                    <i class="fas fa-phone mr-1"></i>+57 (1) 123-4567
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
