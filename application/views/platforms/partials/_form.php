<?
/**
 * @var Platforms $model
 * @var User $user
 * @var PlatformsCpc $cpc
 * @var CActiveForm $form
 */
?>
<div id="modal-platforms-update">
    <div class="modal-header">
        <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>

        <h3><? if ($model->isNewRecord) { ?>Создать новую рекламную площадку<? } else { ?>Редактирование рекламной площадки<? } ?></h3>
    </div>
    <div class="modal-body">
        <?
        $formId = 'platforms-form';
        $actionUrl = ($model->isNewRecord) ? CController::createUrl('platforms/create') : CController::createUrl('platforms/update/' . $model->id);
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'platforms-form',
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
        <?        $val_error_msg = 'Ошибка площадка не сохранена';
        $val_success_message = ($model->isNewRecord) ? 'Новая площадка создана.' : 'Площадка сохранена.';
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

            <div><?= $val_error_msg; ?></div>
            <div id="html"></div>
        </div>

        <div class="row">
            <?php if( ! $model->isNewRecord ) : ?>
                <div class="right" style="padding-left: 10px;">
                    id <?php echo $model->id; ?>
                </div>
            <?php endif; ?>
            <?= $form->labelEx($model,'server', array('class' => 'left')); ?>
            <? if (!$model->isNewRecord) { ?>
                <div class="right">
                    дата регистрации <?= date('d.m.Y', strtotime($model->created)); ?>
                </div>
            <? } ?>
        </div>

        <div class="row-fluid">
            <?= $form->textField($model, 'server', array('class' => 'span12', 'maxlength' => 250, 'disabled' => (Yii::app()->user->role !== Users::ROLE_ADMIN))); ?>
            <?= $form->error($model, 'server'); ?>
        </div>
        <div class="row-fluid">
            <?= $form->labelEx($model,'url'); ?>
            <?= $form->textField($model, 'url', array('class' => 'span12', 'maxlength' => 2048, 'disabled' => (Yii::app()->user->role !== Users::ROLE_ADMIN))); ?>
            <?= $form->error($model, 'url'); ?>
        </div>
        <div class="row">
            <div class="platforms-update-checkboxes">
                <?php echo $form->checkBox($model,'is_active', array('disabled' => (Yii::app()->user->role !== Users::ROLE_ADMIN))); ?>
                <?php echo $form->labelEx($model,'is_active'); ?>

                <?php if(Yii::app()->user->role === Users::ROLE_ADMIN) : ?>
                    <?php echo $form->checkBox($model,'is_external', array('disabled' => (Yii::app()->user->role !== Users::ROLE_ADMIN))); ?>
                    <?php echo $form->labelEx($model,'is_external'); ?>
                <?php endif; ?>

                <?php echo $form->checkBox($model,'is_vat', array('disabled' => (Yii::app()->user->role !== Users::ROLE_ADMIN))); ?>
                <?php echo $form->labelEx($model,'is_vat'); ?>
                <!--div class="clear"></div-->
            </div>
        </div>
        <div id="clickcost-note" class="notification png_bg" style="display:none;">
        </div>
        <div class="row">
            <div class="left" style="width: 160px;">
                <label for="Platforms_tagIds">Сегменты: </label>
                <?= $form->checkBoxList(
                    $model,
                    'tagIds',
                    CHtml::listData(
                        (Yii::app()->user->role === Users::ROLE_ADMIN ? Tags::model()->findAll() : $model->tags),
                        'id', 'name'
                    ),
                    array(
                        'labelOptions'=>array('class'=>'inline'),
                        'disabled' => Yii::app()->user->role !== Users::ROLE_ADMIN
                    ));
                ?>
            </div>
            <div class="right clickcost">
                <?= $form->labelEx($cpc,'date'); ?>
                <?= $form->textField($cpc, 'date', array(
                    'value' => date('d.m.Y', strtotime($cpc->date)),
                    'class' => 'input-date input150',
                    'data-date-format' => 'dd.mm.yyyy',
                    'disabled' => (Yii::app()->user->role !== Users::ROLE_ADMIN)
                )); ?>
            </div>
            <div class="right clickcost">
                <?= $form->labelEx($model, 'currency'); ?>
                <?= $form->dropDownList($model, 'currency', PlatformsCpc::getCurrencies(), array('class'=>'selectpicker span1 col-1', 'disabled' => (Yii::app()->user->role !== Users::ROLE_ADMIN))); ?>
            </div>
            <div class="right clickcost">
                <?= $form->labelEx($cpc,'cost'); ?>
                <?= $form->textField($cpc,'cost',array('class'=>'span1','maxlength'=>100, 'disabled' => (Yii::app()->user->role !== Users::ROLE_ADMIN))); ?>
            </div>

            <div class="right clickcost">
                <?= $form->labelEx($model,'visits_count'); ?>
                <?= $form->textField($model,'visits_count',array('class'=>'span1','maxlength'=>100, 'disabled' => (Yii::app()->user->role !== Users::ROLE_ADMIN))); ?>
            </div>
            
            <div class="right" style="width: 345px;">
                <?= $form->labelEx($model, 'hosts'); ?>
                <?= $form->textArea($model, 'hosts', array(
                    'value' => $model->getHostsDecoded(),
                    'disabled' => (Yii::app()->user->role !== Users::ROLE_ADMIN))
                ); ?>
                <?= $form->error($model, 'hosts'); ?>
            </div>

        </div>

        <div class="spacer"></div>
        <div class="row">
            <div class="left">
                <?= $form->textField($user,'email', array('placeholder' => 'контактный e-mail', 'class' => 'input150p', 'disabled' => (Yii::app()->user->role !== Users::ROLE_ADMIN))); ?>
            </div>
            <div class="right" style="width: 345px;">
                <?= $form->textField($user,'login', array('placeholder' => 'контактное лицо', 'class' => 'input150p', 'disabled' => (Yii::app()->user->role !== Users::ROLE_ADMIN))); ?>
                <?php if(Yii::app()->user->role === Users::ROLE_ADMIN) : ?>
                        <?php echo $form->textField($user,'password', array('placeholder' => 'пароль', 'class' => 'input150p', 'disabled' => (Yii::app()->user->role === 'platform'))); ?>

                    <div class="right">
                        <a id="generate-password" title="Генерация пароля" class="btn" href="#"><i class="icon-edit"></i> </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div>
                <div class="left">
                    <?= $form->labelEx($user, 'billing_details_type'); ?>
                </div>
                <div class="right">
                    <?php echo $form->checkBox($user,'is_auto_withdrawal'); ?>
                    <?php echo $form->labelEx($user,'is_auto_withdrawal', array('class' => 'inline')); ?>
                </div>
                <div class="clearfix"></div>
                <?= $form->dropDownList($user,'billing_details_type', array(
                    'WEB-money' => 'WEB-money',
                    'Яндекс.Деньги' => 'Яндекс.Деньги',
                    'Банковские реквизиты' => 'Банковские реквизиты',
                    'Другие' => 'Другие',
                ), array('prompt' => 'Выберите тип реквизитов', 'style' => 'width: 160px;')); ?>
                <?= $form->textField($user,'billing_details_text', array('class' => 'right', 'style' => 'width: 329px;')); ?>
            </div>
        </div>
        <div class="form-description">* Обязательные поля.</div>

        <div class="form-actions">
            <a data-dismiss="modal" onclick="return facyboxClose();" class="btn btn-close" href="/"><i
                    class="icon-14 icon-close-white"></i>Отменить</a>
            <button class="btn btn-primary" type="submit"><i class="icon-14 icon-ok-sign"></i>Сохранить
            </button>
            <? if (!$model->isNewRecord) { ?>
                <button onclick="return delPlatform(<?= $model->id ?>);" class="btn btn-danger right"
                        type="submit"><i class="icon-14 icon-trash"></i>Удалить
                </button>
            <? } ?>
        </div>
        <? $this->endWidget(); ?>

    </div>
    <!-- form -->
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
            $('.input-date').datepicker({'weekStart': 1, 'offset_y':15});
        }
        $('#generate-password').click(function() {
            var length = 8,
                charset = "abcdefghijklnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
                retVal = "";
            for (var i = 0, n = charset.length; i < length; ++i) {
                retVal += charset.charAt(Math.floor(Math.random() * n));
            }
            $('#<?= CHtml::activeId($user,'password'); ?>').val(retVal);
            return false;
        });
    </script>

</div>
