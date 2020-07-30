<?php

namespace app\controllers;

use app\models\Currency;
use app\models\Earthquakes;
use DateTime;
use yii\data\ActiveDataProvider;
use yii\web\Controller;

class SiteController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex($category = '')
    {
        if ($category!='') {
            $newsProvider = new ActiveDataProvider([
                'query' => \app\models\News::find()->joinWith('categories')->where(['category'=>$category])->
                orderBy('pubDate DESC'),
                'pagination' => [
                    'pageSize' => 3,
                ],
            ]);
        } else {
            $newsProvider = new ActiveDataProvider([
                'query' => \app\models\News::find()->orderBy('pubDate DESC'),
                'pagination' => [
                    'pageSize' => 3,
                ],
            ]);
        }

        $seismicProvider = new ActiveDataProvider([
            'query' => Earthquakes::find()->orderBy('time_in_source DESC')->limit(7),
            'pagination' => false
        ]);
        return $this->render('index',['listDataProvider' => $newsProvider,
            'seismicDataProvider' => $seismicProvider]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Displays сurrency page.
     *
     * @return string
     */
    public function actionFinance($date = '22.07.2020')
    {

        $financeProvider = new ActiveDataProvider([
            'query' => Currency::find()->Where(['date'=>strtotime( str_replace('.', '-', $date ) )])
                ->orderBy('сharCodes ASC'),
            'pagination' => false
        ]);
        return $this->render('finance',['financeDataProvider' => $financeProvider]);
    }
}
