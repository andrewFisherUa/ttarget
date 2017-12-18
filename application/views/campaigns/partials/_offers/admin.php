<?
/**
 * @var Controller $this
 * @var Campaigns $campaign
 * @var Platforms[] $platforms
 * @var GoogleDataProvider $dataProvider
 */
?>
<?
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
    <a href="<?= $this->createUrl('/offers/create?returnTo=' . $campaign->id); ?>" id="offers-add"
       class="btn title-right-btn" style="<?=(!$hasActions ? "display:none;":"")?>"><i class="icon-16 icon-add"></i> Добавить предложение</a>

    <h3 class="campaign-information-header-title">Предложения кампании <!-- <input type="text" class="main-search">  -->
    </h3>
</div>
<div class="campaign-information-table">
    <?
    $this->widget('zii.widgets.grid.CGridView', array(
        'id' => 'offers-grid',
        'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering'),
        'dataProvider' => $offers,
        'template' => '{items}',
        'rowCssClassExpression' => '"offers ".(!$data["is_active"] ? " inactive" : "")',
        'rowHtmlOptionsExpression' => 'array("data-offers-id" => $data[\'id\'])',
        'afterAjaxUpdate' => 'activeTable',
        'columns' => array(
            array(
                'name' => 'id',
                'header' => 'ID<i class="icon-sort' . ($sort == 'id' ? ' icon-sort-down' : ($sort == 'id.desc' ? ' icon-sort-up' : '')) . '"></i>',
            ),
        	/*
        	array(
        			'name' => 'imageMain',
        			'header' => 'Изображение',
        			'value' => '!empty($data[\'imageMain\'])?"<img src=".$data[\'imageMain\']." height="30" />":" --- "',
        	),
        	*/
            array(
                'name' => 'name',
                'header' => 'Предложение<i class="icon-sort' . ($sort == 'name' ? ' icon-sort-down' : ($sort == 'name.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'value' => 'Yii::app()->controller->renderPartial(\'partials/_name_column_admin\', array("class" => "offers","id"=>$data["id"],"name"=>$data["name"]))',
                'type' => 'raw',
            ),
            array(
                'name' => 'payment',
                'header' => 'Выплата<i class="icon-sort' . ($sort == 'payment' ? ' icon-sort-down' : ($sort == 'payment.desc' ? ' icon-sort-up' : '')) . '"></i>',
            ),
            array(
                'name' => 'reward',
                'header' => 'Вознаграждение<i class="icon-sort' . ($sort == 'reward' ? ' icon-sort-down' : ($sort == 'reward.desc' ? ' icon-sort-up' : '')) . '"></i>',
            ),
            array(
                'name' => 'date_start',
                'header' => 'Период действия<i class="icon-sort' . ($sort == 'date_start' ? ' icon-sort-down' : ($sort == 'date_start.desc' ? ' icon-sort-up' : '')) . '"></i>',
            	'value' => '$data->getPeriodStr()'
            
    		),
           	array(
                'name' => 'is_active',
                'header' => 'Активность<i class="icon-sort' . ($sort == 'is_active' ? ' icon-sort-down' : ($sort == 'is_active.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'value' => 'Chtml::dropDownList("", $data["is_active"], array("1" => "Активен", "0" => "Не активен"), array("onchange" => "return changeActivity($(this).val(), ".($data["id"]).")"))',
                'type' => 'raw'
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
        $('tr.offers td:not(:nth-child(4))').on('click', function () {
            $('.teaser[data-offers-id=' + $(this).parent().data('offers-id') + ']').toggle();
        });

        $('.offers:odd').addClass('odd')
        $('.offers:even').addClass('even')
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

    $('#offers-add').each(function (index) {
        var id = $(this).data('id');
        $(this).bind('click', function () {
            $.ajax({
                type: "POST",
                url: "<?= Yii::app()->request->baseUrl;?>/offers/editForm?campaign_id=<?= $campaign->id ?>",
                data: {"update_id": id, "YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>"},
                beforeSend: function () {
                    $("#offers-grid").addClass("ajax-sending");
                },
                complete: function () {
                    $("#offers-grid").removeClass("ajax-sending");
                },
                success: function (data) {
                    $.fancybox(data,
                        $.extend({}, fancyDefaults, {
                            "width": 560,
                            "minWidth": 560,
                            "afterClose": function () {
                                updateGrid();
                                var q = $(".main-search").val();
                		    	if(q != 'undefined' && q != ''){
                		    		$.fn.yiiGridView.update('news-grid', {data: {filter:  q} });
								}
                            }, //onclosed functi
                            "height": 550,
                            'onComplete': function() {
                                $(document).scrollTop(0);
                                $("#fancybox-wrap").css({'top':'20px', 'bottom':'auto'});
                             }
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
                            $('.input-date').datepicker({'weekStart': 1, 'offset_y': 15});
                        }
                        if ($.fn.selectpicker()) {
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
	try {
		event.stopPropagation();
	} catch (e) {
		// TODO: handle exception
	}
    
    $.ajax({
        type: "POST",
        url: "<?= Yii::app()->request->baseUrl;?>/offers/editForm/" + id + "?campaign_id=<?=$campaign->id?>",
        data: {"YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>"},
        beforeSend: function () {
            $("#offers-grid").addClass("ajax-sending");
        },
        complete: function () {
            $("#offers-grid").removeClass("ajax-sending");
        },
        success: function (data) {
            $.fancybox(data,
                $.extend({}, fancyDefaults, {
                    "width": 560,
                    "minWidth": 560,
                    "afterClose": function () {
                        updateGrid(id);
                    }, //onclosed functi
                    "height": 560,
                    'onComplete': function() {
                        $(document).scrollTop(0);
                        $("#fancybox-wrap").css({'top':'20px', 'bottom':'auto'});
                     }
                })
            );//fancybox
            //  console.log(data);
        } //success
    });//ajax
    return false;
}

var changeActivity = function (val, id) {
    $.ajax({
        type: "POST",
        url: "<?= $this->createUrl('offers/changeActivity'); ?>",
        data: {"update_id": id, "YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>", "val": val},
        beforeSend: function () {
            $("#offers-grid").addClass("ajax-sending");
        },
        complete: function () {
            $("#offers-grid").removeClass("ajax-sending");
        }
    });//ajax
    return false;
}

var updateGrid = function(nid) {
    $("#offers-grid").addClass("ajax-sending");
    $.fn.yiiGridView.update('offers-grid',{
        complete: function(jqXHR, status) {
            if (status=='success'){
                
            }
            $("#offers-grid").removeClass("ajax-sending");
        }
    });

}


var delCampaign = function (id) {
    if (confirm('Удалить кампанию?')) {
        document.location = "<?= Yii::app()->request->baseUrl;?>/campaigns/delete/" + id;
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