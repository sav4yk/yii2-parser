<?php

/* @var $this yii\web\View */

use kartik\checkbox\CheckboxX;
use kartik\range\RangeInput;
use yii\helpers\Html;

use yii\jui\AutoComplete;
use yii\web\JsExpression;
$this->title = 'Настройки';
$cookies = Yii::$app->request->cookies;
?>
    <h1><?= Html::encode($this->title) ?></h1>
    <?php if (isset($data['info'])): ?>
        <div class="alert alert-success" role="alert">
            Данные сохранены
        </div>
    <?php elseif (!$cookies->getValue('Address')): ?>
        <div class="alert alert-warning" role="alert">
            На нашем ресурсе огромное количество обновляемых данных.
            По умолчанию данные фильтруются для города Севастополь радиусом 500 км.<br>
            Вы можете указать свои данные, которые будут храниться у вас на компьютере.
        </div>
    <?php endif; ?>
    <?= Html::beginForm(['settings'], 'post') ?>
<div class="form-group row">
    <div class="col-sm-6">
        <div class="form-group row">
            <?= Html::label('Ваш населенный пункт', 'Address', ['class' => 'col-sm-4 col-form-label']) ?>
            <div class="col-sm-8">    <?= AutoComplete::widget([
                    'name' => 'Address',
                    'id' => 'Address',
                    'value' => $data['Address'] ?? $cookies->getValue('Address', 'Россия, Севастополь'),
                    'options' => ['class' => 'form-control', 'required' => true],
                    'clientOptions'=>[
                        'minLenght' => 3,
                        'autoFill'=>true,
                        'source' => new JsExpression('function(request, response) {
                    var search_query =request.term;
                    search_result = [];
                    $.ajax({
                        type: "GET",
                        url: "https://geocode-maps.yandex.ru/1.x/?apikey=16128ca7-c2fd-4dba-8e95-1424570d669b&geocode=" + search_query,
                        dataType: "xml",
                        success: function(xml){
                            $(xml).find("featureMember").each(function (i,e) {
                                search_result.push({
                                label: $(e).find("GeoObject").find("metaDataProperty").find("GeocoderMetaData").find("text").text(),
                                value:$(e).find("GeoObject").find("metaDataProperty").find("GeocoderMetaData").find("text").text(),
                                longlat:$(e).find("GeoObject").find("Point").find("pos").text()});
                            });
                            response (search_result);
                        }
                    });
                    }'),
                        'select' => new JsExpression("function( event, ui ) {
                         var longlat = ui.item.longlat;
                         $('#longlat').val(longlat);
                }")
                    ]
                ]);
                ?></div>
        </div>
        <div class="form-group row">
            <?= Html::label('Ваши координаты', 'longlat', ['class' => 'col-sm-4 col-form-label']) ?>
            <div class="col-sm-8">
                <?= Html::input('text', 'longlat', $data['longlat'] ?? $cookies->getValue('longlat', '33.526402 44.556972'),['class' => 'form-control', 'id'=> 'longlat','readonly'=> true]) ?>
            </div>
        </div>
        <div class="form-group row">
            <?= Html::label('Радиус обзора', 'radius', ['class' => 'col-sm-4 col-form-label']) ?>
            <div class="col-sm-8"><?= RangeInput::widget([
                    'name' => 'radius',
                    'value' => $data['radius'] ?? $cookies->getValue('radius',  200),
                    'html5Container' => ['style' => 'width:70%'],
                    'html5Options' => ['min' => 200, 'max' => 4000],
                    'addon' => ['append' => ['content' => 'км']]
                ]);?></div>
        </div>
        <div class="row">
            <div class="col-4"></div>
            <div class="col-8"></div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="form-group">
            <label class="cbx-label" for="kv-adv-118">
                <?= CheckboxX::widget([
                    'name' => 'kv-adv-118',
                    'initInputType' => CheckboxX::INPUT_CHECKBOX,
                    'options'=>['id'=>'kv-adv-118'],
                    'pluginOptions' => [
                        'enclosedLabel' => true,
                        'threeState'=>false
                    ]
                ]); ?>
                Default
            </label>
        </div>
    </div>
</div>
    <?= Html::submitButton('Сохранить', ['class' => 'btn btn-warning']) ?>
    <?= Html::endForm() ?>

