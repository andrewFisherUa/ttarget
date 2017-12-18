<? /**
* @var Campaigns $model
 *@var CampaignsController $this
 */ ?>
<div id="modal-campaign-settings">
	<div class="modal-header">
	    <a href="#" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>
	    <h3><? if ($model->isNewRecord){?>Создать новую кампанию<? } else {?>Редактирование кампании<? }?></h3>
	</div>
	<?
        $formId='campaigns-form';
        $actionUrl = ($model->isNewRecord) ? CController::createUrl('campaigns/create'):CController::createUrl('campaigns/update/'.$model->id);
        $form=$this->beginWidget('CActiveForm', array(
            'id'=>'campaigns-form',
            //'htmlOptions' => array('enctype' => 'multipart/form-data'),
            'action' => $actionUrl,
            //'enableAjaxValidation'=>true,
            'enableClientValidation'=>true,
            'focus'=>array($model,'name'),
            'errorMessageCssClass' => 'input-notification-error  error-simple png_bg',

            'clientOptions'=>array('validateOnSubmit'=>true,
            'validateOnType'=>false,
            'errorCssClass' => 'err',
            'successCssClass' => 'suc',
            'afterValidate' => 'js:function(form,data,hasError){ $.js_afterValidate(form,data,hasError);  }',
            'errorCssClass' => 'err',
            'successCssClass' => 'suc',
            'afterValidateAttribute' => 'js:function(form, attribute, data, hasError){
                 $.js_afterValidateAttribute(form, attribute, data, hasError);
            }'
        ),
	)); ?>
	<?= $form->hiddenField($model, 'clone_id'); ?>
	<?= $form->hiddenField($model,'client_id'); ?>
	<div class="modal-body">
		<!-- tabs container -->
		<div id="tabs">
			<ul>
				<li><a href="#tabs-1">Тип кампании</a></li>
				<li><a href="#tabs-2">Параметры</a></li>
				<?if($step == 'fields' || $step == 'children'):?>
					<?if($model->cost_type == Campaigns::COST_TYPE_ACTION):?>
					<li><a href="#tabs-3">Цели</a></li>
					<?elseif($model->cost_type == Campaigns::COST_TYPE_RTB):?>
					<li><a href="#tabs-3">Креативы</a></li>
					<?endif;?>
				<?endif;?>
			</ul>
			<div id="tabs-1">
				<div style="width: 100%; height: 200px;">
					<div class="row">
				       	<?= $form->dropDownList($model, 'cost_type', $model->getAvailableCostTypes()); ?>
				        <?= $form->error($model, 'cost_type'); ?>
				    </div>
				</div>
				<?if($model->isNewRecord):?>
				<div class="form-actions" style="margin-top: 0px; margin-left: 20px;">
				    <a data-dismiss="modal" onclick="return facyboxClose();" class="btn btn-close" href="/"><i class="icon-14 icon-close-white"></i>Отменить</a>
				    <button class="btn btn-primary" id="form-next-type" onclick="return false;" type="button"><i class="icon-14 icon-ok-sign"></i>Далее</button>
				    <? if(!$model->isNewRecord){?>
				    <button onclick="return delCampaign(<?= $model->id ?>);" class="btn btn-danger right" type="submit"><i class="icon-14 icon-trash"></i>Удалить</button>
				    <? }?>
				</div>
				<?endif;?>
			</div>
			<div id="tabs-2">
				<div>
					<?= $form->errorSummary($model); ?>
					<?
					$val_error_msg = 'Ошибка кампания не сохранена';
				    $val_success_message = ($model->isNewRecord) ? 'Новая кампания создана.' :'Кампания сохранена.';
				  	?>
				  	<div id="success-note" class="notification success png_bg" style="display:none;">
				        <a href="#" class="close"><img src="<?= Yii::app()->request->baseUrl.'/js/ajaxform/images/icons/cross_grey_small.png';  ?>" title="Закрыть" alt="Закрыть"/></a>
				        <div><?=$val_success_message;?></div>
				    </div>
				</div>
				<div id="error-note" class="notification errorshow png_bg" style="display:none;">
			        <a href="#" class="close"><img src="<?= Yii::app()->request->baseUrl.'/js/ajaxform/images/icons/cross_grey_small.png';  ?>" title="Закрыть" alt="Закрыть"/></a>
			        <div><?=$val_error_msg;?></div>
			    </div>
			    <div id="form-fields-common" class="form-fields-common">
			    	<fieldset>
						<div class="row">
							<?= $form->labelEx($model,'name'); ?>
							<?= $form->textField($model,'name',array('size'=>60,'maxlength'=>45, 'style' => 'width: 430px;')); ?>
							<?= $form->error($model,'name'); ?>
						</div>
						<div class="campaign-settings-dateslimit clearfix row">
							<div class="pull-right m21t">
								<?= $form->label($model, 'max_clicks', array('class' => 'inline')); ?>
			                    <?= $form->textField($model,'max_clicks',array('maxlength'=>10, 'class' => 'w40 m5l disabled')); ?>
			                    <?= $form->error($model,'max_clicks'); ?>
							</div>

							<div class="date-wrapper row">
								<?= $form->labelEx($model,'date_start'); ?>
								<?= $form->textField($model,'date_start',array('size'=>60,'maxlength'=>10,'class'=>'input-date','data-date-format'=>'yyyy-mm-dd')); ?>
								<?= $form->error($model,'date_start'); ?>
							</div>
							<div class="date-wrapper row">
								<?= $form->labelEx($model,'date_end'); ?>
								<?= $form->textField($model,'date_end',array('size'=>60,'maxlength'=>10,'class'=>'input-date','data-date-format'=>'yyyy-mm-dd')); ?>
								<?= $form->error($model,'date_end'); ?>
							</div>
						</div>
						<div class="row">
							<?= $form->labelEx($model,'comment'); ?>
							<?= $form->textField($model,'comment',array('size'=>60,'maxlength'=>250, 'style' => 'width: 430px;')); ?>
							<?= $form->error($model,'comment'); ?>
						</div>
			            <div class="row">
			                <div class="pull-right">
			                    <?= $form->label($model, 'day_clicks', array('class' => 'inline')); ?>
			                    <?= $form->textField($model,'day_clicks',array('maxlength'=>10, 'class' => 'w40 m5l')); ?>
			                    <?= $form->error($model,'day_clicks'); ?>
			                </div>
			                <label class="checkbox-wrapper" for="Campaigns_limit_per_day">
			                    <?= $form->checkBox($model,'limit_per_day'); ?>
			                    <?= $model->getAttributeLabel('limit_per_day'); ?>
			                </label>
			            </div>
			            <div class="row">
			                <label class="pull-right">
			                    <input type="checkbox" id="allow_bounce_check" <?= $model->bounce_check != null ? 'checked="checked"' : ''; ?>>
			                    <?= $model->getAttributeLabel('bounce_check'); ?>
			                    <?= $form->textField($model, 'bounce_check', array('class' => 'w40 m5l')); ?>
			                    <?= $form->error($model, 'bounce_check'); ?>
			                </label>
			                <label class="checkbox-wrapper" for="Campaigns_is_active"><?= $form->checkBox($model,'is_active'); ?> Активная</label>
			            </div>
					</fieldset>
			    </div>
			    <div style="display: <?=($model->isNewRecord ? 'none' : ($model->cost_type=='click' || $model->cost_type=='action' ? 'block' : 'none'))?>" class="form-fields-cpa form-fields-cpc">
			    	<!-- begin track.js -->
					<div class="row">
						<?= $form->labelEx($model, 'track_js'); ?>
			            <?= $form->textArea($model, 'track_js',array('style' => 'width:430px; height: 50px; background-color: white;')); ?>
			            <?= $form->error($model, 'track_js'); ?>
			        </div>
			        <!-- end track.js -->
			    </div>
			    <div id="form-fields-rtb" style="display: <?=(!$model->isNewRecord && $model->cost_type=='rtb' ? 'block' : 'none')?>;" class="form-fields-rtb">
			    	<div class="row">
		            	<?= $form->labelEx($model,'rtb_url'); ?>
						<?= $form->textField($model,'rtb_url',array('size'=>60,'maxlength'=>250, 'style' => 'width: 430px;')); ?>
						<?= $form->error($model,'rtb_url'); ?>
		            </div>
		            <div class="row">
		            	<?= $form->labelEx($model,'rtb_cost'); ?>
						<?= $form->textField($model,'rtb_cost',array('size'=>60,'maxlength'=>250)); ?>
						<?= $form->error($model,'rtb_cost'); ?>
		            </div>
		            <div class="row">
			        	<?= $form->labelEx($model, 'rtb_cost_type'); ?>
			            <?= $form->dropDownList($model, 'rtb_cost_type', $model->getAvailableRTBCostTypes()); ?>
			            <?= $form->error($model, 'rtb_cost_type'); ?>
			        </div>
			    </div>
			    <div id="form-fields-geo" class="form-fields-common">
			    	<div id="news-geo-title" class="spacer-10 datatable-title">Настройка ГЕО</div>
			        <?= $form->hiddenField($model, 'countriesIds', array('id' => 'checked_countries', 'value' => '')); ?>
			        <?= $form->hiddenField($model, 'citiesIds', array('id' => 'checked_cities', 'value' => '')); ?>
			        <div class="dataTables_filter" id="news-geo_filter"><label><input type="text" id="geo_search" style="width: 410px;"></label></div>
			        <div id="tree">
			            <? foreach ($countries as $country) { ?>
			                <ul>
			                    <li data-id="<?= $country->id; ?>" data-type="country" <?= in_array($country->id, $model->countriesIds) ? "data-jstree='{\"selected\" : true}'" : "" ?>>
			                        <?= $country->name; ?>
			                        <ul>
			                            <? foreach ($country->cities as $city) { ?>
			                                <li data-id="<?= $city->id; ?>" data-type="city" <?= in_array($city->id, $model->citiesIds) ? "data-jstree='{\"selected\" : true}'" : "" ?>>
			                                    <?= $city->name; ?>
			                                </li>
			       						<? } ?>
			                        </ul>
			                    </li>
			                </ul>
			            <? } ?>
			        </div>
			    </div>
			    <div class="form-actions" style="margin-top: 0px; margin-left: 20px;">
				    <a data-dismiss="modal" onclick="return facyboxClose();" class="btn btn-close" href="/"><i class="icon-14 icon-close-white"></i>Отменить</a>
				    <button class="btn btn-primary" id="form-submit" onclick="return false;" type="button"><i class="icon-14 icon-ok-sign"></i>Coхранить</button>
				    <? if(!$model->isNewRecord){?>
				    <button onclick="return delCampaign(<?= $model->id ?>);" class="btn btn-danger right" type="submit"><i class="icon-14 icon-trash"></i>Удалить</button>
				    <? }?>
				</div>
			</div>
			<div id="tabs-3">
		  	<?if(!$model->isNewRecord):?>
		  		<?if($model->cost_type == Campaigns::COST_TYPE_ACTION):?>
		  		<?$this->renderPartial('form/_actions',array('form' => $form, 'model' => $model));?>
		  		<?elseif($model->cost_type == Campaigns::COST_TYPE_RTB):?>
		  		<?$this->renderPartial('form/_creatives',array('form' => $form, 'model' => $model));?>
		  		<?endif;?>
		  	<?endif;?>
			</div>
		</div>
	</div>
	<? $this->endWidget(); ?>
</div>
<script type="text/javascript">
$(function(){
	$('#tabs').tabs({
		active: <?=($step == 'type' ? '0' : ($step == 'fields' ? '1' : '2'))?>,
		disabled: <?=($step == 'type' ? '[1]' : ($step == 'fields' ? ($model->isNewRecord ? '[0, 2]' : '[0]') : '[0]'))?>
	});

	var activateFields = function(type){
		if(type == '<?=Campaigns::COST_TYPE_CLICK?>'){
			$('.form-fields-cpa').hide();
			$('.form-fields-rtb').hide();
			$('.form-fields-cpc').show();
		} else if(type == '<?=Campaigns::COST_TYPE_ACTION?>') {
			$('.form-fields-cpc').hide();
			$('.form-fields-rtb').hide();
			$('.form-fields-cpa').show();
		} else if(type == '<?=Campaigns::COST_TYPE_RTB?>') {
			$('.form-fields-cpa').hide();
			$('.form-fields-cpc').hide();
			$('.form-fields-rtb').show();
		}

		updateLabels(type);
	};

	var updateLabels = function(type){
		var labels = <?= CJavaScript::encode($model->getAvailableLimitLabels()); ?>;

		console.log(type);

		$('label[for=Campaigns_max_clicks]').text(<?= CJavaScript::encode($model->getAttributeLabel('max_clicks')); ?>
			+ labels[type]);
		$('label[for=Campaigns_day_clicks]').text(<?= CJavaScript::encode($model->getAttributeLabel('day_clicks')); ?>
			+ labels[type]);
	}

	$('#ui-id-2').live('click', function(){
		var _cType = $('#Campaigns_cost_type').val();
		activateFields(_cType);
	});
	
	$('#form-next-type').live('click',function(){
		//show fields tab
		var _cType = $('#Campaigns_cost_type').val();
		activateFields(_cType);
		$('#tabs').tabs('enable', 1);
		$('#tabs').tabs('option', 'active', [1]);
		$(document).scrollTop(0);
        $(".fancybox-wrap").css({'top':'20px', 'bottom':'auto'});
	});

	if ($.fn.datepicker) {
    	$('.input-date').datepicker({'weekStart': 1, 'offset_y':15});
	}
	if ($.fn.selectpicker) {
        $('.selectpicker').selectpicker();
    }

	$('#tree').jstree({
        "core":{
            "themes":{
                "icons":false,
                "dots":false
            }
        },
        "search": {
            "show_only_matches" : true,
            "fuzzy" : false
        },
        "plugins" : [ "checkbox", "search" ]
    });

    var to = false;
    $('#geo_search').keyup(function () {
        if(to) { clearTimeout(to); }
        to = setTimeout(function () {
            var v = $('#geo_search').val();
            $('#tree').jstree(true).search(v);
        }, 250);
    });

	$('#form-submit').click(function(){
		var checked_countries = [];
        var checked_cities = [];
        $.each($('#tree').jstree("get_selected",true), function(i, node){
            if(node.data.type == 'country'){
                checked_countries.push(node.data.id);
            }else{
                checked_cities.push(node.data.id);
            }
        });
        $('#checked_countries').val(checked_countries.join(','));
        $('#checked_cities').val(checked_cities.join(','));

		
        
		$('#campaigns-form').ajaxSubmit({
			'dataType': 'json',
			'error': function(){
				alert('Error occurs while savind data');
			},
			success: function(json){
				if(json.success){
					$.fancybox.close();
				}
			},
            beforeSend: function () {
                $("#modal-body").addClass("ajax-sending");
            },
            complete: function () {
                $("#modal-body").removeClass("ajax-sending");
            },
		});
	});
    
	 $('a.delete-campaign-action').bind('click', function(e){
         var el = $(this);
         $.ajax({
             url: '<?= $this->createUrl('campaignsActions/delete');?>/'+el.data('id'),
             dataType: 'json',
             beforeSend: function () {
                 $("#campaign-actions").addClass("ajax-sending");
             },
             complete: function () {
                 $("#campaign-actions").removeClass("ajax-sending");
             },
             success: function(data){
                 if(data.success == true){
                     el.closest('tr').remove();
                     if($('#campaign-actions-table>tbody>tr').length == 0){
                         $('#campaign-actions-table>tbody').append('<tr><td colspan="4"><div class="text-center">Нет целей.</div></td></tr>');
                     }
                 }
             }
         });
         return false;
     });

     $('#allow_bounce_check').on('change', function(){
         if(this.checked){
             $('#Campaigns_bounce_check').removeAttr('disabled');
         }else{
             $('#Campaigns_bounce_check').attr('disabled', 'disabled');
             $('#Campaigns_bounce_check').val('');
         }
     });

     $('#Campaigns_limit_per_day').on('change', function(){
         if(this.checked){
             $('#Campaigns_day_clicks').removeAttr('disabled');
         }else{
             $('#Campaigns_day_clicks').attr('disabled', 'disabled');
             $('#Campaigns_day_clicks').val('');
         }
     });

     $('#allow_bounce_check').trigger('change');
	 $('#Campaigns_limit_per_day').trigger('change');
	 $('#Campaigns_cost_type').trigger('change');
});
</script>