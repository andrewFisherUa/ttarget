<?
Yii::app()->clientScript->registerCoreScript('jquery');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.css', 'screen');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/json2/json2.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/jquery.form.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/form_ajax_binding.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile('http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/ajaxform/client_val_form.css', 'screen');

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-datepicker.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/datepicker.css', 'screen');
?>
<div class="page-content">
    <div class="campaign-status-bar">
        <div class="campaign-status-info">
            <span class="campaign-status-time">с <?= DateHelper::getRusDate($platform->created); ?></span>
            <? if($platform->is_active == 1){?>
                <span class="campaign-status button-small-rounded">Активная</span>
            <? } else { ?>
                <span class="campaign-status button-small-rounded-inactive">Не активна</span>
            <? }?>
        </div>
        <h4 class="campaign-status-title">Площадка</h4>
    </div>
</div>
<h1 class="page-title left">
    <a id="edit-campaign" href="<?= $this->createUrl('/platforms/update/'.$platform->id); ?>" onclick="return getUpdateForm('<?= $platform->id; ?>');"><i class="icon-cog-big"></i></a> <?= $platform->server;?>
</h1>
<div class="platform-id left">
    id <?= $platform->id; ?>
</div>
<div class="clearfix"></div>
<div class="campaign-description">
    <strong>Статистика переходов:</strong> <?= $total_clicks; ?>
    <span class="separator">|</span>
    <strong>За период:</strong>
    <?= CHtml::dropDownList('period',$period,
        array(
            "today" => 'сегодня',
            "yesterday" => 'вчера',
            "month" => 'месяц',
            "custom" => 'выбранный интервал',
        ),
        array('id' => 'period', 'class' => 'input150')
    ); ?>

    <a style="display: <?= $period == 'custom' ? 'inline' : 'none';?>" href="" onclick="return showPeriodSelect()">С <?= date('d.m.Y', strtotime($dateFrom));?> до <?= date('d.m.Y', strtotime($dateTo));?></a>
    <a class="campaign-generatereport" href="" onclick="return genReport()" id="reports">Сформировать отчёт</a>
</div>
<div class="campaign-visits-limit">
    <?= (Yii::app()->user->role === Users::ROLE_PLATFORM ? 'доход' : 'бюджет') . ': ' . $total_price; ?>
</div>
<div class="navbar navbar-white">
    <div class="navbar-inner">
        <ul class="nav">
            <li <? if($this->action->id == 'traffic') echo 'class="active"'?>>
                <a href="<?= $this->createUrl('traffic', array('id' => $platform->id, 'period' => $period, 'date_from' => $dateFrom, 'date_to' => $dateTo));?>">Трафик</a>
            </li>
            <li <? if($this->action->id=='news') echo 'class="active"'?>>
                <a href="<?= $this->createUrl('news', array('id' => $platform->id, 'period' => $period, 'date_from' => $dateFrom, 'date_to' => $dateTo));?>">Новости</a>
            </li>
        </ul>
    </div>
</div>
<div class="platform-information-table">
    <? $this->renderPartial($view, array('report' => $report, 'platform' => $platform)); ?>
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
    var baseUrl = '<?= $this->createUrl($this->action->getId(), array('id' => $platform->id)); ?>';
    var reportUrl = '<?= $this->createUrl('report', array('id' => $platform->id)); ?>';
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

    function genReport()
    {
        document.location.href = reportUrl + '?period=<?= $period; ?>'
            + '&date_from=<?= $dateFrom; ?>'
            + '&date_to=<?= $dateTo; ?>'
            + '&report=partner';
        return false;
    }

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

    $('#platform-add').each(function(index) {
        $(this).bind('click', function() {
            getUpdateForm();
            return false;
        });
    });

    function getUpdateForm(id)
    {
        var formData = {
            "YII_CSRF_TOKEN":"<?= Yii::app()->request->csrfToken;?>"
            <? if($platform->user_id !== NULL) : ?>,"user_id": "<?= $platform->user_id?>"<? endif; ?>
        };
        var fancyOptions = $.extend({}, fancyDefaults, {
            "width": 560,
            "minWidth": 560
        });
        if(typeof id != "undefined"){
            formData.update_id = id;
        }else{
            fancyOptions.afterClose = function() {
                window.location.reload();
            }
        }

        $.ajax({
            type: "POST",
            url: "<?= Yii::app()->request->baseUrl;?>/platforms/returnForm",
            data: formData,
            beforeSend : function() {
                $("#news-grid").addClass("ajax-sending");
            },
            complete : function() {
                $("#news-grid").removeClass("ajax-sending");
            },
            success: function(data) {
                $.fancybox(data, fancyOptions);//fancybox
                //  console.log(data);
            } //success
        });//ajax
        return false;
    }

    var facyboxClose = function(){
        $.fancybox.close();
        return false;
    }

    var delPlatform = function(id){
        if(confirm('Удалить площадку?')){
            document.location = "<?= Yii::app()->request->baseUrl;?>/platforms/delete/"+id;
        }

        return false;
    }


</script>

