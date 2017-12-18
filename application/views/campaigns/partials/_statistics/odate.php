<?php
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date.desc';
$columns = array(
    array(
        'name' => 'date',
        'header' => 'Дата<i class="icon-sort' . ($sort == 'date' ? ' icon-sort-down' : ($sort == 'date.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportOffersDate->itemCount ? 'весь период' : null
    ),
    array(
        'name' => 'clicks',
        'header' => 'Переходы<i class="icon-sort' . ($sort == 'clicks' ? ' icon-sort-down' : ($sort == 'clicks.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportOffersDate->total('clicks')
    ),
    array(
        'name' => 'actions',
        'header' => 'Действия<i class="icon-sort' . ($sort == 'actions' ? ' icon-sort-down' : ($sort == 'actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportOffersDate->total('actions')
    ),
    array(
        'name' => 'declined_actions',
        'header' => 'Отклоненные<i class="icon-sort' . ($sort == 'declined_actions' ? ' icon-sort-down' : ($sort == 'declined_actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportOffersDate->total('declined_actions')
    ),
    array(
        'name' => 'sum_payment',
        'header' => 'Выплата<i class="icon-sort' . ($sort == 'sum_payment' ? ' icon-sort-down' : ($sort == 'sum_payment.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportOffersDate->total('sum_payment')
    ),
    array(
        'name' => 'sum_reward',
        'header' => 'Вознаграждение<i class="icon-sort' . ($sort == 'sum_reward' ? ' icon-sort-down' : ($sort == 'sum_reward.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportOffersDate->total('sum_reward'),
        'visible' => Yii::app()->user->role === Users::ROLE_ADMIN
    ),
    array(
        'name' => 'avg_conversions',
        'header' => 'Конверсии, %<i class="icon-sort' . ($sort == 'avg_conversions' ? ' icon-sort-down' : ($sort == 'avg_conversions.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportOffersDate->total('avg_clicks_per_action')
    ),
);
$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'offers-bydate-grid',
    'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering table-blue'),
    'template' => '{items}{pager}',
    'dataProvider' => $reportOffersDate,
    'columns' => $columns
));