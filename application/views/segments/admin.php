<?php
/**
 * @var SegmentsController $this
 * @var Segments $model
 */
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.css', 'screen');

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-select.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/bootstrap-select.css', 'screen');

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/jquery.form.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/form_ajax_binding.js', CClientScript::POS_HEAD);
?>

<div class="page-title-row  page-title-row-big " style="padding-bottom: 20px;">
    <div class="title-with-button">
        <a href="/" id="segment-add" class="btn title-right-btn"><i class="icon-16 icon-add"></i> Добавить сегмент</a>
        <h1 class="page-title">
            Сегменты <!--input type="text" value="" class="main-search"-->
        </h1>
    </div>
</div>
<div class="page-content">
    <?
    $sort = isset($_GET['Segments_sort'])?$_GET['Segments_sort']:'';
    $this->widget('zii.widgets.grid.CGridView', array(
        'id'=>'segments-grid',
        'htmlOptions' => array('class' => 'table table-striped table-bordered table-shadow table-centering'),
        'dataProvider'=>$model->search(),
        'template' => '{items}{pager}',
        'columns'=>array(
            array(
                'name' => 'name',
                'htmlOptions'=>array('style' => 'text-align: left;'),
                'type' => 'raw',
                'value' => '"<a onclick=\'return editSegment(null, ".$data->id.")\' href=\'#\'>".$data->paddedName."</a>"'
            ),
        )
    )); ?>
</div>
<script type="text/javascript">
    jQuery(function ($) {
        $('#segment-add').on('click', editSegment);
    });

    function editSegment(e, id) {
        var _data = {"YII_CSRF_TOKEN":"<?= Yii::app()->request->csrfToken;?>"};
        if(typeof id != "undefined"){
            _data["id"] = id;
        }
        $.ajax({
            type: "POST",
            url: "<?= $this->createUrl('returnForm'); ?>",
            data: _data,
            beforeSend : function() {
                $("#segments-grid").addClass("ajax-sending");
            },
            complete : function() {
                $("#segments-grid").removeClass("ajax-sending");
            },
            success: function(data) {
                $.fancybox(data,
                    $.extend({}, fancyDefaults, {
                        "width": 575,
                        "minWidth": 575,
                        "afterShow": function () {

                            if ($.fn.selectpicker) {
                                $('select.selectpick:visible').selectpicker();
                            }
                        },
                        "afterClose":    function() {
                            $.fn.yiiGridView.update('segments-grid', {data:{Segments:{name: $(".main-search").val()}}});
                        } //onclosed functi
                    })
                );//fancybox
                //  console.log(data);
            } //success
        });//ajax
        return false;
    }

    var delSegment = function(id){
        $.ajax({
            url: "<?= $this->createUrl('returnSubSegments'); ?>/" + id,
            beforeSend : function() {
                $("#modal-segments-settings").addClass("ajax-sending");
            },
            complete : function() {
                $("#modal-segments-settings").removeClass("ajax-sending");
            },
            success: function(data) {
                if(confirm("Удаляются следующие сегменты:\n" + data)){
                    document.location = "<?= $this->createUrl('delete'); ?>/"+id;
                }
            } //success
        });//ajax

        return false;
    }
</script>