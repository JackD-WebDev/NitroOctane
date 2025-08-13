<?php

return [

    'failed' => 'Ang mga kredensyal na ito ay hindi tumutugma sa aming mga tala.',
    'password' => 'Mali ang ibinigay na password.',
    'throttle' => 'Masyadong maraming pagtatangkang mag-login. Pakisubukang muli sa loob ng :seconds segundo.',
    'login' => [
        'success' => 'Matagumpay na naka-login si :username',
        'already_logged_in' => 'Nakalog-in ka na bilang :username',
        'fail' => 'Nabigo ang pagtatangkang mag-login. Pakisuri ang iyong mga kredensyal at subukang muli.',
        'error' => [
            'title' => 'Error sa Pag-login',
            'message' => 'Nagkaroon ng error sa pag-login ni :username. Pakisubukang muli.',
        ],
        '2fa' => [
            'confirmed' => 'Matagumpay na nakumpirma ang 2FA code',
            'enabled' => 'Matagumpay na na-enable ang 2FA code',
            'disabled' => 'Matagumpay na na-disable ang 2FA code',
            'success' => 'Matagumpay na na-verify ang 2FA code',
            'fail' => 'Nabigo ang pag-verify ng 2FA code. Pakisubukang muli.',
        ],
    ],
    'logout' => [
        'success' => 'Matagumpay na naka-logout',
        'fail' => 'Nabigo ang pagtatangkang mag-logout. Pakisubukang muli.',
        'error' => [
            'title' => 'Error sa Pag-logout',
            'message' => 'Nagkaroon ng error sa pag-logout. Pakisubukang muli.',
        ],
    ],
    'confirm_password' => [
        'success' => 'Matagumpay na nakumpirma ang password',
        'fail' => 'Nabigo ang pagkumpirma ng password. Pakisubukang muli.',
        'error' => [
            'title' => 'Error sa Pagkumpirma ng Password',
            'message' => 'Nagkaroon ng error sa pagkumpirma ng password. Pakisubukang muli.',
        ],
    ],
    'register' => [
        'success' => 'Matagumpay na na-register si :username',
        'fail' => 'Nabigo ang pagtatangkang magrehistro. Pakisubukang muli.',
        'username' => [
            'invalid_characters' => 'Ang username ay naglalaman ng hindi pinapayagang mga karakter. Alphanumeric, dash, at underscore lamang ang pinapayagan.',
            'whitespace' => 'Ang username ay hindi pwedeng puro espasyo lamang.',
        ],
        'error' => [
            'title' => 'Error sa Pagrehistro',
            'message' => 'Nagkaroon ng error sa pagrehistro ni :username. Pakisubukang muli.',
        ],
        'verification' => [
            'sent' => 'Matagumpay na naipadala ang verification email',
        ],
    ],
    'password_reset' => [
        'success' => 'Matagumpay na na-reset ang password',
        'fail' => 'Nabigo ang pag-reset ng password. Pakisubukang muli.',
        'error' => [
            'title' => 'Error sa Pag-reset ng Password',
            'message' => 'Nagkaroon ng error sa pag-reset ng password. Pakisubukang muli.',
        ],
    ],
    'password_update' => [
        'success' => 'Matagumpay na na-update ang password',
        'fail' => 'Nabigo ang pag-update ng password. Pakisubukang muli.',
        'error' => [
            'title' => 'Error sa Pag-update ng Password',
            'message' => 'Nagkaroon ng error sa pag-update ng password. Pakisubukang muli.',
        ],
    ],
    'recovery_codes' => [
        'generated' => 'Matagumpay na nagawa ang mga recovery code',
        'fail' => 'Nabigo ang pag-generate ng mga recovery code. Pakisubukang muli.',
        'error' => [
            'title' => 'Error sa Pag-generate ng Recovery Codes',
            'message' => 'Nagkaroon ng error sa pag-generate ng recovery codes. Pakisubukang muli.',
        ],
    ],
    'browser_sessions' => [
        'retrieved' => 'Matagumpay na nakuha ang mga browser session',
        'error' => [
            'title' => 'Error sa Pagkuha ng Browser Sessions',
            'message' => 'Nagkaroon ng error sa pagkuha ng browser sessions. Pakisubukang muli.',
        ],
        'logout' => [
            'success' => 'Matagumpay na na-logout ang ibang browser sessions',
            'fail' => 'Nagkaroon ng error sa pag-logout ng ibang browser sessions. Pakisubukang muli.',
            'error' => [
                'title' => 'Error sa Pag-logout ng Ibang Browser Sessions',
                'message' => 'Nagkaroon ng error sa pag-logout ng ibang browser sessions. Pakisubukang muli.',
            ],
        ],
        'session_error' => [
            'title' => 'Error sa Session',
            'message' => [
                'not_configured' => 'Hindi naka-configure ang session storage para sa operasyong ito.',
                'unavailable' => 'Hindi available ang mga session. Pakisuri ang iyong session configuration.',
                'not_found' => 'Hindi natagpuan ang session. Pakisuri ang iyong session ID.',
            ],
        ],
    ],
];
