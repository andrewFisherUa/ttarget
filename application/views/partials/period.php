<? /**
 * @var Controller $this
 * @var string $period
 * @var string $dateFrom
 * @var string $dateTo
 */

//JQuery URI plugin
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/uri/URI.min.js', CClientScript::POS_HEAD);
?>
<strong>За период:</strong>
<?= CHtml::dropDownList('period',$period,
    array(
        "today" => 'сегодня',
        "yesterday" => 'вчера',
        "month" => 'месяц',
        "custom" => 'выбранный интервал',
        "all" => 'все время',
    ),
    array('id' => 'period', 'class' => 'input150 tableFilterSelect')
); ?>
<a style="display: <?= $period == 'custom' ? 'inline' : 'none';?>" href="" onclick="return showPeriodSelect()">С <?= date('d.m.Y', strtotime($dateFrom));?> до <?= date('d.m.Y', strtotime($dateTo));?></a>

<div id="custom-period" style="display:none">
    <div id="modal-campaign-getreport" class="show">
        <div class="modal-header">
            <a href="" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="$.fancybox.close(); return false;"></a>
            <h3>Выберите интервал</h3>
        </div>
        <div class="modal-body">
            <div class="getreport-row">
                <label>С <input name="date_from" type="text" data-date-format="dd.mm.yyyy" id="mmdate_from" value="<?= date('d.m.Y', strtotime($dateFrom));?>" size="16" class="input-date tableFilterSelect"></label>
                <label>до <input name="date_to" type="text" data-date-format="dd.mm.yyyy" id="mmdate_to" value="<?= date('d.m.Y', strtotime($dateTo));?>" size="16" class="input-date tableFilterSelect"></label>
                <button  name="report" value="full" class="btn" onclick="getCustomPeriod()">Обновить</button>
                <br/><br/>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">

    var baseUrl = new URI('<?=$this->createUrl($this->action->getId(),array_diff_key($_GET, 
    									array('date_from' => '', 'date_to' => '')))?>');
	
    									
    var periodSelected = $('#period').prop('selectedIndex');

    $(function () {
    	
		$('.tableFilterSelect:not(div)').on('change',function(e){
			if($(this).attr('name') == 'period' && $(this).val() == 'custom'){
				showPeriodSelect();
			} else {
				// store values into url
				filterTableResults();
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

    //Build uri and redirecting
    function filterTableResults(){
    	var params = {};
		$('.tableFilterSelect').each( function( i, select ) {
			params[$(select).attr('name')] = $(select).val();
		});
		baseUrl.query(params);
		document.location.href = baseUrl;
	};

    function getCustomPeriod()
    {
    	filterTableResults();
		$.fancybox.close();
    }
</script>