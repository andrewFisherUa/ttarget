<div id="modal-billing-update">
	<div class="modal-header">
	    <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>
	    <h3><? if ($model->isNewRecord){?>Создать новый исходящий счёт<? } else {?>Редактирование нового исходящего счёта<? }?></h3>
	</div>
	<div class="modal-body">
	<?
		$formId='billing-form';
	   	$actionUrl = ($model->isNewRecord)?CController::createUrl('billing/create'):CController::createUrl('billing/update/'.$model->id);
		$form=$this->beginWidget('CActiveForm', array(
	     'id'=>'platforms-form',
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
	        <div>
	            <?   echo $val_error_msg;  ?>        </div>
	    </div>

	    
	<fieldset>
		<div class="row">
			<?= $form->labelEx($model,'number'); ?>
			<?= $form->textField($model,'number'); ?>
			<?= $form->error($model,'number'); ?>
		</div>
		<div class="row">
			<?= $form->labelEx($model,'client_id'); ?>
			<?= $form->dropDownList($model, 'client_id', CHtml::listData(Users::model()->findAll('t.role = "user"'), 'id', 'login'), array('class'=>'selectpicker')); ?>
			<?= $form->error($model,'client_id'); ?>
		</div>
		<div class="form-billing-info clearfix">
			<div class="input-date input-billing-date-set" data-date="01.02.2012" data-date-format="dd.mm.yyyy">
				<div class="row">
	                <label for="input-billing-date-set">Дата выставления</label>
	                <?= $form->textField($model,'issuing_date', array('class'=>'input-date','data-date-format'=>'yyyy-mm-dd')); ?>
	                <?= $form->error($model,'issuing_date'); ?>
                </div>
            </div>

            <div class="input-billing-sum">
            	<div class="row">
	                <label for="input-billing-sum">Сумма</label>
	                <?= $form->textField($model,'sum'); ?>
					<?= $form->error($model,'sum'); ?>
	            </div>
            </div>

            <div class="input-date input-billing-date-paid" data-date="01.02.2012" data-date-format="dd.mm.yyyy">
            	<div class="row">
	                <label for="input-billing-date-paid">Дата оплаты</label>
	                <?= $form->textField($model,'paid_date', array('class'=>'input-date','data-date-format'=>'yyyy-mm-dd')); ?>
	                <?= $form->error($model,'paid_date'); ?>
                </div>
            </div>
		</div>
		<div class="row">
			<?= $form->labelEx($model,'comment'); ?>
			<?= $form->textArea($model,'comment',array('rows'=>6, 'cols'=>50)); ?>
			<?= $form->error($model,'comment'); ?>
		</div>
		<div class="row left">
			<?= $form->checkBox($model,'is_paid'); ?>
			<?= $form->labelEx($model,'is_paid'); ?>
			<?= $form->error($model,'is_paid'); ?>
		</div>
	</fieldset>  

	<div class="form-description">* Обязательные поля.</div>
		
		<div class="form-actions">
            <a data-dismiss="modal" onclick="return facyboxClose();" class="btn btn-close" href="/"><i class="icon-14 icon-close-white"></i>Отменить</a>
            <button class="btn btn-primary" type="submit"><i class="icon-14 icon-ok-sign"></i>Сохранить
            </button>
            <? if(!$model->isNewRecord){?>
            <button onclick="return delBill(<?= $model->id ?>);" class="btn btn-danger right" type="submit"><i class="icon-14 icon-trash"></i>Удалить
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
    	if ($.fn.selectpicker) {
	        $('.selectpicker').selectpicker();
	    }
	    
	    if ($.fn.datepicker) {
        	$('input.input-date').datepicker({'weekStart': 1, 'offset_y':15});
    	}
	    
	</script>
</div>