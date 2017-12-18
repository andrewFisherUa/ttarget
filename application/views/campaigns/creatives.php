<?
/**
 * @var Controller $this
 * @var Campaigns $campaign
 * @var CampaignCorrection $campaignCorrection
 */

Yii::app()->clientScript->registerCoreScript('jquery');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/jquery-ui.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.css', 'screen');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-datepicker.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/datepicker.css', 'screen');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/jquery.form.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/form_ajax_binding.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/ajaxform/client_val_form.css', 'screen');

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/jquery.iframe-transport.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/jquery.fileupload.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/jquery.fileupload.css', 'screen');

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/json2/json2.js');
Yii::app()->clientScript->registerScriptFile('http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-select.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/bootstrap-select.css', 'screen');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/jstree/jstree.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/jstree/themes/default/style.min.css', 'screen');

//jquery chosen plugin
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxChosen/chosen.jquery.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxChosen/chosen.ajaxaddition.jquery.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/ajaxChosen/chosen.css', 'screen');

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
<div style="color: #0088CC; font-size:14px; font-weight:600;">RTB</div>
<div class="campaign-description"><?= $campaign->comment; ?></div>
<div class="clearfix">
    <?/* if(Yii::app()->user->role === Users::ROLE_ADMIN) : ?>
        <? $this->renderPartial('partials/_correction/main', array('campaignCorrection' => new CampaignCorrection($campaign))); ?>
    <? endif; */?>
    <div class="campaign-visits-limit">
        <strong>Лимит <?= $campaign->cost_type == Campaigns::COST_TYPE_CLICK ? 'переходов' : 'действий'; ?>:</strong>
        <?= is_null($campaign->max_clicks) ? "---" : ($campaign->max_clicks . " (выполнено " . ($campaign->totalDone()) . ")"); ?>
        <span class="separator">|</span>
        <strong>Лимит суточных <?= $campaign->cost_type == Campaigns::COST_TYPE_CLICK ? 'переходов' : 'действий'; ?>:</strong>
        <?= ($campaign->limit_per_day == "0" ? "---" : ($campaign->day_clicks . " (выполнено " . ($campaign->totalDayDone()) . ")")); ?>

        <?/* if(Yii::app()->user->role === Users::ROLE_ADMIN) : ?>
            <a class="campaign-reports" href="#" onclick="return updateReports()">Отчетный период</a>
        <? endif; */?>
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
					
					<?=$this->renderPartial('/campaigns/partials/_nav_menu_admin', array('campaign' => $campaign, 'period' => $period, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo, '_filterUrlParams' => $_filterUrlParams))?>

                </div>
            </div>
        </div>
        <div class="campaign-information-header-row2 clearfix">
			<div>
			    <a href="#" id="creative-add"
			       class="btn title-right-btn" style=""><i class="icon-16 icon-add"></i> Создать креатив</a>
			
			    <h3 class="campaign-information-header-title">Креативы<!-- <input type="text" class="main-search">  -->
			    </h3>
			</div>
			<div class="campaign-information-table">
			    <?
                $sort = isset($_REQUEST['sort']) ? $_REQUEST['sort'] : 'id.desc';
			    $this->widget('zii.widgets.grid.CGridView', array(
			        'id' => 'creative-grid',
			        'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering'),
			        'dataProvider' => $report,
			        'template' => '{items}{pager}',
			        'rowCssClassExpression' => '"creatives ".(!$data["is_active"] ? " inactive" : "")',
			        'rowHtmlOptionsExpression' => 'array("data-creatives-id" => $data[\'id\'])',
//			        'afterAjaxUpdate' => 'activeTable',
			        'columns' => array(
			            array(
			                'name' => 'id',
			                'header' => 'ID<i class="icon-sort' . ($sort == 'id' ? ' icon-sort-down' : ($sort == 'id.desc' ? ' icon-sort-up' : '')) . '"></i>',
			            ),
			            array(
			                'name' => 'name',
			                'header' => 'Описание<i class="icon-sort' . ($sort == 'name' ? ' icon-sort-down' : ($sort == 'name.desc' ? ' icon-sort-up' : '')) . '"></i>',
			                'type' => 'raw',
			            	'value' => '"<a href=\"'.$this->createUrl('campaignsCreatives/returnForm/').'/".$data["id"]."\" class=\"creative-edit\" data-id=".$data["id"]." onclick=\"return false;\"><i class=\"icon-14 icon-cog-dark\"></i></a>".$data["name"]'
			            ),
		        		array(
		        			'name' => 'size',
		        			'header' => 'Размер',
		        			'type' => 'raw',
		        		),
			            array(
			                'name' => 'is_active',
			                'header' => 'Статус<i class="icon-sort' . ($sort == 'is_active' ? ' icon-sort-down' : ($sort == 'is_active.desc' ? ' icon-sort-up' : '')) . '"></i>',
			            	'value' => '$data["is_active"] ? "Активен" : "Неактивен"'
			            ),
			            array(
			                'name' => 'shows',
			                'header' => 'Показы<i class="icon-sort' . ($sort == 'shows' ? ' icon-sort-down' : ($sort == 'shows.desc' ? ' icon-sort-up' : '')) . '"></i>',
			            ),
			            array(
			                'name' => 'clicks',
			                'header' => 'Переходы <i class="icon-sort' . ($sort == 'clicks' ? ' icon-sort-down' : ($sort == 'clicks.desc' ? ' icon-sort-up' : '')) . '"></i>',
			    		),
			            array(
			                'name' => 'ctr',
			                'header' => 'CTR',
			    		),
                        array(
                            'name' => 'status',
                            'header' => 'Участие в аукционе <i class="icon-sort' . ($sort == 'status' ? ' icon-sort-down' : ($sort == 'status.desc' ? ' icon-sort-up' : '')) . '"></i>',
                            'value' => 'CampaignsCreatives::model()->getAuctionStatus($data["id"], $data["status"])',
                            'type' => 'raw',
                        ),
		        		array(
		        			'name' => 'rtb_id',
		        			'header' => 'DSP ID',
		        		),
			        	array(
			        		'name' => 'is_winner',
			        		'header' => 'Побед.',
			        		'value' => '$data["is_winner"] ? "Да" : "Нет"'
			        	),
                        array(
                            'name' => 'cost',
                            'header' => 'Bid<i class="icon-sort' . ($sort == 'cost' ? ' icon-sort-down' : ($sort == 'cost.desc' ? ' icon-sort-up' : '')) . '"></i>',
                        )
			        ),
//			        'enableSorting' => true,
			    ));
			    ?>
			</div>
			
			<script type="text/javascript">
//			var activeTable = function () {
//			    $('.teaser').hide();
//			    $('tr.offers td:not(:nth-child(4))').on('click', function () {
//			        $('.teaser[data-offers-id=' + $(this).parent().data('offers-id') + ']').toggle();
//			    });
//
//			    $('.offers:odd').addClass('odd')
//			    $('.offers:even').addClass('even')
//			}
//
//			activeTable();
			</script>
        </div>
    </div>
</div>

<script type="text/javascript">
$(function(){

	

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
                        "minWidth": 430
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

    $('#creative-add').click(function(){
    	$.ajax({
            type: "POST",
            url: "<?= $this->createUrl('/campaignsCreatives/returnForm', array('campaign_id' => $campaign->id)); ?>",
            data: {"YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>"},
            success: function (data) {
                $.fancybox(data,
                    $.extend({}, fancyDefaults, {
                        "width": 430,
                        "minWidth": 430,
                        'onComplete': function() {
                            $(document).scrollTop(0);
                            $("#fancybox-wrap").css({'top':'20px', 'bottom':'auto'});
                         }
                    })
                );
            }
        });
        return false;
    });
    $('.creative-edit').live('click',function(){
    	$.ajax({
            type: "POST",
            url: $(this).attr('href'),
            data: {"YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>"},
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
                );
            }
        });
        return false;
    });

    $('.creative-rejection').live('click',function(){
        try {
            event.stopPropagation();
        } catch (e) {
            // TODO: handle exception
        }

        id = $(this).attr('creative_id');
        console.log(id);

        $.ajax({
            type: "POST",
            url: "<?= Yii::app()->request->baseUrl;?>/campaignsCreatives/returnFormRejection?n=" + id,
            data: {"YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>"},
            beforeSend: function () {
                $("#creative-grid").addClass("ajax-sending");
            },
            complete: function () {
                $("#creative-grid").removeClass("ajax-sending");
            },
            success: function (data) {
                $.fancybox(data,
                    $.extend({}, fancyDefaults, {
                        "width": 543,
                        "minWidth": 543,
                        "afterClose": function () {
                        } //onclosed functi
                    })
                );//fancybox
                //  console.log(data);
            } //success
        });//ajax

        return false;
    });
});
</script>

