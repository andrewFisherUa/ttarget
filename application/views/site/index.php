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

Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/bootstrap-table.css', 'screen');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-table.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-table-naturalsorting.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-table-cookie.js', CClientScript::POS_HEAD);
?>
<div class="page-title-row  page-title-row-big ">
    <div class="title-with-button">
        
        <div class="title-right-btn">
            <? $this->renderPartial('/partials/period', array('period' => $period, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo)); ?>
        </div>
        <?= CHtml::dropDownList(
            'cost_type',
            isset($_REQUEST['cost_type']) ? $_REQUEST['cost_type'] : '',
            Campaigns::model()->getAvailableCostTypes(),
            array('empty' => 'Все кампании', 'class' => 'title-right-btn input150 tableFilterSelect')
        ); ?>
        <?= CHtml::dropDownList(
            'is_active',
            isset($_REQUEST['is_active']) ? $_REQUEST['is_active'] : '',
            array('1' => 'Активные'),
            array('empty' => 'Все кампании', 'class' => 'title-right-btn input150 tableFilterSelect')
        ); ?>
        <h1 class="page-title">
            Главная<!--input type="text" value="" class="main-search"-->
        </h1>
    </div>
</div>

<div class="page-content">
    <?
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'days_left';
    $columns = array(
        array(
            'name' => 'user_login',
            'header' => 'Клиент',
            'type' => 'raw',
            'value' => '"<a href=\"".$data["user_id"]."\" class=\"user-story-title inline\">"
                ."<i class=\"icon-14 icon-cog-dark\"></i></a>"
                ."<a href=\"".Yii::app()->createUrl("/users/".$data["user_id"])."\">"
                .$data["user_login"]."</a>"',
            'htmlOptions' => array('class' => 'align-left'),
            'headerHtmlOptions' => array('data-sortable' => 'true', 'data-sorter' => 'stripped_sort')
        ),
        array(
            'name' => 'name',
            'header' => 'Кампания',
            'type' => 'raw',
            'value' => '"<a href=\'". Yii::app()->createUrl("/campaigns/".$data["id"])."\'>".$data["name"]."</a>"',
            'htmlOptions' => array('class' => 'align-left'),
            'headerHtmlOptions' => array('data-sortable' => 'true', 'data-sorter' => 'stripped_sort')
        ),
        array(
            'name' => 'shows',
            'header' => 'Показы',
            'htmlOptions' => array('class' => 'min'),
            'value' => 'Yii::app()->numberFormatter->formatDecimal($data["shows"])',
            'headerHtmlOptions' => array('data-sortable' => 'true', 'data-sorter' => 'number_sort')
        ),
        array(
            'name' => 'clicks',
            'header' => 'Переходы',
            'htmlOptions' => array('class' => 'min'),
            'value' => 'Yii::app()->numberFormatter->formatDecimal($data["clicks"])',
            'headerHtmlOptions' => array('data-sortable' => 'true', 'data-sorter' => 'number_sort')
        ),
        array(
            'name' => 'bounces',
            'header' => 'Отказы',
            'htmlOptions' => array('class' => 'min'),
            'value' => 'Yii::app()->numberFormatter->formatDecimal($data["bounces"])',
            'headerHtmlOptions' => array('data-sortable' => 'true', 'data-sorter' => 'number_sort')
        ),
        array(
            'name' => 'days_left',
            'header' => 'Осталось дней',
            'headerHtmlOptions' => array('data-sortable' => 'true', 'data-field' => 'days_left')
        ),
        array(
            'name' => 'value_left',
            'header' => 'Остаток объема',
            'htmlOptions' => array('class' => 'min'),
            'value' => 'Yii::app()->numberFormatter->formatDecimal($data["value_left"])',
            'headerHtmlOptions' => array('data-sortable' => 'true', 'data-sorter' => 'number_sort')
        ),
        array(
            'name' => 'actions',
            'header' => 'Действия',
            'htmlOptions' => array('class' => 'min'),
            'value' => 'Yii::app()->numberFormatter->formatDecimal($data["actions"])',
            'headerHtmlOptions' => array('data-sortable' => 'true', 'data-sorter' => 'number_sort')
        ),
        array(
            'name' => 'declined_actions',
            'header' => 'Отклоненные',
            'htmlOptions' => array('class' => 'min'),
            'value' => 'Yii::app()->numberFormatter->formatDecimal($data["declined_actions"])',
            'headerHtmlOptions' => array('data-sortable' => 'true', 'data-sorter' => 'number_sort')
        ),
        array(
//            'name' => 'average_time',
            'header' => 'Среднее время',
            'class' => 'DataColumn',
            'htmlOptions' => array('class' => 'min'),
            'value' => '"-"',
            'headerHtmlOptions' => array('data-sortable' => 'true')
        ),
        array(
            'header' => 'Глубина просм.',
            'class' => 'DataColumn',
            'htmlOptions' => array('class' => 'min'),
            'value' => '"-"',
            'headerHtmlOptions' => array('data-sortable' => 'true')
        ),
    );
    $this->widget('zii.widgets.grid.CGridView', array(
        'id' => 'campaigns-bydate-grid',
        'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering table-blue'),
        'template' => '{items}{pager}',
        'dataProvider' => $report,
        'rowHtmlOptionsExpression' => 'array("id" => $data[\'id\'])',
        'enableSorting' => false,
        'columns' => $columns
    ));
    ?>
</div>
<script type="text/javascript">
    jQuery(function ($) {
        var bt = $('#campaigns-bydate-grid>table').bootstrapTable({
            sortable: true,
            showColumns: true,
            stateSave: true,
            stateSaveIdTable: "saveId",
            sortName: "days_left",
            sortOrder: "desc",
            search: true,
            rowStyle: function(row, index){
                if(index % 2 === 0){
                    return {classes: 'odd'}
                }else{
                    return {classes: 'even'}
                }
            }
        });
        $.ajax({
            type: "GET",
            url: "<?= $this->createUrl("indexGA").'?'.Yii::app()->request->queryString; ?>",
            data:{"YII_CSRF_TOKEN":"<?= Yii::app()->request->csrfToken;?>"},
            success: function(data) {
                $.each(bt.data('bootstrap.table').options.data, function(ri, el){
                    if(typeof data[el['_id']] != "undefined"){
                        bt.data('bootstrap.table').options.data[ri][9] = data[el['_id']]['average_time'];
                        bt.data('bootstrap.table').options.data[ri][10] = data[el['_id']]['page_depth'];
                    }
                });
                bt.data('bootstrap.table').initBody();
            } //success
        });//ajax
    });

    function stripped_sort(a, b){
        return alphanum(a.replace(/<(?:.|\n)*?>/gm, '').toLowerCase(),b.replace(/<(?:.|\n)*?>/gm, '').toLowerCase());
    }

    function number_sort(a,b){
        return alphanum(a.replace(/\s|&nbsp;/gm, ''), b.replace(/\s|&nbsp;/gm, ''));
    }
</script>