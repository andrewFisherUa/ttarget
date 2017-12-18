<?
/**
 * @var CActiveForm $form
 * @var Campaigns $model
 * @var CampaignsController $this
 */
?>
<div id="modal-campaign-settings">
	<div class="modal-header">
	    <a href="#" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>
	    <h3><? if ($model->isNewRecord){?>Создать новую кампанию<? } else {?>Редактирование кампании<? }?></h3>
	</div>
	<div class="modal-body">
	<?
        $formId='campaigns-form';
        $actionUrl = ($model->isNewRecord)?CController::createUrl('campaigns/create'):CController::createUrl('campaigns/update/'.$model->id);
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

		<?= $form->errorSummary($model); ?>
		<?		$val_error_msg = 'Ошибка кампания не сохранена';
	    			$val_success_message = ($model->isNewRecord) ? 'Новая кампания создана.' :'Кампания сохранена.';
	  	?>
	  	<div id="success-note" class="notification success png_bg" style="display:none;">
	        <a href="#" class="close"><img
	                src="<?= Yii::app()->request->baseUrl.'/js/ajaxform/images/icons/cross_grey_small.png';  ?>"
	                title="Закрыть" alt="Закрыть"/></a>
	        <div>
	            <?   echo $val_success_message;  ?>        </div>
	    </div>

	    <div id="error-note" class="notification errorshow png_bg"
	         style="display:none;">
	        <a href="#" class="close"><img
	                src="<?= Yii::app()->request->baseUrl.'/js/ajaxform/images/icons/cross_grey_small.png';  ?>"
	                title="Закрыть" alt="Закрыть"/></a>
	        <div>
	            <?   echo $val_error_msg;  ?>        </div>
	    </div>

        <?= $form->hiddenField($model, 'clone_id'); ?>
		<?= $form->hiddenField($model,'client_id'); ?>
		<fieldset>
			<div class="row">
				<?= $form->labelEx($model,'name'); ?>
				<?= $form->textField($model,'name',array('size'=>60,'maxlength'=>45)); ?>
				<?= $form->error($model,'name'); ?>
			</div>
			<div class="campaign-settings-dateslimit clearfix row">
				<label class="pull-right m21t">
                    <?= $model->getAttributeLabel('max_clicks'); ?>:
                    <?= $form->textField($model,'max_clicks',array('maxlength'=>10, 'class' => 'w40 m5l disabled')); ?>
                    <?= $form->error($model,'max_clicks'); ?>
                </label>

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
				<?= $form->textField($model,'comment',array('size'=>60,'maxlength'=>250)); ?>
				<?= $form->error($model,'comment'); ?>
			</div>

            <div class="row">
                <label class="pull-right">
                    <?= $model->getAttributeLabel('day_clicks'); ?>
                    <?= $form->textField($model,'day_clicks',array('maxlength'=>10, 'class' => 'w40 m5l')); ?>
                    <?= $form->error($model,'day_clicks'); ?>
                </label>
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

        <div class="row">
        	<?= $form->labelEx($model, 'cost_type'); ?>
            <?= $form->dropDownList($model, 'cost_type', $model->getAvailableCostTypes()); ?>
            <?= $form->error($model, 'cost_type'); ?>
        </div>
        
        <!-- begin track.js -->
		<div class="row">
			<?= $form->labelEx($model, 'track_js'); ?>
            <?= $form->textArea($model, 'track_js',array('style' => 'width:494px; height: 50px; background-color: white;')); ?>
            <?= $form->error($model, 'track_js'); ?>
        </div>
        <!-- end track.js -->
        
        <div class="row" id="campaign-rtb-block" style="<?= $model->cost_type == Campaigns::COST_TYPE_CLICK ? 'display: none;' : ''; ?>">
        	<div class="spacer-10"></div>
            <div class="row">
            	<?= $form->labelEx($model,'rtb_url'); ?>
				<?= $form->textField($model,'rtb_url',array('size'=>60,'maxlength'=>250, 'style' => 'width: 495px;')); ?>
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
	        <label>Креативы:</label>
            <table id="campaign-creatives-table" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th><?= CampaignsCreatives::model()->getAttributeLabel('name'); ?></th>
                        <th><?= CampaignsCreatives::model()->getAttributeLabel('type'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <? foreach($model->creatives as $creative) : ?>
                        
                    <? endforeach; ?>
                    <? if(!isset($creative)) : ?>
                        <tr>
                            <td colspan="4"><div class="text-center">Нет креативов.</div></td>
                        </tr>
                    <? endif; ?>
                </tbody>
            </table>
            <button id="add-creative" class="btn btn-primary" onclick="return false;"><i class="icon-16 icon-add"></i> Добавить креатив</button>
        </div>

        <div class="row" id="campaign-actions" style="<?= $model->cost_type == Campaigns::COST_TYPE_CLICK ? 'display: none;' : ''; ?>">
            <div class="spacer-10"></div>
            <label><?= $model->getAttributeLabel('campaignsActions'); ?></label>
            <table id="campaign-actions-table" class="table table-striped table-bordered table-hover">
                <thead>
                    <tr>
                        <th><?= CampaignsActions::model()->getAttributeLabel('name'); ?></th>
                        <th><?= CampaignsActions::model()->getAttributeLabel('target_type'); ?></th>
                        <th><?= CampaignsActions::model()->getAttributeLabel('target'); ?></th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <? foreach($model->campaignsActions as $action) : ?>
                        <tr>
                            <td><a href="#" class="break-word edit-action" data-id="<?= $action->id; ?>"><?= CHtml::encode($action->name); ?></a></td>
                            <td><?= CHtml::encode(Arr::ad($action->getAvailableTargetTypes(),$action->target_type)); ?></td>
                            <td><?= CHtml::encode($action->target); ?></td>
                            <td><a href="#" data-id="<?= $action->id; ?>" class="btn btn-danger delete-campaign-action"><i class="icon-14 icon-trash"></i></a></td>
                        </tr>
                    <? endforeach; ?>
                    <? if(!isset($action)) : ?>
                        <tr>
                            <td colspan="4"><div class="text-center">Нет целей.</div></td>
                        </tr>
                    <? endif; ?>
                </tbody>
            </table>
            <button id="add-action" class="btn btn-primary"><i class="icon-16 icon-add"></i> Добавить цель</button>
            <label class="pull-right">
                Установить стоимость на все цели:
                <?= $form->textField($model, 'actions_cost', array('class' => 'span1', 'style' => 'margin-bottom: 5px;' )); ?>
                <?= $form->error($model, 'actions_cost'); ?>
            </label>
        </div>

        <div id="news-geo-title" class="spacer-10 datatable-title">Настройка ГЕО</div>
        <?= $form->hiddenField($model, 'countriesIds', array('id' => 'checked_countries', 'value' => '')); ?>
        <?= $form->hiddenField($model, 'citiesIds', array('id' => 'checked_cities', 'value' => '')); ?>
        <div class="dataTables_filter" id="news-geo_filter"><label><input type="text" id="geo_search"></label></div>
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

		<div class="form-actions">
            <a data-dismiss="modal" onclick="return facyboxClose();" class="btn btn-close" href="/"><i class="icon-14 icon-close-white"></i>Отменить</a>
            <button class="btn btn-primary" type="submit"><i class="icon-14 icon-ok-sign"></i>Сохранить
            </button>
            <? if(!$model->isNewRecord){?>
            <button onclick="return delCampaign(<?= $model->id ?>);" class="btn btn-danger right" type="submit"><i class="icon-14 icon-trash"></i>Удалить
            </button>
            <? }?>
        </div>
	<? $this->endWidget(); ?>

	</div><!-- form -->
	<script type="text/javascript">
        var campaignEditActionId = false;
        var lastAjaxResponse = {};

        var editAction = function(action_id, campaign_id){
            var data = {};
            if(typeof action_id == "undefined"){
                data['campaign_id'] = campaign_id;
            }else{
                data['id'] = action_id;
            }
            $.ajax({
                type: "GET",
                url: '<?= $this->createUrl('campaignsActions/returnForm');?>',
                data: data,
                beforeSend: function () {
                    $("#users-grid").addClass("ajax-sending");
                },
                complete: function () {
                    $("#users-grid").removeClass("ajax-sending");
                },
                success: function (data) {
                    $.fancybox(data,
                        $.extend({}, fancyDefaults, {
                            "width": 543,
                            "minWidth": 543,
                            "afterClose": function () {
                                editCampaign(undefined, $('#CampaignsActions_campaign_id').val());
                            } //onclosed function
                        })
                    );//fancybox
                    //  console.log(data);
                } //success
            });//ajax
        }

        $('#add-action, .edit-action').click(function(e){
            el = $(this);
            if(el.hasClass('edit-action')){
                campaignEditActionId = el.data('id');
            }else{
                campaignEditActionId = true;
            }
            $('#campaigns-form').submit();
            e.stopPropagation();
            e.preventDefault();
            return false;
        });

        $('#Campaigns_cost_type').bind('change', function(e){
            if($('#Campaigns_cost_type').val() == '<?= Campaigns::COST_TYPE_ACTION; ?>'){
                $('#campaign-actions').show();
            }else{
                $('#campaign-actions').hide();
            }

            if($('#Campaigns_cost_type').val() == '<?= Campaigns::COST_TYPE_RTB; ?>'){
            	$('#campaign-rtb-block').show();
            } else {
            	$('#campaign-rtb-block').hide();
            }
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

		$(function(){
            $('#allow_bounce_check').trigger('change');
			$('#Campaigns_limit_per_day').trigger('change');
		});

		$(".close").click(
	            function () {
	                $(this).parent().hide();
	                return false;
	            }
	    );
		if ($.fn.datepicker) {
        	$('.input-date').datepicker({'weekStart': 1, 'offset_y':15, 'dateFormat': 'yyyy-mm-dd'});
    	}

		$('.input-date').datepicker({'weekStart': 1, 'offset_y':15, 'dateFormat': 'yyyy-mm-dd'});
		$('.input-date').datepicker( "option", "dateFormat", "yyyy-mm-dd" );
		
    	if ($.fn.selectpicker) {
	        $('.selectpicker').selectpicker();
	    }

        $(function () {
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

            $('#campaigns-form').on('submit', function(e){
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
            });

            $('#Campaigns_cost_type').trigger('change');

        });
	</script>
</div>