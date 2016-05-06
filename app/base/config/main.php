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
            // List all supported languages here
            // Make sure, you include your app's default language.
            'languages' => ['uk' => 'uk-UA', 'en' => 'en-US', 'ru' => 'ru-RU'],
            'enableDefaultLanguageUrlCode' => true, // show default language in URL
            'enableLanguagePersistence' => true, // remember selected language
            'enableLanguageDetection' => true, // language detection
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
