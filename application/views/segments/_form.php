<?
/**
 * @var Segments $model
 * @var SegmentsController $this
 */
?>
<div id="modal-segments-settings">
    <div class="modal-header">
        <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>

        <h3><? if ($model->isNewRecord) { ?>Создать сегмент<? } else { ?>Редактировать сегмент<? } ?></h3>
    </div>
    <div class="modal-body">
        <?
        /** @var CActiveForm $form */
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'segments-form',
            //'htmlOptions' => array('enctype' => 'multipart/form-data'),
            'action' => $this->createUrl('save', array('id' => $model->id)),
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
        <?        $val_error_msg = 'Ошибка. Сегмент не сохранен';
        $val_success_message = ($model->isNewRecord) ? 'Новый сегмент создан.' : 'Сегмент сохранен.';
        ?>
        <div id="success-note" class="notification success png_bg" style="display:none;">
            <a href="#" class="close">
                <img src="<?= Yii::app()->request->baseUrl . '/js/ajaxform/images/icons/cross_grey_small.png'; ?>"
                     title="Закрыть" alt="Закрыть"/>
            </a>
            <div><? echo $val_success_message; ?></div>
        </div>

        <div id="error-note" class="notification errorshow png_bg" style="display:none;">
            <a href="#" class="close">
                <img src="<?= Yii::app()->request->baseUrl . '/js/ajaxform/images/icons/cross_grey_small.png'; ?>"
                     title="Закрыть" alt="Закрыть"/>
            </a>
            <div id="html"><? echo $val_error_msg; ?></div>
        </div>

        <div class="row-fluid control-group">
            <div class="controls">
                <?= $form->labelEx($model, 'parent_id'); ?>
                <?= $form->dropDownList($model, 'parent_id',
                    CHtml::listData($model->getOrderedSegments(), 'id', 'paddedName'),
                    array(
                        'class' => 'span12 form-control selectpick',
                        'data-live-search' => 'true',
                        'data-dropup-auto' => 'false',
                        'data-container' => 'body',
                        'empty' => '__Корневой сегмент__'
                    )
                ); ?>
                <?= $form->error($model, 'teaser_id'); ?>
            </div>
            <div class="controls">
                <?php echo $form->labelEx($model,'name'); ?>
                <?php echo $form->textField($model,'name',array('class'=>'span12','maxlength'=>255)); ?>
                <?php echo $form->error($model,'name'); ?>
            </div>
        </div>

        <div class="form-actions">
            <a data-dismiss="modal" onclick="return facyboxClose();" class="btn btn-close" href="/"><i
                    class="icon-14 icon-close-white"></i>Отменить</a>
            <button class="btn btn-primary" type="submit"><i class="icon-14 icon-ok-sign"></i>Сохранить
            </button>
            <? if (!$model->isNewRecord) { ?>
                <button onclick="return delSegment(<?= $model->id ?>);" class="btn btn-danger right" type="submit">
                    <i class="icon-14 icon-trash"></i>Удалить
                </button>
            <? } ?>
        </div>

        <? $this->endWidget(); ?>

    </div>
    <!-- form -->
</div>