<?php
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
?>

<div class="col-md-12" style="display: block;">
    <div class="post-title">
        <a href="<?= Html::encode($model->link) ?>"><h3><?= Html::encode($model->title) ?></h3></a>
    </div>
    <div class="post-info">
        <span><?= Html::encode($model->dateText) ?> / by <a href="#" target="_blank"><?= Html::encode($model->channel) ?></a></span>
    </div>
    <?php foreach($model->categories as $cat): ?>
        <a href="?category=<?= Html::encode($cat->category) ?>" class="badge"><?= $cat->category ?></a>
    <?php endforeach; ?>
    <p><?= HtmlPurifier::process($model->description) ?></p>
    <a class="btn btn-warning"  href="<?= Html::encode($model->link) ?>" role="button">Подробнее</a>
</div>