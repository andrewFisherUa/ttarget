<ul class="nav nav-pills">
    <li class="active"><a href="#tab_offers_clicks" data-toggle="tab">Переходы</a></li>
    <li><a href="#tab_offers_actions" data-toggle="tab">Действия</a></li>
    <li><a href="#tab_offers_payments" data-toggle="tab">Выплаты</a></li>
    <? if(Yii::app()->user->role === Users::ROLE_ADMIN) : ?>
        <li><a href="#tab_offers_rewards" data-toggle="tab">Вознаграждения</a></li>
    <? endif; ?>
</ul>
<div class="tab-content" style="height: 295px;">
    <div class="tab-pane active" id="tab_offers_clicks"><div id="chart_offers_clicks"></div></div>
    <div class="tab-pane" id="tab_offers_actions"><div id="chart_offers_actions"></div></div>
    <div class="tab-pane" id="tab_offers_payments"><div id="chart_offers_payments"></div></div>
    <div class="tab-pane" id="tab_offers_rewards"><div id="chart_offers_rewards"></div></div>
</div>

<script type="text/javascript">
    function offersCharts(options) {
        var data = new google.visualization.DataTable();
        data.addColumn({label: "Дата", type: "date"});
        data.addColumn({label: 'Переходов', type: 'number'});
        data.addRows([
            <? foreach($reportOffersDate->getData() as $row){ echo '[new Date("'.$row['date'].'"), '.$row['clicks'].'],'; }?>
        ]);
        var chart = new google.visualization.LineChart(document.getElementById('chart_offers_clicks'));
        chart.draw(data, $.extend({}, options));

        var data = new google.visualization.DataTable();
        data.addColumn({label: "Дата", type: "date"});
        data.addColumn({label: 'Действий', type: 'number'});
        data.addRows([
            <? foreach($reportOffersDate->getData() as $row){ echo '[new Date("'.$row['date'].'"), '.$row['actions'].'],'; }?>
        ]);
        var chart = new google.visualization.LineChart(document.getElementById('chart_offers_actions'));
        chart.draw(data, $.extend({}, options));

        var data = new google.visualization.DataTable();
        data.addColumn({label: "Дата", type: "date"});
        data.addColumn({label: 'Выплата', type: 'number'});
        data.addRows([
            <? foreach($reportOffersDate->getData() as $row){ echo '[new Date("'.$row['date'].'"), '.$row['sum_payment'].'],'; }?>
        ]);
        var chart = new google.visualization.LineChart(document.getElementById('chart_offers_payments'));
        chart.draw(data, $.extend({}, options));

        <? if(Yii::app()->user->role === Users::ROLE_ADMIN) : ?>
            var data = new google.visualization.DataTable();
            data.addColumn({label: "Дата", type: "date"});
            data.addColumn({label: 'Вознаграждение', type: 'number'});
            data.addRows([
                <? foreach($reportOffersDate->getData() as $row){ echo '[new Date("'.$row['date'].'"), '.$row['sum_reward'].'],'; }?>
            ]);
            var chart = new google.visualization.LineChart(document.getElementById('chart_offers_rewards'));
            chart.draw(data, $.extend({}, options));
        <? endif; ?>
    }
</script>