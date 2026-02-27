<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'melhor_envio' => [
        'client_id' => env('MELHOR_ENVIO_CLIENT_ID', '22703'),
        'client_secret' => env('MELHOR_ENVIO_CLIENT_SECRET', 'vDeSHyeTTE8zVHQQjrgUaO0ockSFXb74eRXY5m1w'),
        'redirect_uri' => env('MELHOR_ENVIO_REDIRECT_URI', env('APP_URL') . '/api/melhor-envio/callback'),
        'production' => env('MELHOR_ENVIO_PRODUCTION', false),
        'user_agent' => env('MELHOR_ENVIO_USER_AGENT', 'Psiké Deloun Arts (contato@psikedeloun.com)'),
    ],

];
