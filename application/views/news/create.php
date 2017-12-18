<?
/* @var $this NewsController */
/* @var $model News */

$parents = $model->getParents($model->campaign_id);

$this->breadcrumbs=array(
	'Клиенты'=>array('users/index'),
	$parents['login'] => array('users/' . $parents['uid']),
	$parents['name'] => array('campaigns/' . $model->campaign_id),
	'Создать новость',
);

$this->menu=array(
	array('label'=>'List News', 'url'=>array('index')),
	array('label'=>'Manage News', 'url'=>array('admin')),
);
?>

<h1>Создать Новость</h1>

<?= $this->renderPartial('_form', array('model'=>$model)); ?>