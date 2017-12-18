<?
/**
 * @var CArrayDataProvider $reportTags
 */
Yii::app()->clientScript->registerCoreScript('jquery');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.css', 'screen');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/json2/json2.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/ajaxform/jquery.form.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/ajaxform/form_ajax_binding.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile('http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/js/ajaxform/client_val_form.css','screen');
Yii::app()->clientScript->registerCoreScript('jquery');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-datepicker.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-select.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/datepicker.css', 'screen');
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/bootstrap-select.css', 'screen');

$this->renderPartial('tabs');
?>
<? if(isset($_GET['error'])){?><br />
<div id="error-note" class="notification errorshow png_bg">
	        <a href="#" onclick="document.location='options/'" class="close"><img src="<?= Yii::app()->request->baseUrl.'/js/ajaxform/images/icons/cross_grey_small.png';  ?>" title="Закрыть" alt="Закрыть"/></a>
	        <div>Невозможно удалить сегмент, так как он связан с текущими записями.</div>
	    </div>
<? }?>
<div class="page-title-row  page-title-row-big ">
    <div class="title-with-button">
        <!--<a href="<?= $this->createUrl('/options/create'); ?>" class="btn title-right-btn"><i class="icon-16 icon-add"></i> Добавить сегмент</a>-->
        <a href="/" id="tag-add" class="btn title-right-btn"><i class="icon-16 icon-add"></i> Добавить сегмент</a>
        <div class="title-right-btn" style="width: 500px;">
            <strong>За период:</strong>
            <?= CHtml::dropDownList('period',$period,
                array(
                    "today" => 'сегодня',
                    "yesterday" => 'вчера',
                    "month" => 'месяц',
                    "custom" => 'выбранный интервал',
                    "all" => 'все время',
                ),
                array('id' => 'period', 'class' => 'input150')
            ); ?>
            <a style="display: <?= $period == 'custom' ? 'inline' : 'none';?>" href="" onclick="return showPeriodSelect()">С <?= date('d.m.Y', strtotime($dateFrom));?> до <?= date('d.m.Y', strtotime($dateTo));?></a>
        </div>
        <h1 class="page-title" style="width: 200px;">
            Сегменты <input type="text" value="" class="main-search">
        </h1>
    </div>
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

<div class="page-content">
	<div class="admin-settings-block">
		<?
			$sort = isset($_GET['sort'])?$_GET['sort']:'name';
			$this->widget('zii.widgets.grid.CGridView', array(
			'id'=>'tags-grid',
			'htmlOptions' => array('class' => 'table table-striped table-bordered table-shadow table-centering table-tags'),
			'dataProvider'=>$reportTags,
			'template' => '{items}{pager}',
            'afterAjaxUpdate' => 'js: function(id, data){ $("[data-toggle=\'tooltip\']").tooltip(); }',
			'columns'=>array(
					array(
						'name' => 'name',
						'type'=>'raw',
						'value'=>'"<a href=\'/\' onclick=\'return editTag(".$data[\'id\'].")\'>".$data[\'name\']."</a>"',
						'header' => '<i class="icon-sort'.($sort == 'name'?' icon-sort-down':($sort == 'name.desc'?' icon-sort-up':'')).'"></i>'
                            .' Название',
					),
                    array(
                        'name' => 'shows',
                        'header' => '<i class="icon-sort'.($sort == 'shows'?' icon-sort-down':($sort == 'shows.desc'?' icon-sort-up':'')).'"></i>'
                            .' Посещаемость <b class="caption" data-toggle="tooltip" data-placement="top" title="сумма показов на площадках">i</b>',
                    ),
                    array(
                        'name' => 'ctr',
                        'header' => '<i class="icon-sort'.($sort == 'ctr'?' icon-sort-down':($sort == 'ctr.desc'?' icon-sort-up':'')).'"></i>'
                            .' CTR <b class="caption" data-toggle="tooltip" data-placement="top" title="средний показатель CTR по площадкам сегмента">i</b>',
                    ),
                    array(
                        'name' => 'count',
                        'header' => '<i class="icon-sort'.($sort == 'count'?' icon-sort-down':($sort == 'count.desc'?' icon-sort-up':'')).'"></i>'
                            .' Кол-во сайтов <b class="caption" data-toggle="tooltip" data-placement="top" title="количество активных площадок сегмента">i</b>',
                    ),
                    array(
                        'name' => 'clicks',
                        'header' => '<i class="icon-sort'.($sort == 'clicks'?' icon-sort-down':($sort == 'clicks.desc'?' icon-sort-up':'')).'"></i>'
                            .' Кол-во переходов <b class="caption" data-toggle="tooltip" data-placement="top" title="общее количество переходов с площадок сегмента">i</b>',
                    ),
				),
		)); ?>
	</div>
    <div class="admin-settings-block">
        <h1>Новый алгоритм ротации тизеров</h1>
        <?= CHtml::form(); ?>
        <label><?= CHtml::checkBox('version', $version == 1, array('uncheckValue' => 0)); ?> Включить новый алгоритм</label>
        <?= CHtml::submitButton('Сохранить'); ?>
        <?= CHtml::endForm(); ?>
    </div>

</div>

<script type="text/javascript">
	$(function(){
        $("[data-toggle='tooltip']").tooltip();
		$(".main-search").keyup(function(event){
		    if(event.keyCode == 13){
		    	var q = $(this).val();
        		$.fn.yiiGridView.update('tags-grid', {data: {Tags:{name:q}} });
        		$('.modal-backdrop').remove();
        		$('.main-search').focusout().blur();
        		
        		return false;
		    }
		});
		$('#tag-add').each(function(index) {
		        $(this).bind('click', function() {
		            $.ajax({
		                type: "POST",
		                url: "<?= Yii::app()->request->baseUrl;?>/options/returnForm",
		                data:{"YII_CSRF_TOKEN":"<?= Yii::app()->request->csrfToken;?>"},
		                beforeSend : function() {
			                $("#tags-grid").addClass("ajax-sending");
			            },
			            complete : function() {
			                $("#tags-grid").removeClass("ajax-sending");
			            },
		                success: function(data) {
		                    $.fancybox(data,
                                $.extend({}, fancyDefaults, {
		                            "width": 560,
		                            "minWidth": 560,
		                            "afterClose":    function() {
		                                    var page=$("li.selected  > a").text();
	                                		$.fn.yiiGridView.update('tags-grid', {url:'',data:{}});
		                            } //onclosed functi
		                        })
                            );//fancybox
		                    //  console.log(data);
		                } //success
		            });//ajax
		            return false;
		        });
		    });
	});
	    
	var editTag = function(id) {
        $.ajax({
            type: "POST",
            url: "<?= Yii::app()->request->baseUrl;?>/options/returnForm",
            data:{"update_id":id, "YII_CSRF_TOKEN":"<?= Yii::app()->request->csrfToken;?>"},
            beforeSend : function() {
                $("#tags-grid").addClass("ajax-sending");
            },
            complete : function() {
                $("#tags-grid").removeClass("ajax-sending");
            },
            success: function(data) {
                $.fancybox(data,
                    $.extend({}, fancyDefaults, {
                        "width": 560,
                        "minWidth": 560,
                        "afterClose":    function() {
                                var page=$("li.selected  > a").text();
                        		$.fn.yiiGridView.update('tags-grid', {url:'',data:{}});
                        } //onclosed functi
                    })
                );//fancybox
                //  console.log(data);
            } //success
        });//ajax
        return false;
    };
		    
	var facyboxClose = function(){
		$.fancybox.close();
		return false;
	}
	
	var delTag = function(id){
		if(confirm('Удалить сегмент?')){
			document.location = "<?= Yii::app()->request->baseUrl;?>/options/delete/"+id;
		}
		
		return false;
	}

    var baseUrl = '<?= $this->createUrl($this->action->getId() ); ?>';
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
