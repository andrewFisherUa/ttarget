<? /**
 * @var CampaignsController $this
 */ ?>
<ul class="nav">
	<?if($campaign->cost_type == 'action'):/*cpa*/?>
    	<li class="<?= $this->action->id == 'view' ? 'active' : ''; ?>"><a href="<?= $this->createUrl('view', array_merge(array('id' => $campaign->id), $_filterUrlParams)); ?>">Новости кампании</a></li>
        <li class="<?= $this->action->id == 'offers' ? 'active' : ''; ?>"><a href="<?= $this->createUrl('offers', array_merge(array('id' => $campaign->id), $_filterUrlParams)); ?>">Предложения</a></li>
    	<li class="<?= $this->action->id == 'transactions' ? 'active' : ''; ?>"><a href="<?= $this->createUrl('transactions', array_merge(array('id' => $campaign->id), $_filterUrlParams)); ?>">Транзакции</a></li>
    	<li class="<?= $this->action->id == 'statistics' ? 'active' : ''; ?>"><a href="<?= $this->createUrl('statistics', array_merge(array('id' => $campaign->id), $_filterUrlParams)); ?>">Статистика</a></li>
    <?elseif($campaign->cost_type=='click'):/*cpc*/?>
    	<li class="<?= $this->action->id == 'view' ? 'active' : ''; ?>"><a href="<?= $this->createUrl('view', array_merge(array('id' => $campaign->id), $_filterUrlParams)); ?>">Новости кампании</a></li>
        <li class="<?= $this->action->id == 'statistics' ? 'active' : ''; ?>"><a href="<?= $this->createUrl('statistics', array_merge(array('id' => $campaign->id), $_filterUrlParams)); ?>">Статистика</a></li>
    <?else:/*rtb*/?>
    	<li class="<?= ($this->id == 'campaignsCreatives' && $this->action->id == 'index') ? 'active' : ''; ?>"><a href="<?= $this->createUrl('/campaigns/creatives/', array_merge(array('id' => $campaign->id), $_filterUrlParams)); ?>">Креатив</a></li>
		<li class="<?= $this->action->id == 'creativeStatistics' ? 'active' : ''; ?>"><a href="<?= $this->createUrl('/campaigns/creativeStatistics', array_merge(array('id' => $campaign->id), $_filterUrlParams)); ?>">Статистика</a></li>
    <?endif;?>
</ul>
<? $this->renderPartial('/partials/period', array('period' => $period, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo)); ?>

<? if(!in_array($this->action->id, array('statistics', 'offers'))): ?>
    <a class="campaign-generatereport" href="#" id="reports">Сформировать отчёт</a>
<? endif; ?>
