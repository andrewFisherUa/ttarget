<?
/**
 * @var Controller $this
 * @var Campaigns $campaign
 * @var Platforms[] $platforms
 * @var GoogleDataProvider $dataProvider
 */
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/json2/json2.js');
Yii::app()->clientScript->registerScriptFile('http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-select.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/bootstrap-select.css', 'screen');

Yii::app()->clientScript->registerCoreScript('jquery');
Yii::app()->clientScript->registerCoreScript('jquery.ui');
Yii::app()->clientScript->registerCssFile(Yii::app()->clientScript->getCoreScriptUrl().'/jui/css/base/jquery-ui.css', 'screen');

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/jquery.zoomcrop.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/jquery.zoomcrop.css', 'screen');

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/jquery.iframe-transport.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/jquery.fileupload.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/jquery.fileupload.css', 'screen');
?>
<div>
    <a href="<?= $this->createUrl('/news/create?returnTo=' . $campaign->id); ?>" id="news-add"
       class="btn title-right-btn"><i class="icon-16 icon-add"></i> Добавить новость</a>

    <h3 class="campaign-information-header-title">Новости кампании <input type="text" class="main-search">
    </h3>
</div>
<div class="campaign-information-table">
    <?
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'create_date.desc';
    $this->widget('zii.widgets.grid.CGridView', array(
        'id' => 'news-grid',
        'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering'),
        'dataProvider' => $report,
        'template' => '{items}',
        'rowCssClassExpression' => '$data["class"] . (!$data["is_active"] ? " inactive" : "") . (isset($data["url_status"]) && $data["url_status"] != 0 && $data["url_status"] != 200 ? " error" : "")',
        'rowHtmlOptionsExpression' => 'array("data-news-id" => $data[\'news_id\'])',
        'afterAjaxUpdate' => 'activeTable',
        'columns' => array(
            array(
                'name' => 'create_date',
                'header' => 'Дата<i class="icon-sort' . ($sort == 'create_date' ? ' icon-sort-down' : ($sort == 'create_date.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'value' => 'isset($data["create_date"]) ? date("d.m.y", strtotime($data["create_date"])) : ""',
            ),
            array(
                'name' => 'name',
                'header' => 'Новость<i class="icon-sort' . ($sort == 'name' ? ' icon-sort-down' : ($sort == 'name.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'value' => 'Yii::app()->controller->renderPartial(\'partials/_name_column_admin\', $data)',
                'type' => 'raw',
            ),
            array(
                'name' => 'id',
                'header' => 'ID<i class="icon-sort' . ($sort == 'id' ? ' icon-sort-down' : ($sort == 'id.desc' ? ' icon-sort-up' : '')) . '"></i>',
            ),
            array(
                'name' => 'is_active',
                'header' => 'Активность<i class="icon-sort' . ($sort == 'is_active' ? ' icon-sort-down' : ($sort == 'is_active.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'value' => 'Chtml::dropDownList("", $data["is_active"], array("1" => "Активен", "0" => "Не активен"), array("onchange" => "return changeActivity($(this).val(), \'".$data["class"]."\', ".($data["id"]).")"))',
                'type' => 'raw'
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
$(function () {
    $(".main-search").keyup(function (event) {
        if (event.keyCode == 13) {
            var q = $(this).val();
            $.fn.yiiGridView.update('news-grid', {data: {filter:  q} });
            $('.modal-backdrop').remove();
            $('.main-search').focusout().blur();

            return false;
        }
    });

    $('#news-add').each(function (index) {
        var id = $(this).data('id');
        $(this).bind('click', function () {
            $.ajax({
                type: "POST",
                url: "<?= Yii::app()->request->baseUrl;?>/news/returnForm?c=<?= $campaign->id ?>",
                data: {"update_id": id, "YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>"},
                beforeSend: function () {
                    $("#news-grid").addClass("ajax-sending");
                },
                complete: function () {
                    $("#news-grid").removeClass("ajax-sending");
                },
                success: function (data) {
                    $.fancybox(data,
                        $.extend({}, fancyDefaults, {
                            "width": 560,
                            "minWidth": 560,
                            "afterClose": function () {
                                updateGrid();
                                var q = $(".main-search").val();
                                $.fn.yiiGridView.update('news-grid', {data: {filter:  q} });
                            } //onclosed functi
                        })
                    );//fancybox
                    //  console.log(data);
                } //success
            });//ajax
            return false;
        });
    });


    $('#reports').each(function (index) {
        var id = <?= $campaign->id;?>;
        $(this).bind('click', function () {
            $.fancybox($('#report-template').html().replace('report-form-id', 'report-form'),
                $.extend({}, fancyDefaults, {
                    "width": 370,
                    "minWidth": 370,
                    "afterShow": function () {
                        if ($.fn.datepicker) {
                            $('input.input-date:visible').datepicker({'weekStart': 1, 'offset_y': 15});
                        }
                        if ($.fn.selectpicker) {
                            $('select.selectpick:visible').selectpicker();
                        }
                    }
                })
            );

            return false;
        });
    });

    if (document.location.hash) {
        $(document.location.hash).click();
    }
});

var showEdit = function (id) {
    try { event.stopPropagation();} catch (e) {}
    $.ajax({
        type: "POST",
        url: "<?= Yii::app()->request->baseUrl;?>/news/returnForm",
        data: {"update_id": id, "YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>"},
        beforeSend: function () {
            $("#news-grid").addClass("ajax-sending");
        },
        complete: function () {
            $("#news-grid").removeClass("ajax-sending");
        },
        success: function (data) {
            $.fancybox(data,
                $.extend({}, fancyDefaults, {
                    "width": 560,
                    "minWidth": 560,
                    "afterClose": function () {
                        updateGrid(id);
                    } //onclosed functi
                })
            );//fancybox
            //  console.log(data);
        } //success
    });//ajax
    return false;
}

var changeActivity = function (val, type, id) {
    var urls = {
        news: "<?= $this->createUrl('news/changeActivity'); ?>",
        teaser: "<?= $this->createUrl('teasers/changeActivity'); ?>"
    }
    $.ajax({
        type: "POST",
        url: urls[type],
        data: {"update_id": id, "YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>", "val": val},
        beforeSend: function () {
            $("#news-grid").addClass("ajax-sending");
        },
        complete: function () {
            $("#news-grid").removeClass("ajax-sending");
        }
    });//ajax
    return false;
}

var addTeaser = function (id) {
	try { event.stopPropagation();} catch (e) {}
    $.ajax({
        type: "POST",
        url: "<?= Yii::app()->request->baseUrl;?>/teasers/returnForm?n=" + id,
        data: {"YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>"},
        beforeSend: function () {
            $("#news-grid").addClass("ajax-sending");
        },
        complete: function () {
            $("#news-grid").removeClass("ajax-sending");
        },
        success: function (data) {
            $.fancybox(data,
                $.extend({}, fancyDefaults, {
                    "width": 543,
                    "minWidth": 543,
                    "afterClose": function () {
                        updateGrid(id);
                    } //onclosed functi
                })
            );//fancybox
            //  console.log(data);
        } //success
    });//ajax
    return false;
}

var updateTeaser = function (id, nid) {
    $.ajax({
        type: "POST",
        url: "<?= Yii::app()->request->baseUrl;?>/teasers/returnForm?n=" + nid,
        data: {"update_id": id, "YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>"},
        beforeSend: function () {
            $("#news-grid").addClass("ajax-sending");
        },
        complete: function () {
            $("#news-grid").removeClass("ajax-sending");
        },
        success: function (data) {
            $.fancybox(data,
                $.extend({}, fancyDefaults, {
                    "width": 543,
                    "minWidth": 543,
                    "afterShow":function(){
                        if ($.fn.selectpicker()) {
                            $('select.selectpicker:visible').selectpicker();
                        }
                    },
                    "afterClose": function () {
                        updateGrid(nid);
                    } //onclosed functi
                })
            );//fancybox
            //  console.log(data);
        } //success
    });//ajax
    return false;
}

var updateGrid = function(nid) {
    $("#news-grid").addClass("ajax-sending");
    $.fn.yiiGridView.update('news-grid',{
        complete: function(jqXHR, status) {
            if (status=='success'){
                if(nid) $('#a-teasers-' + nid).click();
            }
            $("#news-grid").removeClass("ajax-sending");
        }
    });

}


var delCampaign = function (id) {
    if (confirm('Удалить кампанию?')) {
        document.location = "<?= Yii::app()->request->baseUrl;?>/campaigns/delete/" + id;
    }

    return false;
}

var delTeaser = function (id, nid) {
    if (confirm('Удалить тизер?')) {
        $.ajax({
            type: "POST",
            url: "<?= Yii::app()->request->baseUrl;?>/teasers/delete/" + id,
            data: {"YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>"},
            success: function (data) {
                facyboxClose();
            }
        });//ajax
        return false;
    }

    return false;
}

var genReport = function (hval) {
    $('.rep-field').val(hval);
    $('#report-form').submit();
    return false;
}

</script>

<div id="report-template" style="display:none">
    <div id="modal-campaign-getreport" class="modal show">
        <div class="modal-header">
            <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal"
               onclick="return facyboxClose();"></a>

            <h3>Формирование отчёта</h3>
        </div>
        <div class="modal-body">
            <form action="<?= $this->createUrl('campaigns/report', array('id' => $campaign->id)) ?>" method="POST" id="report-form-id">
                <input name="report" type="hidden" id="report" class="rep-field"/>

                <div class="getreport-row">
                    Полный отчёт <a class="btn" href="/" onclick="return genReport('ExcelReportFull');"><i
                            class="icon-16 icon-excel"></i> Сформировать</a>
                </div>
                <div class="getreport-row">
                    Промежуточный отчёт за период<br>
                    <label>с <input type="text" data-date-format="dd.mm.yyyy" name="date_from"
                                    data-date="<?= date('d.m.Y', strtotime($campaign->date_start)); ?>"
                                    value="<?= date('d.m.Y', strtotime($campaign->date_start)); ?>" size="16"
                                    class="input-date"></label>
                    <label>до <input type="text" data-date-format="dd.mm.yyyy" name="date_to"
                                     data-date="<?= date('d.m.Y', strtotime($campaign->date_end)); ?>"
                                     value="<?= date('d.m.Y', strtotime($campaign->date_end)); ?>" size="16"
                                     class="input-date"></label>
                    <a class="btn" href="/" onclick="return genReport('ExcelReportByPeriod');"><i class="icon-16 icon-excel"></i>
                        Сформировать</a>
                </div>
                <div class="getreport-row">
                    Отчёт для партнёра
                    <?= CHtml::dropDownList('platform_id', '', CHtml::listData($platforms, 'id', 'server'), array('class' => 'selectpick',  'data-live-search' => 'true')); ?><br/>
                    <label>с <input type="text" data-date-format="dd.mm.yyyy" name="date_from2"
                                    data-date="<?= date('d.m.Y', strtotime($campaign->date_start)); ?>"
                                    value="<?= date('d.m.Y', strtotime($campaign->date_start)); ?>" size="16"
                                    class="input-date"></label>
                    <label>до <input type="text" data-date-format="dd.mm.yyyy" name="date_to2"
                                     data-date="<?= date('d.m.Y', strtotime($campaign->date_end)); ?>"
                                     value="<?= date('d.m.Y', strtotime($campaign->date_end)); ?>" size="16"
                                     class="input-date"></label>
                    <a class="btn" href="/" onclick="return genReport('ExcelReportForPartner');"><i class="icon-16 icon-excel"></i>
                        Сформировать</a>
                </div>
            </form>
        </div>
    </div>
</div>