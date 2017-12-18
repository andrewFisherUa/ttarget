<?php
/**
 * @var Platforms $platform
 */
?>
<html>
<body>
Площадка
"<a href="http://tt.ttarget.ru/platforms/traffic/<?= $platform->id; ?>"><?= CHtml::encode($platform->server); ?></a>"
не запрашивает тизерные блоки.<br/>
Последний запрос: <?= Yii::app()->dateFormatter->formatDateTime(strtotime($platform->last_request_date)); ?><br/>
<? if($platform->user) : ?>
    Пользователь:
    <a href="http://tt.ttarget.ru/users/<?= $platform->user->id; ?>"><?= CHtml::encode($platform->user->login); ?></a>
    &lt;<a href="mailto:<?= CHtml::encode($platform->user->email); ?>"><?= CHtml::encode($platform->user->email); ?></a>&gt;<br/>
    <?= Users::model()->getAttributeLabel('contact_details'); ?>: <?= CHtml::encode($platform->user->contact_details); ?><br/>
<? endif; ?>

</body>
</html>