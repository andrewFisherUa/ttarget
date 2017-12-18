<? /**
 * @var Users $model
 */
?>
<div class="modal-header">
    <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>

    <h3><? if ($model->isNewRecord) { ?>Создать нового<? } else { ?>Редактирование<? } ?> пользователя</h3>
</div>
<div class="modal-body">
    <?
    $formId = 'users-form';
    $actionUrl = ($model->isNewRecord) ? CController::createUrl('users/create') : CController::createUrl('users/update/' . $model->id);
    /** @var CActiveForm $form */
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'users-form',
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
    <?        $val_error_msg = 'Ошибка. Пользователь не может быть создан.';
    $val_success_message = ($model->isNewRecord) ? 'Новый пользователь создан.' : 'Пользователь сохранён.';
    ?>
    <div id="success-note" class="notification success png_bg" style="display:none;">
        <a href="#" class="close"><img
                src="<?= Yii::app()->request->baseUrl . '/js/ajaxform/images/icons/cross_grey_small.png'; ?>"
                title="Закрыть" alt="Закрыть"/></a>

        <div>
            <?= $val_success_message; ?>        </div>
    </div>

    <div id="error-note" class="notification errorshow png_bg"
         style="display:none;">
        <a href="#" class="close"><img
                src="<?= Yii::app()->request->baseUrl . '/js/ajaxform/images/icons/cross_grey_small.png'; ?>"
                title="Закрыть" alt="Закрыть"/></a>

        <div>
            <?= $val_error_msg; ?>
        </div>
        <div id="html"></div>
    </div>

    <fieldset>
        <div class="row-fluid">
            <div class="span6">
                <?= $form->labelEx($model,'role'); ?>
                <?= $form->dropDownList(
                    $model,
                    'role',
                    Users::getAvailableRoles(),
                    array(
                        'class' => 'span12',
                        'disabled' => Yii::app()->user->role !== Users::ROLE_ADMIN
                    )
                ); ?>
                <?= $form->error($model,'role'); ?>
            </div>
            <div class="span6">
                <?= $form->labelEx($model, 'email'); ?>
                <?= $form->textField($model, 'email', array('class' => 'span12', 'maxlength' => 128)); ?>
                <?= $form->error($model, 'email'); ?>
            </div>
        </div>

        <div class="row-fluid">
            <?= $form->labelEx($model, 'login'); ?>
            <?= $form->textField($model, 'login', array('class' => 'span12', 'maxlength' => 45)); ?>
            <?= $form->error($model, 'login'); ?>
        </div>

        <div class="row">
            <?= $form->labelEx($model, 'contact_details'); ?>
            <?= $form->textArea($model, 'contact_details', array('class' => 'box', 'maxlength' => 1024)); ?>
            <?= $form->error($model, 'contact_details'); ?>
        </div>

        <div class="row-fluid">
            <div class="span6">
                <?= $form->labelEx($model, 'phone'); ?>
                <? $this->widget('CMaskedTextField', array(
                    'model' => $model,
                    'attribute' => 'phone',
                    'mask' => '7 (999) 999-9999',
                    'htmlOptions' => array('class' => 'span12')
                )); ?>
            </div>
            <div class="span6">
                <?= $form->labelEx($model, 'skype'); ?>
                <?= $form->textField($model, 'skype', array('class' => 'span12', 'maxlength' => 128)); ?>
            </div>
        </div>

        <div class="row">
                <div class="left">
                    <?= $form->labelEx($model, 'billing_details_type'); ?>
                </div>
                <div class="right">
                    <?= $form->checkBox($model,'is_auto_withdrawal'); ?>
                    <?= $form->labelEx($model,'is_auto_withdrawal', array('class' => 'inline')); ?>
                </div>
        </div>

        <div class="row-fluid">
            <div class="span4">
                <?= $form->dropDownList(
                    $model,
                    'billing_details_type',
                    $model->getAvailablePaymentTypes(),
                    array('prompt' => 'Выберите тип реквизитов', 'class' => 'span12')
                ); ?>
            </div>
            <div class="span8">
                <?= $form->textField($model,'billing_details_text', array('class' => 'span12')); ?>
            </div>
        </div>

        <div class="row">
            <?= $form->labelEx($model,'status'); ?>
            <?= $form->dropDownList($model, 'status',
                Users::getAvailableStatuses(),
                array(
                    'visible' => Yii::app()->user->role == Users::ROLE_ADMIN
                )
            ); ?>
        </div>

        <? if( !$model->isNewRecord) {
            echo CHtml::Button('Изменить пароль', array('class' => 'btn', 'onclick' => '$("#Users_password, #Users_repeat_password").val("");$("#passwords").toggle();'));
        } ?>
        <div id="passwords" class="row-fluid" <?= !$model->isNewRecord ? 'style="display:none;"' : '';?>>
            <div class="span6">
                <?= $form->labelEx($model, 'password'); ?>
                <?= $form->passwordField($model, 'password', array('class' => 'span12', 'maxlength' => 40)); ?>
                <?= $form->error($model, 'password'); ?>
            </div>
            <div class="span6">
                <?= $form->labelEx($model, 'repeat_password'); ?>
                <?= $form->passwordField($model, 'repeat_password', array('class' => 'span12', 'maxlength' => 40)); ?>
                <?= $form->error($model, 'repeat_password'); ?>
            </div>
        </div>

        <div class="row">
            <label>Картинка</label>
            <? if (strlen($model->logo) && ($model->logo != 'default.jpg')) { ?>
                <img alt="" src="/i/c/<?= $model->logo; ?>">
            <? } ?>
            <div class="browse-button">
                <div class="btn fake-browse-button"><i class="icon-12 icon-image"></i> Изменить изображение</div>
                <br><?= $form->fileField($model, 'logo', array('size' => 60)); ?>
            </div>
            <div class="form-description">Максимальный размер: <?= Yii::app()->params->userImageWidth ?>
                x <?= Yii::app()->params->userImageHeight ?> px
            </div>
        </div>
        <div class="form-description">* Обязательные поля.</div>
        <div class="form-actions">
            <a data-dismiss="modal" onclick="return facyboxClose();" class="btn btn-close" href="/"><i
                    class="icon-14 icon-close-white"></i>Отменить</a>
            <button class="btn btn-primary" type="submit"><i class="icon-14 icon-ok-sign"></i>Сохранить
            </button>
            <? if (!$model->isNewRecord && Yii::app()->user->role === Users::ROLE_ADMIN) { ?>
                <button onclick="return delElement(<?= $model->id ?>);" class="btn btn-danger right"
                        type="submit"><i class="icon-14 icon-trash"></i>Удалить
                </button>
            <? } ?>
        </div>

    </fieldset>

    <? $this->endWidget(); ?>
</div>
<script type="text/javascript">
    $(".close").click(
        function () {
            $(this).parent().hide();
            return false;
        }
    );
</script>