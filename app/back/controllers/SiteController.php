<?php

namespace back\controllers;

use Yii;
use back\components\BackController;

/**
 * Class SiteController
 *
 * @package back\controllers
 */
class SiteController extends BackController
{
    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionIndex()
    {
        return $this->render('index');
    }
}
