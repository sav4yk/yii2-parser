<?php

namespace app\controllers;

use app\models\Currency;
use app\models\Earthquakes;
use Yii;
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
     * Displays settings page.
     *
     * @return string
     */
    public function actionSettings()
    {
        $request = Yii::$app->request;
        if ($request->isPost) {
            $cookies = Yii::$app->response->cookies;
            if ($request->post('Address')) {
                $cookies->add(new \yii\web\Cookie([
                    'name' => 'Address',
                    'value' => $request->post('Address'),
                ]));
            }
            if ($request->post('longlat')) {
                $cookies->add(new \yii\web\Cookie([
                    'name' => 'longlat',
                    'value' => $request->post('longlat'),
                ]));
            }
            if ($request->post('radius')) {
                $cookies->add(new \yii\web\Cookie([
                    'name' => 'radius',
                    'value' => $request->post('radius'),
                ]));
            }
            return $this->render('settings',['info'=>123]);
        } else {
            return $this->render('settings');
        }
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
