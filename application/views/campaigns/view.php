<?
/**
 * @var Controller $this
 * @var Campaigns $campaign
 * @var CampaignCorrection $campaignCorrection
 */
Yii::app()->clientScript->registerCoreScript('jquery');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.css', 'screen');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-datepicker.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/datepicker.css', 'screen');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/jquery.form.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/form_ajax_binding.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/ajaxform/client_val_form.css', 'screen');

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/jstree/jstree.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/jstree/themes/default/style.min.css', 'screen');
?>
<div class="page-content">
    <div class="campaign-status-bar">
        <div class="campaign-status-info">
            <span class="campaign-status-time">с <?= DateHelper::getRusDate($campaign->date_start); ?>
                по <?= DateHelper::getRusDate($campaign->date_end); ?></span>
            <? if ($campaign->getGlobalIsActive()) { ?>
                <span class="campaign-status button-small-rounded">Активная</span>
            <? } else { ?>
                <span class="campaign-status button-small-rounded-inactive">Не активна</span>
            <? } ?>
        </div>
        <h4 class="campaign-status-title">Кампания</h4>
    </div>
</div>
<h1 class="page-title">
    <? if(Yii::app()->user->role === Users::ROLE_ADMIN) : ?>
        <a id="edit-campaign" href="<?= $this->createUrl('/campaigns/edit/' . $campaign->id); ?>"><i
                class="icon-cog-big"></i></a>
    <? endif; ?>
    <?= $campaign->name; ?>
</h1>
<div style="color: #0088CC; font-size:14px; font-weight:600;"><?= $campaign->cost_type == 'action' ? 'CPA' : ($campaign->cost_type == 'click' ? 'CPC' : 'RTB')?></div>
<div class="campaign-description"><?= $campaign->comment; ?></div>
<div class="clearfix">
    <? if(Yii::app()->user->role === Users::ROLE_ADMIN) : ?>
        <?if($campaign->cost_type == 'click'):?>
        <? $this->renderPartial('partials/_correction/main', array('campaignCorrection' => new CampaignCorrection($campaign))); ?>
        <?endif;?>
    <? endif; ?>
    <div class="campaign-visits-limit">
        <strong>Лимит <?= $campaign->cost_type == Campaigns::COST_TYPE_CLICK ? 'переходов' : 'действий'; ?>:</strong>
        <?= is_null($campaign->max_clicks) ? "---" : ($campaign->max_clicks . " (выполнено " . ($campaign->totalDone()) . ")"); ?>
        <span class="separator">|</span>
        <strong>Лимит суточных <?= $campaign->cost_type == Campaigns::COST_TYPE_CLICK ? 'переходов' : 'действий'; ?>:</strong>
        <?= ($campaign->limit_per_day == "0" ? "---" : ($campaign->day_clicks . " (выполнено " . ($campaign->totalDayDone()) . ")")); ?>

        <? if(Yii::app()->user->role === Users::ROLE_ADMIN) : ?>
        	<?if($campaign->cost_type == 'click'):?>
            <a class="campaign-reports" href="#" onclick="return updateReports()">Отчетный период</a>
            <?endif;?>
        <? endif; ?>
    </div>
</div>

<div class="campaign-information table-shadow">
    <div class="campaign-information-header">
        <div class="campaign-information-header-row1">
            <div class="navbar navbar-white">
                <div class="navbar-inner">
                <? $_filterUrlParams = array(
                	'period'   => $period,
                	'dateFrom' => $dateFrom,
                	'dateTo'   => $dateTo
                	);
				?>
                <? $this->renderPartial(
                    Yii::app()->user->role === Users::ROLE_ADMIN ? 'partials/_nav_menu_admin' : 'partials/_nav_menu_user',
                    array('campaign' => $campaign, 'period' => $period, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo, '_filterUrlParams' => $_filterUrlParams)
                )?>
                </div>
            </div>
        </div>
        <div class="campaign-information-header-row2 clearfix">

            <? $this->renderPartial($view, $_data_); ?>

        </div>
    </div>
</div>

<script type="text/javascript">
    var updateReports = function () {
        $.ajax({
            type: "POST",
            url: "<?= $this->createUrl('campaignsReports/returnForm', array('campaign_id' => $campaign->id)); ?>",
            data: {"YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>"},
            beforeSend: function () {
                $("#news-grid").addClass("ajax-sending");
            },
            complete: function () {
                $("#news-grid").removeClass("ajax-sending");
            },
            success: function (data) {
                $.fancybox(data,
                    $.extend({}, fancyDefaults, {
                        "width": 430,
                        "minWidth": 430,
                        "height": 550,
                        'onComplete': function() {
                            $(document).scrollTop(0);
                            $("#fancybox-wrap").css({'top':'20px', 'bottom':'auto'});
                         }
                    })
                );//fancybox
                //  console.log(data);
            } //success
        });//ajax
        return false;
    }

    var facyboxClose = function () {
        $.fancybox.close();
        return false;
    }

    $('#edit-campaign').bind('click', function (e) {
        editCampaign(undefined, <?= $campaign->id; ?>);
        return false;
    });

</script>

