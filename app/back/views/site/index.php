<?php

/**
 * @var $this yii\web\View
 */

$this->title = Yii::$app->name;
$this->registerCssFile('https://fonts.googleapis.com/css?family=Lato:300');
$this->registerCss(<<<'CSS'
.demo {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}
.demo h1 {
    text-align: center;
    font-size: 120px;
    font-weight: 300;
    font-family: 'Lato';
    color: #ddd;
}
.demo p {
    text-align: center;
    font-size: 40px;
    font-weight: 300;
    font-family: 'Lato';
    color: #ddd;
}
CSS
);
?>

<div class="demo">
    <h1 class="demo-title">Yii <?= Yii::getVersion(); ?></h1>
    <p class="text-muted text-center">Application ID: <kbd><?= Yii::$app->id; ?></kbd></p>
</div>
