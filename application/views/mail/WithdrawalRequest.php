<?php
/**
 * @var BillingIncome[] $models
 */
$total_with_vat = 0;
$total = 0;
$user = $models[0]->source_type == BillingIncome::SOURCE_TYPE_PLATFORM ?
    $models[0]->platform->user : Users::model()->findByPk($models[0]->source_id);
?>
<html>
<body>
Запрос на вывод средств от пользователя <?php echo $user->login; ?> <a href="mailto:<?php echo $user->email; ?>">&lt;<?php echo $user->email; ?>&gt;</a>
<table border="1">
    <tr>
        <th>Источник</th>
        <th>Сумма</th>
        <th>НДС</th>
        <th>Сума с НДС</th>
    </tr>
    <?php foreach($models as $model) : ?>
        <?
            $sum_with_vat = $model->source_type == BillingIncome::SOURCE_TYPE_PLATFORM && $model->platform->is_vat ?
                sprintf('%.2f', $model->sum * (1 + Yii::app()->params->VAT / 100))
                : $model->sum;
            $total += $model->sum;
            $total_with_vat += $sum_with_vat;
        ?>
        <tr>
            <td>
                <?php echo $model->source_type == BillingIncome::SOURCE_TYPE_PLATFORM ?
                    $model->platform->server
                    : Users::model()->findByPk($model->source_id)->loginEmail;
                ?>
            </td>
            <td><?php echo $model->sum; ?></td>
            <td>
                <?= ($model->source_type == BillingIncome::SOURCE_TYPE_PLATFORM && $model->platform->is_vat ?
                    Yii::app()->params->VAT : 0);
                ?>%
            </td>
            <td><?= $sum_with_vat; ?></td>
        </tr>
    <?php endforeach; ?>
    <tr>
        <td></td>
        <td><?php echo $total; ?></td>
        <td></td>
        <td><?= $total_with_vat; ?></td>
    </tr>
</table>
<? if(!empty($user->billing_details_type)) { ?>
    Реквизиты:<br/>
    <?= $user->billing_details_type.": ".$user->billing_details_text; ?>
<? }else{ ?>
    Реквизиты не указаны.
<? } ?>
<p>
    Комментарий:<br/>
    <?php echo CHtml::encode($models[0]->comment); ?>
</p>
</body>
</html>