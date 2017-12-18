<?
/**
 * @var News $model
 * @var Countries[] $countries
 */
?>
<div id="modal-offers-settings" style="">
    <div class="modal-header">
        <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>
        <h3>Заявка на подключение оффера</h3>
    </div>
    <div class="modal-body">
        <?
        $formId = 'offers-form';
        $actionUrl = CController::createUrl('offers/editRequest/' . $model->id);
        /** @var CActiveForm $form */
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'request-form',
            //'htmlOptions' => array('enctype' => 'multipart/form-data'),
            'action' => $actionUrl,
            //'enableAjaxValidation'=>true,
            'enableClientValidation' => true,
            'focus' => array($model, 'name'),
            'errorMessageCssClass' => 'input-notification-error  error-simple png_bg',
            'clientOptions' => array('validateOnSubmit' => true,
                'validateOnType' => false,
                'errorCssClass' => 'err',
                'successCssClass' => 'suc',
                'afterValidate' => 'js:function(form,data,hasError){ $.js_afterValidate(form,data,hasError);  }',
                'errorCssClass' => 'err',
                'successCssClass' => 'suc',
                'afterValidateAttribute' => 'js:function(form, attribute, data, hasError){$.js_afterValidateAttribute(form, attribute, data, hasError);
	                                                                                                                            }'
            ),
        )); ?>
        <?= $form->errorSummary($model); ?>
        <?        $val_error_msg = 'Ошибка заявка не сохранена';
        $val_success_message = 'Заявка сохранена';
        ?>
        <fieldset class="offers_limits_group">
	        <div class="campaign-information-header">
	        <div class="campaign-information-header-row1" style="padding-bottom: 20px;">
            <div style="color:black;font-size:14px; font-weight:bolder; padding-bottom: 20px;">
            Комментарий:
            </div>
	        <?=$model->description?>
	        </div>
	        </div>
	        <div class="row control-group">
	            <div class="date-wrapper row" style="padding-top: 10px;">
	            <label class="pull-left" style="margin-left: 20px;">
	            	<?= $form->labelEx($model,'limits_per_day'); ?>
					<?= $form->textField($model,'limits_per_day',array('size'=>60,'maxlength'=>10,'style'=>'width: 100px;')); ?>
					<?= $form->error($model,'limits_per_day'); ?>
	            </label>
	            <label class="pull-left" style="margin-left: 20px;">
	            	<?= $form->labelEx($model,'limits_total'); ?>
					<?= $form->textField($model,'limits_total',array('size'=>60,'maxlength'=>10,'style'=>'width: 100px;')); ?>
					<?= $form->error($model,'limits_total'); ?>
	            </label>
	            </div>
	        </div>
        
        <div class="form-actions" style="padding-left: 20px;">
            <a data-dismiss="modal" onclick="return facyboxClose();" class="btn btn-close" href="/"><i
                    class="icon-14 icon-close-white"></i>Отменить</a>
            <button class="btn btn-primary" type="submit" onclick="return saveRequest();"><i class="icon-14 icon-ok-sign"></i>Сохранить
            </button>
        </div>
        <? $this->endWidget(); ?>
    </div>
    <!-- form -->
</div>
<script type="text/javascript">

    saveRequest = function(form){
	    var data = {};
		data = $("#request-form").serialize();
		data.YII_CSRF_TOKEN = "<?= Yii::app()->request->csrfToken;?>";
	    $.ajax({
	    	type: "POST",
	        dataType: 'json',
	        url: '<?=$actionUrl?>',
	        data: data,
	        complete: function(json){
	        	$.fancybox.close();
	        	//updateGrid();
	        }
	    });
	
		return false;
	};
	
    $(".close").click(
        function () {
            $(this).parent().hide();
            return false;
        }
    );
</script>