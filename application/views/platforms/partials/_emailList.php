<div id="modal-platforms-update">
    <div class="modal-header">
        <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>

        <h3>Список email:</h3>
    </div>
    <div class="modal-body">
        <?= implode(", ", $emails); ?>
    </div>
</div>
 