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
<?=$this->renderPartial('/offers/_reportTotal',array('report' => $reportTotal, 'monthsName' => $monthsName))?>
<?=$this->renderPartial('/offers/_notifications',array('notifications' => $notifications))?>
<div class="page-title-row  page-title-row-big ">
    <div class="title-with-button">
        <?= CHtml::dropDownList(
            'status',
            isset($_REQUEST['status']) ? $_REQUEST['status'] : '',
            OffersUsers::model()->getAvailableStatuses(),
            array('empty' => 'Все заявки', 'class' => 'title-right-btn input150 tableFilterSelect')
        ); ?>
        <h1 class="page-title">
            Главная<!--input type="text" value="" class="main-search"-->
        </h1>
    </div>
</div>
<div class="page-content">
    <?
    $sort = isset($_GET['OffersUsers_sort']) ? $_GET['OffersUsers_sort'] : 'days_left';
    $columns = array(
        array(
            'name' => 'id',
            'header' => 'ID<i class="icon-sort' . ($sort == 'id' ? ' icon-sort-down' : ($sort == 'id.desc' ? ' icon-sort-up' : '')) . '"></i>',
            'type' => 'raw',
            'value' => '$data["id"]',
            'htmlOptions' => array('class' => 'align-left'),
        ),
    	array(
            'name' => 'offer_name',
            'header' => 'Кампания<i class="icon-sort' . ($sort == 'offer_name' ? ' icon-sort-down' : ($sort == 'offer_name.desc' ? ' icon-sort-up' : '')) . '"></i>',
            'type' => 'raw',
            'value' => '(!empty($data["offer"]->campaign->client->logo)?'
                .'"<img src=\"/i/c/".$data["offer"]->campaign->client->logo."\" width=\"50\" height=\"50\" style=\"float: left; margin-right: 10px;\"/>"'
                .':"<img src=\"/i/c/no_image.png\" width=\"50\" height=\"50\" style=\"float: left; margin-right: 10px;\"/>")'
                .'.($data["is_deleted"] ? $data["offer"]->name : ("<a href=\"".Yii::app()->createUrl("/offers/".$data["offer"]->id)."\" class=\"view-offer\" data-id=\"".$data["offer"]->id."\">".$data["offer"]->name."</a>"))',
            'htmlOptions' => array('class' => 'align-left'),
        ),
    	array(
    		'name' => 'view',
    		'header' => 'Предпросмотр',
    		'type' => 'raw',
    		'value' => '!$data["is_deleted"] ? "<a href=\"".$data["offer"]->url."\"  class=\"view-offer\"  target=\"_blank\">Предпросмотр</a>" : "--"',
    		'htmlOptions' => array('class' => ''),
    	),
    	array(
    		'name' => 'countries',
    		'header' => 'Страны',
    		'type' => 'raw',
    		'value' => '($data["offer"]->getCountriesCodes()) ? $data["offer"]->getCountriesCodes() : " -- "',
    		'htmlOptions' => array()
    	),
    	array(
    		'name' => 'price',
    		'header' => 'Цена за действие<i class="icon-sort' . ($sort == 'price' ? ' icon-sort-down' : ($sort == 'price.desc' ? ' icon-sort-up' : '')) . '"></i>',
   			'value' => 'Yii::app()->numberFormatter->formatDecimal($data["offer"]->reward)." руб."'
   		),
    	array(
    		'name' => 'offers_clicks',
   			'header' => 'Клики<i class="icon-sort' . ($sort == 'offers_clicks' ? ' icon-sort-down' : ($sort == 'offers_clicks.desc' ? ' icon-sort-up' : '')) . '"></i>',
   			'value' => 'Yii::app()->numberFormatter->formatDecimal($data["offers_clicks"])'
   		),
    	array(
    		'name' => 'offers_actions',
   			'header' => 'Действия<i class="icon-sort' . ($sort == 'offers_actions' ? ' icon-sort-down' : ($sort == 'offers_actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
   			'value' => 'Yii::app()->numberFormatter->formatDecimal($data["offers_actions"])'
   		),
        array(
            'name' => 'offers_moderation_actions',
            'header' => 'В ожидании<i class="icon-sort' . ($sort == 'offers_moderation_actions' ? ' icon-sort-down' : ($sort == 'offers_moderation_actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
            'value' => 'Yii::app()->numberFormatter->formatDecimal($data["offers_moderation_actions"])'
        ),
        array(
            'name' => 'offers_declined_actions',
            'header' => 'Отклонено<i class="icon-sort' . ($sort == 'offers_declined_actions' ? ' icon-sort-down' : ($sort == 'offers_declined_actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
            'value' => 'Yii::app()->numberFormatter->formatDecimal($data["offers_declined_actions"])'
        ),
        array(
            'name' => 'conversions',
            'header' => 'Конверсии, %<i class="icon-sort' . ($sort == 'conversions' ? ' icon-sort-down' : ($sort == 'conversions.desc' ? ' icon-sort-up' : '')) . '"></i>',
            'value' => 'Yii::app()->numberFormatter->formatDecimal($data["conversions"])'
        ),
    	array(
    		'name' => 'total',
   			'header' => 'Всего<i class="icon-sort' . ($sort == 'total' ? ' icon-sort-down' : ($sort == 'total.desc' ? ' icon-sort-up' : '')) . '"></i>',
   			'value' => 'Yii::app()->numberFormatter->formatDecimal($data["reward_total"])." руб."'
   		),
    	array(
    		'name' => 'status',
    		'header' => 'Статус<i class="icon-sort' . ($sort == 'status' ? ' icon-sort-down' : ($sort == 'status.desc' ? ' icon-sort-up' : '')) . '"></i>',
    		'value' => '$data->getStatusName()'
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
<script type="text/javascript">
var baseUrl = new URI('<?=$this->createUrl($this->action->getId(),array_diff_key($_GET,
		array('status' => '')))?>');

//Build uri and redirecting
function filterTableResults(){
	var params = {};
	$('.tableFilterSelect').each( function( i, select ) {
		params[$(select).attr('name')] = $(select).val();
	});
	baseUrl.query(params);
	document.location.href = baseUrl;
};

$(function(){
	$('.tableFilterSelect').on('change',function(){
		filterTableResults();
	});
});
</script>