<?php
/**
 * @var CampaignsController $this
 * @var CampaignCorrection $correction
 */
?>
<hr>
<? if(isset($correction->error)) : ?>
    <div class="well-small alert-danger">
        <?= $correction->error; ?>
    </div>
<? endif; ?>
<div class="well-small alert-success">
    Метод: <?= Arr::ad($correction->getAvailableMethods(), $correction->method); ?>.
    Расчитываются: <?= Arr::ad($correction->getAvailableCounters(), $correction->counter); ?>.
    Расчитано: <?= $correction->corrected; ?>.
    Задействовано строк: <?= $correction->correctedCount; ?>.
</div>
<? if($correction->getDataCount() > 0) : ?>
    <div id="correction-grid" class="table table-striped table-bordered table-shadow table-centering">
        <table class="items">
            <thead>
                <tr>
                    <? foreach($correction->getAvailableDataFields() as $label) : ?>
                        <th><?= $label; ?></th>
                    <? endforeach; ?>
                    <th>Корректировка</th>
                </tr>
            </thead>
            <tbody>
            <? foreach($correction->getData() as $pos => $row) : ?>
                <tr>
                    <? foreach($correction->getAvailableDataFields() as $attr => $label) : ?>
                        <td><?= $attr == 'date' ? date('d.m.y', strtotime($row[$attr])) : $row[$attr]; ?></td>
                    <? endforeach; ?>
                    <td>
                        <input type="text"
                               name="correction[<?= $pos; ?>][value]"
                               data-min="<?= isset($row[$correction->counter]) ? -((int) $row[$correction->counter]) : 0; ?>"
                               value="<?= $row['correction']; ?>" class="correction">
                        <?
                        foreach($correction->getAvailableKeys() as $attr) :
                            if(isset($row[$attr])) :
                        ?>
                                <input type="hidden" name="correction[<?= $pos; ?>][<?= $attr; ?>]" value="<?= $row[$attr]; ?>">
                        <?
                            endif;
                        endforeach;
                        ?>
                    </td>
                </tr>
            <? endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="form-actions">
        <a data-dismiss="modal" class="btn btn-close" href="/" onclick="return facyboxClose();">
            <i class="icon-14 icon-close-white"></i>Отменить
        </a>
        <button class="btn btn-primary" type="submit" name="correctionSave"  id="correctionSave">
            <i class="icon-14 icon-ok-sign"></i> Сохранить
        </button>
    </div>
<? endif; ?>
<script type="text/javascript">
    $('input.input-date[name=date_from]').datepicker('update', '<?= date('d.m.Y', strtotime($correction->dateFrom)); ?>');
    $('input.input-date[name=date_to]').datepicker('update', '<?= date('d.m.Y', strtotime($correction->dateTo)); ?>');
    $('input.input-date').datepicker('set');

    $('input.correction').on('change', function(e){
        var el = $(this);
        var int = parseInt(el.val());
        var min = parseInt(el.data('min'));
        int = isNaN(int) ? 0 : int;
        if(int < min){
            int = min;
        }
        el.val(int);

        if(int != 0){
            $('.modal #correctionSave').attr('disabled', false);
        }else{
            var sum = 0;
            $('.modal input.correction').each(function(i, el){
                sum += parseInt($(el).val());
                if(sum != 0){
                    return false;
                }
            });
            console.log(sum);
            if(sum == 0){
                $('.modal #correctionSave').attr('disabled', true);
            }
        }
    });
</script>