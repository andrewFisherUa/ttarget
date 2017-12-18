<?
/**
 * @var News $model
 * @var Countries[] $countries
 */
?>
<div id="modal-offers-settings">
    <div class="modal-header">
        <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>

        <h3><? if ($model->isNewRecord) { ?>Создать предложение<? } else { ?>Редактировать предложение<? } ?></h3>
    </div>
    <div class="modal-body">
        <?
        $formId = 'offers-form';
        $actionUrl = ($model->isNewRecord) ? CController::createUrl('offers/save') : CController::createUrl('offers/save/' . $model->id);
        /** @var CActiveForm $form */
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'offers-form',
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
        <?        $val_error_msg = 'Ошибка предложение не сохранено';
        $val_success_message = ($model->isNewRecord) ? 'Новое предложение создано.' : 'Предложение сохранено.';
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

        <div>
            <label>Изображения</label>
            
            <div id="uploader_container" style="width: 100%; height: 100px; border: 1px solid #A6A6A6;">
                <div id="img_list" style="float: left; width: 100%; height: 100%; position: relative; background-color: #F4F4F4;  overflow: auto;">
                <?if(!empty($model->images)):?>
                <?foreach($model->images as $image):?>
                <span id="offer-image-preview-<?=$image->id?>" class="offer-image-container" id="img_<?=$image->id?>" style="float: left; width: 95px; padding: 5px;">
    			<a id="offer-image-link-<?=$image->id?>" href="#" data-file_id="<?=$image->id?>" data-file_name="<?=$image->filename?>" data-width="<?=$image->width?>" data-height="<?=$image->height?>" class="offer-image-ctrl" onclick="return false;">
				<img src="<?=$image->getUrl()?>" height="90" alt="<?=$image->filename?>"/></a>
				<a href="#" class="del-image-btn" data-image_id="<?=$image->id?>" onclick="deleteImageFromForm('<?=$image->id?>', true); return false;">X</a>
				</span>
				<input id="offer-image-<?=$image->id?>" type="hidden" name="Offers[imageIds][existent][]" value="<?=$image->id?>" />
                <?endforeach;?>
                <?endif;?>
                </div>
                <span style="float: right; position: relative; top: -100px;">
                <span class="btn fileinput-button" style="">
                <i class="icon-upload"></i>
                <input id="fileupload" type="file" name="file" >
	            </span><br/>
	            <a href="#" id="urlupload" class="btn" style="" onclick="return false;"><i class="icon-globe"></i> </a><br/>
	            <a href="#" id="crop" class="btn btn-success" style="display: none;"><i class="icon-ok"></i></a>
	            </span>
            	
            </div>
           	<div class="progress-bar"></div>
        </div>
        
        <div class="row control-group">
        	<div class="controls">
            	<?= $form->labelEx($model, 'name'); ?>
                <?= $form->textField($model, 'name', array('size' => 60, 'maxlength' => 250)); ?>
                <?= $form->error($model, 'name'); ?>
            </div>
            <div class="controls">
            	<?= $form->labelEx($model, 'description'); ?>
                <?= $form->textField($model, 'description', array('size' => 60, 'maxlength' => 250)); ?>
                <?= $form->error($model, 'description'); ?>
            </div>
            <div class="controls">
            	<?= $form->labelEx($model, 'url'); ?>
                <?= $form->textField($model, 'url', array(
                    'value' => IDN::decodeUrl($model->url),
                    'size' => 60,
                    'maxlength' => 512,
                    'style' => "width: 495px;")
                ); ?>
                <?= $form->error($model, 'url'); ?>
            </div>
            <div class="date-wrapper row">
				<?= $form->labelEx($model, 'action_id'); ?>
            	<?= $form->dropDownList($model, 'action_id', CHtml::listData($actions, 'id', 'name'), array('maxlength' => 250)); ?>
            	<?= $form->error($model, 'action_id'); ?>
			</div>
        </div>
        <div class="row control-group">
            <div class="date-wrapper row">
            <label class="pull-left">
            	<?= $form->labelEx($model,'date_start'); ?>
				<?= $form->textField($model,'date_start',array('size'=>60,'maxlength'=>10,'class'=>'input-date date-field','data-date-format'=>'yyyy-mm-dd')); ?>
				<?= $form->error($model,'date_start'); ?>
            </label>
            <label class="pull-left">
            	<?= $form->labelEx($model,'date_end'); ?>
				<?= $form->textField($model,'date_end',array('size'=>60,'maxlength'=>10,'class'=>'input-date date-field','data-date-format'=>'yyyy-mm-dd')); ?>
				<?= $form->error($model,'date_end'); ?>
            </label>
            </div>
        </div>
        
        <div class="row control-group">
            <div class="date-wrapper row">
            <label class="pull-left">
            	<?= $form->labelEx($model,'payment'); ?>
				<?= $form->textField($model,'payment',array('size'=>60,'maxlength'=>10,'class'=>'input-payment date-field')); ?>
				<?= $form->error($model,'payment'); ?>
            </label>
            <label class="pull-left">
            	<?= $form->labelEx($model,'reward'); ?>
				<?= $form->textField($model,'reward',array('size'=>60,'maxlength'=>10,'class'=>'input-payment date-field')); ?>
				<?= $form->error($model,'reward'); ?>
            </label>
            </div>
        </div>
        <div class="row control-group">
        	<div class="controls">
            	<?= $form->labelEx($model, 'cookie_expires'); ?>
                <?= $form->textField($model, 'cookie_expires', array('maxlength'=>10, 'class' => 'limits-field')); ?>
                <?= $form->error($model, 'cookie_expires'); ?>
            </div>
        </div>
        <fieldset class="offers_limits_group">
	        <div class="row control-group">
	        	<label class="pull-left checkbox-wrapper" for="Offers_use_limits" style="padding-top: 5px;"><?= $form->checkBox($model,'use_limits'); ?> Использовать лимиты</label>
	            <label class="pull-right">
	                <?= $model->getAttributeLabel('limits_per_day'); ?>
	                <?= $form->textField($model,'limits_per_day',array('maxlength'=>10, 'class' => 'w40 m5l limits-field')); ?>
	                <?= $form->error($model,'limits_per_day'); ?>
	            </label>
	        </div>
	        <div class="row">
                <label class="pull-right">
                <?= $model->getAttributeLabel('limits_total'); ?>
                <?= $form->textField($model,'limits_total',array('maxlength'=>10, 'class' => 'w40 m5l limits-field')); ?>
                <?= $form->error($model,'limits_total'); ?>
                </label>
            </div>
            <div class="row">
                <label class="pull-right">
                <?= $model->getAttributeLabel('user_limits_per_day'); ?>
                <?= $form->textField($model,'user_limits_per_day',array('maxlength'=>10, 'class' => 'w40 m5l limits-field')); ?>
                <?= $form->error($model,'user_limits_per_day'); ?>
                </label>
            </div>
            <div class="row">
                <label class="pull-right">
                <?= $model->getAttributeLabel('user_limits_total'); ?>
                <?= $form->textField($model,'user_limits_total',array('maxlength'=>10, 'class' => 'w40 m5l limits-field')); ?>
                <?= $form->error($model,'user_limits_total'); ?>
                </label>
            </div>
        </fieldset>
        <div class="row control-group">
            <div class="controls">
                <label for="Offers_unique_ip" id="teaser-unique_ip-label"><?= $form->checkBox($model, 'unique_ip'); ?> Учитывать IP для действий</label>
            </div>
        </div>
        <div class="row control-group">
            <div class="controls">
                <label for="Offers_lead_atatus" id="teaser-lead_status-label"><?= $form->checkBox($model, 'lead_status'); ?> Подтверждать ЛИДы</label>
            </div>
        </div>
        <div class="row control-group">
            <div class="controls">
                <label for="Offers_is_active" id="teaser-active-label"><?= $form->checkBox($model, 'is_active'); ?> Активно</label>
            </div>
        </div>
        <div class="row control-group">
            <div class="controls">
                <div class="dataTables_filter">
                <label for="Offers_wm_filter">Показывать только вебмастерам:</label>
                <select style="width: 515px; height: inherit; line-height: inherit;" id="offers-webmasters-filter-select" name="Offers_wm_filter[]" multiple="multiple">
                	<?foreach($offerWMFilterUsers as $k => $rule):?>
                	<option value="<?=$rule->user->id?>" selected="selected"><?=$rule->user->email?></option>
                	<?endforeach;?>
                </select>
                </div>
            </div>
        </div>
        
        <div id="news-geo-title" class="spacer-10 datatable-title">Настройка ГЕО</div>
        <?= $form->hiddenField($model, 'countriesIds', array('id' => 'checked_countries', 'value' => '')); ?>
        <?= $form->hiddenField($model, 'citiesIds', array('id' => 'checked_cities', 'value' => '')); ?>
        <div class="dataTables_filter" id="news-geo_filter"><label><input type="text" id="geo_search"></label></div>
        <div id="tree">
            <? foreach ($countries as $country) { ?>
                <ul>
                    <li data-id="<?= $country->id; ?>" data-type="country" <?= in_array($country->id, $model->countriesIds) ? "data-jstree='{\"selected\" : true}'" : "" ?>>
                        <?= $country->name; ?>
                        <ul>
                            <? foreach ($country->cities as $city) { ?>
                                <li data-id="<?= $city->id; ?>" data-type="city" <?= in_array($city->id, $model->citiesIds) ? "data-jstree='{\"selected\" : true}'" : "" ?>>
                                    <?= $city->name; ?>
                                </li>
                            <? } ?>
                        </ul>
                    </li>
                </ul>
            <? } ?>
        </div>
        
        <?= $form->hiddenField($model, 'campaign_id', array('size' => 10, 'maxlength' => 10, 'value' => $campaign_id)); ?>

        <div class="form-actions">
            <a data-dismiss="modal" onclick="return facyboxClose();" class="btn btn-close" href="/"><i
                    class="icon-14 icon-close-white"></i>Отменить</a>
            <button class="btn btn-primary" type="submit" onclick="return saveOffer();"><i class="icon-14 icon-ok-sign"></i>Сохранить
            </button>
            <? if (!$model->isNewRecord) { ?>
                <button onclick="return delOffer(<?= $model->id ?>);" class="btn btn-danger right" type="submit"><i class="icon-14 icon-trash"></i>Удалить
                </button>
            <? } ?>
        </div>

        <? $this->endWidget(); ?>

    </div>
    <!-- form -->
</div>
<script type="text/javascript">
$(function(){
	$("#offers-webmasters-filter-select").ajaxChosen({
	    type: 'POST',
	    url: '/offers/wmsearch',
	    dataType: 'json'
	 },{
         loadingImg: 'loading.gif'
     });
});
      
$('#tree').jstree({
    "core":{
        "themes":{
            "icons":false,
            "dots":false
        }
    },
    "search": {
        "show_only_matches" : true,
        "fuzzy" : false
    },
    "plugins" : [ "checkbox", "search" ]
});
	
var to = false;
$('#geo_search').keyup(function () {
    if(to) { clearTimeout(to); }
    to = setTimeout(function () {
        var v = $('#geo_search').val();
        $('#tree').jstree(true).search(v);
    }, 250);
});
        
if ($.fn.datepicker) {
	$('.input-date').datepicker({'weekStart': 1, 'offset_y':15});

}
if ($.fn.selectpicker) {
    $('.selectpicker').selectpicker();
}

$('.offer-image-ctrl').off();
$('.offer-image-ctrl').live('click',function(e){
		
});
    
$('#fileupload').fileupload({
    url: "<?= $this->createUrl('imageUpload'); ?>",
    dataType: 'json',
    done: function (e, data) {
        if(typeof data.result.file.error !== "undefined"){
            alert(data.result.file.error);
        } else {
            $('#progress').hide();
            appendImageToForm(data.result);
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
            url: "<?= $this->createUrl('imageUpload'); ?>",
            data: {url: url},
            dataType: 'json',
            complete: function () {
            },
            success: function (data) {
                if (typeof data.file.error === "undefined") {
                	appendImageToForm(data);
                } else {
                    alert(data.file.error);
                }
            }
        });
    }
});

delOffer = function (id) {
    if (confirm('Удалить предложение?')) {
        $(this).bind('click', function () {
            $.ajax({
                type: "POST",
                dataType: 'json',
                url: "<?= Yii::app()->request->baseUrl;?>/offers/delete/" + id,
                data: {"YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>"},
                beforeSend: function () {
                    $("#news-grid").addClass("ajax-sending");
                },
                complete: function () {
                    $("#news-grid").removeClass("ajax-sending");
                },
                success: function (data) {
                	$.fancybox.close();
                	updateGrid();
                	$('#offers-add').show();
                } //success
            });//ajax
            return false;
        });
    }
    return false;
}

appendImageToForm = function(data){
	var _imgHtml  = '<span id="offer-image-preview-'+data.file.id+'" class="offer-image-container" id="img_" style="float: left; width: 95px; padding: 5px;">';
		_imgHtml += '<a id="aa" href="#" data-file_id="'+data.file.id+'" data-file_name="'+data.file.name+'" data-width="'+data.file.width+'" data-height="'+data.file.height+'" class="offer-image-ctrl" onclick="return false;">';
		_imgHtml += '<img src="'+data.file.url+'" height="90" alt="'+data.file.name+'"/>';
		_imgHtml += '</a>';
		_imgHtml += '<a href="#" class="del-image-btn" data-image_id="'+data.file.id+'" onclick="deleteImageFromForm(\''+data.file.id+'\'); return false;">X</a>';
		_imgHtml += '</span>';
		_imgHtml += '<input id="offer-image-'+data.file.id+'" type="hidden" name="Offers[imageIds][upload][]" value="'+data.file.id+'" />';
	$('#img_list').append($(_imgHtml));
};
deleteImageFromForm = function(id, deleteOnServer ){
	$('#offer-image-preview-'+id).remove();
	$('#offer-image-'+id).remove();
	if(deleteOnServer == true){
		$('#img_list').append('<input type="hidden" name="Offers[imageIds][delete][]" value="'+id+'"/>');
    }
};
    
saveOffer = function(form){
	var checked_countries = [];
    var checked_cities = [];
    $.each($('#tree').jstree("get_selected",true), function(i, node){
        if(node.data.type == 'country'){
            checked_countries.push(node.data.id);
        }else{
            checked_cities.push(node.data.id);
        }
    });
    $('#checked_countries').val(checked_countries.join(','));
    $('#checked_cities').val(checked_cities.join(','));

    var data = {};
	data = $("#offers-form").serialize();
	data.YII_CSRF_TOKEN = "<?= Yii::app()->request->csrfToken;?>";
    	
    $.ajax({
    	type: "POST",
        dataType: "json",
        url: '<?=$actionUrl?>',
        data: data,
        success: function(json){
			if(json.success){
				$.fancybox.close();
            	updateGrid();
            	if(json.hasAvailableActions){
            		$('#offers-add').show();
                } else {
                	$('#offers-add').hide();
            	}
			} else {
				
			}
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