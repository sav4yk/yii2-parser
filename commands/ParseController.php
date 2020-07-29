<?php
/**
 * @link http://www.sav4yk.ru/
 */

namespace app\commands;

use Yii;
use DateTime;
use DatePeriod;
use DateInterval;
use app\jobs\Seismic;
use app\jobs\Radiation;
use app\jobs\Rssnews;
use app\jobs\CurrencyDaily;
use yii\console\Controller;
use yii\console\ExitCode;

class ParseController extends Controller
{
    /**
     * This command add commands to queue
     *
     * @return int Exit code
     */
    public function actionIndex()
    {
        if (Yii::$app->queue->push(new Seismic([
            'latitude' => '44.600246',
            'longitude' => '33.530273',
            'radius' => 5,
        ]))) { echo "Seismic ok\n"; } else { echo "Seismic err\n"; }
        if (Yii::$app->queue->push(new Radiation([
            'latitude' => '44.600246',
            'longitude' => '33.530273',
            'radius' => 5,
        ]))) { echo "Radiation ok\n"; } else { echo "Radiation err\n"; }
        if (Yii::$app->queue->push(new Rssnews([
            'url' => 'http://laravel.demiart.ru/feed/',
        ]))) { echo "News Laravel.Demiart ok\n"; } else { echo "News Laravel.Demiart err\n"; }
        if (Yii::$app->queue->push(new Rssnews([
            'url' => 'http://tproger.ru/feed/',
        ]))) { echo "News Tproger ok\n"; } else { echo "News Tproger err\n"; }


        if (Yii::$app->queue->push(new CurrencyDaily()))
        { echo "Daily CBR ok\n"; } else {
            echo "Daily CBR err\n"; }


        return ExitCode::OK;
    }
}
