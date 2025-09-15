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
    'emails' => array_filter(array_map('trim', explode(',', env('ADMIN_EMAILS', '')))),


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
    ],
];

