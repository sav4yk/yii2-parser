<?php

/* @var $this yii\web\View */

use kartik\date\DatePicker;
use yii\grid\GridView;
use yii\widgets\ActiveForm;

$this->title = 'Информация';

?>
<div class="site-index">
    <div id="app">
        <div class="row mb-2">
            <div class="col-md-3 col-equal ">
                <div class="text-right title">Котировки за:</div>
            </div>
            <div class="col-md-9 col-equal mt-2">
                <?php $form = ActiveForm::begin([
                    'id' => 'filter-form',
                    'method' => 'get',
                    'action' => ['site/finance'],
                    'options' => ['data-pjax' => true]
                ]); ?>
            <?= DatePicker::widget([
                'language' => 'ru',
                'name' => 'date',
                'value' => Yii::$app->request->get('date', date('d.m.Y',strtotime('-1day'))),
                'options' => ['placeholder' => 'Укажите дату ...',
                    'onChange' => new \yii\web\JsExpression('$("#filter-form").submit();'),],
                'pluginOptions' => [
                    'format' => 'dd.mm.yyyy',
                    'todayHighlight' => true
                ]
            ]); ?>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
        <div class="row row-equal">
            <div class="col-md-12 col-equal mt-2">
            <?php
                        echo GridView::widget([
                            'dataProvider' => $financeDataProvider,
                            'summary' => '',
                            'columns' => [
                                [
                                    'label' => 'Код',
                                    'attribute' => 'сharCode',
                                    'format' => 'text'
                                ],
                                [
                                    'label' => 'Валюта',
                                    'attribute' => 'name',
                                    'format' => 'text'
                                ],
                                [
                                    'label' => 'Значение',
                                    'attribute' => 'value',
                                    'format' => 'text'
                                ],
                            ]
                        ]);
                        ?>
            </div>
        </div>
    </div>
</div>

