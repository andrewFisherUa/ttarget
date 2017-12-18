<?
/**
 * @var CampaignCorrection $campaignCorrection
 */
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-select.min.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/bootstrap-select.css', 'screen');
?>
    <a class="btn" href="/" id="campaign-correction">Корректировка</a>


<div id="correction-template" style="display:none">
    <div id="modal-campaign-correction" class="modal show">
        <div class="modal-header">
            <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>
            <h3>Корректировка</h3>
        </div>
        <div class="modal-body">
            <form method="POST" action="<?= $this->createUrl('campaigns/addFakeClicks', array('id' => $campaignCorrection->campaign->id)) ?>">
                <div class="correctionSettings">
                    <label class="inline">
                        Корректировать<?= CHtml::dropDownList('counter','', $campaignCorrection->getAvailableCounters()); ?>
                    </label>
                    <label class="inline"> с <input type="text" data-date-format="dd.mm.yyyy" name="date_from" value="<?php echo date('d.m.Y');?>" size="16" class="input-date"></label>
                    <label class="inline"> до <input type="text" data-date-format="dd.mm.yyyy" name="date_to" value="<?php echo date('d.m.Y');?>" size="16" class="input-date"></label>
                    <label class="inline"> количество
                        <input type="text" name="count" value="100" style="width: 24px;">
                    </label>
                    <label class="inline">
                        <?= CHtml::checkBox('hide_empty', true); ?>
                        Спрятать пустые
                    </label>
                    <br>
                    <label class="inline"> метод
                        <?= CHtml::dropDownList('method', '',
                            $campaignCorrection->getAvailableMethods(),
                            array('empty' => 'Автоматически')
                        ); ?>
                    </label>
                    <label class="inline"> тизер
                        <?= CHtml::dropDownList(
                            'teaser_id',
                            '',
                            CHtml::listData(
                                $campaignCorrection->getAvailableTeasers(),
                                'id',
                                'title',
                                'news.name'
                            ),
                            array(
                                'empty' => 'Автоматически',
                                'class' => 'selectpick',
                                'data-live-search' => 'true',
                                'data-container' => 'body',
                            )
                        ); ?>
                    </label>
                    <label class="inline"> площадка
                        <?
                        echo CHtml::dropDownList(
                            'platform_id',
                            '',
                                array(
                                    '' => 'Автоматически',
                                    CampaignCorrection::PLATFORMS_EXCLUDE_EXTERNAL => 'Без внешних сетей'
                                ) +
                                CHtml::listData(
                                    $campaignCorrection->getAvailablePlatforms(),
                                    'id',
                                    'server'
                                )
                            ,
                            array(
                                //'empty' => 'Автоматически',
                                'class' => 'selectpick',
                                'data-live-search' => 'true',
                                'data-container' => 'body',
                            )
                        ); ?>
                    </label>

                    <button class="btn btn-primary" type="submit" id="correctionRecalc">
                        <i class="icon-14 icon-refresh"></i> Расчитать
                    </button>
                </div>
                <div class="correctionTable">
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        $('#campaign-correction').each(function (index) {
            $(this).bind('click', function () {
                $.fancybox($('#correction-template').html(),
                    $.extend({}, fancyDefaults, {
                        "width": 800,
                        "minWidth": 800,
                        "afterShow": function () {
                            if ($.fn.datepicker) {
                                $('.input-date').datepicker({'weekStart': 1, 'offset_y': 15});
                            }
                            if ($.fn.selectpicker) {
                                $('.modal select.selectpick:visible').selectpicker();
                            }

                            $('.correctionSettings:visible').find('input,select').on('change', function(e){
                                $('.correctionTable:visible').html('');
                            });

                            $('#modal-campaign-correction #correctionRecalc').on('click', function(e){
                                $('.correctionTable:visible').html('');
                            });

                            $('#modal-campaign-correction form').on('submit', function (ev) {
                                $.ajax({
                                    type: $(this).attr('method'),
                                    url: $(this).attr('action'),
                                    data: $(this).serialize(),
                                    beforeSend: function(){ $("#modal-campaign-correction .modal-body").addClass("ajax-sending");},
                                    complete: function(){ $("#modal-campaign-correction .modal-body").removeClass("ajax-sending");},
                                    success: function (data) {
                                        $('.correctionTable:visible').html(data);
                                    }
                                });
                                return false;
                            });
                        }
                    })
                );

                return false;
            });
        });
    });

</script>