<?
/**
 * @var CampaignsCreatives $model
 * @var Platforms[] $platforms
 */
?>
<div id="modal-campaign-creatives">

    <div class="modal-header">
        <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>
        <h3><?= ($model->getIsNewRecord() ? 'Создать креатив' : 'Изменить креатив'); ?></h3>
    </div>
    <div class="modal-body">
        <?
        /** @var CActiveForm $form */
        $form = $this->beginWidget(
            'CActiveForm',
            array(
                'id' => 'campaignsCreatives-form',
                //'htmlOptions' => array('enctype' => 'multipart/form-data'),
                'action' => $model->getIsNewRecord() ? $this->createUrl('create') : $this->createUrl('update', array('id' => $model->id)),
                //'enableAjaxValidation'=>true,
                'enableClientValidation' => true,
                'focus' => array($model, 'name'),
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
        <?$val_error_msg = 'Креатив не сохранен.';$val_success_message = 'Креатив сохранен.';?>
        <div id="success-note" class="notification success png_bg" style="display:none;">
            <a href="#" class="close"><img
                    src="<?= Yii::app()->request->baseUrl.'/js/ajaxform/images/icons/cross_grey_small.png';  ?>"
                    title="Закрыть" alt="Закрыть"/></a>
            <div><?   echo $val_success_message;  ?></div>
        </div>

        <div id="error-note" class="notification errorshow png_bg"
             style="display:none;">
            <a href="#" class="close"><img src="<?= Yii::app()->request->baseUrl . '/js/ajaxform/images/icons/cross_grey_small.png'; ?>"title="Закрыть" alt="Закрыть"/></a>
            <div><?= $val_error_msg; ?></div>
        </div>
        
        <div class="row-fluid">
            <div class="span9" style="width: 358px;">
                <?= $form->hiddenField($model, 'campaign_id'); ?>
                <?= $form->hiddenField($model, 'id'); ?>
                <?= $form->hiddenField($model, 'count_shows_total'); ?>
                <?= $form->hiddenField($model, 'count_actions_total'); ?>
                <?= $form->labelEx($model, 'name'); ?>
                <?= $form->textField($model,'name',array('class' => 'span12;', 'style' => 'width: 370px;')); ?>
                <?= $form->error($model,'name'); ?>
            </div>
        </div>

        <div class="row-fluid">
            <div class="span6" style="width: 250px;">
                <?= $form->labelEx($model, 'link'); ?>
                <?= $form->textField($model,'link',array('class' => 'span12;', 'style' => 'width: 250px;')); ?>
                <?= $form->error($model,'link'); ?>
            </div>

            <div class="span6" style="width: 30%;">
                <?= $form->labelEx($model, 'cost'); ?>
                <?= $form->textField($model,'cost',array('class' => 'span12')); ?>
                <?= $form->error($model,'cost'); ?>
            </div>
        </div>

        <div class="row-fluid">
            <div class="span6">
            <?= $form->labelEx($model, 'type'); ?>
            <?= $form->dropDownList($model,'type', $model->getAvailableTypes(),array('class' => 'span12', 'style' => 'width: 150px;')); ?>
            <?= $form->error($model,'type'); ?>
            </div>
            <div class="span6" id="sizeContainer">
                <?= $form->labelEx($model, 'size'); ?>
                <?= $form->dropDownList($model,'size', $model->getAvailableSizes(),array('class' => 'span12', 'style' => 'width: 150px;')); ?>
                <?= $form->error($model,'size'); ?>
            </div>
        </div>
        
        <div class="row-fluid">
        <label>Файл</label>
            <span class="btn fileinput-button" style="width: 466px; text-align: left;">
                <i class="icon-upload"></i>
                <input id="fileupload" type="file" name="file" >
            </span><br/>
           	<div class="progress-bar"></div>
           	<?= $form->hiddenField($model, 'filename'); ?>
            <?= $form->hiddenField($model, 'filesize'); ?>
           	<div id="fileupload-info" style="background-color: #ffedaa; display: <?=(!empty($model->filename) ? 'block' : 'none')?>;padding-bottom: 8px;padding-left: 10px;padding-top: 8px;width: 478px;">
           	<?if(!empty($model->filename)):?>
                <?if($model->type == CampaignsCreatives::TYPE_IMAGE) :?>
                    <span style="float: left; width: 95px; padding: 5px;">
                        <a href="/i/creatives/<?= $model->filename; ?>" target="_blank">
                            <img src="/i/creatives/<?= $model->filename; ?>" alt="<?= $model->filename; ?>"/>
                        </a>
                    </span>
                <?endif;?>
           	    <b>File:</b> <i style="font-weight: bolder;"><?=$model->filename?></i>,<br/>
                <b><?=$model->filesize?> bytes</b>
                <div class="clearfix"></div>
           	<?endif;?>
           	</div>
           	<div style="display: none;" id="CampaignsCreatives_filename_em" class="input-notification-error  error-simple png_bg">Необходимо выбрать файл.</div>
        </div>
        
        <div class="row-fluid" style="padding-top: 10px;">
            <div class="span6" style="width: 30%;">
                <?= $form->labelEx($model, 'max_shows_hour'); ?>
                <?= $form->textField($model,'max_shows_hour',array('class' => 'span12')); ?>
                <?= $form->error($model,'max_shows_hour'); ?>
            </div>
            <div class="span6" style="width: 30%;">
                <?= $form->labelEx($model, 'max_shows_day'); ?>
                <?= $form->textField($model,'max_shows_day',array('class' => 'span12')); ?>
                <?= $form->error($model,'max_shows_day'); ?>
            </div>
            <div class="span6" style="width: 30%;">
                <?= $form->labelEx($model, 'max_shows_week'); ?>
                <?= $form->textField($model,'max_shows_week',array('class' => 'span12')); ?>
                <?= $form->error($model,'max_shows_week'); ?>
            </div>
        </div>

        <div class="row">
            <label for="CampaignsCreative_typesIds">Типы: </label>
            <ul style="list-style: none; margin: 0; padding: 0; margin-left: 20px; ">
            <?= $form->checkBoxList($model, 'typesIds', CHtml::listData(CampaignsCreativeTypes::model()->findAll(), 'id', 'name'), array(
                'template'=>'<li style="width: 90%; border: 1px transparent solid;display:inline-block;">{input} {label}</li>',
            	'labelOptions'=>array('class'=>'inline'),
            	'separator'=>'',
                'class' => "CampaignsCreative_typesIds"
            )); ?>
            </ul>
            <div style="display: none;" id="CampaignsCreative_typesIds_em" class="input-notification-error  error-simple png_bg">Необходимо выбрать хотя бы один тип.</div>
        </div>

        <div class="row" >
            <label for="creative_category">Категории: </label>
            <div class="span6" style="width: 90%;">
                <div style="height: 130px; overflow-y: scroll;">
                    <?=
                        $form->checkBoxList($model, 'categoryIds', CHtml::listData(CampaignsCreativeYandexCategory::model()->findAll(), 'id', 'name'), array(
                            'template'=>'<li style="width: 90%; border: 1px transparent solid;display:inline-block;">{input} {label}</li>',
                            'labelOptions'=>array('class'=>'inline'),
                            'separator'=>'',
                            'class' => "CampaignsCreative_categoryIds"
                        )); ?>
                </div>
            </div>
            <div class="clearfix"></div>
            <div style="display: none;" id="CampaignsCreative_categoryIds_em" class="input-notification-error  error-simple png_bg">Необходимо выбрать хотя бы одну категорию.</div>
        </div>

        <div class="row">
            <?= $form->hiddenField($model, 'segmentsIds', array('id' => 'checked', 'value' => '')); ?>
            <?= $form->labelEx($model, 'segments'); ?>
            <div id="tree">
                <?= Segments::getTreeHtml($model->segmentsIds); ?>
            </div>
        </div>

        <br/>

        <label id="teaser-active-label"><?= $form->checkBox($model, 'is_active'); ?> Активный</label>
        <div class="form-actions">
            <a data-dismiss="modal" onclick="return facyboxClose();" class="btn btn-close" href="/"><i
                    class="icon-14 icon-close-white"></i>Отменить</a>
            <button class="btn btn-primary" type="submit" id="save-creative" onclick="return false;"><i class="icon-14 icon-ok-sign"></i>Сохранить
            </button>
            <? if (!$model->isNewRecord) { ?>
                <button id="delete-creative"
                        class="btn btn-danger right" type="submit"><i class="icon-14 icon-trash"></i>Удалить
                </button>
            <? } ?>
        </div>
        <? $this->endWidget(); ?>
    </div>
</div>
<script type="text/javascript">
    jQuery(function ($) {
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
            "plugins": ["checkbox", "search"]
        });
    });

$(function(){
    var hideUploaded = function(){
        $('#fileupload-info').hide();
        $('#CampaignsCreatives_filename').val('');
    }
    $("#CampaignsCreatives_size").on('change', hideUploaded);
	$("#CampaignsCreatives_type").on('change', function(e){
        if($(this).find(':selected').val() == <?= CJavaScript::encode(CampaignsCreatives::TYPE_IMAGE); ?>){
            $("#sizeContainer").show()
        }else{
            $("#sizeContainer").hide()
        }
        hideUploaded();
    });

	$('#cancel-save-action').click(function(){
		$('#action-form').hide();
		$('#actions-control-block').show();
    });

	$('.CampaignsCreative_typesIds').click(function(){
		$('#CampaignsCreative_typesIds_em').hide();
	});
	
    $('#save-creative').click(function(){
        //Check additional fields
        var _hasTypeId = false;
        var _hasCategoryId = false;
        var _error = false;
        $('.CampaignsCreative_typesIds').each(function(i, el){
            if($(el).is(':checked')){
            	_hasTypeId = true;
            }
        });
        $('.CampaignsCreative_categoryIds').each(function(i, el){
            if($(el).is(':checked')){
                _hasCategoryId = true;
            }
        });

        if(!_hasTypeId){
            $('#CampaignsCreative_typesIds_em').show();
            _error = true;
        } else {
        	$('#CampaignsCreative_typesIds_em').hide();
        }

        if(!_hasCategoryId){
            $('#CampaignsCreative_categoryIds_em').show();
            _error = true;
        } else {
            $('#CampaignsCreative_categoryIds_em').hide();
        }

        if($('#CampaignsCreatives_filename').val()){
        	$('#CampaignsCreatives_filename_em').hide();
        } else {
        	$('#CampaignsCreatives_filename_em').show();
        	_error = true;
        }

        if(!_error){
            var checked = [];
            $.each($('#tree').jstree("get_selected",true), function(i, node){
                checked.push(node.data.id);
            });
            $('#checked').val(checked.join(','));

        	$('#campaignsCreatives-form').ajaxSubmit({
    			dataType: 'json',
    			success: function(data){
    				if(typeof data.success != 'undefined'){
    					$.fancybox.close();
    					$.fn.yiiGridView.update('creative-grid');
    				} else {
    					//TODO: display error
    				}
    			}
            });
        }

        return false;
    });

    $('#fileupload').fileupload({
        url: "<?=$this->createUrl('fileupload')?>",
        dataType: 'json',
        done: function (e, data) {
            if(typeof data.result.file.error !== "undefined"){
                alert(data.result.file.error);
            } else {
                $('#progress').hide();
                var _previewHtml = '';
                if(typeof data.result.file.url != 'undefined' && data.result.file.type.substr(0,5) == 'image'){
                    _previewHtml = '<span style="float: left; width: 95px; padding: 5px;">';
                    _previewHtml += '<a href="'+data.result.file.url+'" target="_blank">'
                	_previewHtml += '<img src="'+data.result.file.url+' "alt="'+data.result.file.name+'"/></a>';
                    _previewHtml += '</span>'
                }
                $('#CampaignsCreatives_filename').val(data.result.file.outputFilename);
                $('#CampaignsCreatives_filesize').val(data.result.file.size);
                $('#fileupload-info').html(_previewHtml + '<b>Uploaded:</b> <i style="font-weight: bolder;">' + data.result.file.name + '</i>, <br/> <b>' + data.result.file.size + ' bytes</b>' + '<div class="clearfix"></div>');
                $('#CampaignsCreatives_filename_em').hide();
                $('#fileupload-info').show();
                
            }
        },
        submit:function (e, data) {
            $('#progress .progress-bar').css(
                'width',
                0 + '%'
            );
            $('#fileupload-info').hide();
            $('#progress').show();
        },
        progressall: function (e, data) {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            $('#progress .progress-bar').css(
                'width',
                progress + '%'
            );
        }
    }).bind('click', function (e, data) {
        $(this).data('fileupload').options.url = '<?= $this->createUrl('fileupload'); ?>?type=' + $("#CampaignsCreatives_type").val();

    });

    $('#delete-creative').click(function() {
        var count_shows_total = parseInt($("#CampaignsCreatives_count_shows_total").val());
        var count_actions_total = parseInt($("#CampaignsCreatives_count_actions_total").val());
        if (count_shows_total >=1 || count_actions_total >=2 ) {
            if (count_shows_total >= 1 && count_actions_total == 0) {
                alert("У этого креатива уже есть просмотры. Вы не можете его удалить.");
            } else if (count_shows_total == 0 && count_actions_total >=2 ) {
                alert("У этого креатива уже есть переходы. Вы не можете его удалить.");
            } else if (count_shows_total >=1 && count_actions_total >=2 ) {
                alert("У этого креатива уже есть просмотры и переходы. Вы не можете его удалить.");
            }
        } else {
            var id = parseInt($("#CampaignsCreatives_id").val());
            if (confirm('Удалить креатив?')) {
                $.ajax({
                    type: "POST",
                    url: "<?= Yii::app()->request->baseUrl;?>/campaignsCreatives/delete/" + id,
                    data: {"YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>"},
                    success: function (data) {
                        facyboxClose();
                        $.fn.yiiGridView.update('creative-grid');
                    }
                });//ajax
                return false;
            }
        }

        return false;
    });

});
</script>