<? /**
 * @var BillingIncome $modelI
 */ ?>
<div class="section-billing" id="bill-out">
	<div class="title-with-button">
        <!--<a href="<?= $this->createUrl('/billing/create'); ?>" class="btn title-right-btn"><i class="icon-16 icon-add"></i> Добавить исходящий счёт</a>-->
        <a href="/" id="bill-add" class="btn title-right-btn"><i class="icon-16 icon-add"></i> Добавить исходящий счёт</a>
        <h1 class="page-title">
                Исходящие счета <input id="main-search-bill-out" type="text" value="" data-bill="out" class="main-search">
        </h1>
    </div>

<?
	$sort = isset($_GET['BillingOutgoing_sort'])?$_GET['BillingOutgoing_sort']:'';
	$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'billing-outgoing-grid',
	'htmlOptions' => array('class' => 'table table-bordered table-striped table-shadow table-billing'),
	'dataProvider'=>$model->search(),
	'template' => '{items}{pager}',
	'columns'=>array(
			array(
				'name' => 'number',
				'type'=>'raw',
				'value' => '"<a onclick=\'return editBilling(".$data->id.")\' href=\'".Yii::app()->createUrl("/billing/update/".$data->id)."\'>" . $data->number . "</a>"',
				//'value' => '"И" . $data->id',
				'header' => '№ Счёта<i class="icon-sort'.($sort == 'number'?' icon-sort-down':($sort == 'number.desc'?' icon-sort-up':'')).'"></i>',
			),
			array(
				'name' => 'client_id',
				'value' => '$data->login',
				'header' => 'Клиент<i class="icon-sort'.($sort == 'client_id'?' icon-sort-down':($sort == 'client_id.desc'?' icon-sort-up':'')).'"></i>',
			),
			array(
				'name' => 'issuing_date',
				'name' => 'issuing_date',
				'value' => 'substr($data->issuing_date, 0, 10)',
				'header' => 'Дата выставления<i class="icon-sort'.($sort == 'issuing_date'?' icon-sort-down':($sort == 'issuing_date.desc'?' icon-sort-up':'')).'"></i>',
			),
			array(
				'name' => 'sum',
				'header' => 'Сумма, руб.<i class="icon-sort'.($sort == 'sum'?' icon-sort-down':($sort == 'sum.desc'?' icon-sort-up':'')).'"></i>',
			),
			array(
				'name' => 'is_paid',
				'type'=>'raw',
				'value'=>'$data->is_paid == 1 ? "<i class=\'icon-16 icon-billing-status-good\'></i> Оплачен" : "<i class=\'icon-16 icon-billing-status-bad\'></i> Не оплачен"',
				'header' => 'Статус<i class="icon-sort'.($sort == 'is_paid'?' icon-sort-down':($sort == 'is_paid.desc'?' icon-sort-up':'')).'"></i>',
			),
			array(
				'name' => 'paid_date',
				'value' => '($data->is_paid == 1 && strlen($data->paid_date))?substr($data->issuing_date, 0, 10):"--"',
				'header' => 'Дата оплаты<i class="icon-sort'.($sort == 'paid_date'?' icon-sort-down':($sort == 'paid_date.desc'?' icon-sort-up':'')).'"></i>',
			)
			,array(
				'name' => 'comment',
				'header' => 'Комментарий<i class="icon-sort'.($sort == 'comment'?' icon-sort-down':($sort == 'comment.desc'?' icon-sort-up':'')).'"></i>',
			),
			
		)
)); ?>

<div class="billing-summary">
    <span class="billing-debt">Неоплачено: <?= floatval($sum['outgoing'][0]);?> руб.</span> |
    <span class="billing-paid">Оплачено: <?= floatval($sum['outgoing'][1]);?> руб.</span> |
    <span class="billing-total">Всего: <?= floatval($sum['outgoing'][1])+floatval($sum['outgoing'][0]);?> руб.</span>
</div>


</div>

<div class="section-billing" id="bill-in">

	<div>
        <a href="/" id="bill-i-add" class="btn title-right-btn"><i class="icon-16 icon-add"></i> Добавить входящий счёт</a>
        <a href="#" id="bill-i-report" class="platforms-generatereport">Сформировать отчет</a>
        <h1 class="page-title">
                Входящие счета <input id="main-search-bill-in" type="text" value="" data-bill="in" class="main-search">
        </h1>
    </div>
    <div class="title-with-button">
        <? $this->renderPartial('/partials/period', array('period' => $period, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo)); ?>
        <?= CHtml::dropDownList(
            'IncomeFilter[is_paid]',
            isset($_REQUEST['IncomeFilter']['is_paid']) ? $_REQUEST['IncomeFilter']['is_paid'] : '',
            array('1' => 'Оплачен', '0' => 'Не оплачен'),
            array('empty' => 'Все статусы', 'class' => 'input150 tableFilterSelect')
        ); ?>
        <?= CHtml::dropDownList(
            'IncomeFilter[source_type]',
            isset($_REQUEST['IncomeFilter']['source_type']) ? $_REQUEST['IncomeFilter']['source_type'] : '',
            BillingIncome::getAvailableSourceTypes(),
            array(
                'empty' => 'Все источники',
                'class' => 'tableFilterSelect',
                'onchange' => '$("#IncomeFilter_source_id").val("");'
            )
        ); ?>
        <? if(isset($_REQUEST['IncomeFilter']['source_type'])) {
            if($_REQUEST['IncomeFilter']['source_type'] == BillingIncome::SOURCE_TYPE_PLATFORM){
                echo CHtml::dropDownList(
                    'IncomeFilter[source_id]',
                    isset($_REQUEST['IncomeFilter']['source_id']) ? $_REQUEST['IncomeFilter']['source_id'] : '',
                    CHtml::listData(Platforms::model()->printable()->findAll(), 'id', 'server'),
                    array('empty' => 'Все площадки', 'class' => 'selectpicker tableFilterSelect',  'data-live-search' => 'true')
                );
            } elseif($_REQUEST['IncomeFilter']['source_type'] == BillingIncome::SOURCE_TYPE_WEBMASTER) {
                echo CHtml::dropDownList(
                    'IncomeFilter[source_id]',
                    isset($_REQUEST['IncomeFilter']['source_id']) ? $_REQUEST['IncomeFilter']['source_id'] : '',
                    CHtml::listData(Users::model()->webmaster()->printable()->findAll(), 'id', 'loginEmail'),
                    array('empty' => 'Все вебмастера', 'class' => 'selectpicker tableFilterSelect', 'data-live-search' => 'true')
                );
            }
        } ?>
    </div>

<?
	$sort = isset($_GET['BillingIncome_sort'])?$_GET['BillingIncome_sort']:'';
	$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'billing-incoming-grid',
	'htmlOptions' => array('class' => 'table table-bordered table-striped table-shadow table-billing'),
	'dataProvider'=>$modelI->search(),
	'template' => '{items}{pager}',
	'columns'=>array(
			array(
				'name' => 'number',
				'type'=>'raw',
				'value' => '"<a onclick=\'return editBillingI(".$data->id.")\' href=\'".Yii::app()->createUrl("/billingIncome/update/".$data->id)."\'>" . $data->number . "</a>"',
				//'value' => '"В" . $data->id',
				'header' => '№ Счёта<i class="icon-sort'.($sort == 'number'?' icon-sort-down':($sort == 'number.desc'?' icon-sort-up':'')).'"></i>',
			),
			array(
				'name' => 'source_name',
				'value' => '$data->source_name',
				'header' => 'Источник<i class="icon-sort'.($sort == 'platform_id'?' icon-sort-down':($sort == 'platform_id.desc'?' icon-sort-up':'')).'"></i>',
			),
			array(
				'name' => 'issuing_date',
				'name' => 'issuing_date',
				'value' => 'substr($data->issuing_date, 0, 10)',
				'header' => 'Дата выставления<i class="icon-sort'.($sort == 'issuing_date'?' icon-sort-down':($sort == 'issuing_date.desc'?' icon-sort-up':'')).'"></i>',
			),
			array(
				'name' => 'sum',
				'header' => 'Сумма, руб.<i class="icon-sort'.($sort == 'sum'?' icon-sort-down':($sort == 'sum.desc'?' icon-sort-up':'')).'"></i>',
			),
			array(
				'name' => 'is_paid',
				'type'=>'raw',
				'value'=>'$data->is_paid == 1 ? "<i class=\'icon-16 icon-billing-status-good\'></i> Оплачен" : "<i class=\'icon-16 icon-billing-status-bad\'></i> Не оплачен"',
				'header' => 'Статус<i class="icon-sort'.($sort == 'is_paid'?' icon-sort-down':($sort == 'is_paid.desc'?' icon-sort-up':'')).'"></i>',
			),
			array(
				'name' => 'paid_date',
				'value' => '($data->is_paid == 1 && strlen($data->paid_date))?substr($data->issuing_date, 0, 10):"--"',
				'header' => 'Дата оплаты<i class="icon-sort'.($sort == 'paid_date'?' icon-sort-down':($sort == 'paid_date.desc'?' icon-sort-up':'')).'"></i>',
			)
			,array(
				'name' => 'comment',
				'header' => 'Комментарий<i class="icon-sort'.($sort == 'comment'?' icon-sort-down':($sort == 'comment.desc'?' icon-sort-up':'')).'"></i>',
			),
			
		)
)); ?>

<div class="billing-summary">
    <span class="billing-debt">Неоплачено: <?= floatval($sum['incoming'][0]);?> руб.</span> |
    <span class="billing-paid">Оплачено: <?= floatval($sum['incoming'][1]);?> руб.</span> |
    <span class="billing-total">Всего: <?= floatval($sum['incoming'][1])+floatval($sum['incoming'][0]);?> руб.</span>
</div>


</div>

<div id="i-report-template" style="display:none">
    <div id="modal-campaign-getreport" class="modal show">
        <div class="modal-header">
            <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>
            <h3>Формирование отчёта</h3>
        </div>
        <div class="modal-body">
            <form action="<?= $this->createUrl('report') ?>" method="POST" id="report-form-id">
                <div class="getreport-row">
                    <div class="pull-left">
                    Период отчета<br/>
                    <label>С <input type="text" data-date-format="dd.mm.yyyy" name="date_from" value="<?php echo date('d.m.Y', strtotime('-1 month'));?>" size="16" class="input-date"></label>
                    <label>до <input type="text" data-date-format="dd.mm.yyyy" name="date_to" value="<?php echo date('d.m.Y');?>" size="16" class="input-date"></label>
                    </div>
                    <div class="span2">
                        Активность площадки<br/>
                        <?= CHtml::dropDownList('is_active', '', array('' => 'Все', '1' => 'Активная', '0' => 'Не активная'), array('class' => 'input150 fix')); ?>
                    </div>
                    <div class="clearfix"></div>
                    Отчет по<br/>
                    <?= CHtml::dropDownList('report', '', array('notpaid' => 'неоплаченному трафику', 'paid' => 'запросы на вывод средств'));?><br />

                </div>
                <div class="getreport-row" style="background: none;">
                    <button type="submit" class="btn"><i class="icon-16 icon-excel"></i> Сформировать</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script type="text/javascript">
		$(".main-search").keyup(function(event){
		    if(event.keyCode == 13){
		    	var q = $(this).val();
		    	if($(this).data('bill') == 'in'){
	        		$.fn.yiiGridView.update('billing-incoming-grid', {data: {income_search:q} });
	        		$('.modal-backdrop').remove();
	        		$('.main-search').focusout().blur();
		    	} else {
		    		$.fn.yiiGridView.update('billing-outgoing-grid', {data: {BillingOutgoing:{id:q}} });
	        		$('.modal-backdrop').remove();
	        		$('.main-search').focusout().blur();
		    	}
        		
        		return false;
		    }
		});
	$(function(){
		$('#bill-add').each(function(index) {
            $(this).bind('click', function() {
                $.ajax({
                    type: "POST",
                    url: "<?= Yii::app()->request->baseUrl;?>/billing/returnForm",
                    data:{"YII_CSRF_TOKEN":"<?= Yii::app()->request->csrfToken;?>"},
                    beforeSend : function() {
                        $("#billing-outgoing-grid").addClass("ajax-sending");
                    },
                    complete : function() {
                        $("#billing-outgoing-grid").removeClass("ajax-sending");
                    },
                    success: function(data) {
                        $.fancybox(data,
                            $.extend({}, fancyDefaults, {
                                "width": 560,
                                "minWidth": 560,
                                "afterClose":    function() {
										var q = $("#main-search-bill-out").val();
										if(q != 'undefined' && q != ''){
											//$("#main-search-bill-out").trigger('focus');
		                    	        	$.fn.yiiGridView.update('billing-outgoing-grid', {data: {BillingOutgoing:{id:q}} });
										} else {
											window.location.href=window.location.href;
										}
                                } //onclosed function
                            })
                        );//fancybox
                        //  console.log(data);
                    } //success
                });//ajax
                return false;
            });
        });
        $('#bill-i-add').each(function(index) {
            $(this).bind('click', function() {
                $.ajax({
                    type: "POST",
                    url: "<?= Yii::app()->request->baseUrl;?>/billingIncome/returnForm",
                    data:{"YII_CSRF_TOKEN":"<?= Yii::app()->request->csrfToken;?>"},
                    beforeSend : function() {
                        $("#billing-incoming-grid").addClass("ajax-sending");
                    },
                    complete : function() {
                        $("#billing-incoming-grid").removeClass("ajax-sending");
                    },
                    success: function(data) {
                        $.fancybox(data,
                            $.extend({}, fancyDefaults, {
                                "width": 560,
                                "minWidth": 560,
                                "afterClose":    function() {
                                        //window.location.href=window.location.href;
	                                	var q = $("#main-search-bill-in").val();
	                    		    	if(q != 'undefined' && q != ''){
											//$("#main-search-bill-in").trigger('focus');
											$.fn.yiiGridView.update('billing-incoming-grid', {data: {income_search:q} });
										} else {
											window.location.href=window.location.href;
										}
                                } //onclosed functi
                            })
                        );//fancybox
                        //  console.log(data);
                    } //success
                });//ajax
                return false;
            });
        });

        $('#bill-i-report').bind('click', function(ev){
            $.fancybox($('#i-report-template').html(),
                $.extend({}, fancyDefaults, {
                    "width": 370,
                    "minWidth": 370,
                    "afterShow": function(){
                        if ($.fn.datepicker) {
                            $('.input-date').datepicker({'weekStart': 1, 'offset_y':15});
                        }
                    }
                })
            );//fancybox
        });
	});

	var editBilling = function(id) {
        $.ajax({
            type: "POST",
            url: "<?= Yii::app()->request->baseUrl;?>/billing/returnForm",
            data:{"update_id":id, "YII_CSRF_TOKEN":"<?= Yii::app()->request->csrfToken;?>"},
            beforeSend : function() {
                $("#billing-outgoing-grid").addClass("ajax-sending");
            },
            complete : function() {
                $("#billing-outgoing-grid").removeClass("ajax-sending");
            },
            success: function(data) {
                $.fancybox(data,
                    $.extend({}, fancyDefaults, {
                        "width": 560,
                        "minWidth": 560,
                        "afterClose":    function() {
                                window.location.href=window.location.href;
                        } //onclosed functi
                    })
                );//fancybox
                //  console.log(data);
            } //success
        });//ajax
        return false;
    };
    var editBillingI = function(id) {
        $.ajax({
            type: "POST",
            url: "<?= Yii::app()->request->baseUrl;?>/billingIncome/returnForm",
            data:{"update_id":id, "YII_CSRF_TOKEN":"<?= Yii::app()->request->csrfToken;?>"},
            beforeSend : function() {
                $("#billing-incoming-grid").addClass("ajax-sending");
            },
            complete : function() {
                $("#billing-incoming-grid").removeClass("ajax-sending");
            },
            success: function(data) {
                $.fancybox(data,
                    $.extend({}, fancyDefaults, {
                        "width": 560,
                        "minWidth": 560,
                        "afterClose":    function() {
                                window.location.href=window.location.href;
                        } //onclosed functi
                    })
                );//fancybox
                //  console.log(data);
            } //success
        });//ajax
        return false;
    };
		    
	var facyboxClose = function(){
		$.fancybox.close();
		return false;
	}
	
	var delBill = function(id){
		if(confirm('Удалить счёт?')){
			document.location = "<?= Yii::app()->request->baseUrl;?>/billing/delete/"+id;
		}
		
		return false;
	}
	var delBillI = function(id){
		if(confirm('Удалить счёт?')){
			document.location = "<?= Yii::app()->request->baseUrl;?>/billingIncome/delete/"+id;
		}
		
		return false;
	}
</script>
