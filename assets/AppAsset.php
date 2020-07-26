<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/site.css',
        'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.css'
    ];
    public $js = [
        'https://cdn.jsdelivr.net/npm/vue/dist/vue.js',
        'https://unpkg.com/chart.js@2.8.0/dist/Chart.bundle.js',
        'https://unpkg.com/vue-chartkick@0.5.1'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
    public $jsOptions = ['position' => \yii\web\View::POS_HEAD];
    public function init()
    {
        parent::init();
        if (\yii::$app->request->getPathInfo() == 'site/index' || \yii::$app->request->getPathInfo() == '') {
            $this->js[] = ['js/radchart.js', 'position' => \yii\web\View::POS_END]; // dynamic file added
        }
    }
}
