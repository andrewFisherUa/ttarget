<?php
/** @var CampaignsReports $report */
?>
<html>
<body>
<?= $report->type == CampaignsReports::TYPE_PERIOD ? 'Промежуточный' : 'Полный'; ?>
 отчет по кампании "<?= $report->campaign->name; ?>"
</body>
</html>