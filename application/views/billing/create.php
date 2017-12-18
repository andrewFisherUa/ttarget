<?
/* @var $this BillingController */
/* @var $model BillingOutgoing */

$this->breadcrumbs=array(
	'Биллинг'=>array('index'),
	'Создать исходящий счёт',
);

$this->menu=array(
	array('label'=>'List BillingOutgoing', 'url'=>array('index')),
	array('label'=>'Manage BillingOutgoing', 'url'=>array('admin')),
);
?>

<h1>Создать исходящий счёт</h1>

<?= $this->renderPartial('_form', array('model'=>$model)); ?>