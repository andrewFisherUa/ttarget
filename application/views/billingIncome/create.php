<?
/* @var $this BillingIncomeController */
/* @var $model BillingIncome */

$this->breadcrumbs=array(
	'Биллинг'=>array('billing/index'),
	'Создать входящий счёт',
);

$this->menu=array(
	array('label'=>'List BillingIncome', 'url'=>array('index')),
	array('label'=>'Manage BillingIncome', 'url'=>array('admin')),
);
?>

<h1>Создать входящий счёт</h1>

<?= $this->renderPartial('_form', array('model'=>$model)); ?>