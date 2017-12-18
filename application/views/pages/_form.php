<?
/**
 * @var Pages $model
 * @var PagesController $this
 */
?>
<div id="modal-pages-settings">
    <div class="modal-header">
        <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>

        <h3><? if ($model->isNewRecord) { ?>Создать страницу<? } else { ?>Редактировать страницу<? } ?></h3>
    </div>
    <div class="modal-body">
        <?
        /** @var CActiveForm $form */
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'pages-form',
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
        <?        $val_error_msg = 'Ошибка. Страница не сохранена';
        $val_success_message = ($model->isNewRecord) ? 'Новая страница создана.' : 'Страница сохранена.';
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
                <?php echo $form->labelEx($model,'url'); ?>
                <?php echo $form->textField($model,'url',array(
                    'value' => IDN::decodeUrl($model->url),
                    'class'=>'span12',
                    'maxlength'=>255
                )); ?>
                <?php echo $form->error($model,'url'); ?>
            </div>
        </div>

        <div class="row-fluid control-group">
            <div class="controls">
                <?= $form->hiddenField($model, 'segmentsIds', array('id' => 'checked', 'value' => '')); ?>
                <?php echo $form->labelEx($model,'segments'); ?>
                <div id="tree">
                    <?= Segments::getTreeHtml($model->segmentsIds); ?>
                </div>
            </div>
        </div>


        <div class="form-actions">
            <a data-dismiss="modal" onclick="return facyboxClose();" class="btn btn-close" href="/"><i
                    class="icon-14 icon-close-white"></i>Отменить</a>
            <button class="btn btn-primary" id="formSubmit" type="submit"><i class="icon-14 icon-ok-sign"></i>Сохранить
            </button>
            <? if (!$model->isNewRecord) { ?>
                <button onclick="return delPage(<?= $model->id ?>);" class="btn btn-danger right" type="submit">
                    <i class="icon-14 icon-trash"></i>Удалить
                </button>
            <? } ?>
        </div>

        <? $this->endWidget(); ?>

    </div>
    <!-- form -->
</div>

<script type="text/javascript">
    jQuery(function ($) {
        $('#formSubmit').on('click', function(e){
            var checked = [];
            $.each($('#tree').jstree("get_selected",true), function(i, node){
                checked.push(node.data.id);
            });
            $('#checked').val(checked.join(','));
        });

        $('#tree').jstree({
            "core": {
                "themes": {
                    "icons": false,
                    "dots": false
                }
            },
            "search": {
                "show_only_matches": true,
                "fuzzy": false
            },
            "checkbox" : {
                "three_state": false,
            },
            "plugins": ["checkbox", "search"]
        });

        $('#tree').on('after_open.jstree', function(){
            $.fancybox.update();
        })
    });


</script>