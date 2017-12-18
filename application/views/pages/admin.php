<?php
/**
 * @var PagesController $this
 * @var Pages $model
 */
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.css', 'screen');

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-select.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/bootstrap-select.css', 'screen');

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/jquery.form.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/form_ajax_binding.js', CClientScript::POS_HEAD);

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/jstree/jstree.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/jstree/themes/default/style.min.css', 'screen');
?>

<div class="page-title-row  page-title-row-big " style="padding-bottom: 20px;">
    <div class="title-with-button">
        <a href="/" id="url-add" class="btn title-right-btn"><i class="icon-16 icon-add"></i> Добавить страницу</a>
        <h1 class="page-title">
            Страницы <!--input type="text" value="" class="main-search"-->
        </h1>
    </div>
</div>
<div class="page-content">
    <?
    $sort = isset($_GET['Pages_sort'])?$_GET['Pages_sort']:'';
    $this->widget('zii.widgets.grid.CGridView', array(
        'id'=>'pages-grid',
        'htmlOptions' => array('class' => 'table table-striped table-bordered table-shadow table-centering'),
        'dataProvider'=>$model->search(),
        'template' => '{items}{pager}',
        'columns'=>array(
            'id',
            array(
                'name' => 'url',
                'htmlOptions'=>array('style' => 'text-align: left;'),
                'type' => 'raw',
                'value' => '"<a onclick=\'return editUrl(null, ".$data->id.")\' href=\'#\'>".IDN::decodeURL($data->url)."</a>"'
            ),
            array(
                'name' => 'segments',
                'type' => 'raw',
                'value' => '$data->getSegmentsHtml()'
            )
        )
    )); ?>
</div>
<script type="text/javascript">
    jQuery(function ($) {
        $('#url-add').on('click', editUrl);
    });

    function editUrl(e, id) {
        var _data = {"YII_CSRF_TOKEN":"<?= Yii::app()->request->csrfToken;?>"};
        if(typeof id != "undefined"){
            _data["id"] = id;
        }
        $.ajax({
            type: "POST",
            url: "<?= $this->createUrl('returnForm'); ?>",
            data: _data,
            beforeSend : function() {
                $("#pages-grid").addClass("ajax-sending");
            },
            complete : function() {
                $("#pages-grid").removeClass("ajax-sending");
            },
            success: function(data) {
                $.fancybox(data,
                    $.extend({}, fancyDefaults, {
                        "width": 575,
                        "minWidth": 575,
                        "afterClose":    function() {
                            $.fn.yiiGridView.update('pages-grid', {data:{Pages:{name: $(".main-search").val()}}});
                        } //onclosed functi
                    })
                );//fancybox
                //  console.log(data);
            } //success
        });//ajax
        return false;
    }

    var delPage = function(id){
        if(confirm('Удалить страницу?')){
            document.location = "<?= $this->createUrl('delete'); ?>/"+id;
        }

        return false;
    }
</script>