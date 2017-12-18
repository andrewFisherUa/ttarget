<? if($class == 'news') : ?>
    <a class="campaign-story-title inline" href="<?=Yii::app()->createUrl('news/view', array('id' => $id))?>" id="a-teasers-<?= $id ?>" onclick="return false;"><?= $name ?></a>
    <div class="campaign-story-description"><?= $description ?></div>
    <a class="campaign-story-outlink" href="<?= CHtml::encode($url) ?>" target="_blank"><?= CHtml::encode($url_decoded) ?></a>
<? else : ?>
    <div class="right">
        <? if($is_external) : ?>
            Внешняя сеть
        <? else : ?>
            Сегмент: <br/>
            <ul><? foreach($tag_names as $tag) : ?><li><?= $tag; ?></li><? endforeach; ?></ul>
        <? endif; ?>
    </div>
    <img alt="Bear" src="<?= Yii::app()->params->teaserImageBaseUrl . '/' . $picture; ?>" class="teaser-image">
    <?= $title; ?>
<? endif; ?>