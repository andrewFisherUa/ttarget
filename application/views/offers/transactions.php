<?/**
 *
 */?>
<div class="page-title-row  page-title-row-big ">
    <div class="title-right-btn">
        <? $this->renderPartial('/partials/period', array('period' => $period, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo)); ?>
        <?= CHtml::dropDownList('status',
            Yii::app()->request->getParam('status', ''),
            ActionsLog::getAvailableStatuses(),
            array('class' => 'input150 tableFilterSelect', 'empty' => 'Все статусы')
        ); ?>
    </div>
    <h1 class="page-title">Транзакции</h1>
</div>
<?
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date.desc';
$columns = array(
    array(
        'name' => 'id',
        'header' => 'ID<i class="icon-sort' . ($sort == 'id' ? ' icon-sort-down' : ($sort == 'id.desc' ? ' icon-sort-up' : '')) . '"></i>',
    ),
    array(
        'name' => 'date',
        'header' => 'Дата<i class="icon-sort' . ($sort == 'date' ? ' icon-sort-down' : ($sort == 'date.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportActionsLog->itemCount ? 'весь период' : null
    ),
    array(
        'name' => 'campaign_id',
        'header' => 'ID кампании<i class="icon-sort' . ($sort == 'campaign_id' ? ' icon-sort-down' : ($sort == 'campaign_id.desc' ? ' icon-sort-up' : '')) . '"></i>',
    ),
    array(
        'name' => 'target_name',
        'header' => 'Предложение<i class="icon-sort' . ($sort == 'target_name' ? ' icon-sort-down' : ($sort == 'target_name.desc' ? ' icon-sort-up' : '')) . '"></i>',
    ),
    array(
        'name' => 'ip',
        'header' => 'IP<i class="icon-sort' . ($sort == 'ip' ? ' icon-sort-down' : ($sort == 'ip.desc' ? ' icon-sort-up' : '')) . '"></i>',
    ),
    array(
        'name' => 'geo',
        'header' => 'ГЕО<i class="icon-sort' . ($sort == 'geo' ? ' icon-sort-down' : ($sort == 'geo.desc' ? ' icon-sort-up' : '')) . '"></i>',
    ),
//    array(
//        'name' => 'referrer_url',
//        'header' => 'Referrer URL<i class="icon-sort' . ($sort == 'referrer_url' ? ' icon-sort-down' : ($sort == 'referrer_url.desc' ? ' icon-sort-up' : '')) . '"></i>',
//    ),
    array(
        'name' => 'target_url_decoded',
        'header' => 'URL цели<i class="icon-sort' . ($sort == 'target_url_decoded' ? ' icon-sort-down' : ($sort == 'target_url_decoded.desc' ? ' icon-sort-up' : '')) . '"></i>',
    ),
    array(
        'name' => 'reward',
        'header' => 'Выплата<i class="icon-sort' . ($sort == 'reward' ? ' icon-sort-down' : ($sort == 'reward.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportActionsLog->total('reward')
    ),
    array(
        'name' => 'status',
        'header' => 'Статус<i class="icon-sort' . ($sort == 'status' ? ' icon-sort-down' : ($sort == 'status.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'type' => 'raw',
        'value' => '$data["status"] == ActionsLog::STATUS_ACCEPTED ? "<i class=\"icon-ok\" title=\"Подтвержденая\"></i></a>" :'
            .'($data["status"] == ActionsLog::STATUS_DECLINED ? "<i class=\"icon-remove\" title=\"Отклоненая\"></i></a>" :'
            .'"<i class=\"icon-time\" title=\"В ожидании\"></i></a>")'
    )
);
$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'actions-details-grid',
    'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering table-blue'),
    'template' => '{items}{pager}',
    'dataProvider' => $reportActionsLog,
    'rowCssClassExpression' =>
        '( $row%2 ? $this->rowCssClass[1] : $this->rowCssClass[0] ) .
        ($data["status"] != ActionsLog::STATUS_ACCEPTED ? " disabled" : "")',
    'columns' => $columns
));
?>