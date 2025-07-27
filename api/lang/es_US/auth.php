<?php

return [

    'failed' => 'Estas credenciales no coinciden con nuestros registros.',
    'password' => 'La contraseña proporcionada es incorrecta.',
    'throttle' => 'Demasiados intentos de acceso. Por favor intente nuevamente en :seconds segundos.',
    'login' => [
        'success' => ':username ha iniciado sesión con éxito',
        'already_logged_in' => 'Ya ha iniciado sesión como :username',
        'fail' => 'El intento de inicio de sesión falló. Por favor, verifique sus credenciales e intente nuevamente.',
        'error' => [
            'title' => 'Error de inicio de sesión',
            'message' => 'Error al iniciar sesión :username. Por favor, inténtelo de nuevo.'
        ],
        '2fa' => [
            'confirmed' => 'Código 2FA confirmado con éxito',
            'enabled' => 'Código 2FA habilitado con éxito',
            'disabled' => 'Código 2FA deshabilitado con éxito',
            'success' => 'Código 2FA verificado con éxito',
            'fail' => 'La verificación del código 2FA falló. Por favor, inténtelo de nuevo.'
        ],
    ],
    'logout' => [
        'success' => 'Cierre de sesión exitoso',
        'fail' => 'El intento de cierre de sesión falló. Por favor, inténtelo de nuevo.',
        'error' => [
            'title' => 'Error de cierre de sesión',
            'message' => 'Error al cerrar sesión. Por favor, inténtelo de nuevo.'
        ],
    ],
    'confirm_password' => [
        'success' => 'Contraseña confirmada con éxito',
        'fail' => 'La confirmación de la contraseña falló. Por favor, inténtelo de nuevo.',
        'error' => [
            'title' => 'Error de confirmación de contraseña',
            'message' => 'Error al confirmar la contraseña. Por favor, inténtelo de nuevo.'
        ],
    ],
    'register' => [
        'success' => ':username registrado con éxito',
        'fail' => 'El intento de registro falló. Por favor, inténtelo de nuevo.',
        'username' => [
            'invalid_characters' => 'El nombre de usuario contiene caracteres no válidos. Solo se permiten caracteres alfanuméricos, guiones y guiones bajos.',
            'whitespace' => 'El nombre de usuario no puede ser solo espacios en blanco.'
        ],
        'error' => [
            'title' => 'Error de registro',
            'message' => 'Error al registrar :username. Por favor, inténtelo de nuevo.'
        ],
        'verification' => [
            'sent' => 'Correo electrónico de verificación enviado con éxito'
        ],
    ],
    'password_reset' => [
        'success' => 'Restablecimiento de contraseña exitoso',
        'fail' => 'El restablecimiento de la contraseña falló. Por favor, inténtelo de nuevo.',
        'error' => [
            'title' => 'Error de restablecimiento de contraseña',
            'message' => 'Error al restablecer la contraseña. Por favor, inténtelo de nuevo.'
        ]
    ],
    'password_update' => [
        'success' => 'Contraseña actualizada con éxito',
        'fail' => 'La actualización de la contraseña falló. Por favor, inténtelo de nuevo.',
        'error' => [
            'title' => 'Error de actualización de contraseña',
            'message' => 'Error al actualizar la contraseña. Por favor, inténtelo de nuevo.'
        ]
    ],
    'recovery_codes' => [
        'generated' => 'Códigos de recuperación generados con éxito',
        'fail' => 'La generación de códigos de recuperación falló. Por favor, inténtelo de nuevo.',
        'error' => [
            'title' => 'Error al generar códigos de recuperación',
            'message' => 'Error al generar códigos de recuperación. Por favor, inténtelo de nuevo.'
        ]
    ],
    'browser_sessions' => [
        'retrieved' => 'Sesiones de navegador obtenidas con éxito',
        'error' => [
            'title' => 'Error al obtener sesiones de navegador',
            'message' => 'Error al obtener sesiones de navegador. Por favor, inténtelo de nuevo.'
        ],
        'logout' => [
            'success' => 'Otras sesiones de navegador cerradas con éxito',
            'fail' => 'Error al cerrar otras sesiones de navegador. Por favor, inténtelo de nuevo.',
            'error' => [
                'title' => 'Error al cerrar otras sesiones de navegador',
                'message' => 'Error al cerrar otras sesiones de navegador. Por favor, inténtelo de nuevo.'
            ]
        ],
        'session_error' => [
            'title' => 'Error de sesión',
            'message' => [
                'not_configured' => 'Almacenamiento de sesión no configurado para esta operación.',
                'unavailable' => 'Sesiones no disponibles. Por favor, verifique su configuración de sesión.',
                'not_found' => 'Sesión no encontrada. Por favor, verifique su ID de sesión.'
            ]
        ]
    ]
];
