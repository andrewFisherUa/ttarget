<?
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
        
        <div class="title-right-btn">
            <? $this->renderPartial('/partials/period', array('period' => $_filterPeriod, 'dateFrom' => $_filterDateFrom, 'dateTo' => $_filterDateTo)); ?>
        </div>
        <?= CHtml::dropDownList(
            'status',
            $_filterStatus,
            OffersUsers::model()->getAvailableStatuses(),
            array('empty' => 'Статус заявки', 'class' => 'title-right-btn input150 tableFilterSelect')
        ); ?>
        <h1 class="page-title">
            Заявки<input type="text" value="" class="main-search">
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
        ),array(
            'name' => 'name',
            'header' => 'Оффер<i class="icon-sort' . ($sort == 'name' ? ' icon-sort-down' : ($sort == 'name.desc' ? ' icon-sort-up' : '')) . '"></i>',
            'type' => 'raw',
            'value' => '!$data["is_deleted"] ? $data["offer"]->name : "оффер удален"',
            'htmlOptions' => array('class' => 'align-left'),
        ),array(
            'name' => 'user_login',
            'header' => 'Пользователь<i class="icon-sort' . ($sort == 'user_login' ? ' icon-sort-down' : ($sort == 'user_login.desc' ? ' icon-sort-up' : '')) . '"></i>',
            'type' => 'raw',
            'value' => '$data["user"]->login',
            'htmlOptions' => array('class' => 'align-left'),
        ),array(
            'name' => 'user_email',
            'header' => 'email<i class="icon-sort' . ($sort == 'user_email' ? ' icon-sort-down' : ($sort == 'user_email.desc' ? ' icon-sort-up' : '')) . '"></i>',
            'type' => 'raw',
            'value' => '$data["user"]->email',
            'htmlOptions' => array('class' => 'align-left'),
        ),array(
            'name' => 'created_date',
            'header' => 'Дата отправки<i class="icon-sort' . ($sort == 'created_date' ? ' icon-sort-down' : ($sort == 'created_date.desc' ? ' icon-sort-up' : '')) . '"></i>',
            'type' => 'raw',
            'value' => '$data["created_date"]',
            'htmlOptions' => array('class' => 'align-left'),
        //),array(
    	//	'name' => 'status',
    	//	'header' => 'Статус<i class="icon-sort' . ($sort == 'status' ? ' icon-sort-down' : ($sort == 'status.desc' ? ' icon-sort-up' : '')) . '"></i>',
    	//	'type' => 'raw',
    	//	'value' => '$data["status_name"]',
    	//	'htmlOptions' => array('class' => 'align-left'),
    	),array(
            'name' => 'status',
            'header' => 'Статус<i class="icon-sort' . ($sort == 'status' ? ' icon-sort-down' : ($sort == 'status.desc' ? ' icon-sort-up' : '')) . '"></i>',
            'value' => '"<div style=\"width: 200px;\">".Chtml::dropDownList("", $data["status"], $data->getAvailableStatuses(), array("style"=>"width: 150px;","onchange" => "return changeStatus($(this).val(), ".($data["id"]).")"))."<a style=\"padding-left: 10px; margin-bottom: 2px;\" id=\"a-tizer-".$data["id"]."\" class=\"campaign-story-title inline\" onclick=\"return showEditRequest(".$data["id"].")\" href=\"".Yii::app()->createUrl(\'offers/editRequest\',array(\'id\' => $data["id"]))."\"><i class=\"icon-14 icon-cog-dark\"></i></a></div>"',
            'type' => 'raw'
        ),
    );
    $this->widget('zii.widgets.grid.CGridView', array(
        'id' => 'offers-users-grid',
        'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering table-blue'),
        'template' => '{items}{pager}',
        'dataProvider' => $requests,
        'columns' => $columns
    ));
    ?>
</div>
<script type="text/javascript">
$(function(){
	$(".main-search").keyup(function (event) {
        if (event.keyCode == 13) {
            var q = $(this).val();
            $.fn.yiiGridView.update('offers-users-grid', {data: {filter:  q} });
            $('.modal-backdrop').remove();
            $('.main-search').focusout().blur();

            return false;
        }
    });
});

var changeStatus = function (val, id) {
    var urls = {
        news: "<?= $this->createUrl('news/changeActivity'); ?>",
        teaser: "<?= $this->createUrl('teasers/changeActivity'); ?>"
    }
    $.ajax({
        type: "POST",
        url: '<?= Yii::app()->request->baseUrl;?>/offers/changeStatus/' + id,
        data: {"YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>", "status": val},
        dataType: 'json',
        beforeSend: function () {
            $("#offers-users-grid").addClass("ajax-sending");
        },
        complete: function () {
            $("#offers-users-grid").removeClass("ajax-sending");
        },
        success: function(json){
			if(json.count_new > 0){
				$('#offers_cnt_new').html(json.count_new);
			} else {
				$('#offers_cnt_new').hide();
			}
        }
    });//ajax
    return false;
}

var showEditRequest = function (id) {
    $.ajax({
    	type: "POST",
        url: '<?= Yii::app()->request->baseUrl;?>/offers/editRequestForm/' + id,
        data: {"YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>", "id": id},
        beforeSend: function () {
            $("#news-grid").addClass("ajax-sending");
        },
        complete: function () {
            $("#news-grid").removeClass("ajax-sending");
        },
        success: function (data) {
            $.fancybox(data,
                $.extend({}, fancyDefaults, {
                    "width": 500,
                    "minWidth": 360,
                    "afterClose": function () {
                        //updateGrid(id);
                    } //onclosed functi
                })
            );//fancybox
            //  console.log(data);
        } //success
    });//ajax
    return false;
}
</script>