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
?>
<div class="page-title-row  page-title-row-big ">
    <div class="title-with-button">
        
        
        <h1 class="page-title">
            Уведомления<!--input type="text" value="" class="main-search"-->
        </h1>
    </div>
</div>
<div class="page-content">
    <?
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'days_left';
    $columns = array(
        array(
            'name' => 'id',
            'header' => 'ID<i class="icon-sort' . ($sort == 'id' ? ' icon-sort-down' : ($sort == 'id.desc' ? ' icon-sort-up' : '')) . '"></i>',
            'type' => 'raw',
            'value' => '$data["id"]',
            'htmlOptions' => array('class' => 'align-left'),
        ),
        array(
            'name' => 'text',
            'header' => 'Текст<i class="icon-sort' . ($sort == 'offer_name' ? ' icon-sort-down' : ($sort == 'offer_name.desc' ? ' icon-sort-up' : '')) . '"></i>',
            'type' => 'raw',
            'value' => '',
            'htmlOptions' => array('class' => 'align-left'),
        ),
    	array(
    		'name' => 'view',
    		'header' => 'Предпросмотр',
    		'type' => 'raw',
    		'value' => '"<a href=\"".$data->url."\"  class=\"view-offer\"  target=\"_blank\">Предпросмотр</a>"',
    		'htmlOptions' => array('class' => ''),
    	),
    	array(
    		'name' => 'countries',
    		'header' => 'Страны',
    		'type' => 'raw',
    		'value' => '$data->getCountriesCodes() ? $data->getCountriesCodes() : " -- "',
    		'htmlOptions' => array()
    	),
        array(
            'name' => 'price',
            'header' => 'Выплата<i class="icon-sort' . ($sort == 'price' ? ' icon-sort-down' : ($sort == 'price.desc' ? ' icon-sort-up' : '')) . '"></i>',
            'value' => 'Yii::app()->numberFormatter->formatDecimal($data->reward)." руб. за конверсию"'
        ),
    );
    $this->widget('zii.widgets.grid.CGridView', array(
        'id' => 'offers-users-grid',
        'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering table-blue'),
        'template' => '{items}{pager}',
        'dataProvider' => $offers,
        'columns' => $columns
    ));
    ?>
</div>