<?php
/**
 * @var Blocks $model
 * @var CController $this
 * @var CActiveForm $form
 * @var Platforms[] $platforms
 * @var ClientCode $clientCode
 */
Yii::app()->clientScript->registerCoreScript('jquery');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.css', 'screen');
//Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/js/ajaxform/client_val_form.css','screen');

if(Yii::app()->user->role == Users::ROLE_ADMIN){
    $this->renderPartial('tabs');
}
?>
<div class="row">
    <? $form=$this->beginWidget('CActiveForm', array(
        'id'=>'blocks-form',
        'action' => '',
    )); ?>

    <div class="span6">
        <div class="row">
            <h3>
                <?= $model->getIsNewRecord() ? 'Создание' : 'Редактирование'; ?> рекламного блока
            </h3>
        </div>
        <div class="errorSummary">
            <?= $form->errorSummary($model); ?>
        </div>
        <div class="row">
            <?= $form->label($model, 'name'); ?>
            <?= $form->textField($model, 'name', array('class' => 'span4')); ?>
        </div>
        <div class="row">
            <?= $form->label($model, 'platform_id'); ?>
            <?= $form->dropDownList($model, 'platform_id',
                CHtml::listData($platforms, 'id', 'server')
            ); ?>
        </div>
        <div class="row">
            <?= $form->label($model, 'size'); ?>
            <?= $form->dropDownList($model, 'size', $model->getAvailableSizes(), array('onchange' => 'blockSizeChanged(); updateBlockCode();')); ?>
        </div>
        <div id="Blocks_custom_size" class="row <?= $model->size == 'custom' ? '' : 'hide'; ?>">
            <?= $form->textField($model, 'custom_horizontal_size', array('class' => 'span1', 'onchange' => 'blockCustomSizeChanged(this); updateBlockCode();')); ?>
            x <?= $form->textField($model, 'custom_vertical_size', array('class' => 'span1', 'onchange' => 'blockCustomSizeChanged(this); updateBlockCode();')); ?>
        </div>
        <div class="row">
            <label>Количество тизеров</label>
            <div class="span3">
                по горизонтали <?= $form->textField($model, 'horizontal_count', array('class' => 'span1', 'onchange' => 'blockChanged(this, 1, null, true)')); ?>
            </div>
            <div class="span3">
                по вертикали <?= $form->textField($model, 'vertical_count', array('class' => 'span1', 'onchange' => 'blockChanged(this, 1, null, true)')); ?>
            </div>
        </div>
        <div class="row">
            <?= $form->label($model, 'header_align'); ?>
            <?= $form->dropDownList($model, 'header_align', $model->getAvailableHeaderAligns(), array('onchange' => 'render();')); ?>
        </div>
        <div class="row">
            <?= $form->label($model, 'font_name'); ?>
            <?= $form->dropDownList($model, 'font_name', $model->getAvailableFontNames(), array('onchange' => 'render();')); ?>
        </div>
        <div class="row">
            <?= $form->label($model, 'font_size'); ?>
            <?= $form->dropDownList($model, 'font_size', $model->getAvailableFontSizes(), array('onchange' => 'render();', 'class' => 'span2')); ?>
        </div>
        <div class="row">
            <?= $form->label($model, 'font_color'); ?>
            <?= $form->textField($model, 'font_color', array('onchange' => 'render();', 'class' => 'span2')); ?>
        </div>
        <div class="row">
            <?= $form->label($model, 'image_size'); ?>
            <?= $form->dropDownList($model, 'image_size', $model->getAvailableImageSizes(), array('onchange' => 'render();', 'class' => 'span2')); ?>
        </div>
        <div class="row">
            <?= $form->label($model, 'external_border_width'); ?>
            <?= $form->textField($model, 'external_border_width', array('class' => 'span1', 'onchange' => 'blockChanged(this, 0, 1000, true);')); ?>
            <?= $form->textField($model, 'external_border_color', array('class' => 'span2', 'onchange' => 'render();')); ?>
        </div>
        <div class="row">
            <?= $form->label($model, 'internal_border_width'); ?>
            <?= $form->textField($model, 'internal_border_width', array('class' => 'span1', 'onchange' => 'blockChanged(this, 0, getMaxInternalBorder(), true);')); ?>
            <?= $form->textField($model, 'internal_border_color', array('class' => 'span2', 'onchange' => 'render();')); ?>
        </div>
        <div class="row m12t">
            <?= $form->checkBox($model, 'header', array('onchange' => 'render(); updateBlockCode();')); ?>
            <?= $form->label($model, 'header', array('class' => 'inline')); ?>
        </div>
        <div class="row m12t">
            <?= $form->checkBox($model, 'use_client_code'); ?>
            <?= $form->labelEx($model, 'use_client_code', array('class' => 'inline')); ?>
            <div id="clientCode">
            </div>
            <? $this->renderPartial('partials/clientCodeScripts', array('block' => $model)); ?>
        </div>
        <div class="row m12t" id="Blocks_controls">
            <? if(!$model->getIsNewRecord()) : ?>
                <a onclick="return confirm('Удалить блок?');" class="btn btn-danger" href="<?= $this->createUrl('deleteBlock', array('id' => $model->id)); ?>">
                    <i class="icon-14 icon-trash"></i>Удалить
                </a>
            <? endif; ?>
            <button class="btn btn-primary" type="submit"><i class="icon-14 icon-ok-sign"></i>Сохранить</button>
        </div>
    </div>
    <div class="span6">
        <div class="row">
            <h3>Пред просмотр</h3>
            <style type="text/css">
                #ttarget_div, #ttarget_div_title{
                    margin-right: auto;
                    margin-left: auto;
                }
            </style>
            <div id="preview_container" style="background-color: #e8e8e0; padding: 10px; float: left; min-width: 470px;">
                <style type="text/css">
                </style>
                <div id="ttarget_div_title"><div>Новости Ttarget</div></div>
                <div id="ttarget_div"></div>
            </div>
            <div id="preview_teasers" style="display: none;">
                <div><a href="http://tt.ttarget.ru/" target="_blank"><img src="/i/t/notfound.png"><b>Пройди тест онлайн- узнай свою готовность к ЕГЭ</b><br/><small>описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани </small></a></div>
                <div><a href="http://tt.ttarget.ru/" target="_blank"><img src="/i/t/notfound.png"><b>Внимание, выпускники: информация о ЕГЭ по математике</b><br/><small>описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани </small></a></div>
                <div><a href="http://tt.ttarget.ru/" target="_blank"><img src="/i/t/notfound.png"><b>Пройди тест онлайн и узнай свою готовность к ЕГЭ</b><br/><small>описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани </small></a></div>
                <div><a href="http://tt.ttarget.ru/" target="_blank"><img src="/i/t/notfound.png"><b>Известные модели анорексички</b><br/><small>описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани </small></a></div>
                <div><a href="http://tt.ttarget.ru/" target="_blank"><img src="/i/t/notfound.png"><b>Как отдохнуть и «не вылететь в трубу»?</b><br/><small>описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани </small></a></div>
                <div><a href="http://tt.ttarget.ru/" target="_blank"><img src="/i/t/notfound.png"><b>Секреты быстрого и безопасного похудения</b><br/><small>описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани </small></a></div>
                <div><a href="http://tt.ttarget.ru/" target="_blank"><img src="/i/t/notfound.png"><b>Внимание, выпускники: информация о ЕГЭ по математике</b><br/><small>описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани </small></a></div>
                <div><a href="http://tt.ttarget.ru/" target="_blank"><img src="/i/t/notfound.png"><b>Сочетание каких алкогольных напитков способно "убить"</b><br/><small>описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани </small></a></div>
                <div><a href="http://tt.ttarget.ru/" target="_blank"><img src="/i/t/notfound.png"><b>Рецепты правильного похудения к лету и никакого кофе!</b><br/><small>описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани описание оп опи опис описа описан описани </small></a></div>
            </div>
        </div>

        <div class="row">
            <h3>CSS-шаблон</h3>
            <?= $form->textArea($model, 'css', array(
                'onchange' => 'cssChanged();',
                'id' => 'cssCode',
                'style' => 'height: 160px; width: 470px;'
            )); ?>
            <p class="text-center"><small>Шаблон можно менять по своему усмотрению. Проверка не осуществляется. %BLOCK_ID% заменяется на динамически генерируемый id блока тизеров. </small></p>
        </div>
    </div>

    <? $this->endWidget(); ?>
</div>
<div id="htmlContainer" class="row hide">
    <h3>Код для установки:<h3>
    <textarea style="width: 98%;" id="htmlCode"></textarea>
</div>
<div class="row" style="margin-top: 70px;">
    <h4>
    <? foreach(Blocks::model()->available()->findAll() as $block) : ?>
        <?= CHtml::link(
            $block->name.(Yii::app()->user->role == Users::ROLE_ADMIN ? ' ('.$block->platform->server.')' : ''),
            $this->createUrl('constructor', array('id' => $block->id)),
            array(
                'class' => 'dashed'
            )
        ); ?><br/>
    <? endforeach; ?>
    </h4>
</div>
<script type="text/javascript">
    var debug = false;
    var itemImageWidth = 0.45;
    var templates = <?= CJSON::encode($model->getTemplates()); ?>;
    var baseStyle = {
        "#%BLOCK_ID%_title": "",
        "#%BLOCK_ID%_title div":
             "    color: white;\n"
            +"    font: bold 15px Tahoma, Geneva, Arial, Helvetica, sans-serif;\n"
            +"    padding: 3px 9px 3px 9px;\n"
            +"    letter-spacing: 0.6pt;\n"
            +"    text-transform: uppercase;\n"
            +"    background: #788F88;\n"
            +"    background: -webkit-gradient(linear, left top, left bottom, color-stop(0, #788F88), color-stop(0.7, #5C646D));\n"
            +"    background: -moz-linear-gradient(center top, #788F88 0%, #5C646D 70%);\n"
            +"    background: -webkit-linear-gradient(top, #788F88 0%, #5C646D 70%);\n"
            +"    background: -o-linear-gradient(top, #788F88 0%, #5C646D 70%);\n"
            +"    background: linear-gradient(top, #788F88 0%, #5C646D 70%);\n"
            +"    background: -ms-linear-gradient(top, #788F88 0%, #5C646D 70%);\n"
            +"    -pie-background: linear-gradient(#788F88, #5C646D);\n"
            +"    text-shadow: 1px 2px 3px #383D43;\n",
        "#%BLOCK_ID%":
            "     clear: both;\n"
            +"    overflow: hidden;\n",
        "#%BLOCK_ID% div":
            "    display: inline-block;\n"
            +"    float: left;\n"
            +"    background-color:#ffffff;\n"
            +"    overflow: hidden;\n",
        "#%BLOCK_ID% img":
            "    float: left;\n"
            +"    margin-right: 2px;\n",
        "#%BLOCK_ID% div a":
            "    text-decoration: none;\n"
            +"    line-height: normal;\n"
    };

    function blockSizeChanged(rerender, recalc){
        var blockSize = $('#Blocks_size').val();
        if(rerender == undefined) rerender = true;
        if(recalc == undefined) recalc = true;
        if(blockSize != 'custom'){
            $('#Blocks_image_size option').removeAttr('disabled');
            $('#Blocks_horizontal_count, #Blocks_vertical_count, #Blocks_image_size').attr('disabled', 'disabled');
            $('#Blocks_custom_size').hide();
            var blockTemplate = templates[blockSize];
            selectImageSize(blockTemplate.imageSize);
            $('#Blocks_horizontal_count').val(blockTemplate.hCount);
            $('#Blocks_vertical_count').val(blockTemplate.vCount);
        }else{
            $('#Blocks_horizontal_count, #Blocks_vertical_count, #Blocks_image_size').removeAttr('disabled');
            $('#Blocks_custom_size').show();
            blockCustomSizeChanged(undefined, false, recalc);
        }

        if(rerender) render();
    }

    function blockCustomSizeChanged(el, rerender, recalc){
        if(typeof el != "undefined") {
            el.value = parseInt(el.value);
            if(el.id == "Blocks_custom_horizontal_size"){
                if(el.value < 120){
                    el.value = 120;
                }
            }else if(el.value < 50){
                el.value = 50;
            }
        }
        if(rerender == undefined) rerender = true;
        if(recalc == undefined) recalc = true;

        var blockDim = getBlockDim();
        var imageSize = getMaxImageSize(blockDim);
        filterImageSize(imageSize);
        if(recalc){
            selectImageSize(imageSize);
            $('#Blocks_horizontal_count').val(getHorizontalAutoItemsCount(blockDim, imageSize));
            $('#Blocks_vertical_count').val(getVerticalAutoItemsCount(blockDim, imageSize));
        }
        if(rerender) render();
    }

    function blockChanged(el, minValue, maxValue, rerender){
        var value = parseInt(el.value);
        debug && console.log('block changed', value, maxValue);
        if(isNaN(value) || value < minValue){
            value = minValue;
        }else if(maxValue != null && value > maxValue){
            value=maxValue;
        }
        el.value = value;
        if(rerender){
            render();
        }
    }

    function getBlockDim(){
        var blockDim;
        if($('#Blocks_size').val() == 'custom'){
            blockDim = [$('#Blocks_custom_horizontal_size').val(), $('#Blocks_custom_vertical_size').val()];
        }else{
            blockDim = $('#Blocks_size').val().split('x');
        }
        blockDim[0] = parseInt(blockDim[0]);
        blockDim[0] = parseInt(blockDim[0]);
        return blockDim;
    }

    function getImageDim(imageDim){
        if(typeof imageDim == "undefined"){
            imageDim = $('#Blocks_image_size').val()
        }
        imageDim = imageDim.split('x');
        imageDim[0] = parseInt(imageDim[0]);
        imageDim[1] = parseInt(imageDim[1]);
        return imageDim;
    }

    function render()
    {
        var style = $.extend({}, baseStyle);
        var blockDim = getBlockDim();
        var imageDim = getImageDim();
        var externalBorderWidth = parseInt($('#Blocks_external_border_width').val());
        var internalBorderWidth = parseInt($('#Blocks_internal_border_width').val());
        var maxInternalBorder = getMaxInternalBorder();
        if(internalBorderWidth > maxInternalBorder){
            internalBorderWidth = maxInternalBorder;
            $('#Blocks_internal_border_width').trigger('change');
        }
        var verticalPadding = getVerticalPadding(blockDim, imageDim, internalBorderWidth);
        var headerAlign = $('#Blocks_header_align').val();

        var itemWidth = getItemWidth(blockDim, internalBorderWidth);
        var itemHeight = getItemHeight(blockDim, imageDim, verticalPadding, internalBorderWidth);

        style['#%BLOCK_ID%'] +=
            "    width: "+blockDim[0]+"px;\n"
            +"    height: "+blockDim[1]+"px;\n";
        style['#%BLOCK_ID% div'] +=
            "    border-bottom: "+internalBorderWidth+"px solid "+$('#Blocks_internal_border_color').val()+";\n"
            +"    border-right: "+internalBorderWidth+"px solid "+$('#Blocks_internal_border_color').val()+";\n"
            +"    padding: "+(verticalPadding/2)+"px 5px; \n"
            +"    width: "+itemWidth+"px; \n"
            +"    height: "+itemHeight+"px; \n"
            +"    -webkit-column-width: "+itemWidth+"px; \n"
            +"    -moz-column-width: "+itemWidth+"px; \n"
            +"    column-width: "+itemWidth+"px; \n";
        style['#%BLOCK_ID% img'] +=
            "    width: "+imageDim[0]+"px;\n"
            +"    height: "+imageDim[1]+"px; \n";
        style['#%BLOCK_ID% div a'] +=
            "    color: "+$('#Blocks_font_color').val()+";\n"
            +"    font-size: "+$('#Blocks_font_size').val()+";\n";
        if($('#Blocks_font_name').val() != ""){
            style['#%BLOCK_ID% div a'] += "    font-family: "+$('#Blocks_font_name').val()+";\n";
        }
        if(externalBorderWidth > 0){
            style['#%BLOCK_ID%'] += '    border: '+externalBorderWidth+'px solid '+$('#Blocks_external_border_color').val()+";\n"
        }
        if($('#Blocks_header')[0].checked){
            $('#ttarget_div_title').show();
            style['#%BLOCK_ID%_title'] +=
                "    width: "+(blockDim[0]+(externalBorderWidth * 2))+"px;\n";
            style['#%BLOCK_ID%_title div'] +=
                "    text-align: "+headerAlign+";\n";
            if(headerAlign != 'center'){
                style['#%BLOCK_ID%_title div'] +=
                    "    float: "+headerAlign+";\n";
            }
        }else{
            $('#ttarget_div_title').hide();
            delete style['#%BLOCK_ID%_title'];
            delete style['#%BLOCK_ID%_title div'];
        }
        //border workaround for browsers without subpixel rendering
        style["#%BLOCK_ID% div:nth-last-child(-n+"+$('#Blocks_horizontal_count').val()+")"] =
            "    height: "+(itemHeight+1)+"px; \n";
        //remove right border
        if($('#Blocks_horizontal_count').val() > 1){
            style["#%BLOCK_ID% div:nth-child("+$('#Blocks_horizontal_count').val()+"n)"] =
                "    border-right: none;\n";
        }

        var styleText = '';
        $.each(style, function(key, value){
            styleText += key+"{\n"+value+"}\n";
        });

        $('#cssCode').val(styleText);
        cssChanged();
    }

    function cssChanged(){
        $('#preview_container style').html($('#cssCode').val().replace(/%BLOCK_ID%/g, 'ttarget_div'));
        debug && console.log('css changed', $('#cssCode').val().replace(/%BLOCK_ID%/g, 'ttarget_div'));
        var teasersContainer = $('#preview_container #ttarget_div');
        var needCount = $('#Blocks_vertical_count').val()*$('#Blocks_horizontal_count').val();
        var count = 0;
        while(count < needCount){
            $('#preview_teasers div').each(function(i, div){
                debug && console.log('add ', div);
                teasersContainer.append($(div).clone());
                count++;
                if(count == needCount) return false;
            });
        }
    }

    function getMaxInternalBorder(){
        var hCount = parseInt($('#Blocks_horizontal_count').val());
        var vCount = parseInt($('#Blocks_vertical_count').val());
        var imageDim = getImageDim();
        var blockDim = getBlockDim();
        var width = 0;
        if(hCount == 1 && vCount == 1){
            return 0;
        }else if(hCount == 1){
            width = getMaxVerticalInternalBorder(blockDim, imageDim, vCount);
        }else if(vCount == 1){
            width = getMaxHorizontalInternalBorder(blockDim, imageDim, hCount);
        }else{
             width = Math.min(
                getMaxVerticalInternalBorder(blockDim, imageDim, vCount),
                getMaxHorizontalInternalBorder(blockDim, imageDim, hCount)
            );
        }
        if(width < 0) width = 0;
        return width;
    }

    function getMaxHorizontalInternalBorder(blockDim, imageDim, hCount){
        return Math.floor((blockDim[0] - ((imageDim[0] / itemImageWidth) + 10) * hCount) / (hCount -1));
    }

    function getMaxVerticalInternalBorder(blockDim, imageDim, vCount){
        return Math.floor((blockDim[1] - (imageDim[1] * vCount)) / (vCount - 1));
    }

    function getItemHeight(blockDim, imageDim, verticalPadding, internalBorderWidth){
        if($('#Blocks_size').val() == 'custom'){
            return imageDim[1];
        }else{
            var count = $('#Blocks_vertical_count').val();
            return (blockDim[1] - (verticalPadding * count) - (internalBorderWidth * (count - 1))) / count;
        }
    }

    function getItemWidth(blockDim, internalBorderWidth){
        var hCount = parseInt($('#Blocks_horizontal_count').val());
        return ((blockDim[0] - (internalBorderWidth * (hCount - 1))) / hCount) - 10;
    }

    function getVerticalPadding(blockDim, imageDim, marginBottom){
        var vCount = $('#Blocks_vertical_count').val();
        var vPadding = (blockDim[1] - (imageDim[1] * vCount) - (marginBottom * (vCount - 1)))/vCount;
        if($('#Blocks_size').val() != 'custom'){
            var blockTemplate = templates[$('#Blocks_size').val()];
            if(typeof blockTemplate.vPadding != "undefined" && vPadding > blockTemplate.vPadding){
                vPadding = blockTemplate.vPadding;
            }
        }
        return vPadding
    }

    function getMaxImageSize(blockDim){
        var maxWidth = 0;
        $('#Blocks_image_size option').each(function(i,e){
            var imageDim = getImageDim($(e).val());
            if(imageDim[0] > maxWidth && imageDim[1] <= blockDim[1] && imageDim[0] <= blockDim[0]*itemImageWidth){
                maxWidth = imageDim[0];
            }
        });
        return maxWidth;
    }

    function selectImageSize(imageSize){
        $('#Blocks_image_size option').removeAttr('selected');
        $('#Blocks_image_size option[value='+imageSize+'x'+imageSize+']').attr('selected', 'selected');
    }

    function filterImageSize(maxImageSize){
        $('#Blocks_image_size option').each(function(i,e){
            e = $(e);
            var imageSize = getImageDim(e.val());
            if(imageSize[0] > maxImageSize){
                e.attr('disabled', 'disabled');
            }else{
                e.removeAttr('disabled');
            }
        });
    }

    function getHorizontalAutoItemsCount(blockDim, imageSize){
        var itemWidth = imageSize/itemImageWidth;
        return Math.floor(blockDim[0] / itemWidth);
    }

    function getVerticalAutoItemsCount(blockDim, imageSize){
        return Math.floor(blockDim[1] / imageSize);
    }

    function updateBlockCode(){
        <? if(!$model->getIsNewRecord()) : ?>
            console.log($('#Blocks_use_client_code').prop('checked'));
            var code;
            if(!$('#Blocks_use_client_code').prop('checked') || typeof blockCode == "undefined"){
                code = <?= CJavaScript::encode(ClientCode::getSimpleBlock()); ?>;
            }else{
                code = blockCode;
            }
            code = code
                .replace(/%BLOCK_ID%/g, <?= $model->id; ?>)
                .replace(/%COUNT%/g, $('#Blocks_vertical_count').val()*$('#Blocks_horizontal_count').val())
                .replace(/%USE_TITLE%/g, $('#Blocks_header').prop('checked') ? ', title: true' : '');
            $('#htmlContainer').show();
            $('#htmlCode').val(code).height($('#htmlCode')[0].scrollHeight);
        <? endif; ?>
    }

    $(function(){
        <? if(empty($model->css)) {
            echo "blockSizeChanged(true, false);";
        }else{
            echo "blockSizeChanged(false, false);";
            echo "cssChanged();";
        } ?>
    });

</script>
