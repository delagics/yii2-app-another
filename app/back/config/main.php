<?php
$params = array_merge(
    require(__DIR__ . '/../../base/config/params.php'),
    require(__DIR__ . '/../../base/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-back',
    'name' => env('BACK_APP_NAME', 'Awesome application'),
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'back\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'request' => [
            'baseUrl'=>'/admin',
        ],
        'urlManager' => [
            'class' => 'base\components\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'scriptUrl' => '/admin/index.php',
            'languages' => explode(',', env('BACK_APP_LANGUAGES', 'en')),
            'enableDefaultLanguageUrlCode' => true,
            'ignoreLanguageUrlPatterns' => [],
            'rules'=>[],
        ],
        'i18n' => [
            'translations' => [
                /*'user*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@dektrium/user/messages',
                    'sourceLanguage' => 'en-US',
                ],*/
                'rbac*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@back/messages',
                    'sourceLanguage' => 'en-US',
                ],
            ],
        ],
    ],
    'modules' => [
        'user' => [
            // following line will restrict access to profile, recovery, registration and settings controllers from backend
            'as backend' => 'back\filters\BackFilter',
        ],
    ],
    'params' => $params,
];
