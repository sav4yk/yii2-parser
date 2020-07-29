<?php

namespace app\controllers;

use app\models\Currency;
use Yii;
use yii\data\ActiveDataProvider;
use yii\rest\ActiveController;

class FinanceController extends ActiveController
{
    public $modelClass = 'app\models\Currency';
    public $from;
    public $to;
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

            $this->from = strtotime(str_replace('/', '-', $this->request->get('from')));
            $this->to   = strtotime(str_replace('/', '-', $this->request->get('to')));
            $actions['index']['prepareDataProvider'] = function ($action) {
                return new ActiveDataProvider([
                    'query' => $this->modelClass::find()->where(
                        [
                            'and',
                            ['between', 'date',$this->from ,$this->to],
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
