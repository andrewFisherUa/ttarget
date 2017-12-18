<?php /** @var Campaigns $campaign */ ?>
<html>
<body>
До отключения кампании "<?= $campaign->name; ?>" осталось <?= Yii::app()->params->CampaignNotifyClicksLeft; ?> переходов.
</body>
</html>