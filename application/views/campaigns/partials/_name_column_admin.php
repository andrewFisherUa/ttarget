<? if($class == 'news') : ?>
    <a id="a-tizer-<?= $id ?>" class="campaign-story-title inline" onclick="return showEdit(<?= $id ?>)" href="<?=Yii::app()->createUrl('news/update',array('id' => $id))?>"><i class="icon-14 icon-cog-dark"></i></a>
    <a class="campaign-story-title inline" href="<?=Yii::app()->createUrl('news/view', array('id' => $id))?>" id="a-teasers-<?= $id ?>" onclick="return false;"><?= $name ?></a>
    <div class="campaign-story-description"><?= $description ?></div>
    <a class="campaign-story-outlink" href="<?= CHtml::encode($url); ?>" target="_blank">
        <?= $url_status != 0 && $url_status != 200 ? "<b>(".$url_status.")</b>" : ""; ?>
        <?= CHtml::encode($url_decoded) ?>
    </a>
    <a class="btn btnf title-right-btn" id="news-add" href="/" onclick="return addTeaser(<?= $id; ?>)"><i class="icon-16 icon-add"></i> Добавить тизер</a>
<? elseif($class == 'offers'): ?>
    <a id="a-tizer-<?= $id ?>" class="campaign-story-title inline" onclick="return showEdit(<?= $id ?>);" href="<?=Yii::app()->createUrl('offers/update',array('id' => $id))?>"><i class="icon-14 icon-cog-dark"></i></a>
	<a class="campaign-story-title inline" href="<?=Yii::app()->createUrl('offers/view', array('id' => $id))?>" id="a-teasers-<?= $id ?>"><?= $name ?></a>
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
    <a href="#" onclick="return updateTeaser(<?= $id; ?>,<?= $news_id; ?>)" class="teaser-link">
        <b><?= $title; ?></b>
        <? if(!empty($description)) : ?>
            <br/><small><?= $description; ?></small>
        <? endif; ?>
    </a>
<? endif; ?>