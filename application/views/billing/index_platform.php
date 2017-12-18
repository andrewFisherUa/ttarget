<div class="section-billing" id="bill-out">
	<div class="title-with-button">
        <h1 class="page-title">
                Доход с площадок
        </h1>
    </div>
<?php
	$sort = isset($_GET['BillingOutgoing_sort'])?$_GET['BillingOutgoing_sort']:'';
	$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'billing-platforms-grid',
	'htmlOptions' => array('class' => 'table table-bordered table-striped table-shadow table-centering'),
	'dataProvider'=>$platforms->search(),
	'template' => '{items}{pager}',
	'columns'=>array(
			array(
				'name' => 'created',
				'header' => 'Дата регистрации<i class="icon-sort'.($sort == 'created'?' icon-sort-down':($sort == 'created.desc'?' icon-sort-up':'')).'"></i>',
			),
            array(
                'name' => 'server',
                'header' => 'Площадка<i class="icon-sort'.($sort == 'server'?' icon-sort-down':($sort == 'server.desc'?' icon-sort-up':'')).'"></i>',
            ),
			array(
				'name' => 'billing_paid',
//				'header' => 'Сумма выплат<i class="icon-sort'.($sort == 'billing_paid'?' icon-sort-down':($sort == 'billing_paid.desc'?' icon-sort-up':'')).'"></i>',
                'header' => 'Сумма выплат',
                'footer' => number_format(BillingIncome::model()->getPaidByUser(Yii::app()->user->id),2,'.',' '),
			),
            array(
                'name' => 'billing_debit',
//                'header' => 'К выводу<i class="icon-sort'.($sort == 'billing_debit'?' icon-sort-down':($sort == 'billing_debit.desc'?' icon-sort-up':'')).'"></i>',
                'header' => 'К выводу',
                'footer' => number_format(BillingIncome::model()->getDebitByUser(Yii::app()->user),2,'.',' '),
                'footerHtmlOptions' => array('style' => 'color: red;')
            ),
			array(
				'name' => 'is_active',
				'value'=>'$data->is_active == 1 ? "Активна" : "Не активна"',
				'header' => 'Статус<i class="icon-sort'.($sort == 'is_active'?' icon-sort-down':($sort == 'is_active.desc'?' icon-sort-up':'')).'"></i>',
			),
		)
)); ?>
    <div class="spacer">
        <a href="/" class="btn bill-i-add"><i class="icon-16 icon-add"></i> запрос на вывод</a>
    </div>
</div>

<div class="section-billing" id="bill-in">
	<div class="title-with-button">
        <h1 class="page-title">
                Исходящие счета <input type="text" value="" data-bill="in" class="main-search">
        </h1>
    </div>

<?php
	$sort = isset($_GET['BillingIncome_sort'])?$_GET['BillingIncome_sort']:'';
	$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'billing-incoming-grid',
	'htmlOptions' => array('class' => 'table table-bordered table-striped table-shadow table-billing'),
	'dataProvider'=>$modelI->searchGrouped(),
	'template' => '{items}{pager}',
	'columns'=>array(
			array(
				'name' => 'number',
				'header' => '№ Счёта<i class="icon-sort'.($sort == 'number'?' icon-sort-down':($sort == 'number.desc'?' icon-sort-up':'')).'"></i>',
			),
			array(
				'name' => 'platform_id',
				'value' => '$data->server',
				'header' => 'Площадка<i class="icon-sort'.($sort == 'platform_id'?' icon-sort-down':($sort == 'platform_id.desc'?' icon-sort-up':'')).'"></i>',
			),
			array(
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
        <span class="billing-debt">Неоплачено: <?php echo floatval($sum['incoming'][0]).' '.$currency;?></span> |
        <span class="billing-paid">Оплачено: <?php echo floatval($sum['incoming'][1]).' '.$currency;;?></span> |
        <span class="billing-total">Всего: <?php echo floatval($sum['incoming'][1])+floatval($sum['incoming'][0]).' '.$currency;;?></span>
    </div>
    <div class="spacer">
        <a href="/" class="btn bill-i-add"><i class="icon-16 icon-add"></i> запрос на вывод</a>
    </div>

</div>

<script type="text/javascript">
		$(".main-search").keyup(function(event){
		    if(event.keyCode == 13){
		    	var q = $(this).val();
                $.fn.yiiGridView.update('billing-incoming-grid', {data: {income_search:q} });
                $('.modal-backdrop').remove();
                $('.main-search').focusout().blur();
        		return false;
		    }
		});
	$(function(){
        $('.bill-i-add').each(function(index) {
            $(this).bind('click', function() {
                $.ajax({
                    type: "POST",
                    url: "<?php echo Yii::app()->request->baseUrl;?>/billingIncome/returnFormPlatform",
                    data:{"YII_CSRF_TOKEN":"<?php echo Yii::app()->request->csrfToken;?>"},
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
                                       
                                       	var q = $(".main-search").val();
	                    		    	if(q != 'undefined' && q != ''){
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
	});
</script>
