<?php

namespace base\widgets;

use Yii;
use yii\base\Widget;
use yii\bootstrap\Dropdown;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * LangSelect widget.
 *
 * @package base\widgets
 */
class LangSelect extends Widget
{
    /**
     * @var string current language label.
     */
    public $label;
    /**
     * @var string current language URL.
     */
    public $url;
    /**
     * @var array list of other available languages, without current.
     */
    public $items;
    /**
     * @var array list of language labels
     */
    public $labels;
    /**
     * @var boolean whether the labels should be HTML-encoded.
     */
    public $encodeLabels = false;
    /**
     * @var boolean|false only show this widget if we're not on the error page
     */
    public $disableOnError = false;
    /**
     * @var boolean|true whether to redirect to home page on language switch
     */
    public $redirectHomeOnSwitch = false;
    /**
     * @var string language URL parameter name
     */
    public $langParam = 'language';
    /**
     * @var boolean temporary property that stores check if current route is error action
     */
    protected $isError;
    /**
     * @var string HTML template to wrap widget result.
     * Available tokens are: {label}, {url} and {items}.
     */
    public $template = '<li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            {label} <b class="caret"></b>
        </a>{items}
    </li>';

    /**
     * Executes the widget.
     *
     * @return string the result of widget execution to be outputted.
     */
    public function run()
    {
        $route = Yii::$app->controller->route;
        $appLang = Yii::$app->language;
        $params = Yii::$app->request->get();
        $this->isError = $route === Yii::$app->errorHandler->errorAction;

        if ($this->isError && $this->disableOnError) {
            return false;
        } else {
            array_unshift($params, '/' . $route);
            foreach (Yii::$app->urlManager->languages as $lang) {
                $isWildcard = substr($lang, -2) === '-*';
                $lang = $isWildcard ? substr($lang, 0, 2) : $lang;
                $params[$this->langParam] = $lang;
                $url = ($this->redirectHomeOnSwitch) ? ['/', $this->langParam => $lang] : $params;
                if ($lang === $appLang || $isWildcard && substr($appLang, 0, 2) === $lang) {
                    $this->label = $this->getLabel($lang);
                    $this->url = $url;
                    continue;
                }
                $this->items[] = [
                    'label' => $this->getLabel($lang),
                    'url' => $url,
                    'active' => false,
                ];
            }
            return strtr($this->template, [
                '{label}' => $this->encodeLabels ? Html::encode($this->label) : $this->label,
                '{url}' => Yii::$app->urlManager->createUrl($this->url),
                '{items}' => $this->items ? Dropdown::widget([
                    'items' => $this->items,
                    'encodeLabels' => $this->encodeLabels
                ]) : '',
            ]);
        }
    }

    /**
     * Get language label.
     *
     * @param $code string language code
     * @uses \base\widgets\LangSelect::$labels, \yii\base\Module::$params
     * @return string
     */
    public function getLabel($code)
    {
        if ($this->labels === null) {
            $this->labels = ArrayHelper::getValue(Yii::$app->params, 'languages', []);
        }
        return isset($this->labels[$code]) ? $this->labels[$code] : $code;
    }
}
