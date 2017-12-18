<div id="modal-billing-update-platform">
	<div class="modal-header">
	    <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>
	    <h3>Запрос на вывод средств</h3>
	</div>
	<div class="modal-body">
	<? 
		$formId='billing-form';
	   	$actionUrl = ($model->isNewRecord)?CController::createUrl('billingIncome/createPlatform'):CController::createUrl('billingIncome/update/'.$model->id);
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
	        <div id="html">
	            <?   echo $val_error_msg;  ?>        </div>
	    </div>

	<fieldset>
		<div class="row-fluid">
            <div class="span5">
                <label for="BillingIncome_source_id" class="required">Рекламная площадка <span class="required">*</span></label>
            </div>
            <div class="span5">
                    <?= $form->labelEx($model,'sum'); ?>
            </div>
        </div>
        <div class="row-fluid platforms">
                <div class="span5">
                    <?= $form->dropDownList($model, 'source_id[]', CHtml::listData(Platforms::model()->printable()->findAllByAttributes(array('user_id' => Yii::app()->user->id)), 'id', 'server'), array('class'=>'selectpicker')); ?>
                </div>
                <div class="span5">
                    <?= $form->textField($model,'sum[]'); ?>
                </div>
                <div class="span1">
                    <a class="btn" onclick="addPlatformRow()"><i class="icon-plus"></i> </a>
                </div>
		</div>
        <div class="row-fluid">
            <div class="span5">
                <?= $form->error($model,'source_id'); ?>
            </div>
            <div class="span5">
                <?= $form->error($model,'sum'); ?>
            </div>
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
	<script type="text/javascript">
        var platformsCount = 1;
        var platformsMax = <?= Platforms::model()->countByAttributes(array('user_id' => Yii::app()->user->id)); ?>;
        var row = '<div class="row-fluid platforms">'
            +'<div class="span5">'
            +'<?= CJavaScript::quote($form->dropDownList($model, 'source_id[]', CHtml::listData(Platforms::model()->findAllByAttributes(array('user_id' => Yii::app()->user->id)), 'id', 'server'), array('class'=>'selectpicker'))); ?>'
            +'</div>'
            +'<div class="span5">'
            +'<?= CJavaScript::quote($form->textField($model,'sum[]')); ?>'
            +'</div>'
            +'<div class="span1"><a class="btn" onclick="delPlatformRow(this)"><i class="icon-minus"></i> </a></div>'
            +'</div>';

        var platformId = 1;
        var addPlatformRow = function(){
            if(platformsCount >= platformsMax){
                return false;
            }
            platformsCount++;
            $('.platforms:last').after(
                row.replace('BillingIncome_sum', 'BillingIncome_sum'+platformId)
                    .replace('BillingIncome_source_id', 'BillingIncome_source_id'+platformId)
            );
            $('.platforms:last .selectpicker').selectpicker();

            var set = $('#platforms-form').data('settings');
            set.attributes.push({'errorCssClass': 'err', 'id':'BillingIncome_sum'+platformId,'inputID':'BillingIncome_sum'+platformId,'errorID':'BillingIncome_sum_em_','model':'BillingIncome','name':'sum','enableAjaxValidation':false,'clientValidation':function(value, messages, attribute) {
                if(jQuery.trim(value)=='') {
                    messages.push("\u041d\u0435\u043e\u0431\u0445\u043e\u0434\u0438\u043c\u043e \u0437\u0430\u043f\u043e\u043b\u043d\u0438\u0442\u044c \u043f\u043e\u043b\u0435 \u0421\u0443\u043c\u043c\u0430.");
                }
                if(jQuery.trim(value)!='') {
                    if(!value.match(/^\s*[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/)) {
                        messages.push("\u0421\u0443\u043c\u043c\u0430 \u0434\u043e\u043b\u0436\u0435\u043d \u0431\u044b\u0442\u044c \u0447\u0438\u0441\u043b\u043e\u043c.");
                    }

                }
                if(value<0.01) {
                    messages.push("\u0421\u0443\u043c\u043c\u0430 \u0441\u043b\u0438\u0448\u043a\u043e\u043c \u043c\u0430\u043b (\u041c\u0438\u043d\u0438\u043c\u0443\u043c: 0.01).");
                }
            }});
            $('#platforms-form').data('settings', set);

            platformId++;
        }

        var delPlatformRow = function(el){
            $(el).closest('div.platforms').remove();
            platformsCount--;
        }

    	if ($.fn.selectpicker) {
	        $('.selectpicker').selectpicker();
	    }

        $(function(){
            var old = $.js_afterValidate;
            $.js_afterValidate = function(form, data, hasError){
                if(!hasError){
                    var used = {};
                    var dup = false;
                    var summary = {};
                    var sum = 0;
                    $('.platforms select').each(function(){
                        if(used[$(this).val()] == true){
                            summary.BillingIncome_source_id = ['Для каждой площадки можно добавить только 1 запрос на вывод.'];
                            hasError = true;
                        }
                        used[$(this).val()] = true;
                    });

                    $('.platforms input:text').each(function(){
                        sum += parseFloat($(this).val());
                    });
                    if(sum < <?= Yii::app()->params->PlatformBillingMinimalWithdrawal; ?>){
                        summary.BillingIncome_sum = ['Сумма для вывода по всем площадкам не должна быть меньше <?= Yii::app()->params->PlatformBillingMinimalWithdrawal; ?>'];
                        hasError = true;
                    }
                    if(hasError){
                        $.fn.yiiactiveform.updateSummary(form,summary);
                        return false;
                    }

                }
                old(form, data, hasError);
            }
        });
	</script>
</div>