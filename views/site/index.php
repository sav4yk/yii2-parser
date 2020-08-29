<?php

/* @var $this yii\web\View */

use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\widgets\ListView;

$this->title = 'Информация';
$cookies = Yii::$app->request->cookies;
?>
<div class="site-index">
    <div id="app">
        <div class="row row-equal">
            <div class="col-md-6 col-equal mt-2">
                <div class="row">
                    <div class="col-md-12"><div class="title">Радиационная обстановка</div>
                        <div class="text-center w-100">
                            <?= $cookies->getValue('Address', 'Россия, Севастополь') ?>
                            <?= $cookies->getValue('longlat', '33.526402 44.556972') ?>
                            <?= $cookies->getValue('radius', '200') ?> км
                        </div>
                        <br>
                        <line-chart :legend="true"  :data="charturl" :refresh="60" :download="true" legend="bottom" suffix=" мкР/ч" :library="chartOptions"/></div>
                </div>
                <div class="row">
                    <div class="col-md-12  pt-5">
                        <?php
                        Pjax::begin();
                        echo '<div class="title">Сейсмическая обстановка</div>';
                        echo GridView::widget([
                            'dataProvider' => $seismicDataProvider,
                            'summary' => '',
                            'columns' => [
                                [
                                    'label' => 'Сообщение',
                                    'attribute' => 'title',
                                    'format' => 'text'
                                ],
                                [
                                    'label' => 'Время в источнике',
                                    'attribute' => 'time_in_source',
                                    'format' => ['date', 'php:d.m.Y H:i:s']
                                ],
                                [
                                    'label' => 'Широта',
                                    'attribute' => 'lat',
                                    'format' => 'text'
                                ],
                                [
                                    'label' => 'Долгота',
                                    'attribute' => 'lon',
                                    'format' => 'text'
                                ],
                            ]
                        ]);
                        Pjax::end();
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-equal">
                <?php
                Pjax::begin();
                echo ListView::widget([
                    'dataProvider' => $listDataProvider,
                    'itemView' => '_list',
                    'summary' => '',
                    'pager' => [
                        'firstPageLabel' => 'Первая',
                        'lastPageLabel' => 'Последняя',
                        'nextPageLabel' => 'Следующая',
                        'prevPageLabel' => 'Предыдущая',
                        'maxButtonCount' => 5,
                    ],
                ]);
                Pjax::end();
                ?>
            </div>
        </div>
    </div>
</div>
