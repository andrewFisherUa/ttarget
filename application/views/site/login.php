<div id="modal-teaser-settings" class="modal show login">
	<div class="modal-body">
		<? $form=$this->beginWidget('CActiveForm', array(
			'id'=>'login-form',
			'enableClientValidation'=>true,
			'clientOptions'=>array(
				'validateOnSubmit'=>true,
			),
		)); ?>
	
	
		<div class="row">
			<?= $form->labelEx($model,'email'); ?>
			<?= $form->textField($model,'email'); ?>
			<?= $form->error($model,'email'); ?>
		</div>
	
		<div class="row">
			<?= $form->labelEx($model,'password'); ?>
			<?= $form->passwordField($model,'password'); ?>
			<?= $form->error($model,'password'); ?>
		</div>
	
		<div class="row rememberMe">
			<?= $form->checkBox($model,'rememberMe'); ?>
			<?= $form->label($model,'rememberMe'); ?>
			<?= $form->error($model,'rememberMe'); ?>
		</div>
	
		<div class="row buttons">
			<?= CHtml::submitButton('Вход'); ?>
		</div>
	
		<? $this->endWidget(); ?>
	</div>
</div>
