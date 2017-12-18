<?php /** @var Campaigns $campaign */ ?>
<html>
<body>
До отключения кампании "<?= $campaign->name; ?>" остался <?= Yii::app()->params->CampaignNotifyDaysLeft; ?> день.
</body>
</html>