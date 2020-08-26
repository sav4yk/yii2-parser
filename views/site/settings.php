<?php

/* @var $this yii\web\View */

use kartik\checkbox\CheckboxX;
use kartik\range\RangeInput;
use yii\helpers\Html;

$this->title = 'Настройки';

?>
    <h1><?= Html::encode($this->title) ?></h1>
    <?php if (isset($info)): ?>
        <div class="alert alert-success" role="alert">
            Данные сохранены
        </div>
    <?php else: ?>
        <div class="alert alert-warning" role="alert">
            На нашем ресурсе огромное количество обновляемых данных.
            По умолчанию данные фильтруются для города Севастополь радиусом 200 км.<br>
            Вы можете указать свои данные, которые будут храниться у вас на компьютере.
        </div>
    <?php endif; ?>
    <?php

    use yii\jui\AutoComplete;
    use yii\web\JsExpression;
    $cookies = Yii::$app->request->cookies;
    ?>
    <?= Html::beginForm(['settings/update'], 'post') ?>
<div class="form-group row">
    <div class="col-sm-6">
        <div class="form-group row">
            <?= Html::label('Ваш населенный пункт', 'Address', ['class' => 'col-sm-4 col-form-label']) ?>
            <div class="col-sm-8">    <?= AutoComplete::widget([
                    'name' => 'Address',
                    'id' => 'Address',
                    'value' => $cookies->getValue('Address', ''),
                    'options' => ['class' => 'form-control'],
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
                <?= Html::input('text', 'longlat', $cookies->getValue('longlat', ''),['class' => 'form-control', 'id'=> 'longlat','readonly'=> true]) ?>
            </div>
        </div>
        <div class="form-group row">
            <?= Html::label('Радиус обзора', 'radius', ['class' => 'col-sm-4 col-form-label']) ?>
            <div class="col-sm-8"><?= RangeInput::widget([
                    'name' => 'radius',
                    'value' => $cookies->getValue('radius', 200),
                    'html5Container' => ['style' => 'width:70%'],
                    'html5Options' => ['min' => 50, 'max' => 1000],
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

