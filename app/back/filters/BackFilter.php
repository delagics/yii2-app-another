<?php

namespace back\filters;

use Yii;
use yii\base\ActionFilter;
use yii\web\ForbiddenHttpException;

/**
 * Class BackFilter is used to allow access only to admin and security controller in frontend when using Yii2-user.
 *
 * @package back\filters
 */
class BackFilter extends ActionFilter
{
    /**
     * @var array
     */
    public $controllers = ['profile', 'recovery', 'registration', 'settings'];

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
