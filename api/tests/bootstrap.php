<?php

putenv('APP_ENV=testing');
putenv('MAIL_MAILER=array');
putenv('QUEUE_CONNECTION=sync');
putenv('MAIL_HOST=127.0.0.1');
putenv('MAIL_PORT=1025');

require __DIR__ . '/../vendor/autoload.php';
