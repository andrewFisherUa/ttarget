<?
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.css', 'screen');

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/jquery.form.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/form_ajax_binding.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/ajaxform/client_val_form.css', 'screen');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/uri/URI.min.js', CClientScript::POS_HEAD);
?>

<div class="page-title-row page-title-row-big ">
	<div class="title-with-button">
		<?= CHtml::dropDownList(
            'pageSize',
    		$pageSize,
            array('10'=>'10','50' => '50','100' => '100'),
            array('class' => 'title-right-btn input150 tableFilterSelect', 'style' => 'width: 70px;')
        ); ?>
        <strong class="title-right-btn">Показывать:</strong>
		<?= CHtml::dropDownList(
            'is_new',
            $_filterIsNew,
            array(0 => 'Все',1 => 'Новые'),
            array('empty' => 'Отображать', 'class' => 'title-right-btn input150 tableFilterSelect')
        ); ?>
		<a href="#" id="mark_read" class="btn title-right-btn" style="display: none;">Отметить прочитанным</a>
	<h1 class="page-title">
	Лента активности <input type="text" value="" class="main-search">
	</h1>
	</div>
</div>

<div class="page-content">
<?php
	$sort = isset($_GET['Notifications_sort'])?$_GET['Notifications_sort']:'create_date.desc';
	$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'news-grid',
	'htmlOptions' => array('class' => 'table table-striped table-bordered table-shadow table-centering moderated-table'),
	'dataProvider'=>$notifications,
	'template' => '{items}{pager}',
    'rowCssClassExpression' => '"platform_notification"',
    'afterAjaxUpdate' => 'activeTable',
	'columns'=>array(
			array(
				'name' => 'mark',
				'type' => 'raw',
				'header' => '<input type="checkbox" id="mark_checkbox_all"/>',
				'value' => '($data->is_new ? "<input type=\"checkbox\" class=\"mark_checkbox\" value=\"".$data->id."\" />" : "")'
			),
			array(
				'name' => 'create_date',
				'type'=>'raw',
				'header' => 'Дата<i class="icon-sort'.($sort == 'create_date'?' icon-sort-down':($sort == 'create_date.desc'?' icon-sort-up':'')).'"></i>',
			),
			array(
				'header' => 'Новость<i class="icon-sort'.($sort == 'platform.server'?' icon-sort-down':($sort == 'platform.server.desc'?' icon-sort-up':'')).'"></i>',
				'name' => 'platform.server',
				'type'=>'raw',
				'htmlOptions' => array('class' => 'align-left'),
				'value'=>'"<a data-type=\'notification\' data-id=\'".$data->id."\' href=\'campaigns/".$data->teaser->news->campaign_id."#a-teasers-".$data->teaser->news->id."\' class=\'moderated-title".($data->is_new ? "-new" : "")."\'>Площадка ".$data->platform->server." отключила тизер \"".$data->teaser->title."\"</a>"'
			),
			array(
				'header' => 'Рекламная кампания<i class="icon-sort'.($sort == 'campaign.name'?' icon-sort-down':($sort == 'campaign.name.desc'?' icon-sort-up':'')).'"></i>',
				'name' => 'campaign.name',
				'type'=>'raw',
				'value'=>'"<a class=\'campaign-story-title inline\' href=\'campaigns/".$data->teaser->news->campaign_id."\'>".$data->teaser->news->campaign->name."</a>"'
			),
            array(
                'header' => 'Пользователь<i class="icon-sort'.($sort == 'user.login'?' icon-sort-down':($sort == 'user.login.desc'?' icon-sort-up':'')).'"></i>',
                'name' => 'user.login',
                'type'=>'raw',
                'value'=>'$data->user === null ? "Нет" : $data->user->login." (".$data->user->email.")"'
            ),
		)
)); ?>
</div>
<script type="text/javascript">
	$(function(){
        activeTable();
		$(".main-search").keyup(function(event){
		    if(event.keyCode == 13){
		    	var q = $(this).val();
        		$.fn.yiiGridView.update('news-grid', {data: {News:{name:q}} });
        		$('.modal-backdrop').remove();
        		$('.main-search').focusout().blur();
        		
        		return false;
		    }
		});

		$('#mark_checkbox_all').live('click', function(){
			if($(this).attr('checked') == 'checked'){
				$('.mark_checkbox').each(function(i, elem){
					$('#mark_read').show();
				});
				$('.mark_checkbox').attr({checked: 'checked'});
			} else {
				$('.mark_checkbox').removeAttr('checked');
				$('#mark_read').hide();
			}
	    });
	    $('.mark_checkbox').live('click', function(event){
	    	event.stopPropagation();
			var checked = false;
			$('.mark_checkbox').each(function(i, elem){
				if($(elem).attr('checked') == 'checked'){
					checked = true;
		    	}
			});
	    	if(checked){
	    		$('#mark_read').show();
	        } else {
	        	$('#mark_read').hide();
	        }
	    });
	    $('#mark_read').click(function(){

			var data = {ids: {}};
			$('.mark_checkbox').each(function(i, elem){
				if($(elem).attr('checked') == 'checked'){
					data.ids[i] = $(elem).val();
				}
			});
			$.ajax({
				url: '<?=$this->createUrl('changeNewAll');?>',
				type: 'POST',
				dataType: 'json',
				data: data,
				beforeSend : function() {
	                $("#news-grid").addClass("ajax-sending");
	            },
	            comlete: function(){
	            	$("#news-grid").removeClass("ajax-sending");
	            },
				success: function(json){
					$.fn.yiiGridView.update('news-grid');
					$('#mark_read').hide();
					if(json.count_new > 0){
						$('#notification_cnt_new').html(json.count_new);
					} else {
						$('#notification_cnt_new').hide();
					}
					
				}
			});

			return false;
	    });

		$('.tableFilterSelect:not(div)').on('change',function(e){
			filterTableResults();
		});

		
	});
    var activeTable = function(){
        //$('.platform_notification td').on('click', function(){
        //    var el = $(this).parent().find('.moderated-title-new');
        //    if(el.length == 0) return;
        //    $.ajax({
        //        url: "<?php echo $this->createUrl('changeNew'); ?>",
        //        data: {"id":el.data('id'),"type":el.data('type')},
        //        beforeSend : function() {
        //            $("#news-grid").addClass("ajax-sending");
        //        },
        //        complete : function() {
        //            $("#news-grid").removeClass("ajax-sending");
        //        },
        //        success: function(data) {
        //            if(data.success == true){
        //                el.removeClass('moderated-title-new').addClass('moderated-title');
        //            }
        //        }
        //    });
        //});
    }

    var facyboxClose = function(){
        $.fancybox.close();
        return false;
    }

    var baseUrl = new URI('<?=$this->createUrl($this->action->getId())?>');
    
  	//Build uri and redirecting
    function filterTableResults(){
    	var params = {};
		$('.tableFilterSelect').each( function( i, select ) {
			params[$(select).attr('name')] = $(select).val();
		});
		baseUrl.query(params);
		document.location.href = baseUrl;
	};
    
</script>
