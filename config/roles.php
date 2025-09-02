<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Roles del Sistema
    |--------------------------------------------------------------------------
    |
    | Aquí puedes definir todos los roles disponibles en el sistema
    | y sus permisos correspondientes.
    |
    */

    'roles' => [
        'user' => [
            'name' => 'Usuario',
            'description' => 'Usuario regular del sistema',
            'permissions' => [
                'reservations.view',
                'reservations.create',
                'calendar.view',
            ],
        ],
        
        'admin' => [
            'name' => 'Administrador',
            'description' => 'Administrador del sistema con acceso completo',
            'permissions' => [
                'reservations.view',
                'reservations.create',
                'reservations.edit',
                'reservations.delete',
                'reservations.change_status',
                'calendar.view',
                'calendar.edit',
                'users.manage',
            ],
        ],
        
        'moderator' => [
            'name' => 'Moderador',
            'description' => 'Usuario con permisos limitados de moderación',
            'permissions' => [
                'reservations.view',
                'reservations.create',
                'reservations.edit',
                'reservations.change_status',
                'calendar.view',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permisos del Sistema
    |--------------------------------------------------------------------------
    |
    | Lista de todos los permisos disponibles en el sistema
    |
    */

    'permissions' => [
        'reservations.view' => 'Ver reservas',
        'reservations.create' => 'Crear reservas',
        'reservations.edit' => 'Editar reservas',
        'reservations.delete' => 'Eliminar reservas',
        'reservations.change_status' => 'Cambiar estado de reservas',
        'calendar.view' => 'Ver calendario',
        'calendar.edit' => 'Editar eventos del calendario',
        'users.manage' => 'Gestionar usuarios',
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Roles por Defecto
    |--------------------------------------------------------------------------
    |
    | Rol que se asigna a los nuevos usuarios por defecto
    |
    */

    'default_role' => 'user',

    /*
    |--------------------------------------------------------------------------
    | Roles Especiales
    |--------------------------------------------------------------------------
    |
    | Roles que tienen características especiales
    |
    */

    'special_roles' => [
        'super_admin' => [
            'name' => 'Super Administrador',
            'description' => 'Acceso completo al sistema incluyendo configuración',
            'permissions' => ['*'], // Todos los permisos
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Validación de Roles
    |--------------------------------------------------------------------------
    |
    | Reglas de validación para los roles
    |
    */

    'validation' => [
        'role' => 'required|string|in:user,admin,moderator',
    ],
];
