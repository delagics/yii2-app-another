<?php

return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'db' => [
            'class' => env('DB_CLASS', 'yii\db\Connection'),
            'dsn' => env('DB_DSN', 'mysql:host=localhost;port=3306;dbname=app_db'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'tablePrefix' => env('DB_TABLE_PREFIX', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'enableSchemaCache' => env('DB_SCHEMA_CACHE', false),
            'schemaCacheDuration' => env('DB_SCHEMA_CACHE_DURATION', 3600),
            'enableQueryCache' => env('DB_QUERY_CACHE', true),
            'queryCacheDuration' => env('DB_QUERY_CACHE_DURATION', 3600),
        ],
        'user' => [
            'class' => 'yii\web\User',
            'identityClass' => 'dektrium\user\models\User',
            'loginUrl' => ['/user/security/login'],
            'enableAutoLogin' => true,
        ],
        'authManager' => [
            'class' => 'dektrium\rbac\components\DbManager',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath' => '@base/views/mail',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
    ],
    'modules' => [
        'user' => [
            'class' => 'dektrium\user\Module',
            'enableUnconfirmedLogin' => true,
            'confirmWithin' => 21600,
            'cost' => 12,
            'admins' => explode(',', env('APP_ADMINS', 'admin')),
        ],
        'rbac' => [
            'class' => 'dektrium\rbac\RbacWebModule',
        ],
    ],
];
