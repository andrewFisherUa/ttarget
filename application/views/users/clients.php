<?
/**
 * @var Users $model
 */
Yii::app()->clientScript->registerCoreScript('jquery');
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.css', 'screen');
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/json2/json2.js');
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/ajaxform/jquery.form.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl.'/js/ajaxform/form_ajax_binding.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile('http://ajax.aspnetcdn.com/ajax/jquery.dataTables/1.9.4/jquery.dataTables.min.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl.'/js/ajaxform/client_val_form.css','screen');


    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-datepicker.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/datepicker.css', 'screen');

    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/jstree/jstree.min.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/jstree/themes/default/style.min.css', 'screen');

    $this->pageTitle=Yii::app()->name;
?>

<div class="page-title-row  page-title-row-big ">
    <div class="title-with-button">
        <a href="#" id="add_user" class="btn title-right-btn"><i class="icon-16 icon-add"></i> Добавить клиента</a>
        <?= CHtml::dropDownList(
            'cost_type',
            isset($_REQUEST['Campaigns']['cost_type']) ? $_REQUEST['Campaigns']['cost_type'] : '',
            Campaigns::model()->getAvailableCostTypes(),
            array('empty' => 'Все кампании', 'class' => 'title-right-btn input150')
        ); ?>
        <?= CHtml::dropDownList(
            'is_active',
            isset($_REQUEST['Campaigns']['is_active']) ? $_REQUEST['Campaigns']['is_active'] : '',
            array('1' => 'Активные'),
            array('empty' => 'Все кампании', 'class' => 'title-right-btn input150')
        ); ?>

        <h1 class="page-title">
            Клиенты <input type="text" value="<?= isset($_REQUEST['Users']['login']) ? $_REQUEST['Users']['login'] : '';?>" class="main-search">
        </h1>
    </div>
</div>

<div class="page-content">
 <?  $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'users-grid',
    'ajaxUpdate' => false,
	'dataProvider'=>$model->searchAll(isset($_REQUEST['Campaigns']) ? $_REQUEST['Campaigns'] : array()),
	'template'=> '{items}{pager}',
	'hideHeader' => true,
	'cssFile' => false,
	'columns'=>array(
		array(
			'name' => 'logo',
            'header'=>'',
            'filter'  => false,
            'type'=>'raw',
            'value' => 'UserColumn::get($data)',
            'htmlOptions' => array('style'=>'padding-bottom:39px')
            ),

	),
));?>
</div>
<script type="text/javascript">
	$(function(){
        function gridFilter(){
            var q = $("input.main-search").val();
            $.fn.yiiGridView.update('users-grid', {
                data: {
                    Users: {login: q},
                    Campaigns:{
                        cost_type: $('#cost_type').val(),
                        is_active: $('#is_active').val()
                    }
                }
            });
        }

		$(".main-search").keyup(function(event){
		    if(event.keyCode == 13){
		    	gridFilter();
        		$('.modal-backdrop').remove();
        		$('.main-search').focusout().blur();
        		
        		return false;
		    }
		});

        $('#cost_type, #is_active').on("change", gridFilter);

		$('#add_user').bind('click', editUser);

	    $('.addCompany, .clone-campaign').each(function(index) {
	        //var id = $(this).data('id');
	        $(this).live('click', function() {
	        	 $.ajax({
	                type: "POST",
	                url: $(this).attr('href'),
	                data:{/*"update_id":id,*/"YII_CSRF_TOKEN":"<?= Yii::app()->request->csrfToken;?>"},
	                beforeSend : function() {
	                    $("#users-grid").addClass("ajax-sending");
	                },
	                complete : function() {
	                    $("#users-grid").removeClass("ajax-sending");
	                },
	                success: function(data) {
	                	$.fancybox(data,
                			$.extend({}, fancyDefaults, {
                                "width": 543,
                                "minWidth": 543,
                                'onComplete': function() {
                                	$(document).scrollTop(0);
                                    $(".fancybox-wrap").css({'top':'20px', 'bottom':'auto'});
                                 },
                                 'afterLoad': function(){
                                	 $(document).scrollTop(0);
                                     $(".fancybox-wrap").css({'top':'20px', 'bottom':'auto'});
                                 },
                                "afterClose":    function() {
                                    window.location.href=window.location.href;
                                } //onclosed function
                            })
                        );//fancybox
	                    //  console.log(data);
	                } //success
	            });//ajax
	            return false;
	        });
	    });
	});
	var facyboxClose = function(){
		$.fancybox.close();
		return false;
	}

	var delElement = function(id){
		if(confirm('Удалить пользователя?')){
			document.location = "<?= Yii::app()->request->baseUrl;?>/users/delete/"+id;
		}
		
		return false;
	}

</script>