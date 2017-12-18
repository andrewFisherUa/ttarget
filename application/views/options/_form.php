<div id="modal-platforms-update">
	<div class="modal-header">
	    <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>
	    <h3><? if ($model->isNewRecord){?>Создать новый сегмент<? } else {?>Редактирование сегмента<? }?></h3>
	</div>
	<div class="modal-body">
	<?
		$formId='tags-form';
	   	$actionUrl = ($model->isNewRecord)?CController::createUrl('options/create'):CController::createUrl('options/update/'.$model->id);
        /** @var CActiveForm $form */
		$form=$this->beginWidget('CActiveForm', array(
	     'id'=>'tags-form',
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
		<?		$val_error_msg = 'Ошибка сегмент не сохранен';
	    			$val_success_message = ($model->isNewRecord) ? 'Новый сегмент создан.' :'Сегмент сохранен.';
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


	<div class="row">
		<?= $form->labelEx($model,'name'); ?>
		<?= $form->textField($model,'name',array('size'=>32,'maxlength'=>32)); ?>
		<?= $form->error($model,'name'); ?>
	</div>

    <div class="row">
        <?= $form->checkBox($model, 'is_public'); ?>
        <?= $form->labelEx($model,'is_public', array('class' => 'inline')); ?>
    </div>

	<div class="form-description">* Обязательные поля.</div>
		
		<div class="form-actions">
            <a data-dismiss="modal" onclick="return facyboxClose();" class="btn btn-close" href="/"><i class="icon-14 icon-close-white"></i>Отменить</a>
            <button class="btn btn-primary" type="submit"><i class="icon-14 icon-ok-sign"></i>Сохранить
            </button>
            <? if(!$model->isNewRecord){?>
            <button onclick="return delTag(<?= $model->id ?>);" class="btn btn-danger right" type="submit"><i class="icon-14 icon-trash"></i>Удалить
            </button>
            <? }?>
        </div>
	<? $this->endWidget(); ?>
	
	</div><!-- form -->
	<script type="text/javascript">
		$(".close").click(
	            function () {
	                $(this).parent().hide();
	                return false;
	            }
	    );
	</script>
</div>