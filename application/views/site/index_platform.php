<?
    Yii::app()->clientScript->registerCoreScript('jquery');
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/jquery.form.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/form_ajax_binding.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.css', 'screen');
    Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-select.min.js', CClientScript::POS_HEAD);
    Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/bootstrap-select.css', 'screen');
?>

<div class="page-title-row-big" id="stats"></div>
<script type="text/javascript">
    function updateStats(){
        $.ajax({
            url: "<?= $this->createUrl('/platforms/statsPlatform'); ?>",
            success: function(data){
                $('#stats').html(data);

                $('.bill-i-add').each(function(index) {
                    $(this).bind('click', function() {
                        $.ajax({
                            type: "POST",
                            url: "<?= Yii::app()->request->baseUrl;?>/billingIncome/returnFormPlatform",
                            data:{"YII_CSRF_TOKEN":"<?= Yii::app()->request->csrfToken;?>"},
                            beforeSend : function() {
                                $("#billing-incoming-grid").addClass("ajax-sending");
                            },
                            complete : function() {
                                $("#billing-incoming-grid").removeClass("ajax-sending");
                            },
                            success: function(data) {
                                $.fancybox(data,
                                    $.extend({}, fancyDefaults, {
                                        "width": 560,
                                        "minWidth": 560,
                                        "afterClose":    function() {
                                            window.location.href=window.location.href;
                                        } //onclosed functi
                                    })
                                );//fancybox
                                //  console.log(data);
                            } //success
                        });//ajax
                        return false;
                    });
                });
            }
        });
    }
    $(function(){
        updateStats();
        setInterval(updateStats, 10000);
    })

</script>