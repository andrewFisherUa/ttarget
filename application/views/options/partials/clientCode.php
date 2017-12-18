<? /**
 * @var ClientCode $clientCode
 * @var CActiveForm $form
 * @var $this CController
 */
?>
<div id="ccForm" class="control-group" style="display: <?= $clientCode->isValid() ? 'none' : 'block'; ?>">
    <ol>
        <b>Требуется установленный на сервере PHP</b>
        <li><div>
            Создайте файл <?= $clientCode->file_name; ?> в любой директории вашего сайта, установите на него права доступа rw-rw-rw- (0666, права на запись).<br/>
            Введите полный URL файла (например, http://<span class="ccPlatformName"></span>/js/<?= $clientCode->file_name; ?>)<br/>
            <?= $form->textField($clientCode, 'url', array(
                'value' => IDN::decodeUrl($clientCode->url),
                'maxlength' => 2048,
                'class' => 'span5'
            )); ?>
            <?= $form->error($clientCode, 'url'); ?>
        </div></li>
        <li><div>
            Введите путь к файлу на сервере (например, /home/www/<span class="ccPlatformName"></span>/js/<?= $clientCode->file_name; ?> или ../js/<?= $clientCode->file_name; ?>)<br>
            <?= $form->textField($clientCode, 'path', array(
                'maxlength' => 512,
                'class' => 'span5'
            )); ?>
            <?= $form->error($clientCode, 'path'); ?>
        </div></li>
        <li><div>
            Скачайте <a href="#" id="ccControl">script.php</a>, установите его на сервере и укажите полный URL скрипта
            <?= $form->textField($clientCode, 'control_url', array(
                'value' => IDN::decodeUrl($clientCode->control_url),
                'maxlength' => 2048,
                'class' => 'span5'
            )); ?>
            <?= $form->error($clientCode, 'control_url'); ?>
        </div></li>
        <?= $form->hiddenField($clientCode, 'platform_id'); ?>
    </ol>
    <?= $form->error($clientCode, 'file_name'); ?>
    <button class="btn btn-close" id="ccSave"><i class="icon-14 icon-ok-sign"></i>Проверить размещение</button>
</div>
<div id="ccDescription" style="display: <?= ! $clientCode->isValid() ? 'none' : 'block'; ?>">
    <p><b>Защита от блокировки успешно установлена.</b></p>
    Расположение JS: <?= CHtml::encode($clientCode->url); ?><br/>
    Скрипт управления: <?= CHtml::encode($clientCode->control_url); ?><br/>
    <a href="javascript: void(0)" id="ccEdit">Изменить</a>
</div>
<script type="text/javascript">
    var ccValid;
    var blockCode;

    $('#ccSave').on('click', function(e){
        ccRequest({
            ClientCode: {
                platform_id: $('#ClientCode_platform_id').val(),
                url: $('#ClientCode_url').val(),
                path: $('#ClientCode_path').val(),
                control_url: $('#ClientCode_control_url').val(),
                file_name: "<?= $clientCode->file_name; ?>"
            }
        });
        return false;
    });
    <? if($clientCode->isValid()) : ?>
        $('#ccEdit').on('click', function(e){
            $('#ccDescription').hide();
            $('#ccForm').show();
            ccValid = false;
            $('#Blocks_use_client_code').trigger('change');
        });

        ccValid = true;
        blockCode = <?= CJavaScript::encode($clientCode->getAdvancedBlock()); ?>;
        updateBlockCode();
    <? else : ?>
        ccValid = false;
        blockCode = undefined;
        updateBlockCode();
    <? endif; ?>
    $('#ClientCode_path').on('change', updateControlLink);
    $('#Blocks_use_client_code').trigger('change');

    $('.ccPlatformName').text($('#Blocks_platform_id option:selected').text().trim());
    $('#ClientCode_platform_id').val($('#Blocks_platform_id option:selected').val());
    updateControlLink();
</script>