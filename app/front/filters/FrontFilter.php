<?php

namespace front\filters;

use Yii;
use yii\base\ActionFilter;
use yii\web\ForbiddenHttpException;

/**
 * Class FrontFilter is used to restrict access to admin controller in frontend when using Yii2-user with Yii2
 * advanced template.
 *
 * @package front\filters
 */
class FrontFilter extends ActionFilter
{
    /**
     * @var array
     */
    public $controllers = ['admin'];

    /**
     * @param \yii\base\Action $action
     *
     * @return bool
     * @throws ForbiddenHttpException
     */
    public function beforeAction($action)
    {
        if (in_array($action->controller->id, $this->controllers)) {
            throw new ForbiddenHttpException(Yii::t('app', 'You are not permitted to perform the requested operation.'));
        }

        return true;
    }
}
