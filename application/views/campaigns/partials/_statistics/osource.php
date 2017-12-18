<?php
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'platform_server';
$columns = array(
    array(
        'name' => 'user_login',
        'header' => 'Вебмастер<i class="icon-sort' . ($sort == 'user_login' ? ' icon-sort-down' : ($sort == 'user_login.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'value' => '$data["user_login"]." (".$data["user_email"].")"',
        'footer' => $reportOffersUsers->itemCount ? 'все вебмастера' : null
    ),
    array(
        'name' => 'clicks',
        'header' => 'Переходы<i class="icon-sort' . ($sort == 'clicks' ? ' icon-sort-down' : ($sort == 'clicks.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportOffersUsers->total('clicks')
    ),
    array(
        'name' => 'actions',
        'header' => 'Действия<i class="icon-sort' . ($sort == 'actions' ? ' icon-sort-down' : ($sort == 'actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportOffersUsers->total('actions')
    ),
    array(
        'name' => 'declined_actions',
        'header' => 'Отклоненные<i class="icon-sort' . ($sort == 'declined_actions' ? ' icon-sort-down' : ($sort == 'declined_actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportOffersUsers->total('declined_actions')
    ),
    array(
        'name' => 'sum_payment',
        'header' => 'Выплата<i class="icon-sort' . ($sort == 'sum_payment' ? ' icon-sort-down' : ($sort == 'sum_payment.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportOffersUsers->total('sum_price')
    ),
    array(
        'name' => 'sum_reward',
        'header' => 'Вознаграждение<i class="icon-sort' . ($sort == 'sum_reward' ? ' icon-sort-down' : ($sort == 'sum_reward.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportOffersUsers->total('avg_price'),
        'visible' => Yii::app()->user->role === Users::ROLE_ADMIN
    ),
);
$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'offers-by-users-grid',
    'ajaxUpdate'=>'by-platform-all-grid,by-platform-external-grid,by-platform-internal-grid',
    'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering table-blue'),
    'template' => '{items}{pager}',
    'dataProvider' => $reportOffersUsers,
    'columns' => $columns,
));