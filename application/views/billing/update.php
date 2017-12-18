<?
/* @var $this BillingController */
/* @var $model BillingOutgoing */

$this->breadcrumbs=array(
	'Биллинг'=>array('index'),
	'Счет № И'.$model->id => array('billing/' . $model->id),
	'Редактировать'
);

$this->menu=array(
	array('label'=>'List BillingOutgoing', 'url'=>array('index')),
	array('label'=>'Create BillingOutgoing', 'url'=>array('create')),
	array('label'=>'View BillingOutgoing', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage BillingOutgoing', 'url'=>array('admin')),
);
?>

<h1>Счет № "<b>И<?= $model->id; ?></b>"</h1>

<?= $this->renderPartial('_form', array('model'=>$model)); ?>