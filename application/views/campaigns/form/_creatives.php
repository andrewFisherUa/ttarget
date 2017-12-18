<div class="row" id="campaign-creatives">
	<div class="spacer-10"></div>
	<div class="modal-header">
		<h3>Креативы</h3>
	</div>
	<table id="campaign-actions-table" class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
				<th><?= CampaignsCreatives::model()->getAttributeLabel('id'); ?></th>
				<th><?= CampaignsCreatives::model()->getAttributeLabel('name'); ?></th>
				<th><?= CampaignsCreatives::model()->getAttributeLabel('type'); ?></th>
				<th><?= CampaignsCreatives::model()->getAttributeLabel('dsp_id'); ?></th>
				<th></th>
			</tr>
		</thead>
		<tbody id="campaign-actions-table-tbody">
			<? foreach($model->creatives as $creative) : ?>
			<tr id="campaigns-action">
				<td><a href="#" class="break-word edit-creative" data-id="<?= $creative->id; ?>" onclick="return false;"><?= CHtml::encode($creative->id); ?></a></td>
				<td><a href="#" class="break-word edit-creative" data-id="<?= $creative->id; ?>" onclick="return false;"><?= CHtml::encode($creative->name); ?></a></td>
				<td><?= CHtml::encode($creative->type); ?></td>
				<td><?= CHtml::encode($creative->rtb_id); ?></td>
				<td><a href="#" onclick="return false;" data-id="<?= $creative->id; ?>" class="btn btn-danger delete-creative"><i class="icon-14 icon-trash"></i></a></td>
			</tr>
			<? endforeach; ?>
			<? if(!isset($creative)) : ?>
			<tr id="actions-empty-row">
				<td colspan="5"><div class="text-center">Нет креативов.</div></td>
			</tr>
			<? endif; ?>
		</tbody>
	</table>
	<!--
	<div id="creative-form" style="display: none;"></div>
	<div id="creatives-control-block">
		<button id="add-creative" class="btn btn-primary" onclick="return false"><i class="icon-16 icon-add"></i> Добавить креатив</button>
	</div>
	 -->
</div>

<script type="text/javascript">
$(function(){
	$('#add-creative').click(function(){

	});
	$('.edit-creative').click(function(){

	});
	$('.delete-creative').click(function(){

	});
});
</script>