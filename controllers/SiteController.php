<?php

namespace app\controllers;

use app\models\Currency;
use app\models\Earthquakes;
use app\models\News;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\Cookie;

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
        $cookies = Yii::$app->request->cookies;

        if ($category != '') {
            $newsProvider = new ActiveDataProvider([
                'query' => News::find()->joinWith('categories')->where(['category' => $category])->
                orderBy('pubDate DESC'),
                'pagination' => [
                    'pageSize' => 3,
                ],
            ]);
        } else {
            $newsProvider = new ActiveDataProvider([
                'query' => News::find()->orderBy('pubDate DESC'),
                'pagination' => [
                    'pageSize' => 3,
                ],
            ]);
        }
        $longlat = preg_split('/\s+/', $cookies->getValue('longlat', '33.526402 44.556972'));
        $lat = $longlat[1];
        $lon = $longlat[0];
        $radius = $cookies->getValue('radius', 500);

        $seismicProvider = new ActiveDataProvider([
            'query' => Earthquakes::find()->select([
                '*',
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
                ->orderBy([
                    //  'distance' => SORT_ASC,
                    'time_in_source' => SORT_DESC,
                ])
                ->limit(7),
            'pagination' => false
        ]);
        return $this->render('index', [
            'listDataProvider' => $newsProvider,
            'seismicDataProvider' => $seismicProvider
        ]);
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
                $cookies->add(new Cookie([
                    'name' => 'Address',
                    'value' => $request->post('Address'),
                ]));
                $data['Address'] = $request->post('Address');
            }
            if ($request->post('longlat')) {
                $cookies->add(new Cookie([
                    'name' => 'longlat',
                    'value' => $request->post('longlat'),
                ]));
                $data['longlat'] = $request->post('longlat');
            }
            if ($request->post('radius')) {
                $cookies->add(new Cookie([
                    'name' => 'radius',
                    'value' => $request->post('radius'),
                ]));
                $data['radius'] = $request->post('radius');
            }
            $data['info'] = $request->post('radius');
            return $this->render('settings', ['data' => $data]);
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
            'query' => Currency::find()->Where(['date' => strtotime(str_replace('.', '-', $date))])
                ->orderBy('сharCodes ASC'),
            'pagination' => false
        ]);
        return $this->render('finance', ['financeDataProvider' => $financeProvider]);
    }
}
