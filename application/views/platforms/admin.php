<?
    Yii::app()->clientScript->registerCoreScript('jquery');
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.css', 'screen');
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/json2/json2.js');
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/jquery.form.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/form_ajax_binding.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile('http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/ajaxform/client_val_form.css', 'screen');

    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-datepicker.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-select.min.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/datepicker.css', 'screen');
    Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/bootstrap-select.css', 'screen');
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/uri/URI.min.js', CClientScript::POS_HEAD);
/**
 * @var Platforms $model
 * @var PlatformsController $this
 */
?>
<div class="page-title-row  page-title-row-big " style="padding-bottom: 20px;">
     <div class="title-with-button">
        <!--<a href="<?= $this->createUrl('/platforms/create'); ?>" class="btn title-right-btn"><i class="icon-16 icon-add"></i> Добавить площадку</a>-->
        <?php if(Yii::app()->user->role === Users::ROLE_ADMIN) : ?>
            <a href="/" id="platform-add" class="btn title-right-btn"><i class="icon-16 icon-add"></i> Добавить площадку</a>
        <?php endif; ?>
        <a class="platforms-generatereport" href="admin" id="reports">Сформировать отчёт</a>
		<h1 class="page-title">
            Рекламные площадки <input type="text" value="" class="main-search">
        </h1>
    </div>
    <?php if(Yii::app()->user->role === Users::ROLE_ADMIN) : ?>
    <div class="title-with-button">
    	<?= CHtml::dropDownList(
            'pageSize',
    		$pageSize,
            array('10'=>'10','50' => '50','100' => '100'),
            array('class' => 'title-right-btn input150 tableFilterSelect', 'style' => 'width: 70px;')
        ); ?>
        <strong class="title-right-btn">Показывать:</strong>
    	<?= CHtml::dropDownList(
            'is_active',
            $filter['is_active'],
            array('1' => 'Активные','2' => 'Неактивные'),
            array('empty' => 'Все площадки', 'class' => 'title-right-btn input150 tableFilterSelect')
        ); ?>
        <?= CHtml::dropDownList(
            'tag_id',
            $filter['tag_id'],
            array_merge(Tags::model()->findTagsUsedByPlatforms(),array()),
            array('empty' => 'Все сегменты', 'class' => 'title-right-btn input150 tableFilterSelect')
        ); ?>
        <a class="title-right-btn" style="display: <?= $filter['period'] == 'custom' ? 'inline' : 'none';?>" href="" onclick="return showPeriodSelect()">С <?= date('d.m.Y', strtotime($filter['dateFrom']));?> до <?= date('d.m.Y', strtotime($filter['dateTo']));?></a>
		<?= CHtml::dropDownList('period',$filter['period'],
		    array(
		        "today" => 'сегодня',
		        "yesterday" => 'вчера',
		        "month" => 'месяц',
		        "custom" => 'выбранный интервал',
		        "all" => 'все время',
		    ),
		    array('id' => 'period', 'class' => 'title-right-btn input150 tableFilterSelect')
		); ?>
        <strong class="title-right-btn">Добавлены:</strong>
		<div id="custom-period" style="display:none">
		    <div id="modal-campaign-getreport" class="show">
		        <div class="modal-header">
		            <a href="" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="$.fancybox.close(); return false;"></a>
		            <h3>Выберите интервал</h3>
		        </div>
		        <div class="modal-body">
		            <div class="getreport-row">
		                <label>С <input name="date_from" type="text" data-date-format="dd.mm.yyyy" id="mmdate_from" value="<?= date('d.m.Y', strtotime($filter['dateFrom']));?>" size="16" class="input-date tableFilterSelect"></label>
		                <label>до <input name="date_to" type="text" data-date-format="dd.mm.yyyy" id="mmdate_to" value="<?= date('d.m.Y', strtotime($filter['dateTo']));?>" size="16" class="input-date tableFilterSelect"></label>
		                <button  name="report" value="full" class="btn" onclick="getCustomPeriod()">Обновить</button>
		                <br/><br/>
		            </div>
		        </div>
		    </div>
		</div>
        <div class="title-right-btn"> <a href="#" id="email-list">Список email</a></div>
   	</div>
   	<?endif;?>
</div>
<div class="page-content">
<?
	$sort = isset($_GET['Platforms_sort'])?$_GET['Platforms_sort']:'';
	$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'news-grid',
	'htmlOptions' => array('class' => 'table table-striped table-bordered table-shadow table-centering'),
	'dataProvider'=>$platforms,
	'template' => '{items}{pager}',
	'columns'=>array(
            array(
                'type'=>'raw',
                'value'=>'"<a onclick=\'return editPlatform(".$data->id.")\' href=\'".Yii::app()->createUrl("/platforms/update/".$data->id)."\'><i class=\'icon-14 icon-cog-dark\'></i></a> <a href=\'".Yii::app()->createUrl("/platforms/news/".$data->id)."\'><i class=\'icon-signal\'></i></a>"',
                'header'=>'',
            ),
			array(
					'name' => 'id',
					'header' => 'ID<i class="icon-sort'.($sort == 'id'?' icon-sort-down':($sort == 'id.desc'?' icon-sort-up':'')).'"></i>',
					'visible' => (Yii::app()->user->role === Users::ROLE_ADMIN)
			),
			array(
				'name' => 'server',
				'type'=>'raw',
				'value'=>'"<a href=\'".Yii::app()->createUrl("/platforms/news/".$data->id)."\'>".$data->server."</a>"',
				'header' => 'Сервер<i class="icon-sort'.($sort == 'server'?' icon-sort-down':($sort == 'server.desc'?' icon-sort-up':'')).'"></i>',
			),
            array(
                'name' => 'contacts',
            	'type'=>'raw',
                'header' => 'Контакты<i class="icon-sort'.($sort == 'contacts'?' icon-sort-down':($sort == 'contacts.desc'?' icon-sort-up':'')).'"></i>',
                'visible' => (Yii::app()->user->role === Users::ROLE_ADMIN),
            	'value' => '!empty($data->user) ? \'<a href="mailto:\'.$data->user->email.\'" target="_blank">\'.$data->user->login.\'</a>\' : \' -- \''
            ),
			array(
				'header' => 'Активность<i class="icon-sort'.($sort == 'is_active'?' icon-sort-down':($sort == 'is_active.desc'?' icon-sort-up':'')).'"></i>',
				'name' => 'is_active',
				'type'=>'raw',
				'value'=>'$data->is_active == 1 ? "<i class=\'icon-12 icon-status-green\'></i>" : "<i class=\'icon-12 icon-status-red\'></i>"'
			),
            array(
				'header' => 'Доступность кода<i class="icon-sort'.($sort == 'is_code_active'?' icon-sort-down':($sort == 'is_code_active.desc'?' icon-sort-up':'')).'"></i>',
				'name' => 'is_code_active',
				'type'=>'raw',
				'visible' => (Yii::app()->user->role === Users::ROLE_ADMIN),
				'value'=>'$data->is_code_active ? "<i class=\'icon-12 icon-status-green\'></i>" : "<i class=\'icon-12 icon-status-red\'></i>"'
			),array(
				'header' => 'Посещаемость<i class="icon-sort'.($sort == 'visits_count'?' icon-sort-down':($sort == 'visits_count.desc'?' icon-sort-up':'')).'"></i>',
				'name' => 'visits_count',
				'visible' => (Yii::app()->user->role === Users::ROLE_ADMIN),
			)
			,array(
				'header' => 'Внешняя сеть<i class="icon-sort'.($sort == 'is_external'?' icon-sort-down':($sort == 'is_external.desc'?' icon-sort-up':'')).'"></i>',
				'name' => 'is_external',
				'type'=>'raw',
				'value'=>'$data->is_external == 1 ? "Да" : "Нет"',
                'visible' => Yii::app()->user->role === Users::ROLE_ADMIN,
			),
			array(
				'header' => 'Бюджет за сегодня<i class="icon-sort'.($sort == 'daily_profit'?' icon-sort-down':($sort == 'daily_profit.desc'?' icon-sort-up':'')).'"></i>',
				'name' => 'daily_profit',
                'value' => '$data->getDailyProfit()',
                'visible' => Yii::app()->user->role === Users::ROLE_ADMIN,
			),
			array(
				'header' => 'К выводу<i class="icon-sort'.($sort == 'total_debit'?' icon-sort-down':($sort == 'total_debit.desc'?' icon-sort-up':'')).'"></i>',
				'name' => 'total_debit',
				'type'=>'raw',
				'value'=>'$data->getBilling_debit()',
                'visible' => Yii::app()->user->role === Users::ROLE_ADMIN,
			),
			array(
				'header' => 'Сегмент<i class="icon-sort'.($sort == 'tag_names'?' icon-sort-down':($sort == 'tag_names.desc'?' icon-sort-up':'')).'"></i>',
				'name' => 'tag_names',
            ),
			
		)
)); ?>
</div>
<script type="text/javascript">
	$(function(){
        $(".main-search").keyup(function(event){
		    if(event.keyCode == 13){
		    	var q = $(this).val();
        		$.fn.yiiGridView.update('news-grid', {data: {Platforms:{server:q}} });
        		$('.modal-backdrop').remove();
        		$('.main-search').focusout().blur();
        		
        		return false;
		    }
		});
		$('#platform-add').each(function(index) {
            $(this).bind('click', function() {
                $.ajax({
                    type: "POST",
                    url: "<?= Yii::app()->request->baseUrl;?>/platforms/returnForm",
                    data:{"YII_CSRF_TOKEN":"<?= Yii::app()->request->csrfToken;?>"},
                    beforeSend : function() {
                        $("#news-grid").addClass("ajax-sending");
                    },
                    complete : function() {
                        $("#news-grid").removeClass("ajax-sending");
                    },
                    success: function(data) {
                        $.fancybox(data,
                            $.extend({}, fancyDefaults, {
                                "width": 575,
                                "minWidth": 575,
                                "afterClose":    function() {
                                        var page=$("li.selected  > a").text();
                                        var q = $(".main-search").val();
                                        $.fn.yiiGridView.update('news-grid', {url:'',data:{Platforms:{server:q}}});
                                } //onclosed functi
                            })
                        );//fancybox
                        //  console.log(data);
                    } //success
                });//ajax
                return false;
            });
        });

		$('.tableFilterSelect:not(div)').on('change',function(e){
			if($(this).attr('name') == 'period' && $(this).val() == 'custom'){
				showPeriodSelect();
			} else {
				// store values into url
				filterTableResults();
			}
		});
	});
	    
	var editPlatform = function(id) {
        $.ajax({
            type: "POST",
            url: "<?= Yii::app()->request->baseUrl;?>/platforms/returnForm",
            data:{"update_id":id, "YII_CSRF_TOKEN":"<?= Yii::app()->request->csrfToken;?>"},
            beforeSend : function() {
                $("#news-grid").addClass("ajax-sending");
            },
            complete : function() {
                $("#news-grid").removeClass("ajax-sending");
            },
            success: function(data) {
                $.fancybox(data,
                    $.extend({}, fancyDefaults, {
                        "width": 575,
                        "minWidth": 575,
                        "afterClose":    function() {
                                var page=$("li.selected  > a").text();
                        		$.fn.yiiGridView.update('news-grid', {url:'',data:{}});
                        } //onclosed functi
                    })
                );//fancybox
                //  console.log(data);
            } //success
        });//ajax
        return false;
    };

    $('#reports').each(function(index) {
        $(this).bind('click', function() {
            $.fancybox($('#report-template').html().replace('report-form-id', 'report-form').replace('__platform_id__', 'platform_id').replace('__platform_id__', 'platform_id'),
                $.extend({}, fancyDefaults, {
                    "width": 370,
                    "minWidth": 370,
                    "afterShow":function(){
                        if ($.fn.datepicker) {
                            $('.input-date').datepicker({'weekStart': 1, 'offset_y':15});
                        }
                        if ($.fn.selectpicker()) {
                            $('select.selectpick:visible').selectpicker();
                        }
                    }
                })
            );
            return false;
        });
    });

    $('#email-list').on('click', function() {
        $.ajax({
            type: "GET",
            url: "<?= $this->createUrl(Yii::app()->controller->getAction()->getId(), array_merge($_GET, array('emailList' => 1))); ?>",
            beforeSend : function() {
                $("#news-grid").addClass("ajax-sending");
            },
            complete : function() {
                $("#news-grid").removeClass("ajax-sending");
            },
            success: function(data) {
                $.fancybox(data,
                    $.extend({}, fancyDefaults, {
                        "width": 575,
                        "minWidth": 575,
                    })
                );//fancybox
            } //success
        });//ajax
        return false;
    });
		    
	var facyboxClose = function(){
		$.fancybox.close();
		return false;
	}
	
	var delPlatform = function(id){
		if(confirm('Удалить площадку?')){
			document.location = "<?= Yii::app()->request->baseUrl;?>/platforms/delete/"+id;
		}
		
		return false;
	}

	var baseUrl = new URI('<?=$this->createUrl($this->action->getId(),array_diff_key($_GET,
			array('date_from' => '', 'date_to' => '')))?>');

			
	var periodSelected = $('#period').prop('selectedIndex');

	function showPeriodSelect()
    {
        $.fancybox($('#custom-period').html().replace('mmdate_from', 'date_from').replace('mmdate_to', 'date_to'),
            $.extend({}, fancyDefaults, {
                "width": 450,
                "minWidth": 450,
                "afterShow":function(){
                    if ($.fn.datepicker) {
                        $('.input-date').datepicker({'weekStart': 1, 'offset_y':15});
                    }
                },
                "afterClose":function(){
                    $('#period').prop('selectedIndex', periodSelected);
                }
            })
        );
        return false;
    }

    //Build uri and redirecting
    function filterTableResults(){
    	var params = {};
		$('.tableFilterSelect').each( function( i, select ) {
			params[$(select).attr('name')] = $(select).val();
		});
		baseUrl.query(params);
		document.location.href = baseUrl;
	};

    function getCustomPeriod()
    {
    	filterTableResults();
		$.fancybox.close();
    }
	
</script>

<div id="report-template" style="display:none">
    <div id="modal-campaign-getreport" class="modal show">
        <div class="modal-header">
            <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>
            <h3>Формирование отчёта</h3>
        </div>
        <div class="modal-body">
            <form action="<?= $this->createUrl('report') ?>" method="POST" id="report-form-id">
                <?php if(Yii::app()->user->role === Users::ROLE_ADMIN) : ?>
                    <div class="getreport-row">
                        Обзорный отчет
                        <button type="submit" name="report" value="overview" class="btn"><i class="icon-16 icon-excel"></i> Сформировать</button>
                    </div>
                <?php endif; ?>
                <div class="getreport-row">
                    Полный отчёт за период<br/>
                    <label>С <input type="text" data-date-format="dd.mm.yyyy" name="full_date_from" value="<?php echo date('d.m.Y', strtotime('-1 month'));?>" size="16" class="input-date"></label>
                    <label>до <input type="text" data-date-format="dd.mm.yyyy" name="full_date_to" value="<?php echo date('d.m.Y');?>" size="16" class="input-date"></label>
                    <button  name="report" value="full" type="submit" class="btn"><i class="icon-16 icon-excel"></i> Сформировать</button>
                    <?php if(!empty($model->user_id)) : ?>
                        <br/>
                        <label><input type="checkbox" name="user_id" value="<?= $model->user_id; ?>"> Только для этого пользователя</label>
                    <?php endif; ?>
                </div>
                <?php if(Yii::app()->user->role === Users::ROLE_ADMIN) : ?>
                    <div class="getreport-row">
                        Отчет по <select name="isExternal" class="span2"><option value="1">Внешним</option><option value="0">внутреним</option></select> площадкам<br>
                        <label>С <input type="text" data-date-format="dd.mm.yyyy" name="external_date_from" value="<?php echo date('d.m.Y', strtotime('-1 month'));?>" size="16" class="input-date"></label>
                        <label>до <input type="text" data-date-format="dd.mm.yyyy" name="external_date_to" value="<?php echo date('d.m.Y');?>" size="16" class="input-date"></label>
                        <button type="submit" name="report" value="external" class="btn"><i class="icon-16 icon-excel"></i> Сформировать</button>
                    </div>
                <?php endif; ?>
                <div class="getreport-row">
                    Отчёт для партнёра
                    <?php
                    echo CHtml::dropDownList('platform_id', '', CHtml::listData(Platforms::model()->printable()->findAll((Yii::app()->user->role !== Users::ROLE_ADMIN ? 'user_id = '.Yii::app()->user->id.' AND ' : '').'is_external = 0'), 'id', 'server'), array('class'=>'selectpick', 'data-live-search' => 'true'));
                    ?><br />
                    <label>С <input type="text" data-date-format="dd.mm.yyyy" name="partner_date_from" value="<?php echo date('d.m.Y', strtotime('-1 month'));?>" size="16" class="input-date"></label>
                    <label>до <input type="text" data-date-format="dd.mm.yyyy" name="partner_date_to" value="<?php echo date('d.m.Y');?>" size="16" class="input-date"></label>
                    <button type="submit" name="report" value="partner" class="btn"><i class="icon-16 icon-excel"></i> Сформировать</button><br/>
                    <br/>
                </div>
            </form>
        </div>
    </div>
</div>
