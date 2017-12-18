<div class="campaign-information table-shadow" style="width: 350px; margin-bottom: 10px; padding-bottom: 20px;">
<div class="navbar-inner" style="padding-bottom: 0px; margin-bottom: 0px; height: 20px; padding-top: 10px; padding-left: 20px;">Итоговые данные:</div>
<div class="campaign-information-header" style="padding-top: 0px;">
<table style="width: 100%">
<tr>
	<td style="width: 50%">Действия за <?=ucfirst($monthsName)?>: <?=(int)$report['actions_months']?></td>
	<td style="width: 50%">Действия за год: <?=(int)$report['actions_year']?></td>
</tr>
<tr>
	<td style="width: 50%">Выплата за <?=ucfirst($monthsName)?>: <?=(int)$report['sum_cost_months']?> руб.</td>
	<td style="width: 50%">Выплата за год: <?=(int)$report['sum_cost_year']?> руб.</td>
</tr>
</table>
</div>
</div>