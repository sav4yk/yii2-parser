<?php

/* @var $this yii\web\View */

use yii\helpers\Html;

$this->title = 'Настройки';

?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>
    <p class="lead">Для отображения необходимых вам данных заполните поля ниже:</p>
    <?php

    use yii\jui\AutoComplete;
    use yii\web\JsExpression;
    ?>
    <?= Html::beginForm(['settings/update'], 'post') ?>
    <?= Html::label('Ваш населенный пункт', 'Address', ['class' => 'label username']) ?>
    <?= AutoComplete::widget([
        'name' => 'Address',
        'id' => 'Address',
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
    ?>
    <?= Html::label('Ваши координаты', 'longlat', ['class' => 'label username']) ?>
    <?= Html::input('text', 'longlat', '',['class' => 'fsd', 'id'=> 'longlat','readonly'=> true]) ?>
    <?= Html::endForm() ?>

</div>
