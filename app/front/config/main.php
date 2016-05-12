<?php
$params = array_merge(
    require(__DIR__ . '/../../base/config/params.php'),
    require(__DIR__ . '/../../base/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-front',
    'name' => env('FRONT_APP_NAME', 'Awesome application'),
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'front\controllers',
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
        'urlManager' => [
            'class' => 'base\components\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'scriptUrl' => '/admin/index.php',
            'languages' => explode(',', env('FRONT_APP_LANGUAGES', 'en')),
            'enableDefaultLanguageUrlCode' => true,
            'ignoreLanguageUrlPatterns' => [],
            'rules'=>[],
        ],
    ],
    'modules' => [
        'user' => [
            // following line will restrict access to admin controller from frontend application
            'as frontend' => 'front\filters\FrontFilter',
        ],
    ],
    'params' => $params,
];
