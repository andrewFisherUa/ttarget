<?
/**
 * @var Teasers $model
 * @var Platforms[] $platforms
 */
?>
<div id="modal-teaser-settings" class="modal show">

    <div class="modal-header">
        <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>

        <h3><? if ($model->isNewRecord) { ?>Создать новый тизер<? } else { ?>Редактирование тизера<? } ?></h3>
    </div>
    <div class="modal-body">
        <?
        $formId = 'teasers-form';
        $actionUrl = ($model->isNewRecord)
            ? CController::createUrl('teasers/create')
            : CController::createUrl(
                'teasers/update/' . $model->id
            );
        /** @var CActiveForm $form */
        $form = $this->beginWidget(
            'CActiveForm',
            array(
                'id' => 'teasers-form',
                //'htmlOptions' => array('enctype' => 'multipart/form-data'),
                'action' => $actionUrl,
                //'enableAjaxValidation'=>true,
                'enableClientValidation' => true,
                'focus' => array($model, 'title'),
                'errorMessageCssClass' => 'input-notification-error  error-simple png_bg',
                'clientOptions' => array(
                    'validateOnSubmit' => true,
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
            )
        ); ?>
        <?= $form->errorSummary($model); ?>
        <? $val_error_msg = 'Неверная картинка.'; ?>
        <? $val_success_message = ($model->isNewRecord) ? 'Новый тизер создан.' : 'Тизер сохранен.'; ?>
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
                <?= $val_error_msg; ?>        </div>
        </div>
        <div>
            <label>Картинка</label>
            <div id="cropper" style="width: <?= Yii::app()->params->teaserImageWidth; ?>px; height: <?= Yii::app()->params->teaserImageHeight; ?>px; border: 1px solid black; float: left;">
                <? if (strlen($model->picture) && ($model->picture != 'default.jpg')) { ?>
                    <img alt="" src="/i/t/<?= $model->picture; ?>" width="200">
                <? } ?>
            </div>
            <span class="btn fileinput-button">
                <i class="icon-upload"></i>
                <input id="fileupload" type="file" name="file" >
            </span><br/>
            <a href="#" id="urlupload" class="btn"><i class="icon-globe"></i> </a><br/>
            <a href="#" id="crop" class="btn btn-success" style="display: none;"><i class="icon-ok"></i> </a>

            <div class="clearfix"></div>
            <div id="progress" class="progress hide" style="width: <?= Yii::app()->params->teaserImageWidth; ?>px">
                <div class="progress-bar"></div>
            </div>
            <?= $form->hiddenField($model, 'picture'); ?>
        </div>
        <br/>

        <?= $form->labelEx($model, 'title'); ?>
        <?= $form->textField($model, 'title', array('size' => 60, 'maxlength' => 250)); ?>
        <?= $form->error($model, 'title'); ?>

        <?= $form->labelEx($model, 'description'); ?>
        <?= $form->textField($model, 'description', array('size' => 60, 'maxlength' => 75)); ?>
        <?= $form->error($model, 'description'); ?>

        <? if (!$model->isNewRecord) { ?>
            <br/><br/>
            <label for="teaser-link">Ссылка на тизер для платформы
                <?php
                    echo CHtml::openTag('select', array(
                        'class'=>'selectpicker',
                        'data-live-search' => 'true',
                        'data-dropup-auto' => 'false',
                        'id' => 'teaser_platform_id'
                    ));
                    echo CHtml::tag('option', array('value' => ''), 'Автоопределение',true);
                    foreach(Platforms::model()->printable()->findAll() as $platform){
                        echo CHtml::tag('option', array('value' => $platform->getLink()), CHtml::encode($platform->server),true);
                    }
                    echo CHtml::closeTag('select');
                ?>
            </label>
            <div class="" style="word-break: break-all;">
                <?= $model->getEncryptedAbsoluteUrl() ?><span id="platform_link"></span>
            </div><br />
        <? } ?>

        <div class="row">
            <label for="Teasers_tagIds">Сегменты: </label>
            <?= $form->checkBoxList($model, 'tagIds', CHtml::listData(Tags::model()->findAll(), 'id', 'name'), array('labelOptions'=>array('class'=>'inline'))); ?>
        </div>
        <br/>

        <label id="teaser-active-label"><?= $form->checkBox($model, 'is_active'); ?> Активный</label>
        <p>
            <?= $form->checkBox($model, 'is_external'); ?>
            <?= $form->labelEx($model, 'is_external', array('class' => 'inline')); ?>
        </p>
        <div class="datatable-title" id="teaser-forbid-title">Запретить</div>

        <table class="table datatable" id="news-geo">
            <thead>
            <tr>
                <td></td>
            </tr>
            </thead>
            <tbody>
            <tr class="present">
                <td><label><input class="enable-all-button" type="checkbox"> Выбрать все</label></td>
            </tr>

            <?
            echo $form->checkBoxList(
                $model,
                'platformIds',
                CHtml::listData($platforms, 'id', 'server'),
                array('template' => '<tr><td>{input} {label}</td></tr>', 'separator' => '')
            ); ?>
            </tbody>
        </table>

        <div class="form-actions">
            <a data-dismiss="modal" onclick="return facyboxClose();" class="btn btn-close" href="/"><i
                    class="icon-14 icon-close-white"></i>Отменить</a>
            <button class="btn btn-primary" type="submit"><i class="icon-14 icon-ok-sign"></i>Сохранить
            </button>
            <? if (!$model->isNewRecord) { ?>
                <button onclick="return delTeaser(<?= $model->id ?>, <?= $model->news_id ?>);"
                        class="btn btn-danger right" type="submit"><i class="icon-14 icon-trash"></i>Удалить
                </button>
            <? } ?>
        </div>
        <?= $form->hiddenField($model, 'news_id', array('size' => 10, 'maxlength' => 10)); ?>
        <?

        ?>
        <? $this->endWidget(); ?>
    </div>
</div>
<script type="text/javascript">
    var by_tags = <?php
        $byTag = array();
        foreach($platforms as $platform){
            foreach($platform->tags as $tag){
                $byTag[$tag->id][] = $platform->id;
            }
        }
        echo CJSON::encode($byTag);
    ?>;

    $.fn.dataTableExt.afnFiltering = [];
    $('#Teasers_tagIds input').on('change', function(ev){
        $('.enable-all-button').prop('checked', false);
        updatePlatformsList();
    });

    $('#teaser_platform_id').on('change', function(ev){
        $('#platform_link').html($(this).val());
    });

    function updatePlatformsList(){
        $table.$('tr.present').has('td>input').removeClass('present');
        $('#Teasers_tagIds input:checked:not(:disabled)').each(function(i, el){
            el = $(el);
            if(typeof by_tags[el.val()] !== "undefined"){
                $.each(by_tags[el.val()], function(i,platform_id){
                    $table.$('tr').has('input[value='+platform_id+']').addClass('present');
                });
            }
        });
        $table.fnDraw();
    }

    if ($.fn.dataTable) {
        $table = $('#news-geo').dataTable({
            bPaginate: false,
            "sScrollY": "auto",
            bInfo: false,
            bSort: false,
            oLanguage: {
                "sSearch": "",
                "sZeroRecords": "Не найдено подходящих записей"
            }
        });
    }

    $.fn.dataTableExt.afnFiltering.push(function (oSettings, aData, iDataIndex) {
        if ($($table.fnGetNodes(iDataIndex)).hasClass('present')) {
            return true;
        } else return false;
    });

    updatePlatformsList();

    $('.enable-all-button').change(function () {
        var checkboxes = $($(this).parents('table').get(0)).find('input[type=checkbox]:visible');
        if ($(this).is(':checked'))
            $(checkboxes).prop('checked', true);
        else
            $(checkboxes).prop('checked', false);
    });

    $(function(){
        $('#Teasers_is_external').on('change', function(e){
            if(this.checked){
                $('#Teasers_tagIds input').prop('disabled', true);
            }else{
                $('#Teasers_tagIds input').prop('disabled', false);
            }
            updatePlatformsList();
        });
        $('#Teasers_is_external').trigger('change');

        $('#fileupload').fileupload({
            url: "<?= $this->createUrl('image'); ?>",
            dataType: 'json',
            done: function (e, data) {
                if(typeof data.result.file.error !== "undefined"){
                    alert(data.result.file.error);
                }else {
                    $('#progress').hide();
                    cropper.init(data.result.file.url);
                }
            },
            submit:function (e, data) {
                $('#progress .progress-bar').css(
                    'width',
                    0 + '%'
                );
                $('#progress').show();
            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress .progress-bar').css(
                    'width',
                    progress + '%'
                );
            }
        });

        $('#urlupload').on("click", function(e){
            var url;
            if(url = prompt('URL')){
                $.ajax({
                    url: "<?= $this->createUrl('image'); ?>",
                    data: {url: url},
                    dataType: 'json',
                    complete: function () {

                    },
                    success: function (data) {
                        if (typeof data.file.error === "undefined") {
                            cropper.init(data.file.url);
                        } else {
                            alert(data.file.error);
                        }
                    }
                });
            }
        });

        $('#crop').on("click", function(e){
            $.ajax({
                url: "<?= $this->createUrl('image'); ?>",
                data: {fileName: cropper.fileName, crop: cropper.data},
                dataType: 'json',
                success: function(data){
                    if(typeof data.fileName !== "undefined") {
                        $('#Teasers_picture').val(data.fileName);
                        cropper.done('/i/t/' + data.fileName);
                    }else{
                        alert(data.error);
                    }
                }
            });
        });

        var cropper = {
            data: {},
            fileName: '',
            el: $('#cropper'),
            init: function (url) {
                cropper.fileName = url;
                if(cropper.el.data('ZoomCrop')){
                    cropper.el.ZoomCrop('unload');
                }else{
                    cropper.el.empty();
                }
                $('#cropper').ZoomCrop({
                    image: url,
                    updated: cropper.updated
                });
                $('#crop').show();
            },
            updated: function (size, crop, position) {
                console.log('updated', size, crop, position);
                cropper.data = {
                    w: size.width,
                    h: size.height,
                    x: crop.x1,
                    y: crop.y1
                }
            },
            done: function (url){
                $('#crop').hide();
                $('#cropper').ZoomCrop('unload');
                $('#cropper').html('<img src="'+url+'"/>');
            }
        }
    });
</script>