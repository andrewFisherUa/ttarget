<?
/* @var $this BillingIncomeController */
/* @var $model BillingIncome */

$this->breadcrumbs=array(
	'Биллинг'=>array('billing/index'),
	'Счет № В'.$model->id => array('billingincome/' . $model->id),
	'Редактировать'
);

$this->menu=array(
	array('label'=>'List BillingIncome', 'url'=>array('index')),
	array('label'=>'Create BillingIncome', 'url'=>array('create')),
	array('label'=>'View BillingIncome', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage BillingIncome', 'url'=>array('admin')),
);
?>

<h1>Счет № "<b>В<?= $model->id; ?></b>"</h1>

<?= $this->renderPartial('_form', array('model'=>$model)); ?>