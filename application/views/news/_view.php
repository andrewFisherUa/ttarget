<?
/* @var $this NewsController */
/* @var $data News */
?>

<div class="view">

	<b><?= CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?= CHtml::link(CHtml::encode($data->id), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?= CHtml::encode($data->getAttributeLabel('name')); ?>:</b>
	<?= CHtml::encode($data->name); ?>
	<br />

	<b><?= CHtml::encode($data->getAttributeLabel('title')); ?>:</b>
	<?= CHtml::encode($data->title); ?>
	<br />

	<b><?= CHtml::encode($data->getAttributeLabel('description')); ?>:</b>
	<?= CHtml::encode($data->description); ?>
	<br />

	<b><?= CHtml::encode($data->getAttributeLabel('url')); ?>:</b>
	<?= CHtml::encode($data->url); ?>
	<br />

	<b><?= CHtml::encode($data->getAttributeLabel('is_active')); ?>:</b>
	<?= CHtml::encode($data->is_active); ?>
	<br />

	<? /*
	<b><?= CHtml::encode($data->getAttributeLabel('campaign_id')); ?>:</b>
	<?= CHtml::encode($data->campaign_id); ?>
	<br />

	*/ ?>

</div>