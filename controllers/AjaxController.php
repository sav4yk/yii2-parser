<?php

namespace app\controllers;

use app\models\RadiationPoints;
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
        $rad_points = RadiationPoints::find()->select(['date', 'value','station'])->orderBy('date')->all();
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
