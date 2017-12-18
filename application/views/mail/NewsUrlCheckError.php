<?php
/**
 * @var News $news
 * @var ConnectionRequest $request
 */
?>
<html>
<body>
Новость
"<a href="http://tt.ttarget.ru/campaigns/<?= $news->campaign_id; ?>#a-teasers-<?= $news->id; ?>"><?= CHtml::encode($news->name); ?></a>"
кампании "<?= CHtml::encode($news->campaign->name); ?>" не прошла проверку URL.<br/>
URL: <a href="<?= $request->url; ?>"><?= CHtml::encode(IDN::decodeUrl($request->url)); ?></a><br/>
<? if($request->reply->result == 0) : ?>
    Код ответа: <?= $request->reply->info['http_code']; ?>
<? else : ?>
    Код ошибки соединения: <?= $request->reply->result; ?>
<? endif; ?>

</body>
</html>