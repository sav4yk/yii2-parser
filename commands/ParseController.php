<?php
/**
 * @link http://www.sav4yk.ru/
 */

namespace app\commands;

use Yii;
use app\jobs\Seismic;
use app\jobs\Radiation;
use app\jobs\Rssnews;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command add commands to queue
 *
 * @author Sav4yk <mail@sav4yk.ru>
 */
class ParseController extends Controller
{
    /**
     * @return int Exit code
     */
    public function actionIndex()
    {
        if (Yii::$app->queue->push(new Seismic([
            'latitude' => '44.600246',
            'longitude' => '33.530273',
            'radius' => 5,
        ]))) {
            echo "Seismic ok\n";
        } else {
            echo "Seismic err\n";
        }
        if (Yii::$app->queue->push(new Radiation([
            'latitude' => '44.600246',
            'longitude' => '33.530273',
            'radius' => 5,
        ]))) {
            echo "Radiation ok\n";
        } else {
            echo "Radiation err\n";
        }
        if (Yii::$app->queue->push(new Rssnews([
            'url' => 'https://laravel.demiart.ru/feed/'
        ]))) {
            echo "News Laravel.Demiart ok\n";
        } else {
            echo "News Laravel.Demiart err\n";
        }
        if (Yii::$app->queue->push(new Rssnews([
            'url' => 'https://tproger.ru/feed/'
        ]))) {
            echo "News Tproger ok\n";
        } else {
            echo "News Tproger err\n";
        }
        return ExitCode::OK;
    }
}
