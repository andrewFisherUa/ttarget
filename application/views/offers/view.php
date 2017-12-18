<?
/**
 * @var $offerUser OffersUsers
 */
Yii::app()->clientScript->registerCoreScript('jquery');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.css', 'screen');

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-datepicker.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/datepicker.css', 'screen');

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/jquery.form.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/form_ajax_binding.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/ajaxform/client_val_form.css', 'screen');

?>
<div class="page-title-row  page-title-row-big ">
    <div class="title-with-button">
        
        
        <h1 class="page-title">
            Предложения<!--input type="text" value="" class="main-search"-->
        </h1>
    </div>
</div>
<!-- TODO: offer detail view -->
<div class="campaign-information table-shadow">
    <div class="campaign-information-header">
        <div class="campaign-information-header-row1">
            <div class="navbar navbar-white">
	            <div id="offer-view-container" style="">
	            	<table style="width: 100%">
	            		<tr>
	            			<td style="width: 100px;">
	            				<div style="min-height: 30px;">Предложение:</div>
	            			</td>
	            			<td style="">
	            			<div class="navbar-inner"><?=$offer->name?></div>
	            			</td>
	            		</tr>
	            		<tr>
	            			<td style="width: 100px;">
	            				&nbsp;
	            			</td>
	            			<td style="">
	            			<div style="padding-bottom: 20px;"><?=$offer->description?></div>
	            			<div style="min-height: 30px;">
            				<a href="<?=$offer->url?>" target="_blank">предпросмотр</a>
            				</div>
	            			</td>
	            		</tr>
	            		<tr>
	            			<td style="">
	            				<div style="min-height: 30px;">Срок действия:</div>
	            			</td>
	            			<td style="">
	            			<div class="navbar-inner"><?=$offer->getPeriodStr()?></div>
	            			</td>
	            		</tr>
	            		<tr>
	            			<td style="">
	            				<div style="min-height: 30px;">Выплата:</div>
	            			</td>
	            			<td style="">
	            				<div class="navbar-inner"><?=Yii::app()->numberFormatter->formatDecimal($offer->reward)?> руб.</div>
	            			</td>
	            		</tr>
	            		<tr>
	            			<td style="">
	            				<div style="min-height: 30px;">География:</div>
	            			</td>
	            			<td style="">
	            			<div class="navbar-inner"><?=$offer->getCountriesNames() ? $offer->getCountriesNames() : ' любая'?></div>
	            			</td>
	            		</tr>
	            		<tr>
	            			<td style="">
	            				<div style="min-height: 30px;">Изображения:</div>
	            			</td>
	            			<td style="">
	            			<div class="navbar-inner">
	            				<?foreach($offer->images as $image):?>
	            				<div style="display: inline-block; margin-left: 10px; margin-right: 10px;">
	            					<a href="<?=$image->getUrl()?>" target="_blank"><img style="display: inline;" src="<?=$image->getUrl()?>" width="<?=$image->thumbWidth?>" height="<?=$image->thumbHeight?>" alt="<?=$image->filename?>"/></a>
	            					<div style="font-size: 10px; text-align: center;"><?=$image->width?> X <?=$image->height?></div>
	            				</div>
	            				<?endforeach;?>
	            			</div>
	            			</td>
	            		</tr>
	            		
	            	</table>
	            	<div style="margin-top: 20px; border-top: 1px solid #E5E5E5; padding-top: 20px;">
	            	<?if($offer->isActive() /*&& $offer->isGoing()*/):?>
	            		<?if( (!$offer->isUserJoined(Yii::app()->user->id)) ):?>
		            	<div id="join_success" class="notification success png_bg" style="background-color:#A1EDA0; display:none;">
					        <a href="#" class="close" onclick="return false;"><img
					                src="<?=Yii::app()->request->baseUrl.'/js/ajaxform/images/icons/cross_grey_small.png';?>"
					                title="Закрыть" alt="Закрыть"/></a>
					        <div>Ваша заявка успешно отправлена</div>
					    </div>
					   <div id="join_error" class="notification errorshow png_bg" style="display:none; background-image: none;">
					        <a href="#" class="close" onclick="return false;"><img
					                src="<?=Yii::app()->request->baseUrl.'/js/ajaxform/images/icons/cross_grey_small.png';?>"
					                title="Закрыть" alt="Закрыть"/></a>
					        <div id="join_error_inner"></div>
					    </div>
		            	<div class="form-actions notification" id="join_form"  style="border: 1px solid #E5E5E5;">
		            		<div class="controls">
					        	<div class="navbar-inner">Заявка на подключение оффера</div>
					        	<label for="join_description" id="teaser-unique_ip-label">Укажите Ваш опыт работы с партнерскими программами,
					        	Ваш источник траффика, и примерные объемы</label>
					        	<textarea id="description" name="description" class="box" style="background-color: white; width: 70%"></textarea>
					        </div>
				            <button class="btn btn-primary" style="margin-left: 10px; margin-bottom: 10px;" type="submit" onclick="return sendOfferRequest(<?=$offer->id?>);"><i class="icon-14 icon-ok-sign"></i>Сохранить
				            </button>
				        </div>
				        <?else:?>
					        <?if($offerUser->isAccepted()):?>
					        <div class="form-actions notification" id="join_form" style="border: 1px solid #E5E5E5;">
					        	<div class="navbar-inner">Ваша заявка одобрена</div>
					        	<div class="controls">
					        		<label for="join_url" id="teaser-unique_ip-label">Ваша персональная ссылка для этого предложения</label>
					        		<input name="join_url" style="width: 250px;" value="<?=$offerUser->getUrl()?>" onclick="this.select()"/>
					        	</div>
                                <div id="shortLink" class="controls">
                                    <label for="short_url" id="teaser-unique_ip-label">Короткая ссылка</label>
                                    <? if($offerUser->shortLink) : ?>
                                        <input id="showLink" style="width: 250px;" value="<?=$offerUser->shortLink->getUrl()?>" onclick="this.select()"/>
                                    <? else: ?>
                                        <input id="showLink" style="display: none; width: 250px;" value="" onclick="this.select()"/>
                                        <a id="getLink" class="btn btn-primary">Получить</a>
                                    <? endif; ?>
                                </div>
					        </div>
					        <?else:?>
					        <div class="form-actions notification" id="join_form" style="border: 1px solid #E5E5E5; background-image: none;">
					        	<div class="navbar-inner">Статус вашей заявки: <?=$offerUser->getStatusName()?></div>
					        </div>
					        <?endif;?>
				        <?endif;?>
	            	<?endif;?>
	            	</div>
	            </div>
	            
            </div>
        </div>
    </div>
</div>
<!-- TODO: offer join interface -->
<script type="text/javascript">
	var sendOfferRequest = function(id){

		if(true){
			$.ajax({
				url: "<?= Yii::app()->request->baseUrl;?>/offers/join/" + id,
				type: 'post',
				dataType: 'json',
				data: {"YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>", description: $('#description').val()},
				success: function (data) {
					if(data.success){
						$('#join_form').hide();
						$('#join_error').hide();
			            $('#join_success').show(400);
			            $('#offer-view-container').append('<div class="form-actions notification" id="join_form" style="border: 1px solid #E5E5E5;"><div class="navbar-inner">Статус вашей заявки: на модерации</div></div>')
					} else {
						$('#join_success').hide();
						$('#join_error_inner').html(data.errors)
						$('#join_error').show(400);
					};
		        }
			});
		};
	};

$(function(){
	$('.close').live('click',function(){
		$(this).parent().hide(400)
	});
    <? if($offerUser && $offerUser->isAccepted()) : ?>
    $('#getLink').on('click', function(){
        $.ajax({
            url: "<?= $this->createUrl('shortLink', array('id' => $offerUser->id)); ?>",
            data: {"YII_CSRF_TOKEN": "<?= Yii::app()->request->csrfToken;?>"},
            type: 'post',
            dataType: 'json',
            beforeSend : function() {
                $("#shortLink").addClass("ajax-sending");
            },
            complete : function() {
                $("#shortLink").removeClass("ajax-sending");
            },
            success: function(data){
                if(data.success){
                    $('#showLink').val(data.url);
                    $('#showLink').show();
                    $('#getLink').hide();
                }else{
                    alert('Не удалось получить короткую ссылку.');
                }
            }
        });
    });
    <? endif; ?>
});
</script>