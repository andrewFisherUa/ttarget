<?/**
 *@var Controller $this
 */
Yii::app()->clientScript->registerCoreScript('jquery');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.css', 'screen');

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-datepicker.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/datepicker.css', 'screen');

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/jquery.form.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/form_ajax_binding.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/ajaxform/client_val_form.css', 'screen');

//JQuery URI plugin
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/uri/URI.min.js', CClientScript::POS_HEAD);
?>
<div class="page-title-row  page-title-row-big ">
    <div class="title-with-button">
        <h1 class="page-title">
            Главная<!--input type="text" value="" class="main-search"-->
        </h1>
    </div>
</div>
<div class="page-content">
    <h3>Популярные сегменты</h3>
    <?
    $sort = isset($_GET['OffersUsers_sort']) ? $_GET['OffersUsers_sort'] : 'days_left';
    $columns = array(
        array(
            'name' => 'name',
            'header' => 'Сегмент',
            'htmlOptions' => array('class' => 'align-left'),
        ),
        array(
            'name' => 'scount',
            'header' => 'Переходы',
        ),
    );
    $this->widget('zii.widgets.grid.CGridView', array(
        'id' => 'offers-users-grid',
        'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering table-blue'),
        'template' => '{items}{pager}',
        'dataProvider' => $topTags,
        'columns' => $columns
    ));
    ?>

    <a href="<?= $this->createUrl('weblogCSV'); ?>">Скачать CSV</a>
</div>
