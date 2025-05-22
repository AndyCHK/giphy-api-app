<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Interacciones API
    |--------------------------------------------------------------------------
    |
    | Aquí se definen las configuraciones relacionadas con el registro
    | de interacciones con las APIs.
    |
    */

    'interactions' => [
        // Tamaño máximo en caracteres para los cuerpos de solicitud/respuesta
        'max_content_length' => env('API_MAX_CONTENT_LENGTH', 10000),

        // Mensaje que se agregará cuando el contenido se trunca
        'truncated_message' => '... [contenido truncado]',
    ],
];
