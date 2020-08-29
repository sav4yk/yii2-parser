<?php

namespace app\controllers;

use app\models\RadiationPoints;
use Yii;
use yii\web\Controller;

class AjaxController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],

        ];
    }

    /**
     * Return json radiation data to chart on main page.
     *
     * @return string
     */
    public function actionRadiation()
    {
        $cookies = Yii::$app->request->cookies;
        $longlat = preg_split('/\s+/', $cookies->getValue('longlat', '33.526402 44.556972'));

        $lat = $longlat[1];
        $lon = $longlat[0];
        $radius = $cookies->getValue('radius', 500);
        $rad_points_near = RadiationPoints::find()->select([
            'station',
            '(
                6371 * acos (
                    cos ( radians(' . $lat . ') )
                    * cos( radians( lat ) )
                    * cos( radians( lon ) - radians(' . $lon . ') )
                    + sin ( radians(' . $lat . ') )
                    * sin( radians( lat ) )
                )
            ) AS distance'
        ])
            ->having(['<', 'distance', $radius])
            ->groupBy(['station'])
            ->orderBy([
                'distance' => SORT_ASC,
            ])
            ->asArray()
            ->limit(5)
            ->all();
        $in_array = [];
        foreach ($rad_points_near as $point):
            $in_array[] = $point['station'];
        endforeach;
        $rad_points = RadiationPoints::find()->select(['date', 'value', 'station'])
            ->WHERE(['in', 'station', $in_array])
            ->orderBy('date')
            ->all();
        foreach ($rad_points as $t) {
            $arr[$t->station][$t->date] = $t->value;
        }
        $arrkey = array_keys($arr);
        foreach ($arrkey as $key) {
            $ob['name'] = $key;
            $ob['data'] = $arr[$key];
            $arrr[] = $ob;
        }
        return \GuzzleHttp\json_encode($arrr);
    }

}
