<?
/**
 * @var $this CampaignsController
 * @var Campaigns $campaign,
 * @var string $period,
 * @var string $dateFrom,
 * @var string $dateTo,
 * @var array|null $reportActions
 * @var array|null $reportActionsDetails
 * @var array $reportDate
 * @var array $reportPlatformAll
 * @var array $reportPlatformExternal
 * @var array $reportPlatformInternal
 * @var array $reportCity
 * @var array $reportCountry
 *
 * @var CReportDataProvider|null $reportOffersActions
 */
?>

<div class="navbar navbar-white">
    <div class="navbar-inner">
        <ul class="nav">
            <li class="active"><a href="<?= $this->createUrl('statistics', array('id' => $campaign->id)); ?>">Ttarget</a></li>
            <li><a href="<?= $this->createUrl('googleAnalytics', array('id' => $campaign->id)); ?>">Google Analytics</a></li>
        </ul>
    </div>
</div>
<hr>

<?if($campaign->cost_type == Campaigns::COST_TYPE_RTB):?>
<a href="#rdate">По дате</a>:
<a href="#rgeo">ГЕО</a>,
<a href="#rplatform">По площадке</a>,

<?endif;?>
<?if($campaign->cost_type == Campaigns::COST_TYPE_ACTION || $campaign->cost_type == Campaigns::COST_TYPE_CLICK):?>
<a href="#teasers">Тизеры</a>:
    <a href="#ttarget">по целям</a>,
    <a href="#tdate">по дате</a>,
    <a href="#tcharts">графики</a>,
    <a href="#tgeo">по ГЕО</a>,
    <a href="#tsource">по площадке</a>.
<br>
<?endif;?>
<?if($campaign->cost_type == Campaigns::COST_TYPE_ACTION) : ?>
    <a href="#offers">Предложения:</a>
        <a href="#otarget">по предложению</a>,
        <a href="#odate">по дате</a>,
        <a href="#ocharts">графики</a>,
        <a href="#osource">по вебмастеру</a>.
    <hr>
<?endif; ?>

<?if($campaign->cost_type == Campaigns::COST_TYPE_ACTION || $campaign->cost_type == Campaigns::COST_TYPE_CLICK):?>

<h3 id="teasers">Тизеры:</h3>
<h5 id="ttarget">По целям:</h5>
<?
if(isset($reportActions)){
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'date.desc';
    $columns = array(
        array(
            'name' => 'date',
            'header' => 'Дата<i class="icon-sort' . ($sort == 'date' ? ' icon-sort-down' : ($sort == 'date.desc' ? ' icon-sort-up' : '')) . '"></i>',
            'footer' => $reportActions->itemCount ? 'весь период' : null
        ),
        array(
            'name' => 'name',
            'header' => 'Цель<i class="icon-sort' . ($sort == 'name' ? ' icon-sort-down' : ($sort == 'name.desc' ? ' icon-sort-up' : '')) . '"></i>',
        ),
        array(
            'name' => 'actions',
            'header' => 'Действия<i class="icon-sort' . ($sort == 'actions' ? ' icon-sort-down' : ($sort == 'actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
            'footer' => $reportActions->total('actions')
        ),
        array(
            'name' => 'declined_actions',
            'header' => 'Отклоненные<i class="icon-sort' . ($sort == 'declined_actions' ? ' icon-sort-down' : ($sort == 'declined_actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
            'footer' => $reportActions->total('declined_actions')
        ),
        array(
            'name' => 'cost',
            'header' => 'Стоимость (без НДС)<i class="icon-sort' . ($sort == 'cost' ? ' icon-sort-down' : ($sort == 'cost.desc' ? ' icon-sort-up' : '')) . '"></i>',
        ),
        array(
            'name' => 'sum_cost',
            'header' => 'Сумма<i class="icon-sort' . ($sort == 'sum_cost' ? ' icon-sort-down' : ($sort == 'sum_cost.desc' ? ' icon-sort-up' : '')) . '"></i>',
            'footer' => $reportActions->total('sum_cost')
        ),
    );
    $this->widget('zii.widgets.grid.CGridView', array(
        'id' => 'actions-bydate-grid',
        'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering table-blue'),
        'template' => '{items}{pager}',
        'dataProvider' => $reportActions,
        'columns' => $columns
    ));
}
?>
<h5 id="tdate">По дате:</h5>
<?
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date.desc';
$columns = array(
    array(
        'name' => 'date',
        'header' => 'Дата<i class="icon-sort' . ($sort == 'date' ? ' icon-sort-down' : ($sort == 'date.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportDate->itemCount ? 'весь период' : null
    ),
    array(
        'name' => 'shows',
        'header' => 'Показы<i class="icon-sort' . ($sort == 'shows' ? ' icon-sort-down' : ($sort == 'shows.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportDate->total('shows')
    ),
    array(
        'name' => 'clicks',
        'header' => 'Переходы<i class="icon-sort' . ($sort == 'clicks' ? ' icon-sort-down' : ($sort == 'clicks.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportDate->total('clicks')
    ),
    array(
        'name' => 'ctr',
        'header' => 'CTR<i class="icon-sort' . ($sort == 'ctr' ? ' icon-sort-down' : ($sort == 'ctr.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportDate->total('ctr')
    ),
    array(
        'name' => 'sum_price',
        'header' => 'Расход (без НДС)<i class="icon-sort' . ($sort == 'sum_price' ? ' icon-sort-down' : ($sort == 'sum_price.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportDate->total('sum_price')
    ),
    array(
        'name' => 'avg_price',
        'header' => 'Цена за переход<i class="icon-sort' . ($sort == 'avg_price' ? ' icon-sort-down' : ($sort == 'avg_price.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportDate->total('avg_price')
    ),
);
if($campaign->cost_type == Campaigns::COST_TYPE_ACTION){
    $columns[] = array(
        'name' => 'actions',
        'header' => 'Действия<i class="icon-sort' . ($sort == 'actions' ? ' icon-sort-down' : ($sort == 'actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportDate->total('actions')
    );
    $columns[] = array(
        'name' => 'declined_actions',
        'header' => 'Отклоненные<i class="icon-sort' . ($sort == 'declined_actions' ? ' icon-sort-down' : ($sort == 'declined_actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportDate->total('declined_actions')
    );
    $columns[] = array(
        'name' => 'avg_clicks_per_action',
        'header' => 'Среднее количество переходов<i class="icon-sort' . ($sort == 'avg_clicks_per_action' ? ' icon-sort-down' : ($sort == 'avg_clicks_per_action.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportDate->total('avg_clicks_per_action')
    );
    $columns[] = array(
        'name' => 'avg_action_clicks_cost',
        'header' => 'Средняя стоимость действия<i class="icon-sort' . ($sort == 'avg_action_clicks_cost' ? ' icon-sort-down' : ($sort == 'avg_action_clicks_cost.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportDate->total('avg_action_clicks_cost')
    );
}
$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'bydate-grid',
    'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering table-blue'),
    'template' => '{items}{pager}',
    'dataProvider' => $reportDate,
    'columns' => $columns
));
?>

<div class="spacer"></div>
<h5 id="tcharts">Графики:</h5>
<ul class="nav nav-pills">
    <li class="active"><a href="#tab_clicks" data-toggle="tab">Переходы</a></li>
    <li><a href="#tab_shows" data-toggle="tab">Показы</a></li>
    <? if($campaign->cost_type == Campaigns::COST_TYPE_ACTION) : ?>
        <li><a href="#tab_actions" data-toggle="tab">Действия</a></li>
    <? endif; ?>
    <li><a href="#tab_sum_price" data-toggle="tab">Расходы (без НДС)</a></li>
</ul>
<div class="tab-content" style="height: 295px;">
    <div class="tab-pane active" id="tab_clicks"><div id="chart_clicks"></div></div>
    <div class="tab-pane" id="tab_shows"><div id="chart_shows"></div></div>
    <? if($campaign->cost_type == Campaigns::COST_TYPE_ACTION) : ?>
        <div class="tab-pane" id="tab_actions"><div id="chart_actions"></div></div>
    <? endif; ?>
    <div class="tab-pane" id="tab_sum_price"><div id="chart_sum_price"></div></div>
</div>

<h5 id="tgeo">По ГЕО:</h5>
<ul class="nav nav-pills">
    <li class="active"><a href="#tab_country" data-toggle="tab">Страны</a></li>
    <li><a href="#tab_city" data-toggle="tab">Города</a></li>
</ul>
<div class="tab-content">
    <div class="tab-pane active" id="tab_country">
        <?
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'country_name';
        $columns = array(
            array(
                'name' => 'country_name',
                'header' => 'Страна<i class="icon-sort' . ($sort == 'country_name' ? ' icon-sort-down' : ($sort == 'country_name.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCountry->itemCount ? 'весь период' : null
            ),
            array(
                'name' => 'shows',
                'header' => 'Показы<i class="icon-sort' . ($sort == 'shows' ? ' icon-sort-down' : ($sort == 'shows.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCountry->total('shows')
            ),
            array(
                'name' => 'clicks',
                'header' => 'Переходы<i class="icon-sort' . ($sort == 'clicks' ? ' icon-sort-down' : ($sort == 'clicks.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCountry->total('clicks')
            ),
            array(
                'name' => 'ctr',
                'header' => 'CTR<i class="icon-sort' . ($sort == 'ctr' ? ' icon-sort-down' : ($sort == 'ctr.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCountry->total('ctr')
            ),
            array(
                'name' => 'sum_price',
                'header' => 'Расход (без НДС)<i class="icon-sort' . ($sort == 'sum_price' ? ' icon-sort-down' : ($sort == 'sum_price.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCountry->total('sum_price')
            ),
            array(
                'name' => 'avg_price',
                'header' => 'Цена за переход<i class="icon-sort' . ($sort == 'avg_price' ? ' icon-sort-down' : ($sort == 'avg_price.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCountry->total('avg_price')
            ),
        );
        if($campaign->cost_type == Campaigns::COST_TYPE_ACTION){
            $columns[] = array(
                'name' => 'actions',
                'header' => 'Действия<i class="icon-sort' . ($sort == 'actions' ? ' icon-sort-down' : ($sort == 'actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCountry->total('actions')
            );
            $columns[] = array(
                'name' => 'declined_actions',
                'header' => 'Отклоненные<i class="icon-sort' . ($sort == 'declined_actions' ? ' icon-sort-down' : ($sort == 'declined_actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCountry->total('declined_actions')
            );
            $columns[] = array(
                'name' => 'avg_clicks_per_action',
                'header' => 'Среднее количество переходов<i class="icon-sort' . ($sort == 'avg_clicks_per_action' ? ' icon-sort-down' : ($sort == 'avg_clicks_per_action.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCountry->total('avg_clicks_per_action')
            );
            $columns[] = array(
                'name' => 'avg_action_clicks_cost',
                'header' => 'Средняя стоимость действия<i class="icon-sort' . ($sort == 'avg_action_clicks_cost' ? ' icon-sort-down' : ($sort == 'avg_action_clicks_cost.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCountry->total('avg_action_clicks_cost')
            );
        }
        $this->widget('zii.widgets.grid.CGridView', array(
            'id' => 'by-country-grid',
            'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering table-blue'),
            'template' => '{items}{pager}',
            'dataProvider' => $reportCountry,
            'columns' => $columns,
        ));
        ?>
    </div>
    <div class="tab-pane" id="tab_city">
        <?
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'city_name';
        $columns = array(
            array(
                'name' => 'city_name',
                'header' => 'Город<i class="icon-sort' . ($sort == 'city_name' ? ' icon-sort-down' : ($sort == 'city_name.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCity->itemCount ? 'весь период' : null
            ),
            array(
                'name' => 'shows',
                'header' => 'Показы<i class="icon-sort' . ($sort == 'shows' ? ' icon-sort-down' : ($sort == 'shows.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCity->total('shows')
            ),
            array(
                'name' => 'clicks',
                'header' => 'Переходы<i class="icon-sort' . ($sort == 'clicks' ? ' icon-sort-down' : ($sort == 'clicks.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCity->total('clicks')
            ),
            array(
                'name' => 'ctr',
                'header' => 'CTR<i class="icon-sort' . ($sort == 'ctr' ? ' icon-sort-down' : ($sort == 'ctr.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCity->total('ctr')
            ),
            array(
                'name' => 'sum_price',
                'header' => 'Расход (без НДС)<i class="icon-sort' . ($sort == 'sum_price' ? ' icon-sort-down' : ($sort == 'sum_price.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCity->total('sum_price')
            ),
            array(
                'name' => 'avg_price',
                'header' => 'Цена за переход<i class="icon-sort' . ($sort == 'avg_price' ? ' icon-sort-down' : ($sort == 'avg_price.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCity->total('avg_price')
            ),
        );
        if($campaign->cost_type == Campaigns::COST_TYPE_ACTION){
            $columns[] = array(
                'name' => 'actions',
                'header' => 'Действия<i class="icon-sort' . ($sort == 'actions' ? ' icon-sort-down' : ($sort == 'actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCity->total('actions')
            );
            $columns[] = array(
                'name' => 'declined_actions',
                'header' => 'Отклоненные<i class="icon-sort' . ($sort == 'declined_actions' ? ' icon-sort-down' : ($sort == 'declined_actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCity->total('declined_actions')
            );
            $columns[] = array(
                'name' => 'avg_clicks_per_action',
                'header' => 'Среднее количество переходов<i class="icon-sort' . ($sort == 'avg_clicks_per_action' ? ' icon-sort-down' : ($sort == 'avg_clicks_per_action.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCity->total('avg_clicks_per_action')
            );
            $columns[] = array(
                'name' => 'avg_action_clicks_cost',
                'header' => 'Средняя стоимость действия<i class="icon-sort' . ($sort == 'avg_action_clicks_cost' ? ' icon-sort-down' : ($sort == 'avg_action_clicks_cost.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCity->total('avg_action_clicks_cost')
            );
        }
        $this->widget('zii.widgets.grid.CGridView', array(
            'id' => 'by-city-grid',
            'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering table-blue'),
            'template' => '{items}{pager}',
            'dataProvider' => $reportCity,
            'columns' => $columns
        ));
        ?>
    </div>
</div>

<? if(Yii::app()->user->role === Users::ROLE_ADMIN) : ?>
<div class="spacer"></div>
<h5 id="tsource">По площадке:</h5>
<ul class="nav nav-pills" id="platformTabs">
    <li class="active"><a href="#tab_platform_all" data-toggle="tab">Весь трафик</a></li>
    <li><a href="#tab_platform_external" data-toggle="tab">Внешний трафик</a></li>
    <li><a href="#tab_platform_internal" data-toggle="tab">Внутренний трафик</a></li>
    <li><input type="text" class="main-search"></li>
</ul>
<div class="tab-content">
    <div class="tab-pane active" id="tab_platform_all">
        <?
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'platform_server';
        $columns = array(
            array(
                'name' => 'platform_server',
                'header' => 'Сервер<i class="icon-sort' . ($sort == 'platform_server' ? ' icon-sort-down' : ($sort == 'platform_server.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformAll->itemCount ? 'все площадки' : null
            ),
            array(
                'name' => 'platform_id',
                'header' => 'ID<i class="icon-sort' . ($sort == 'platform_id' ? ' icon-sort-down' : ($sort == 'platform_id.desc' ? ' icon-sort-up' : '')) . '"></i>',
            ),
            array(
                'name' => 'shows',
                'header' => 'Показы<i class="icon-sort' . ($sort == 'shows' ? ' icon-sort-down' : ($sort == 'shows.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformAll->total('shows')
            ),
            array(
                'name' => 'clicks',
                'header' => 'Переходы<i class="icon-sort' . ($sort == 'clicks' ? ' icon-sort-down' : ($sort == 'clicks.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformAll->total('clicks')
            ),
            array(
                'name' => 'ctr',
                'header' => 'CTR<i class="icon-sort' . ($sort == 'ctr' ? ' icon-sort-down' : ($sort == 'ctr.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformAll->total('ctr')
            ),
            array(
                'name' => 'sum_price',
                'header' => 'Расход (без НДС)<i class="icon-sort' . ($sort == 'sum_price' ? ' icon-sort-down' : ($sort == 'sum_price.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformAll->total('sum_price')
            ),
            array(
                'name' => 'avg_price',
                'header' => 'Цена за переход<i class="icon-sort' . ($sort == 'avg_price' ? ' icon-sort-down' : ($sort == 'avg_price.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformAll->total('avg_price')
            ),
        );
        if($campaign->cost_type == Campaigns::COST_TYPE_ACTION){
            $columns[] = array(
                'name' => 'actions',
                'header' => 'Действия<i class="icon-sort' . ($sort == 'actions' ? ' icon-sort-down' : ($sort == 'actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformAll->total('actions')
            );
            $columns[] = array(
                'name' => 'declined_actions',
                'header' => 'Отклоненные<i class="icon-sort' . ($sort == 'declined_actions' ? ' icon-sort-down' : ($sort == 'declined_actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformAll->total('declined_actions')
            );
            $columns[] = array(
                'name' => 'avg_clicks_per_action',
                'header' => 'Среднее количество переходов<i class="icon-sort' . ($sort == 'avg_clicks_per_action' ? ' icon-sort-down' : ($sort == 'avg_clicks_per_action.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformAll->total('avg_clicks_per_action')
            );
            $columns[] = array(
                'name' => 'avg_action_clicks_cost',
                'header' => 'Средняя стоимость действия<i class="icon-sort' . ($sort == 'avg_action_clicks_cost' ? ' icon-sort-down' : ($sort == 'avg_action_clicks_cost.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformAll->total('avg_action_clicks_cost')
            );
        }
        $this->widget('zii.widgets.grid.CGridView', array(
            'id' => 'by-platform-all-grid',
            'ajaxUpdate'=>'by-platform-all-grid,by-platform-external-grid,by-platform-internal-grid',
            'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering table-blue'),
            'template' => '{items}{pager}',
            'dataProvider' => $reportPlatformAll,
            'columns' => $columns,
        ));
        ?>
    </div>
    <div class="tab-pane" id="tab_platform_external">
        <?
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'platform_server';
        $columns = array(
            array(
                'name' => 'platform_server',
                'header' => 'Сервер<i class="icon-sort' . ($sort == 'platform_server' ? ' icon-sort-down' : ($sort == 'platform_server.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformExternal->itemCount ? 'все площадки' : null
            ),
            array(
                'name' => 'platform_id',
                'header' => 'ID<i class="icon-sort' . ($sort == 'platform_id' ? ' icon-sort-down' : ($sort == 'platform_id.desc' ? ' icon-sort-up' : '')) . '"></i>',
            ),
            array(
                'name' => 'shows',
                'header' => 'Показы<i class="icon-sort' . ($sort == 'shows' ? ' icon-sort-down' : ($sort == 'shows.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformExternal->total('shows')
            ),
            array(
                'name' => 'clicks',
                'header' => 'Переходы<i class="icon-sort' . ($sort == 'clicks' ? ' icon-sort-down' : ($sort == 'clicks.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformExternal->total('clicks')
            ),
            array(
                'name' => 'ctr',
                'header' => 'CTR<i class="icon-sort' . ($sort == 'ctr' ? ' icon-sort-down' : ($sort == 'ctr.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformExternal->total('ctr')
            ),
            array(
                'name' => 'sum_price',
                'header' => 'Расход (без НДС)<i class="icon-sort' . ($sort == 'sum_price' ? ' icon-sort-down' : ($sort == 'sum_price.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformExternal->total('sum_price')
            ),
            array(
                'name' => 'avg_price',
                'header' => 'Цена за переход<i class="icon-sort' . ($sort == 'avg_price' ? ' icon-sort-down' : ($sort == 'avg_price.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformExternal->total('avg_price')
            ),
        );
        if($campaign->cost_type == Campaigns::COST_TYPE_ACTION){
            $columns[] = array(
                'name' => 'actions',
                'header' => 'Действия<i class="icon-sort' . ($sort == 'actions' ? ' icon-sort-down' : ($sort == 'actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformExternal->total('actions')
            );
            $columns[] = array(
                'name' => 'declined_actions',
                'header' => 'Отклоненные<i class="icon-sort' . ($sort == 'declined_actions' ? ' icon-sort-down' : ($sort == 'declined_actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformExternal->total('declined_actions')
            );
            $columns[] = array(
                'name' => 'avg_clicks_per_action',
                'header' => 'Среднее количество переходов<i class="icon-sort' . ($sort == 'avg_clicks_per_action' ? ' icon-sort-down' : ($sort == 'avg_clicks_per_action.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformExternal->total('avg_clicks_per_action')
            );
            $columns[] = array(
                'name' => 'avg_action_clicks_cost',
                'header' => 'Средняя стоимость действия<i class="icon-sort' . ($sort == 'avg_action_clicks_cost' ? ' icon-sort-down' : ($sort == 'avg_action_clicks_cost.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformExternal->total('avg_action_clicks_cost')
            );
        }
        $this->widget('zii.widgets.grid.CGridView', array(
            'id' => 'by-platform-external-grid',
            'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering table-blue'),
            'template' => '{items}{pager}',
            'dataProvider' => $reportPlatformExternal,
            'columns' => $columns,
        ));
        ?>
    </div>
    <div class="tab-pane" id="tab_platform_internal">
        <?
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'platform_server';
        $columns = array(
            array(
                'name' => 'platform_server',
                'header' => 'Сервер<i class="icon-sort' . ($sort == 'platform_server' ? ' icon-sort-down' : ($sort == 'platform_server.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformInternal->itemCount ? 'все площадки' : null
            ),
            array(
                'name' => 'platform_id',
                'header' => 'ID<i class="icon-sort' . ($sort == 'platform_id' ? ' icon-sort-down' : ($sort == 'platform_id.desc' ? ' icon-sort-up' : '')) . '"></i>',
            ),
            array(
                'name' => 'shows',
                'header' => 'Показы<i class="icon-sort' . ($sort == 'shows' ? ' icon-sort-down' : ($sort == 'shows.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformInternal->total('shows')
            ),
            array(
                'name' => 'clicks',
                'header' => 'Переходы<i class="icon-sort' . ($sort == 'clicks' ? ' icon-sort-down' : ($sort == 'clicks.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformInternal->total('clicks')
            ),
            array(
                'name' => 'ctr',
                'header' => 'CTR<i class="icon-sort' . ($sort == 'ctr' ? ' icon-sort-down' : ($sort == 'ctr.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformInternal->total('ctr')
            ),
            array(
                'name' => 'sum_price',
                'header' => 'Расход (без НДС)<i class="icon-sort' . ($sort == 'sum_price' ? ' icon-sort-down' : ($sort == 'sum_price.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformInternal->total('sum_price')
            ),
            array(
                'name' => 'avg_price',
                'header' => 'Цена за переход<i class="icon-sort' . ($sort == 'avg_price' ? ' icon-sort-down' : ($sort == 'avg_price.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformInternal->total('avg_price')
            ),
        );
        if($campaign->cost_type == Campaigns::COST_TYPE_ACTION){
            $columns[] = array(
                'name' => 'actions',
                'header' => 'Действия<i class="icon-sort' . ($sort == 'actions' ? ' icon-sort-down' : ($sort == 'actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformInternal->total('actions')
            );
            $columns[] = array(
                'name' => 'declined_actions',
                'header' => 'Отклоненные<i class="icon-sort' . ($sort == 'declined_actions' ? ' icon-sort-down' : ($sort == 'declined_actions.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformInternal->total('declined_actions')
            );
            $columns[] = array(
                'name' => 'avg_clicks_per_action',
                'header' => 'Среднее количество переходов<i class="icon-sort' . ($sort == 'avg_clicks_per_action' ? ' icon-sort-down' : ($sort == 'avg_clicks_per_action.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformInternal->total('avg_clicks_per_action')
            );
            $columns[] = array(
                'name' => 'avg_action_clicks_cost',
                'header' => 'Средняя стоимость действия<i class="icon-sort' . ($sort == 'avg_action_clicks_cost' ? ' icon-sort-down' : ($sort == 'avg_action_clicks_cost.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportPlatformInternal->total('avg_action_clicks_cost')
            );
        }
        $this->widget('zii.widgets.grid.CGridView', array(
            'id' => 'by-platform-internal-grid',
            'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering table-blue'),
            'template' => '{items}{pager}',
            'dataProvider' => $reportPlatformInternal,
            'columns' => $columns,
        ));
        ?>
    </div>
</div>
<? endif; ?>
<?endif;?>

<? if($campaign->cost_type == Campaigns::COST_TYPE_ACTION) : ?>
    <h3 id="offers">Предложения:</h3>
    <h5 id="otarget">По предложению:</h5>
    <?= $this->renderPartial('partials/_statistics/otarget', array('reportOffersActions' => $reportOffersActions)); ?>

    <h5 id="odate">По дате:</h5>
    <?= $this->renderPartial('partials/_statistics/odate', array('reportOffersDate' => $reportOffersDate)); ?>

    <h5 id="ocharts">Графики:</h5>
    <?= $this->renderPartial('partials/_statistics/ocharts', array('reportOffersDate' => $reportOffersDate)); ?>

    <h5 id="osource">По вебмастеру:</h5>
    <?= $this->renderPartial('partials/_statistics/osource', array('reportOffersUsers' => $reportOffersUsers)); ?>
<? endif; ?>


<?if($campaign->cost_type == Campaigns::COST_TYPE_RTB):?>

<?endif;?>

<script type="text/javascript">
    $(function () {
        $(".main-search").keyup(function (event) {
            if (event.keyCode == 13) {
                var q = $(this).val();
                $.fn.yiiGridView.update('by-platform-all-grid', {data: {filter:  q} });
                $('.modal-backdrop').remove();
                $('.main-search').focusout().blur();

                return false;
            }
        });
    });
</script>

<script src="https://www.google.com/jsapi"></script>
<script type="text/javascript">
    google.load("visualization", "1", {packages: ["corechart"]});
    google.setOnLoadCallback(drawCharts);
    function drawCharts() {
        var options = {
            width: 973,
            height: 275,
            pointSize: 8,
            backgroundColor: '#f9f9f9',
            axisTitlesPosition: 'none',
            colors: ['#78889a'],
            curveType: 'function',
            annotation: {shows: {style: 'line'}},
            hAxis: {
                baselineColor: '#58666f',
                format: 'dd.MM'
            },
            vAxis: {
                baselineColor: '#58666f',
                viewWindow: {min:0},
                gridlines: { count: 6, color: '#d5d5d5' }
            },
            legend: { position: 'none' },
            chartArea: { left: 80, top: 20, width:"90%", height: "80%"}
        };

        var data = new google.visualization.DataTable();
        data.addColumn({label: "Дата", type: "date"});
        data.addColumn({label: 'Переходов', type: 'number'});
        data.addRows([
            <? foreach($reportDate->getData() as $row){ echo '[new Date("'.$row['date'].'"), '.$row['clicks'].'],'; }?>
        ]);

        var chart = new google.visualization.LineChart(document.getElementById('chart_clicks'));
        chart.draw(data, $.extend({},options));

        var data = new google.visualization.DataTable();
        data.addColumn({label: "Дата", type: "date"});
        data.addColumn({label: 'Показов', type: 'number'});
        data.addRows([
            <? foreach($reportDate->getData() as $row){ echo '[new Date("'.$row['date'].'"), '.$row['shows'].'],'; }?>
        ]);

        var chart = new google.visualization.LineChart(document.getElementById('chart_shows'));
        chart.draw(data, options);

        var data = new google.visualization.DataTable();
        data.addColumn({label: "Дата", type: "date"});
        data.addColumn({label: 'Расходы (без НДС)', type: 'number'});
        data.addRows([
            <? foreach($reportDate->getData() as $row){ echo '[new Date("'.$row['date'].'"), '.$row['sum_price'].'],'; }?>
        ]);

        var chart = new google.visualization.LineChart(document.getElementById('chart_sum_price'));
        chart.draw(data, options);

        <? if($campaign->cost_type == Campaigns::COST_TYPE_ACTION) : ?>
            var data = new google.visualization.DataTable();
            data.addColumn({label: "Дата", type: "date"});
            data.addColumn({label: 'Действий', type: 'number'});
            data.addRows([
                <? foreach($reportDate->getData() as $row){ echo '[new Date("'.$row['date'].'"), '.$row['actions'].'],'; }?>
            ]);

            var chart = new google.visualization.LineChart(document.getElementById('chart_actions'));
            chart.draw(data, $.extend({},options));
        <? endif; ?>

        if(typeof offersCharts == "function"){ offersCharts(options); }
    }
</script>