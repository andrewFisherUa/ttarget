<?if(count($notifications)):?>
<div class="campaign-information table-shadow" style="width: 350px; margin-bottom: 10px; padding-bottom: 20px;">
<div class="navbar-inner" style="padding-bottom: 0px; margin-bottom: 0px; height: 20px; padding-top: 10px; padding-left: 20px;">Уведомления:</div>
<div class="campaign-information-header" style="padding-top: 0px;">
<?foreach($notifications as $notification):?>
<div class="offer-notification" id="offer-note-<?=$notification->id?>">
<div style="float: right; "><a href="#" onclick="closeNotification(<?=$notification->id?>); return false;">скрыть</a></div>
<?=$notification->text?></div>
<?endforeach;?>
<div style="text-align: right; border-top: 1px solid #F2F2F2;"><a href="<?=CController::createUrl('offers/notifications')?>">все уведомления</a></div>
</div>
</div>
<script type="text/javascript">
	var closeNotification = function(id){
		$.ajax({
            type: "POST",
            dataType: 'json',
            url: "<?= Yii::app()->request->baseUrl;?>/offers/readNotification/" + id,
            data: {"YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>"},
            success: function (data) {
            	$('#offer-note-'+id).hide();
            } //success
        });//ajax
	};
</script>
<?endif;?>