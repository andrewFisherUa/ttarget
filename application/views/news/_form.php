<?
/**
 * @var News $model
 * @var Countries[] $countries
 */
?>
<div id="modal-news-settings">
    <div class="modal-header">
        <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>

        <h3><? if ($model->isNewRecord) { ?>Создать новую новость<? } else { ?>Редактирование новости<? } ?></h3>
    </div>
    <div class="modal-body">
        <?
        $formId = 'users-form';
        $actionUrl = ($model->isNewRecord) ? CController::createUrl('news/create') : CController::createUrl('news/update/' . $model->id);
        /** @var CActiveForm $form */
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'news-form',
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
                'afterValidateAttribute' => 'js:function(form, attribute, data, hasError){
	                                                                                                 $.js_afterValidateAttribute(form, attribute, data, hasError);
	                                                                                                                            }'
            ),
        )); ?>

        <?= $form->errorSummary($model); ?>
        <?        $val_error_msg = 'Ошибка новость не сохранена';
        $val_success_message = ($model->isNewRecord) ? 'Новая новость создана.' : 'Новость сохранена.';
        ?>
        <div id="success-note" class="notification success png_bg" style="display:none;">
            <a href="#" class="close"><img
                    src="<?= Yii::app()->request->baseUrl . '/js/ajaxform/images/icons/cross_grey_small.png'; ?>"
                    title="Закрыть" alt="Закрыть"/></a>

            <div>
                <? echo $val_success_message; ?>        </div>
        </div>

        <div id="error-note" class="notification errorshow png_bg"
             style="display:none;">
            <a href="#" class="close"><img
                    src="<?= Yii::app()->request->baseUrl . '/js/ajaxform/images/icons/cross_grey_small.png'; ?>"
                    title="Закрыть" alt="Закрыть"/></a>

            <div>
                <? echo $val_error_msg; ?>        </div>
        </div>

        <div class="row control-group">
            <?= $form->labelEx($model, 'name'); ?>
            <div class="controls">
                <?= $form->textField($model, 'name', array('size' => 60, 'maxlength' => 250)); ?>
                <?= $form->error($model, 'name'); ?>
            </div>
        </div>

        <div class="row control-group">
            <?= $form->labelEx($model, 'description'); ?>
            <div class="controls">
                <?= $form->textField($model, 'description', array('size' => 60, 'maxlength' => 250)); ?>
                <?= $form->error($model, 'description'); ?>
            </div>
        </div>

        <div class="row control-group">
            <?= $form->labelEx($model, 'url'); ?>
            <div class="controls">
                <?= $form->textField($model, 'url', array(
                    'value'=> IDN::decodeUrl($model->url),
                    'size' => 60,
                    'maxlength' => 512
                )); ?>
                <?= $form->error($model, 'url'); ?>
            </div>
        </div>

        <div class="row control-group">
            <?= $form->labelEx($model, 'url_type'); ?>
            <div class="controls">
                <?= $form->dropDownList($model, 'url_type', $model->getAvailableUrlTypes()); ?>
            </div>
        </div>

        <div class="row control-groupw">
            <div class="controls inline">
                <? if ($model->isNewRecord) { ?>
                    <?= $form->hiddenField($model, 'failures', array('size' => 10, 'maxlength' => 10)); ?>
                <? } else { ?>
                    <?= $form->labelEx($model, 'failures'); ?>
                    <?= $form->textField($model, 'failures', array('size' => 3, 'maxlength' => 3)); ?>
                    <?= $form->error($model, 'failures'); ?>
                <? } ?>
            </div>
        </div>
        <div class="row control-group">
            <div class="controls">
                <label for="News_is_active" id="teaser-active-label"><?= $form->checkBox($model, 'is_active'); ?>
                    Активная</label>
            </div>
        </div>
        <?= $form->hiddenField($model, 'campaign_id', array('size' => 10, 'maxlength' => 10)); ?>

        <div class="form-actions">
            <a data-dismiss="modal" onclick="return facyboxClose();" class="btn btn-close" href="/"><i
                    class="icon-14 icon-close-white"></i>Отменить</a>
            <button class="btn btn-primary" type="submit"><i class="icon-14 icon-ok-sign"></i>Сохранить
            </button>
            <? if (!$model->isNewRecord) { ?>
                <button onclick="return delElement(<?= $model->id ?>);" class="btn btn-danger right" type="submit"><i
                        class="icon-14 icon-trash"></i>Удалить
                </button>
            <? } ?>
        </div>

        <? $this->endWidget(); ?>

    </div>
    <!-- form -->
</div>
<script type="text/javascript">
    if ($.fn.selectpicker) {
        $('.selectpicker').selectpicker();
    }

    var delElement = function (id) {
        if (confirm('Удалить новость?')) {
            document.location = "<?php echo Yii::app()->request->baseUrl;?>/news/delete/" + id;
        }

        return false;
    }
    $(".close").click(
        function () {
            $(this).parent().hide();
            return false;
        }
    );
</script>