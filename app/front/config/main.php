<?php
$params = array_merge(
    require(__DIR__ . '/../../base/config/params.php'),
    require(__DIR__ . '/../../base/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-front',
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
            'rules' => [
            ],
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
