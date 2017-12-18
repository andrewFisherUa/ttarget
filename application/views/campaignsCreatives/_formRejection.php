<?
/**
 * @var creative $model
 */
?>

<div id="modal-creative-settings" class="modal show">

	<div class="modal-header">
		<a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>

		<h3>Причина отказа</h3>
	</div>
	<div class="modal-body">
		<?= $model->rejection ?>
	</div>
</div>
