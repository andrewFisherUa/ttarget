<?
/**
 * @var Controller $this
 * @var Campaigns $campaign
 * @var CArrayDataProvider $report
 * @var GoogleDataProvider $dataProvider
 */
?>
<?
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/json2/json2.js');
Yii::app()->clientScript->registerScriptFile('http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-select.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/bootstrap-select.css', 'screen');

?>
<div>
    <h3 class="campaign-information-header-title">Новости кампании <input type="text" class="main-search"></h3>
</div>

<div class="campaign-information-table">
    <?
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'create_date.desc';
    $this->widget('zii.widgets.grid.CGridView', array(
        'id' => 'news-grid',
        'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering'),
        'dataProvider' => $report,
        'template' => '{items}',
        'rowCssClassExpression' => '$data["class"] . (!$data["is_active"] ? " inactive" : "")',
        'rowHtmlOptionsExpression' => 'array("data-news-id" => $data[\'news_id\'])',
        'afterAjaxUpdate' => 'activeTable',
        'columns' => array(
            array(
                'name' => 'create_date',
                'header' => 'Дата<i class="icon-sort' . ($sort == 'create_date' ? ' icon-sort-down' : ($sort == 'create_date.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'value' => 'isset($data["create_date"]) ? date("d.m.y", strtotime($data["create_date"])) : ""',
            ),
            array(
                'name' => 'news_name',
                'header' => 'Новость<i class="icon-sort' . ($sort == 'name' ? ' icon-sort-down' : ($sort == 'name.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'value' => 'Yii::app()->controller->renderPartial(\'partials/_name_column_user\', $data)',
                'type' => 'raw',
            ),
            array(
                'name' => 'id',
                'header' => 'ID<i class="icon-sort' . ($sort == 'id' ? ' icon-sort-down' : ($sort == 'id.desc' ? ' icon-sort-up' : '')) . '"></i>',
            ),
            array(
                'header' => 'Активность<i class="icon-sort'.($sort == 'is_active'?' icon-sort-down':($sort == 'is_active.desc'?' icon-sort-up':'')).'"></i>',
                'name' => 'is_active',
                'type'=>'raw',
                'value'=>'$data["is_active"] == 1 ? "<i class=\'icon-12 icon-status-green\'></i>" : "<i class=\'icon-12 icon-status-red\'></i>"'
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
                'header' => 'CTR<i class="icon-sort' . ($sort == 'ctr' ? ' icon-sort-down' : ($sort == 'ctr.desc' ? ' icon-sort-up' : '')) . '"></i>'
            ),
        ),
        'enableSorting' => true,
    ));
    ?>
</div>

<div id="custom-period" style="display:none">
    <div id="modal-campaign-getreport" class="show">
        <div class="modal-header">
            <a href="" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="$.fancybox.close(); return false;"></a>
            <h3>Выберите интервал</h3>
        </div>
        <div class="modal-body">
            <div class="getreport-row">
                <label>С <input type="text" data-date-format="dd.mm.yyyy" id="mmdate_from" value="<?= date('d.m.Y', strtotime($dateFrom));?>" size="16" class="input-date"></label>
                <label>до <input type="text" data-date-format="dd.mm.yyyy" id="mmdate_to" value="<?= date('d.m.Y', strtotime($dateTo));?>" size="16" class="input-date"></label>
                <button  name="report" value="full" class="btn" onclick="getCustomPeriod()">Обновить</button>
                <br/><br/>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(function ($) {
        activeTable();
    });
    var activeTable = function () {
        $('.teaser').hide();
        $('tr.news td:not(:nth-child(4))').on('click', function () {
            $('.teaser[data-news-id=' + $(this).parent().data('news-id') + ']').toggle();
        });

        $('.news:odd').addClass('odd')
        $('.news:even').addClass('even')
    }

    var baseUrl = '<?= $this->createUrl($this->action->getId(), array('id' => $campaign->id)); ?>';

    var periodSelected = $('#period').prop('selectedIndex');
    $(function () {
        $('#period').on('change', function(){
            if(this.value != 'custom'){
                document.location.href = baseUrl + '?period=' + this.value;
            }else{
                showPeriodSelect();
            }
        });
    })

    function showPeriodSelect()
    {
        $.fancybox($('#custom-period').html().replace('mmdate_from', 'date_from').replace('mmdate_to', 'date_to'),
            $.extend({}, fancyDefaults, {
                "width": 450,
                "minWidth": 450,
                "afterShow":function(){
                    if ($.fn.datepicker) {
                        $('.input-date').datepicker({'weekStart': 1, 'offset_y':15});
                    }
                },
                "afterClose":function(){
                    $('#period').prop('selectedIndex', periodSelected);
                }
            })
        );
        return false;
    }

    function getCustomPeriod()
    {
        document.location.href = baseUrl + '?period=custom&date_from=' + $('#date_from').val() + '&date_to=' + $('#date_to').val();
        $.fancybox.close();
    }
</script>

<? if ($dataProvider->hasReportData()) { ?>
    <div class="campaign-information-charts-container">
        <div class="campaign-information-chart">
            <h3 class="campaign-information-chart-title">Динамика переходов</h3>
            <ul class="campaign-information-chart-legend">
                <? foreach ($dataProvider->getNewsList() as $id => $title) { ?>
                    <li><i class="icon-12 icon-point-grath" style="background-color: <?= $dataProvider->getColors($id) ?>;"></i> <?= $title; ?></li>
                <? } ?>
            </ul>
            <div id="chart1" class="campaign-information-chart-chart"></div>
        </div>
        <div class="campaign-information-chart">
            <h3 class="campaign-information-chart-title">Динамика показов</h3>
            <ul class="campaign-information-chart-legend">
                <? foreach ($dataProvider->getNewsList() as $id => $title) { ?>
                    <li><i class="icon-12 icon-point-grath" style="background-color: <?= $dataProvider->getColors($id) ?>;"></i> <?= $title; ?></li>
                <? } ?>
            </ul>
            <div id="chart2" class="campaign-information-chart-chart"></div>
        </div>
        <div class="campaign-information-chart">
            <h3 class="campaign-information-chart-title">Динамика CTR</h3>
            <ul class="campaign-information-chart-legend">
                <? foreach ($dataProvider->getNewsList() as $id => $title) { ?>
                    <li><i class="icon-12 icon-point-grath" style="background-color: <?= $dataProvider->getColors($id) ?>;"></i> <?= $title; ?></li>
                <? } ?>
            </ul>
            <div id="chart3" class="campaign-information-chart-chart"></div>
        </div>
    </div>

    <script src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
        google.load("visualization", "1", {packages: ["corechart"]});
        google.setOnLoadCallback(drawChart1);
        function drawChart1() {
            var data = google.visualization.arrayToDataTable(<?= $dataProvider->getClicksChartData() ?>);

            var options = {
                pointSize: 8,
                backgroundColor: '#f2f2f2',
                colors: <?= $dataProvider->getColors() ?>,
                hAxis: {
                    baselineColor: '#58666f',
                    gridlines: { count: -1, color: '#d5d5d5' },
                    slantedText: false,
                    maxAlternation: 1,
                    maxTextLines: 1,
                    textStyle: {
                        color: '#43535d',
                        fontName: 'OpenSans',
                        fontSize: 12
                    }
                },
                vAxis: {
                    baselineColor: '#58666f',
                    gridlines: { count: -1, color: '#d5d5d5' },
                    textStyle: {
                        color: '#43535d',
                        fontName: 'OpenSans',
                        fontSize: 12
                    }
                },
                legend: { position: 'none' },
                chartArea: { left: 20, top: 20, width: 915 }
            };

            var chart = new google.visualization.LineChart(document.getElementById('chart1'));
            chart.draw(data, options);

            var data = google.visualization.arrayToDataTable(<?= $dataProvider->getShowsChartData() ?>);

            var options = {
                pointSize: 8,
                backgroundColor: '#f2f2f2',
                colors: <?= $dataProvider->getColors() ?>,
                hAxis: {
                    baselineColor: '#58666f',
                    gridlines: { count: -1, color: '#d5d5d5' },
                    slantedText: false,
                    maxAlternation: 1,
                    maxTextLines: 1,
                    textStyle: {
                        color: '#43535d',
                        fontName: 'OpenSans',
                        fontSize: 12
                    }
                },
                vAxis: {
                    baselineColor: '#58666f',
                    gridlines: { count: -1, color: '#d5d5d5' },
                    textStyle: {
                        color: '#43535d',
                        fontName: 'OpenSans',
                        fontSize: 12
                    }
                },
                legend: { position: 'none' },
                chartArea: { left: 20, top: 20, width: 915 }
            };

            var chart = new google.visualization.LineChart(document.getElementById('chart2'));
            chart.draw(data, options);

            var data = google.visualization.arrayToDataTable(<?= $dataProvider->getCtrChartData() ?>);
            var options = {
                pointSize: 8,
                backgroundColor: '#f2f2f2',
                colors: <?= $dataProvider->getColors() ?>,
                hAxis: {
                    baselineColor: '#58666f',
                    gridlines: { count: -1, color: '#d5d5d5' },
                    slantedText: false,
                    maxAlternation: 1,
                    maxTextLines: 1,
                    textStyle: {
                        color: '#43535d',
                        fontName: 'OpenSans',
                        fontSize: 12
                    }
                },
                vAxis: {
                    baselineColor: '#58666f',
                    gridlines: { count: -1, color: '#d5d5d5' },
                    textStyle: {
                        color: '#43535d',
                        fontName: 'OpenSans',
                        fontSize: 12
                    }
                },
                legend: { position: 'none' },
                chartArea: { left: 20, top: 20, width: 915 }
            };

            var chart = new google.visualization.LineChart(document.getElementById('chart3'));
            chart.draw(data, options);


        }
    </script>
<? } ?>
<script type="text/javascript">
    $(function(){
        $(".main-search").keyup(function(event){
            if(event.keyCode == 13){
                var q = $(this).val();
                $.fn.yiiGridView.update('news-grid', {data: {filter: q} });
                $('.modal-backdrop').remove();
                $('.main-search').focusout().blur();

                return false;
            }
        });


        $('#reports').each(function(index) {
            var id = <?= $campaign->id;?>;
            $(this).bind('click', function() {
                $.fancybox($('#report-template').html().replace('report-form-id', 'report-form').replace('__platform_id__', 'platform_id').replace('__platform_id__', 'platform_id'),
                    $.extend({}, fancyDefaults, {
                        "width": 370,
                        "minWidth": 370,
                        "afterShow":function(){
                            if ($.fn.datepicker) {
                                $('.input-date').datepicker({'weekStart': 1, 'offset_y':15});
                            }
                        }

                    })
                );

                return false;
            });
        });

    });

    var facyboxClose = function(){
        $.fancybox.close();
        return false;
    }

    var genReport = function(hval){
        $('.rep-field').val(hval);
        $('#report-form').submit();
        return false;
    }

    var showEditLink = function(id){
        $.fancybox($('#views-link-template').html().replace('__news_id__', id).replace('__platform_link__', 'platform_link').replace('__platform_id__','link_platform_id').replace('__platform_id__','link_platform_id'),
            $.extend({}, fancyDefaults, {
                "width": 370,
                "minWidth": 370
            })
        );
        return false;
    }

</script>

<div id="report-template" style="display:none">
    <div id="modal-campaign-getreport" class="modal show">
        <div class="modal-header">
            <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>
            <h3>Формирование отчёта</h3>
        </div>
        <div class="modal-body">
            <form action="<?= $this->createUrl('campaigns/report', array('id' => $campaign->id)) ?>" method="POST" id="report-form-id">
                <input name="report" type="hidden" id="report" class="rep-field" />
                <div class="getreport-row">
                    Промежуточный отчёт за период<br>
                    <label>с <input type="text" data-date-format="dd.mm.yyyy" name="date_start" data-date="<?= date('d.m.Y', strtotime($campaign->date_start));?>" value="<?= date('d.m.Y', strtotime($campaign->date_start));?>" size="16" class="input-date"></label>
                    <label>до <input type="text" data-date-format="dd.mm.yyyy" name="date_end" data-date="<?= date('d.m.Y', strtotime($campaign->date_end));?>" value="<?= date('d.m.Y', strtotime($campaign->date_end));?>" size="16" class="input-date"></label>
                    <a class="btn" href="/" onclick="return genReport('ExcelReportByPeriodForClient');"><i class="icon-16 icon-excel"></i> Сформировать</a>
                </div>
            </form>
        </div>
    </div>
</div>

