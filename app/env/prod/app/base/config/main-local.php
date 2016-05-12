<?php
return [
    'components' => [
        'db' => [
            'class' => env('DB_CLASS', 'yii\db\Connection'),
            'dsn' => env('DB_DSN', 'mysql:host=localhost;port=3306;dbname=app_db'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@base/views/mail',
        ],
    ],
];
