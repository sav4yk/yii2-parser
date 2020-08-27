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
        $longlat= preg_split('/\s+/', $cookies->getValue('longlat',  '33.526402 44.556972'));

        $lat =  $longlat[1];
        $lon = $longlat[0];
        $radius = $cookies->getValue('radius',  500)/111;
         $rad_points_near = RadiationPoints::find()->select(['station', '(ABS(lon-35.383822)+ABS(lat-45.030209)) as crd'])
             ->andwhere(['>', 'lat', $lat - $radius])
            ->andWhere(['<', 'lat', $lat + $radius])
            ->andWhere(['>', 'lon', $lon - $radius])
            ->andWhere(['<', 'lon', $lon + $radius])
             ->groupBy(['station'])
            ->orderBy('crd')
             ->asArray()
             ->limit(5)
             ->all();
         $in_array = [];
         foreach ($rad_points_near as $point):
             $in_array[] = $point['station'];
         endforeach;
        $rad_points = RadiationPoints::find()->select(['date', 'value','station'])
            ->WHERE(['in', 'station',$in_array])
            ->orderBy('date')
            ->all();
        foreach($rad_points as $t) {
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
