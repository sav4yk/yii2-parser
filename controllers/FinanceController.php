<?php

namespace app\controllers;

use app\models\Currency;
use Yii;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;

class FinanceController extends ActiveController
{
    public $modelClass = 'app\models\Currency';

    /**
     * This command get from the table 'currency' daily data with filter (valuteID,from,to).
     *
     * @return array
     */
    public function actions()
    {
        $actions = parent::actions();
        $request = Yii::$app->request;
        if ($request->get('valuteID') && $request->get('from') && $request->get('to')) {
            $actions['index']['prepareDataProvider'] = function ($action) {
                return new ActiveDataProvider([
                    'query' => $this->modelClass::find()->where(
                        [
                            'and',
                            ['between', 'date', $this->request->get('from'), $this->request->get('to')],
                            ['valuteID' => $this->request->get('valuteID')],
                        ]
                    )
                ]);
            };
        } elseif ($request->get('valuteID')) {
            $actions['index']['prepareDataProvider'] = function ($action) {
                return new ActiveDataProvider([
                    'query' => $this->modelClass::find()->where(
                            ['valuteID' => $this->request->get('valuteID')]
                    )
                ]);
            };
        }
        return $actions;
    }
}
