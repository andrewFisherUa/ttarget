<? /**
 * @var CampaignsActionsController $this
 * @var CActiveForm $form
 * @var CampaignsActions $model
 */ ?>
<!-- <div id="modal-campaign-actions">-->
    <div class="modal-header">
        <!-- <a href="#" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a> -->
        <!-- <h3><?= ($model->getIsNewRecord() ? 'Добавить новую цель' : 'Редактирование цели'); ?></h3> -->
    </div>
    <!--<div class="modal-body"> -->
        <?
        $form=$this->beginWidget('CActiveForm', array(
            'id'=>'campaignsActions-form',
            //'htmlOptions' => array('enctype' => 'multipart/form-data'),
            'action' => $model->getIsNewRecord() ? $this->createUrl('create') : $this->createUrl('update', array('id' => $model->id)),
            //'enableAjaxValidation'=>true,
            'enableClientValidation'=>true,
            'errorMessageCssClass' => 'input-notification-error  error-simple png_bg',
            'clientOptions'=>array('validateOnSubmit'=>true,
                'validateOnSubmit' => true,
                'validateOnType'=>false,
                'errorCssClass' => 'err',
                'successCssClass' => 'suc',
                'afterValidate' => 'js:function(form,data,hasError){ $.js_afterValidate(form,data,hasError);  }',
                'errorCssClass' => 'err',
                'successCssClass' => 'suc',
                'afterValidateAttribute' => 'js:function(form, attribute, data, hasError){$.js_afterValidateAttribute(form, attribute, data, hasError);}'
            ),
        )); ?>

        <? //$form->errorSummary($model); ?>
        <?		$val_error_msg = 'Цель не сохранена.';
        $val_success_message = 'Цель сохранена.';
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
            <div id="html">
                <?   echo $val_error_msg;  ?>        </div>
        </div>

        <fieldset>
            <div class="row-fluid">
                <div class="span9" style="width: 358px;">
                    <?= $form->hiddenField($model, 'campaign_id'); ?>
                    <?= $form->labelEx($model, 'name'); ?>
                    <?= $form->textField($model,'name',array('class' => 'span12;', 'style' => 'width: 340px;')); ?>
                    <?= $form->error($model,'name'); ?>
                </div>

                <div class="span3">
                    <?= $form->labelEx($model, 'cost'); ?>
                    <?= $form->textField($model,'cost',array('class' => 'span12')); ?>
                    <?= $form->error($model,'cost'); ?>
                </div>
            </div>
            <div class="row">
                <?= $form->labelEx($model, 'description'); ?>
                <?= $form->textField($model,'description',array('class' => 'modal-input', 'style' => 'width: 465px;')); ?>
                <?= $form->error($model,'description'); ?>
            </div>
            <div class="row-fluid">
                <div class="span6">
                    <?= $form->labelEx($model, 'target_type'); ?>
                    <?= $form->dropDownList($model,'target_type', $model->getAvailableTargetTypes(),array('class' => 'span12', 'style' => 'width: 150px;')); ?>
                    <?= $form->error($model,'target_type'); ?>
                </div>
                <div class="span6" style="width: 235px;">
                    <?= $form->labelEx($model, 'target_match_type'); ?>
                    <?= $form->dropDownList($model,'target_match_type', $model->getAvailableMatchTypes(CampaignsActions::TARGET_TYPE_URL),array('class' => 'span12')); ?>
                    <?= $form->error($model,'target_match_type'); ?>
                </div>
            </div>
            <div class="row">
                <?= $form->labelEx($model, 'target'); ?>
                <?= $form->textField($model,'target',array('class' => 'modal-input', 'style' => 'width: 465px')); ?>
                <?= $form->error($model,'target'); ?>
            </div>
            <?php if(!$model->getIsNewRecord()): ?>
                EID: <?= $model->getEncryptedId(); ?>
            <?php endif; ?>
        </fieldset>

        <div class="form-actions">
            <a data-dismiss="modal" id="cancel-save-action" onclick="return false;" class="btn btn-close" href="/"><i class="icon-14 icon-close-white"></i>Отменить</a>
            <button class="btn btn-primary" id="save-action" type="submit" onclick="return false;"><i class="icon-14 icon-ok-sign"></i>Сохранить</button>
        </div>
        <? $this->endWidget(); ?>

    <!-- </div> --><!-- form -->
<script type="text/javascript">
$(function(){
	$("#CampaignsActions_target_type").on('change', function(){
        var el = $(this);
        if(el.val() == 'url'){
            $('#CampaignsActions_target_match_type option').each(function(){
                this.disabled = false;
            });
        }else{
            $('#CampaignsActions_target_match_type option').each(function(){
                if(this.value != 'match'){
                    this.disabled = true;
                }else{
                    this.selected = true;
                }
            });
        }
    });

    $('#cancel-save-action').click(function(){
		$('#action-form').hide();
		$('#actions-control-block').show();
    });

    $('#save-action').click(function(){
    	$('#campaignsActions-form').ajaxSubmit({
			dataType: 'json',
			success: function(data){
				if(data.success){
					if(data.update_id){
						if(data.htmlRow.length){
							$('#campaign-action-'+data.id).html($(data.htmlRow));
						};
					} else {
						if(data.htmlRow.length){
                            var r = $(data.htmlRow)
                            $('.edit-action', r).click(editAction);
                            $('.delete-action', r).click(deleteAction);
							$('#campaign-actions-table-tbody').append(r);
						};
					}
					$('#actions-empty-row').remove();
					$('#action-form').hide();
					$('#actions-control-block').show();
				} else {
					//TODO: display error
				}
			}
        });
    });
});
</script>
<!-- </div> -->
