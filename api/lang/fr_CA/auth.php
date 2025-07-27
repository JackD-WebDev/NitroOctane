<?php

return [

    'failed' => 'Ces informations d’identification ne correspondent pas à nos enregistrements.',
    'password' => 'Le mot de passe fourni est incorrect.',
    'throttle' => 'Trop de tentatives de connexion. Veuillez réessayer dans :seconds secondes.',
    'login' => [
        'success' => ':username s’est connecté avec succès',
        'already_logged_in' => 'Vous êtes déjà connecté en tant que :username',
        'fail' => 'La tentative de connexion a échoué. Veuillez vérifier vos informations d’identification et réessayer.',
        'error' => [
            'title' => 'Erreur de connexion',
            'message' => 'Erreur lors de la connexion de :username. Veuillez réessayer.'
        ],
        '2fa' => [
            'confirmed' => 'Code 2FA confirmé avec succès',
            'enabled' => 'Code 2FA activé avec succès',
            'disabled' => 'Code 2FA désactivé avec succès',
            'success' => 'Code 2FA vérifié avec succès',
            'fail' => 'La vérification du code 2FA a échoué. Veuillez réessayer.'
        ],
    ],
    'logout' => [
        'success' => 'Déconnexion réussie',
        'fail' => 'La tentative de déconnexion a échoué. Veuillez réessayer.',
        'error' => [
            'title' => 'Erreur de déconnexion',
            'message' => 'Erreur lors de la déconnexion. Veuillez réessayer.'
        ],
    ],
    'confirm_password' => [
        'success' => 'Mot de passe confirmé avec succès',
        'fail' => 'La confirmation du mot de passe a échoué. Veuillez réessayer.',
        'error' => [
            'title' => 'Erreur de confirmation du mot de passe',
            'message' => 'Erreur lors de la confirmation du mot de passe. Veuillez réessayer.'
        ],
    ],
    'register' => [
        'success' => ':username enregistré avec succès',
        'fail' => 'La tentative d’inscription a échoué. Veuillez réessayer.',
        'username' => [
            'invalid_characters' => 'Le nom d’utilisateur contient des caractères non valides. Seuls les caractères alphanumériques, les tirets et les traits de soulignement sont autorisés.',
            'whitespace' => 'Le nom d’utilisateur ne peut pas être uniquement des espaces.'
        ],
        'error' => [
            'title' => 'Erreur d’inscription',
            'message' => 'Erreur lors de l’inscription de :username. Veuillez réessayer.'
        ],
        'verification' => [
            'sent' => 'Courriel de vérification envoyé avec succès'
        ],
    ],
    'password_reset' => [
        'success' => 'Réinitialisation du mot de passe réussie',
        'fail' => 'La réinitialisation du mot de passe a échoué. Veuillez réessayer.',
        'error' => [
            'title' => 'Erreur de réinitialisation du mot de passe',
            'message' => 'Erreur lors de la réinitialisation du mot de passe. Veuillez réessayer.'
        ]
    ],
    'password_update' => [
        'success' => 'Mot de passe mis à jour avec succès',
        'fail' => 'La mise à jour du mot de passe a échoué. Veuillez réessayer.',
        'error' => [
            'title' => 'Erreur de mise à jour du mot de passe',
            'message' => 'Erreur lors de la mise à jour du mot de passe. Veuillez réessayer.'
        ]
    ],
    'recovery_codes' => [
        'generated' => 'Codes de récupération générés avec succès',
        'fail' => 'La génération des codes de récupération a échoué. Veuillez réessayer.',
        'error' => [
            'title' => 'Erreur lors de la génération des codes de récupération',
            'message' => 'Erreur lors de la génération des codes de récupération. Veuillez réessayer.'
        ]
    ],
    'browser_sessions' => [
        'retrieved' => 'Sessions de navigateur récupérées avec succès',
        'error' => [
            'title' => 'Erreur lors de la récupération des sessions de navigateur',
            'message' => 'Erreur lors de la récupération des sessions de navigateur. Veuillez réessayer.'
        ],
        'logout' => [
            'success' => 'Autres sessions de navigateur déconnectées avec succès',
            'fail' => 'Erreur lors de la déconnexion des autres sessions de navigateur. Veuillez réessayer.',
            'error' => [
                'title' => 'Erreur lors de la déconnexion des autres sessions de navigateur',
                'message' => 'Erreur lors de la déconnexion des autres sessions de navigateur. Veuillez réessayer.'
            ]
        ],
        'session_error' => [
            'title' => 'Erreur de session',
            'message' => [
                'not_configured' => 'Le stockage de session n’est pas configuré pour cette opération.',
                'unavailable' => 'Sessions non disponibles. Veuillez vérifier votre configuration de session.',
                'not_found' => 'Session non trouvée. Veuillez vérifier votre identifiant de session.'
            ]
        ]
    ]
];
