<?
/**
 * @var Campaigns $modelC
 * @var Users $model
 */
Yii::app()->clientScript->registerCoreScript('jquery');
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.css', 'screen');
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/json2/json2.js');
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/ajaxform/jquery.form.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/ajaxform/form_ajax_binding.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile('http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/js/ajaxform/client_val_form.css','screen');

    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-datepicker.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-select.min.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/datepicker.css', 'screen');
    Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/bootstrap-select.css', 'screen');

    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/jstree/jstree.min.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/jstree/themes/default/style.min.css', 'screen');
?>
<div class="page-title-row  page-title-row-big ">
    <h1 class="page-title">
        Просмотр пользователя
    </h1>
</div>
<div class="table table-striped table-bordered table-shadow" style="width: 50%;">
	<table class="items" style="background-color: white;">
		<tr>
			<td style="background-color: white; border-bottom: 1px solid #E5E5E5; border-right: 1px solid #E5E5E5; width: 100px;" rowspan="6">
				<img src="/i/c/<?=(!empty($model->logo) ? $model->logo : 'default.jpg')?>" style="width: 70px; height: 70px;" alt="Logo" />
			</td>
			<td style="background-color: white; border-bottom: 1px solid #E5E5E5">Логин:</td>
			<td style="background-color: white; border-bottom: 1px solid #E5E5E5"><?=$model->login?></td>
		</tr>
		<tr>
			<td style="background-color: white; border-bottom: 1px solid #E5E5E5">Email:</td>
			<td style="background-color: white; border-bottom: 1px solid #E5E5E5"><?=$model->email?></td>
		</tr>
		<tr>
			<td style="background-color: white; border-bottom: 1px solid #E5E5E5">Роль:</td>
			<td style="background-color: white; border-bottom: 1px solid #E5E5E5"><?=$model->getRoleName()?></td>
		</tr>
		<tr>
			<td style="background-color: white; border-bottom: 1px solid #E5E5E5">Дата рекистрации:</td>
			<td style="background-color: white; border-bottom: 1px solid #E5E5E5"><?=$model->created_date?></td>
		</tr>
		<tr>
			<td style="background-color: white; border-bottom: 1px solid #E5E5E5">Контакты:</td>
			<td style="background-color: white; border-bottom: 1px solid #E5E5E5"><?=$model->contact_details?></td>
		</tr>
		<tr>
			<td style="background-color: white; border-bottom: 1px solid #E5E5E5">Статус:</td>
			<td style="background-color: white; border-bottom: 1px solid #E5E5E5"><?=$model->is_deleted ? 'Удален' : 'Активен'?></td>
		</tr>
		<tr>
			<td style="background-color: white;" colspan="3">
				<a class="btn title-right-btn user-story-title" href="<?=$model->id?>" style="color: white; font-size: 14px; font-weight:600;">Редактировать</a>
				<a class="btn title-right-btn" href="/site/loginUser/<?=$model->id?>" target="_blank">Войти</a>
			</td>
		</tr>
	</table>
</div>
<?if($model->role == Users::ROLE_USER || $model->role == Users::ROLE_WEBMASTER || $model->role == Users::ROLE_PLATFORM):?>
<div class="page-title-row  page-title-row-big ">
    <h1 class="page-title">
        <?if($model->role == Users::ROLE_USER):?>
        Рекламные кампании <input type="text" value="" class="main-search">
        <?elseif($model->role == Users::ROLE_WEBMASTER):?>
        Предложения
        <?elseif($model->role == Users::ROLE_PLATFORM):?>
        Рекламные площадки
        <?endif;?>
    </h1>
</div>
<?endif;?>
<?if($model->role == Users::ROLE_USER):?>
<div class="page-content">
	<?$sort = isset($_GET['Campaigns_sort'])?$_GET['Campaigns_sort']:'';?>
    <?$gv = Yii::app()->controller->widget('zii.widgets.grid.CGridView', array(
				'id'=>'campaigns-grid',
				'dataProvider'=>$list,
				'template'=> '{items}{pager}',
				'cssFile' => false,
				'htmlOptions' => array('class' => 'table table-striped table-bordered table-hover table-shadow table-campaign'),
				'columns'=>array(
						array(
								'name' => 'name',
								'type'=>'raw',
								'header' => 'Название кампании<i class="icon-sort'.($sort == 'name'?' icon-sort-down':($sort == 'name.desc'?' icon-sort-up':'')).'"></i>',
								'value' => '"<a href=\'". Yii::app()->createUrl("/campaigns/".$data->id)."\'>".$data->name."</a>"'
		
						),
						array(
								'name' => 'id',
								'header' => 'ID<i class="icon-sort'.($sort == 'id'?' icon-sort-down':($sort == 'id.desc'?' icon-sort-up':'')).'"></i>',
								'headerHtmlOptions' => array('class' => 'min'),
						),
						array(
								'name' => 'clicks',
								'header' => 'Переходов<i class="icon-sort'.($sort == 'clicks'?' icon-sort-down':($sort == 'clicks.desc'?' icon-sort-up':'')).'"></i>',
								'headerHtmlOptions' => array('class' => 'min'),
								'class'=>'DataColumn',
								'evaluateHtmlOptions'=>true,
								'htmlOptions'=>array('colspan' => '$data->getGlobalIsActive() ? "1":"4"'),
								'type' => 'raw',
								'value'=>'$data->getGlobalIsActive() ? $data->totalClicks():"Неактивна <a class=\"clone-campaign\" href=\"".Yii::app()->createUrl("campaigns/returnForm", array("update_id" => $data->id, "clone" => 1))."\"><i class=\"icon-retweet\"></i> </a>"'
						),
						array(
								'name' => 'actions',
								'header' => 'Действий<i class="icon-sort'.($sort == 'actions'?' icon-sort-down':($sort == 'actions.desc'?' icon-sort-up':'')).'"></i>',
								'headerHtmlOptions' => array('class' => 'min'),
								'class'=>'DataColumn',
								'type' => 'raw',
								'value'=>'$data->getGlobalIsActive() && $data->cost_type == Campaigns::COST_TYPE_ACTION ? $data->actions:"-"',
								'evaluateHtmlOptions'=>true,
								'htmlOptions'=>array('style' => '"display:".($data->getGlobalIsActive()?"table-cell":"none")'),
						),
						array(
								'name' => 'bounce_rate_diff',
								'header' => 'Отказы<i class="icon-sort'.($sort == 'bounce_rate_diff'?' icon-sort-down':($sort == 'bounce_rate_diff.desc'?' icon-sort-up':'')).'"></i>',
								'headerHtmlOptions' => array('class' => 'bounce-rate'),
								'evaluateHtmlOptions'=>true,
								'htmlOptions'=>array('style' => '"display:".($data->getGlobalIsActive()?"table-cell":"none")'),
								'type' => 'raw',
								'value' => '$data->getBounceRateHtml()',
								'class'=>'DataColumn',
						),
						array(
								'name' => 'days_left',
								'class'=>'DataColumn',
								'evaluateHtmlOptions'=>true,
								'header' => 'Осталось дней<i class="icon-sort'.($sort == 'days_left'?' icon-sort-down':($sort == 'days_left.desc'?' icon-sort-up':'')).'"></i>',
								'headerHtmlOptions' => array('class' => 'min'),
								'htmlOptions'=>array('style' => '"display:".($data->getGlobalIsActive()?"table-cell":"none")'),
						),
				),
		));?>
</div>
<?elseif($model->role == Users::ROLE_WEBMASTER):?>
<div class="page-content">
    <?Yii::app()->controller->widget('zii.widgets.grid.CGridView', array(
				'id'=>'campaigns-grid',
				'dataProvider'=>$list,
				'template'=> '{items}{pager}',
				'cssFile' => false,
				'htmlOptions' => array('class' => 'table table-striped table-bordered table-hover table-shadow table-campaign'),
				'columns'=>array(
						array(
								'name' => 'id',
								'header' => 'ID',
								'type' => 'raw',
								'value' => '$data["id"]',
								'htmlOptions' => array('class' => 'align-left'),
						),
						array(
								'name' => 'offer_name',
								'header' => 'Кампания',
								'type' => 'raw',
								'value' => '(!empty($data["offer"]->campaign->client->logo)?'
								.'"<img src=\"/i/c/".$data["offer"]->campaign->client->logo."\" width=\"50\" height=\"50\" style=\"float: left; margin-right: 10px;\"/>"'
								.':"<img src=\"/i/c/no_image.png\" width=\"50\" height=\"50\" style=\"float: left; margin-right: 10px;\"/>")'
								.'.($data["is_deleted"] ? $data["offer"]->name : ("<a href=\"".Yii::app()->createUrl("/offers/".$data["offer"]->id)."\" class=\"view-offer\" data-id=\"".$data["offer"]->id."\">".$data["offer"]->name."</a>"))',
								'htmlOptions' => array('class' => 'align-left'),
						),
						array(
								'name' => 'countries',
								'header' => 'Страны',
								'type' => 'raw',
								'value' => '($data["offer"]->getCountriesCodes()) ? $data["offer"]->getCountriesCodes() : " -- "',
								'htmlOptions' => array()
						),
						array(
								'name' => 'price',
								'header' => 'Цена за действие',
								'value' => 'Yii::app()->numberFormatter->formatDecimal($data["offer"]->reward)." руб."'
						),
						array(
								'name' => 'offers_clicks',
								'header' => 'Клики',
								'value' => '$data["offers_clicks"]'
						),
						array(
								'name' => 'offers_actions',
								'header' => 'Действия',
								'value' => 'Yii::app()->numberFormatter->formatDecimal($data["offers_actions"])'
						),
						array(
								'name' => 'offers_moderation_actions',
								'header' => 'В ожидании',
								'value' => 'Yii::app()->numberFormatter->formatDecimal($data["offers_moderation_actions"])'
						),
						array(
								'name' => 'offers_declined_actions',
								'header' => 'Отклонено',
								'value' => 'Yii::app()->numberFormatter->formatDecimal($data["offers_declined_actions"])'
						),
						array(
								'name' => 'conversions',
								'header' => 'Конверсии, %',
								'value' => 'Yii::app()->numberFormatter->formatDecimal($data["conversions"])'
						),
						array(
								'name' => 'total',
								'header' => 'Всего',
								'value' => 'Yii::app()->numberFormatter->formatDecimal($data["reward_total"])." руб."'
						),
						array(
								'name' => 'status',
								'header' => 'Статус',
								'value' => '$data->getStatusName()'
						),
		
				),
		));?>
</div>
<?elseif($model->role == Users::ROLE_PLATFORM):?>
<div class="page-content">
<?$sort = isset($_GET['Platforms_sort'])?$_GET['Platforms_sort']:'';?>
<?Yii::app()->controller->widget('zii.widgets.grid.CGridView', array(
				'id'=>'campaigns-grid',
				'dataProvider'=>$list,
				'template'=> '{items}{pager}',
				'cssFile' => false,
				'htmlOptions' => array('class' => 'table table-striped table-bordered table-hover table-shadow table-campaign'),
				'columns'=>array(
						array(
								'type'=>'raw',
								'value'=>'$data->id',
								'header'=>'ID',
						),
						array(
								'name' => 'server',
								'type'=>'raw',
								'value'=>'"<a href=\'".Yii::app()->createUrl("/platforms/news/".$data->id)."\'>".$data->server."</a>"',
								'header' => 'Сервер<i class="icon-sort'.($sort == 'server'?' icon-sort-down':($sort == 'server.desc'?' icon-sort-up':'')).'"></i>',
						),
						array(
								'name' => 'id',
								'header' => 'ID<i class="icon-sort'.($sort == 'id'?' icon-sort-down':($sort == 'id.desc'?' icon-sort-up':'')).'"></i>',
								'visible' => (Yii::app()->user->role === Users::ROLE_ADMIN)
						),
						array(
								'header' => 'Активность<i class="icon-sort'.($sort == 'is_active'?' icon-sort-down':($sort == 'is_active.desc'?' icon-sort-up':'')).'"></i>',
								'name' => 'is_active',
								'type'=>'raw',
								'value'=>'$data->is_active == 1 ? "<i class=\'icon-12 icon-status-green\'></i>" : "<i class=\'icon-12 icon-status-red\'></i>"'
						)
						,array(
								'header' => 'Внешняя сеть<i class="icon-sort'.($sort == 'is_external'?' icon-sort-down':($sort == 'is_external.desc'?' icon-sort-up':'')).'"></i>',
								'name' => 'is_external',
								'type'=>'raw',
								'value'=>'$data->is_external == 1 ? "Да" : "Нет"',
								'visible' => Yii::app()->user->role === Users::ROLE_ADMIN,
						),
						array(
								'header' => 'Сегмент<i class="icon-sort'.($sort == 'tag_names'?' icon-sort-down':($sort == 'tag_names.desc'?' icon-sort-up':'')).'"></i>',
								'name' => 'tag_names',
						),
		
				)
		));?>
</div>
<?endif;?>
<script type="text/javascript">
	$(".main-search").keyup(function(event){
	    if(event.keyCode == 13){
	    	var q = $(this).val();
    		$.fn.yiiGridView.update('campaigns-grid-<?= $model->id;?>', {data: {Campaigns:{name:q}} });
    		$('.modal-backdrop').remove();
    		$('.main-search').focusout().blur();
    		
    		return false;
	    }
	});

    $('.addCompany, .clone-campaign').each(function(index) {
        //var id = $(this).data('id');
        $(this).bind('click', function() {
            $.ajax({
                type: "POST",
                url: $(this).attr('href'),
                data:{/*"update_id":id,*/"YII_CSRF_TOKEN":"<?= Yii::app()->request->csrfToken;?>"},
                beforeSend : function() {
                    $("#users-grid").addClass("ajax-sending");
                },
                complete : function() {
                    $("#users-grid").removeClass("ajax-sending");
                },
                success: function(data) {
                    $.fancybox(data,
                        $.extend({}, fancyDefaults, {
                            "width": 543,
                            "minWidth": 543,
                            'onComplete': function() {
                                $(document).scrollTop(0);
                                $("#fancybox-wrap").css({'top':'20px', 'bottom':'auto'});
                             },
                            "afterClose":    function() {
                                window.location.href=window.location.href;
                            } //onclosed function
                        })
                    );//fancybox
                    //  console.log(data);
                } //success
            });//ajax
            return false;
        });
    });

    var delElement = function(id){
        if(confirm('Удалить пользователя?')){
            document.location = "<?= Yii::app()->request->baseUrl;?>/users/delete/"+id;
        }

        return false;
    }
</script>