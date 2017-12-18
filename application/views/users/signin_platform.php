<?
Yii::app()->clientScript->registerCoreScript('jquery');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/fancybox2/jquery.fancybox.css', 'screen');

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/bootstrap-datepicker.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/css/datepicker.css', 'screen');

Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/jquery.form.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/js/ajaxform/form_ajax_binding.js', CClientScript::POS_HEAD);
Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/js/ajaxform/client_val_form.css', 'screen');

/**
 * @var UsersController $this
 * @var Users $user
 * @var Platforms $platform
 */

?>
<div class="page-title-row  page-title-row-big ">
    <div class="title-with-button">
        <h1 class="page-title"></h1>
    </div>
</div>
<div class="well navbar-white" style="width: 700px;">
    <?
    $formId = 'users-form';
    /** @var CActiveForm $form */
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'signin-platform-form',
        'enableClientValidation' => true,
        'focus' => array($platform, 'url'),
        'errorMessageCssClass' => 'input-notification-error  error-simple png_bg',
        'clientOptions' => array('validateOnSubmit' => true,
            'validateOnType' => false,
            'errorCssClass' => 'err',
            'successCssClass' => 'suc',
            'afterValidate' => 'js:function(form,data,hasError){ $.js_afterValidate(form,data,hasError);  }',
            'afterValidateAttribute' => 'js:function(form, attribute, data, hasError){$.js_afterValidateAttribute(form, attribute, data, hasError);}'
        ),
        'htmlOptions'=>array(
            'class'=>'form-horizontal',
        ),
    )); ?>
    <legend>
        Регистрация площадки
    </legend>

    <? if($user->hasErrors() || $platform->hasErrors()){
        echo $form->errorSummary(array($user, $platform));
    }else{?>
        <p><strong>Заполните пожалуйста все поля отмеченные "*":</strong></p>
    <? } ?>

    <div class="control-group">
        <?= $form->labelEx($platform, 'url', array('class' => 'control-label')); ?>
        <div class="controls">
            <?= $form->textField($platform, 'url', array(
                'value' => IDN::decodeUrl($platform->url),
                'class' => 'span6', 'maxlength' => 2048)
            ); ?>
        </div>
    </div>

    <div class="control-group">
        <?= $form->labelEx($platform, 'server', array('class' => 'control-label')); ?>
        <div class="controls">
            <?= $form->textField($platform, 'server', array('class' => 'span6', 'maxlength' => 250)); ?>
        </div>
    </div>

    <div class="control-group">
        <?= $form->labelEx($platform, 'tagIds', array('class' => 'control-label')); ?>
        <div class="controls">
            <ul class="unstyled multicolumn">
            <?= $form->checkBoxList($platform, 'tagIds',
                CHtml::listData(Tags::model()->findAllByAttributes(array('is_public' => 1)), 'id', 'name'),
                array(
                    'labelOptions'=>array('class'=>'inline'),
                    'separator' => ' ',
                    'template' => '<li>{input}{label}</li>'
                ));
            ?>
            </ul>
        </div>
    </div>

    <div class="control-group">
        <?= $form->labelEx($platform, 'currency', array('class' => 'control-label')); ?>
        <div class="controls">
            <?= $form->dropDownList($platform, 'currency', PlatformsCpc::getCurrencies(), array('class'=>'span6')); ?>
        </div>
    </div>

    <div class="control-group">
        <?= $form->labelEx($user, 'login', array('class' => 'control-label')); ?>
        <div class="controls">
            <?= $form->textField($user, 'login', array('class' => 'span6', 'maxlength' => 128)); ?>
        </div>
    </div>

    <div class="control-group">
        <?= $form->labelEx($user, 'email', array('class' => 'control-label')); ?>
        <div class="controls">
            <?= $form->textField($user, 'email', array('class' => 'span6', 'maxlength' => 128)); ?>
        </div>
    </div>

    <div class="control-group">
        <?= $form->labelEx($user, 'password', array('class' => 'control-label')); ?>
        <div class="controls">
            <?= $form->passwordField($user, 'password', array('class' => 'span6', 'maxlength' => 128)); ?>
        </div>
    </div>

    <div class="control-group">
        <?= $form->labelEx($user, 'repeat_password', array('class' => 'control-label')); ?>
        <div class="controls">
            <?= $form->passwordField($user, 'repeat_password', array('class' => 'span6', 'maxlength' => 128)); ?>
        </div>
    </div>

    <p><strong>Пожалуйста, укажите хотя бы один дополнительный способ связаться с вами.</strong></p>

    <div class="control-group">
        <?= $form->labelEx($user, 'phone', array('class' => 'control-label')); ?>
        <div class="controls">
            <? $this->widget('CMaskedTextField', array(
                'model' => $user,
                'attribute' => 'phone',
                'mask' => '7 (999) 999-9999',
                'htmlOptions' => array('class' => 'span6')
            )); ?>
        </div>
    </div>

    <div class="control-group">
        <?= $form->labelEx($user, 'skype', array('class' => 'control-label')); ?>
        <div class="controls">
            <?= $form->textField($user, 'skype', array('class' => 'span6', 'maxlength' => 128)); ?>
        </div>
    </div>

    <div class="control-group">
        <?= $form->labelEx($user, 'billing_details_type', array('class' => 'control-label')); ?>
        <div class="controls">
            <div class="pull-left">
                <?= $form->dropDownList(
                    $user,
                    'billing_details_type',
                    $user->getAvailablePaymentTypes(),
                    array('class' => 'input150', 'prompt' => 'Выберите тип')
                ); ?>
            </div>
            <div class="pull-left">
                <?= $form->textField($user,'billing_details_text', array('style' => 'width: 296px;')); ?>
            </div>
        </div>
    </div>

    <p class="text-center">
        <?= $form->checkBox($user, 'acceptRules'); ?>
        <label for="Users_acceptRules" class="inline">С <a id="showRules" href="#">правилами</a> ознакомлен и согласен.</label>
    </p>

    <div class="form-actions">


        <button class="btn btn-primary" type="submit"><i class="icon-14 icon-ok-sign"></i>Регистрация</button>
    </div>
    <? $this->endWidget(); ?>
</div>

<div id="correction-template" style="display:none">
    <div id="modal-campaign-correction" class="modal show">
        <div class="modal-header">
            <a href="/" data-dismiss="modal" class="icon-16 icon-close close-modal" onclick="return facyboxClose();"></a>
            <h3>Правила</h3>
        </div>
        <div class="modal-body">
            <div class="text-left">
                <p><u>1. Принимаются:</u></p>
                <p>1.1. Сайты на русском и украинском языке с посещаемостью не менее 50 &nbsp;человек в день (по статистике за последний месяц перед датой подачи заявки на участие в системе).</p>
                <p>1.2. Сайты на платном хостинге.</p>
                <p>1.3. Участвующие хотя бы в одной публичной системе статистики (Rambler Top100, Liveinternet, Top.Mail.ru и др.), либо готовые предоставить данные о своей посещаемости по внутренним статистическим системам.</p>
                <p><u>2. Не принимаются:</u></p>
                <p>2.1. Сайты, нарушающие российское законодательство.</p>
                <p>2.2. Сайты, содержащие порнографические материалы или рекламирующие подобные материалы, имеющие оскорбительное и/или порнографическое содержание.</p>
                <p>2.3. Сайты, злоупотребляющие агрессивными рекламными форматами (pop-up, pop-under, splash и аналогичными).</p>
                <p>2.4. Сайты, содержащие вредоносный код или ссылающиеся на страницы, содержащие его. Сайты, которые не проходят проверки безопасности браузеров IE, Firefox, Opera, Safari, Chrome; находящиеся в черных списках.</p>
                <p>2.5. Не законченные, находящиеся в разработке сайты.</p>
                <p>2.6. Сайты, не имеющие собственного контента; специально созданные для перенаправления пользователя на другие страницы.</p>
                <p>2.7. Сайты, предлагающие или рекламирующие заработок за просмотры/переходы и аналогичные виды заработка в интернете, а также сайты, призывающие посетителей к переходам по рекламе.</p>
                <p>2.8. Сайты, источниками посещаемости которых являются биржи трафика, системы активной раскрутки, автосерфинга и т.п. (При подаче заявки на модерацию убедитесь, что информация об источниках в статистике сайта открыта.)</p>
                <p><u>3. Запрещается:</u></p>
                <p>3.1. Накрутка в любых видах.</p>
                <p>3.2. Перенаправление пользователя по ссылкам сети без его ведома.</p>
                <p>3.3. Манипулирование содержимым рекламного блока: изменять картинки, заголовки.</p>
                <p>3.4. Сокрытие или изменение реферера.</p>
                <p>3.5. Размещение блоков на сайтах, не зарегистрированных в системе.</p>
                <p><u>4. Блокировка площадок:</u></p>
                <p>4.1. Администрация сети оставляет за собой право приостановить размещение рекламы на площадке без объяснения причин.</p>
                <p>4.2. В случае обнаружения накрутки часть переходов может быть списана.</p>
                <p><u>5. Выплаты:</u></p>
                <p>5.1. Минимальная сумма вывода - 500 руб. Выплаты производятся с 1 по 10 число следующего месяца.</p>
                <p>Все сайты в системе проходят премодерацию в течение 24 часов с момента подачи заявки (повторной заявки).</p>
                <p>&nbsp;</p>
                <p>График модерации сайтов:</p>
                <p>будни - с 10 до 19 часов;</p>
                <p>Разница в доходах сайтов одной и той же посещаемости может отличаться в 5–10 раз. Это зависит от:</p>
                <ul>
                    <li><span>количества просмотров страниц на сайте;</span></li>
                    <li><span>расположения рекламных блоков на сайте;</span></li>
                    <li><span>настройки дизайна блоков;</span></li>
                    <li><span>общего количества уже имеющейся рекламы.</span></li>
                </ul>
                <div style="text-indent: -24px;">&nbsp;</div>
                <p>После регистрации вам будут доступны рекомендации по увеличению доходности размещенных тизерных блоков.</p>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    jQuery(function ($) {
        $('#showRules').on('click', function () {
            $.fancybox($('#correction-template').html(),
                $.extend({}, fancyDefaults, {
                    "width": 800,
                    "minWidth": 800,
                    "afterShow": function () {
                    }
                })
            );
            return false;
        });
    });
</script>
