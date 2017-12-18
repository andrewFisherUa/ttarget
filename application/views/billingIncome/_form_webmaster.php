<div id="modal-billing-update-platform">
	<div class="modal-header">
	    <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>
	    <h3>Запрос на вывод средств</h3>
	</div>
	<div class="modal-body">
	<? 
		$formId='billing-form';
	   	$actionUrl = ($model->isNewRecord)?CController::createUrl('billingIncome/create'):CController::createUrl('billingIncome/update/'.$model->id);
		$form=$this->beginWidget('CActiveForm', array(
	     'id'=>'platforms-form',
	     'action' => $actionUrl,
	     //'enableAjaxValidation'=>true,
	     'enableClientValidation'=>true,
	     'focus'=>array($model,'name'),
	     'errorMessageCssClass' => 'input-notification-error  error-simple png_bg',
	     'clientOptions'=>array('validateOnSubmit'=>true,
            'validateOnType'=>false,
            'errorCssClass' => 'err',
            'successCssClass' => 'suc',
            'afterValidate' => 'js:function(form,data,hasError){ $.js_afterValidate(form,data,hasError); }',
            'afterValidateAttribute' => 'js:function(form, attribute, data, hasError){ $.js_afterValidateAttribute(form, attribute, data, hasError); }'
         ),
	)); ?>
	
		<?= $form->errorSummary($model); ?>
		<?		$val_error_msg = 'Ошибка счёт не сохранен';
	    			$val_success_message = ($model->isNewRecord) ? 'Новый счёт создан.' :'Счёт сохранен.';
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
		<div class="row">
            <?= $form->labelEx($model,'sum'); ?>
            <?= $form->textField($model,'sum'); ?>
            <?= $form->error($model,'sum'); ?>
        </div>
        <div class="row">
            <? if(!empty($this->userData->billing_details_type)) { ?>
                Реквизиты:<br/>
                <?= $this->userData->billing_details_type.": ".$this->userData->billing_details_text; ?>
            <? }else{ ?>
                Реквизиты не указаны.
            <? } ?>
        </div>
        <div class="spacer"></div>
		<div class="row">
			<?= $form->labelEx($model,'comment'); ?>
			<?= $form->textArea($model,'comment',array('rows'=>6, 'cols'=>50)); ?>
			<?= $form->error($model,'comment'); ?>
		</div>
	</fieldset>

	<div class="form-description">* Обязательные поля.</div>
		
        <div class="form-actions">
            <a data-dismiss="modal" onclick="return facyboxClose();" class="btn btn-close" href="/"><i class="icon-14 icon-close-white"></i>Отменить</a>
            <button class="btn btn-primary" type="submit"><i class="icon-14 icon-ok-sign"></i>Сохранить
            </button>
            <? if(!$model->isNewRecord){?>
            <button onclick="return delBillI(<?= $model->id ?>);" class="btn btn-danger right" type="submit"><i class="icon-14 icon-trash"></i>Удалить
            </button>
            <? }?>
        </div>

	    <? $this->endWidget(); ?>
	
	</div><!-- form -->
</div>