<?php

require(__DIR__ . '/../app/vendor/autoload.php');
require(__DIR__ . '/../app/base/config/env.php');
require(__DIR__ . '/../app/vendor/yiisoft/yii2/Yii.php');
require(__DIR__ . '/../app/base/config/bootstrap.php');
require(__DIR__ . '/../app/front/config/bootstrap.php');

$config = yii\helpers\ArrayHelper::merge(
    require(__DIR__ . '/../app/base/config/main.php'),
    require(__DIR__ . '/../app/front/config/main.php')
);

$application = new yii\web\Application($config);
$application->run();
