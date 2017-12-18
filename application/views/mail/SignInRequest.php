<?php
/**
 * @var Users $user
 */
?>
<html>
<body>

<p>Запрос на регистрацию аккаунта.</p>

<p>
    <?= $user->getAttributeLabel('login'); ?>: <?= CHtml::encode($user->login); ?><br/>
    <?= $user->getAttributeLabel('email'); ?>: <?= CHtml::encode($user->email); ?><br/>
    <?= $user->getAttributeLabel('phone'); ?>: <?= CHtml::encode($user->phone); ?><br/>
    <?= $user->getAttributeLabel('skype'); ?>: <?= CHtml::encode($user->skype); ?><br/>
    <? if(!empty($user->billing_details_type)) : ?>
        <?= $user->getAttributeLabel('billing_details_type'); ?>: <?= CHtml::encode($user->billing_details_type . ': ' . $user->billing_details_text); ?><br/>
    <? endif; ?>
</p>

<? foreach($user->getRelated('platforms', true) as $platform) : ?>
    <p>
        <?= $platform->getAttributeLabel('url'); ?>: <?= CHtml::encode($platform->url); ?><br/>
        <?= $platform->getAttributeLabel('server'); ?>: <?= CHtml::encode($platform->server); ?><br/>
        <?= $platform->getAttributeLabel('tagIds'); ?>: <?= CHtml::encode($platform->tag_names); ?><br/>
        <?= $platform->getAttributeLabel('currency'); ?>: <?= CHtml::encode(Arr::ad(PlatformsCpc::getCurrencies(), $platform->currency)); ?><br/>
    </p>
<? endforeach; ?>

<a href="http://tt.ttarget.ru/users?Users%5Bstatus%5D=0&Users%5Brole%5D=platform">Список пользователей, ожидающих потдверждения</a>.
</body>
</html>