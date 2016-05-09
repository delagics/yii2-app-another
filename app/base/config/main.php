<?php
return [
    'name' => 'Awesome application',
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'urlManager' => [
            'class' => 'base\components\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'languages' => ['uk', 'ru', 'en'],
            'enableDefaultLanguageUrlCode' => true,
            'ignoreLanguageUrlPatterns' => [],
            'rules'=>[],
        ],
    ],
    'modules' => [
        'user' => [
            'class' => 'dektrium\user\Module',
            'enableUnconfirmedLogin' => true,
            'admins' => ['admin'],
        ],
        'rbac' => [
            'class' => 'dektrium\rbac\Module',
        ],
    ],
];
