<? /**
 * @var CampaignsReportsController $this
 * @var CActiveForm $form
 */ ?>
<div id="modal-campaign-reports">
    <div class="modal-header">
        <a href="#" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>
        <h3>Отчетный период</h3>
    </div>
    <div class="modal-body">
        <?
        $form=$this->beginWidget('CActiveForm', array(
            'id'=>'campaignsReports-form',
            //'htmlOptions' => array('enctype' => 'multipart/form-data'),
            'action' => $this->createUrl('update'),
            //'enableAjaxValidation'=>true,
            'enableClientValidation'=>true,
            'errorMessageCssClass' => 'input-notification-error  error-simple png_bg',
            'clientOptions'=>array('validateOnSubmit'=>true,
                'validateOnSubmit' => true,
                'validateOnType'=>false,
                'errorCssClass' => 'err',
                'successCssClass' => 'suc',
                'afterValidate' => 'js:function(form,data,hasError){ $.js_afterValidate(form,data,hasError);  }',
                'errorCssClass' => 'err',
                'successCssClass' => 'suc',
                'afterValidateAttribute' => 'js:function(form, attribute, data, hasError){$.js_afterValidateAttribute(form, attribute, data, hasError);}'
            ),
        )); ?>

        <? //$form->errorSummary($model); ?>
        <?		$val_error_msg = 'Отчетный период не сохранен.';
        $val_success_message = 'Отчетный период сохранен.';
        ?>
        <div id="success-note" class="notification success png_bg" style="display:none;">
            <a href="#" class="close"><img
                    src="<?= Yii::app()->request->baseUrl.'/js/ajaxform/images/icons/cross_grey_small.png';  ?>"
                    title="Закрыть" alt="Закрыть"/></a>
            <div>
                <?   echo $val_success_message;  ?>        </div>
        </div>

        <div id="error-note" class="notification errorshow png_bg"
             style="display:none;">
            <a href="#" class="close"><img
                    src="<?= Yii::app()->request->baseUrl.'/js/ajaxform/images/icons/cross_grey_small.png';  ?>"
                    title="Закрыть" alt="Закрыть"/></a>
            <div>
                <?   echo $val_error_msg;  ?>        </div>
        </div>

        <fieldset>
            <div id="campaignsReports_period">
            <? foreach($periods as $i => $period) : ?>
                <div class="row">
                    <label for="CampaignsReports_<?= $i; ?>_report_date" class="inline">Промежуточный отчет</label>
                    <?= $form->hiddenField($period,'['.$i.']campaign_id'); ?>
                    <?= $form->hiddenField($period,'['.$i.']id'); ?>
                    <?= $form->hiddenField($period,'['.$i.']type'); ?>
                    <?= $form->textField($period,'['.$i.']report_date',array(
                        'size'=>16,
                        'class' => 'input-date period',
                        'data-date-format' => 'dd.mm.yyyy',
                        'value' => date('d.m.Y', strtotime($period->report_date))
                    )); ?>
                    <? if($i == 0) : ?>
                        <a href="#" class="btn" onclick="return addReport();"><i class="icon-plus"></i> </a>
                    <? else : ?>
                        <a href="#" class="btn" onclick="return delReport(this);"><i class="icon-minus"></i> </a>
                    <? endif; ?>
                </div>
            <? endforeach; ?>
            </div>
            <hr/>
            <div class="row">
                <label for="CampaignsReports_<?= $i; ?>_report_date" class="inline">Полный отчет</label>
                <?= $form->hiddenField($full,'[full]campaign_id'); ?>
                <?= $form->hiddenField($full,'[full]id'); ?>
                <?= $form->hiddenField($full,'[full]type'); ?>
                <?= $form->textField($full,'[full]report_date',array(
                    'size'=>16,
                    'class' => 'input-date full',
                    'data-date-format' => 'dd.mm.yyyy',
                    'value' => date('d.m.Y', strtotime($full->report_date))
                )); ?>
                <?= $form->error($full,'['.$i.']report_date'); ?>
            </div>
            <div class="spacer"></div>
            <div class="row">
                <label> <?= CHtml::checkBox('store',$store); ?> Получать уведомления </label>
            </div>
        </fieldset>



        <div class="form-actions right">
            <a data-dismiss="modal" onclick="return facyboxClose();" class="btn btn-close" href="/"><i class="icon-14 icon-close-white"></i>Отменить</a>
            <button class="btn btn-primary" type="submit"><i class="icon-14 icon-ok-sign"></i>Сохранить</button>
        </div>
        <? $this->endWidget(); ?>

    </div><!-- form -->
    <script type="text/javascript">
        $(".close").click(
            function () {
                $(this).parent().hide();
                return false;
            }
        );
        if ($.fn.datepicker) {
            $('.input-date').datepicker({'weekStart': 1, 'offset_y':15});
        }

        var i = <?= $i; ?>;
        var addReport = function(){
            i++;
            $('#campaignsReports_period').append(
                '<div class="row"> '
                    +'<label for="CampaignsReports_'+i+'_report_date" class="inline">Промежуточный отчет</label> '
                    +'<input name="CampaignsReports['+i+'][campaign_id]" type="hidden" value="'+$('#CampaignsReports_0_campaign_id').val()+'">'
                    +'<input name="CampaignsReports['+i+'][type]" type="hidden" value="period">'
                    +'<input size="16" data-date-format="dd.mm.yyyy" class="input-date period" name="CampaignsReports['+i+'][report_date]" id="CampaignsReports_'+i+'_report_date" type="text"> '
                    +'<a href="#" class="btn" onclick="return delReport(this);"><i class="icon-minus"></i> </a> '
                +'</div> '
            );
            $('#campaignsReports_period .input-date').datepicker({'weekStart': 1, 'offset_y':15});
            return false;
        }

        var delReport = function(el){
            $(el).parent().remove();
            return false;
        }
    </script>
</div>