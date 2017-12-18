<?php
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date';
$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'platforms-stats-partner',
    'htmlOptions' => array('class' => 'table table-striped table-bordered table-shadow table-centering'),
    'dataProvider' => $report,
    'template' => '{items}',
    'columns' => array(
        array(
            'name' => 'date',
            'header' => 'Дата<i class="icon-sort' . ($sort == 'date' ? ' icon-sort-down' : ($sort == 'date.desc' ? ' icon-sort-up' : '')) . '"></i>',
        ),
        array(
            'name' => 'shows',
            'header' => 'Показы<i class="icon-sort' . ($sort == 'shows' ? ' icon-sort-down' : ($sort == 'shows.desc' ? ' icon-sort-up' : '')) . '"></i>',
        ),
        array(
            'name' => 'clicks',
            'header' => 'Переходы<i class="icon-sort' . ($sort == 'clicks' ? ' icon-sort-down' : ($sort == 'clicks.desc' ? ' icon-sort-up' : '')) . '"></i>',
        ),
        array(
            'name' => 'ctr',
            'header' => 'CTR<i class="icon-sort' . ($sort == 'ctr' ? ' icon-sort-down' : ($sort == 'ctr.desc' ? ' icon-sort-up' : '')) . '"></i>',
        ),
        array(
            'name' => 'price',
            'header' => (Yii::app()->user->role === Users::ROLE_PLATFORM ? 'Доход' : 'Расход').', у.е.<i class="icon-sort' . ($sort == 'price' ? ' icon-sort-down' : ($sort == 'price.desc' ? ' icon-sort-up' : '')) . '"></i>',
        ),
        array(
            'name' => 'cost',
            'header' => 'Цена за переход, у.е.<i class="icon-sort' . ($sort == 'cost' ? ' icon-sort-down' : ($sort == 'cost.desc' ? ' icon-sort-up' : '')) . '"></i>',
        ),
        array(
            'name' => 'clickfraud',
            'header' => 'Скликивания<i class="icon-sort' . ($sort == 'clickfraud' ? ' icon-sort-down' : ($sort == 'clickfraud .desc' ? ' icon-sort-up' : '')) . '"></i>',
        ),
    )
));
?>