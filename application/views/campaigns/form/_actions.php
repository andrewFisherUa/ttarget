<div class="row" id="campaign-actions">
	<div class="spacer-10"></div>
	<div class="modal-header">
		<h3>Цели</h3>
	</div>
	<table id="campaign-actions-table" class="table table-striped table-bordered table-hover">
		<thead>
			<tr>
			<th><?= CampaignsActions::model()->getAttributeLabel('name'); ?></th>
			<th><?= CampaignsActions::model()->getAttributeLabel('target_type'); ?></th>
			<th><?= CampaignsActions::model()->getAttributeLabel('target'); ?></th>
			<th></th>
			</tr>
		</thead>
		<tbody id="campaign-actions-table-tbody">
			<? foreach($model->campaignsActions as $action) : ?>
			<tr id="campaigns-action-<?=$action->id?>">
			<td><a href="#" class="break-word edit-action" data-id="<?= $action->id; ?>" onclick="return false;"><?= CHtml::encode($action->name); ?></a></td>
			<td><?= CHtml::encode(Arr::ad($action->getAvailableTargetTypes(),$action->target_type)); ?></td>
			<td><?= CHtml::encode($action->target); ?></td>
			<td><a href="#" onclick="return false;" data-id="<?= $action->id; ?>" class="btn btn-danger delete-action"><i class="icon-14 icon-trash"></i></a></td>
			</tr>
			<? endforeach; ?>
			<? if(!isset($action)) : ?>
			<tr id="actions-empty-row">
			<td colspan="4"><div class="text-center">Нет целей.</div></td>
			</tr>
			<? endif; ?>
		</tbody>
	</table>
	<div id="action-form" style="display: none;"></div>
	<div id="actions-control-block">
	<button id="add-action" class="btn btn-primary" onclick="return false"><i class="icon-16 icon-add"></i> Добавить цель</button>
	<!--<label class="pull-right">Установить стоимость на все цели:
	<?= $form->textField($model, 'actions_cost', array('class' => 'span1', 'style' => 'margin-bottom: 5px;' )); ?>
	<?= $form->error($model, 'actions_cost'); ?>
	</label>-->
	</div>
</div>
<script type="text/javascript">
    function deleteAction(){
        var el = $(this);
        $.ajax({
            url: '<?= $this->createUrl('campaignsActions/delete');?>/'+el.data('id'),
            dataType: 'json',
            beforeSend: function () {
                $("#campaign-actions").addClass("ajax-sending");
            },
            complete: function () {
                $("#campaign-actions").removeClass("ajax-sending");
            },
            success: function(data){
                if(data.success == true){
                    el.closest('tr').remove();
                    $('#action-form').hide();
                    $('#actions-control-block').show();
                    if($('#campaign-actions-table>tbody>tr').length == 0){
                        $('#campaign-actions-table>tbody').append('<tr id="actions-empty-row"><td colspan="4"><div class="text-center">Нет целей.</div></td></tr>');
                    }
                }
            }
        });
    }

    function editAction(){
        var data = {
            campaign_id: '<?=$model->id?>',
            id: $(this).data('id')
        };
        $.ajax({
            type: "GET",
            url: '<?= $this->createUrl('campaignsActions/returnForm');?>',
            data: data,
            beforeSend: function () {
                $("#users-grid").addClass("ajax-sending");
            },
            complete: function () {
                $("#users-grid").removeClass("ajax-sending");
            },
            success: function (data) {
                $('#actions-control-block').hide();
                $('#action-form').html(data);
                $('#action-form').show();
            } //success
        });//ajax


        return false;
    }

    $(function(){
        $('#add-action').click(function(){
            var data = {
                campaign_id: '<?=$model->id?>'
            };
            $.ajax({
                type: "GET",
                url: '<?= $this->createUrl('campaignsActions/returnForm');?>',
                data: data,
                beforeSend: function () {
                    $("#users-grid").addClass("ajax-sending");
                },
                complete: function () {
                    $("#users-grid").removeClass("ajax-sending");
                },
                success: function (data) {
                    $('#actions-control-block').hide();
                    $('#action-form').html(data);
                    $('#action-form').show();
                } //success
            });//ajax


            return false;
        });

        $('.edit-action').click(editAction);
        $('.delete-action').click(deleteAction);
    });

</script>