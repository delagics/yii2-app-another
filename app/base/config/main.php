<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
    ],
    'modules' => [
        'user' => [
            'class' => 'dektrium\user\Module',
            'enableUnconfirmedLogin' => true,
            'admins' => explode(',', env('APP_ADMINS', 'admin')),
        ],
        'rbac' => [
            'class' => 'dektrium\rbac\Module',
        ],
    ],
];
