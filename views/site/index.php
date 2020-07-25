<?php

/* @var $this yii\web\View */

$this->title = 'Информация';
?>
<div class="site-index">
    <div id="app">
        <div class="row">
            <div class="col-md-6"><line-chart :legend="true"  :data="charturl" :refresh="60" :download="true" legend="bottom" suffix=" мкР/ч" :library="chartOptions"/></div>
            <div class="col-md-6"></div>
        </div>
    </div>
</div>
