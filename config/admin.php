<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Correos de Administradores
    |--------------------------------------------------------------------------
    |
    | Lista de correos electrónicos que tendrán acceso de administrador
    | cuando se autentiquen con Google.
    |
    */
    'admin_emails' => [
        'admin@tuapp.com',
        'tu-correo-admin@beltcolombia.com',
        // Agrega aquí los correos que quieras que sean admin
    ],

    /*
    |--------------------------------------------------------------------------
    | Dominios Permitidos
    |--------------------------------------------------------------------------
    |
    | Dominios de correo electrónico que están permitidos para autenticarse
    | en la aplicación.
    |
    */
    'allowed_domains' => [
        '@beltcolombia.com',
        '@belt.com.co', 
        '@beltforge.com',
        '@belforge.com',
    ],
];

