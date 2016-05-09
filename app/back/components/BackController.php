<?php

namespace back\components;

use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use base\components\Controller;

/**
 * Class BackController is the base class of backend web controllers.
 * Not recommended to implement actions in this component.
 *
 * @package back\controllers
 */
class BackController extends Controller
{
    /**
     * Returns a list of behaviors that this component should behave as.
     *
     * @return array the behavior configurations.
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            /*'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],*/
        ];
    }
}