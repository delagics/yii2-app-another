<?php

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use base\widgets\Alert;
use back\assets\AppAsset;

/**
 * @var $this    yii\web\View
 * @var $content string
 */

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">

    <?php NavBar::begin([
        'brandLabel' => Yii::$app->params['brandLabel'],
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-default navbar-fixed-top',
        ],
    ]);

    $menuItems = [
        ['label' => Yii::t('app', 'Home'), 'url' => ['/site/index']],
    ];

    if (Yii::$app->user->isGuest) {
        $menuItems[] = ['label' => Yii::t('app', 'Sign in'), 'url' => ['/user/security/login']];
    } else {
        $menuItems[] = '<li>'
            . Html::beginForm(['/user/security/logout'], 'post')
            . Html::submitButton(Yii::t('app', 'Sign out') . ' ' . Yii::$app->user->identity->username, ['class' => 'btn btn-link'])
            . Html::endForm()
            . '</li>';
    }

    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => $menuItems,
    ]);

    NavBar::end(); ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer role="contentinfo">
    <div class="container">
        <p class="text-muted pull-left">&copy; <?= date('Y') . ' ' . Yii::$app->name; ?></p>
        <p class="text-muted pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
