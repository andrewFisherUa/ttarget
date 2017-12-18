<h4>Сегодня: <span class="campaign-status"> <?= DateHelper::getRusDate(date('Y-m-d')).' г. '.date('H:i');?> </span></h4>
<h4>Активных площадок: <span class="campaign-status"> <?= $active_platforms; ?></span></h4>
<h4>Доход сегодня: <span class="campaign-status"> <?= $todayProfit.' '.$currency; ?></span></h4>
<h4>Общий доход: <span class="campaign-status"> <?= $totalProfit.' '.$currency; ?></span></h4>
<h4 class="inline">
    Для вывода:
    <span class="campaign-status">
        <span style="color: <?= $debit < Yii::app()->params['PlatformBillingMinimalWithdrawal'] ? 'red' : '#00a651'; ?>">
            <?= $debit." ".$currency ; ?>
        </span>
    </span>
</h4>
<? if($debit < Yii::app()->params['PlatformBillingMinimalWithdrawal']) : ?>
    <small>* минимальная сумма вывода - <?= Yii::app()->params['PlatformBillingMinimalWithdrawal'].' '.$currency; ?>.
        Выплаты производятся с 1 по 10 число следующего месяца.
    </small>
<? endif; ?>
<br/><br/>
<div class="spacer"> </div>
<button class="btn bill-i-add" <?= ( $debit < Yii::app()->params['PlatformBillingMinimalWithdrawal'] || $days_left ? 'disabled="disabled"' : '' );?>><i class="icon-16 icon-add"></i> запрос на вывод</button>
<? if($debit < Yii::app()->params['PlatformBillingMinimalWithdrawal'] || $days_left) : ?>
    до формирования запроса осталось
    <? if($days_left) : ?>
        <?= $days_left; ?> дней
        <? if($debit < Yii::app()->params['PlatformBillingMinimalWithdrawal']) echo ' и '; ?>
    <? endif; ?>
    <? if($debit < Yii::app()->params['PlatformBillingMinimalWithdrawal']) echo (Yii::app()->params['PlatformBillingMinimalWithdrawal'] - $debit)." ".$currency;  ?>
<? endif; ?>