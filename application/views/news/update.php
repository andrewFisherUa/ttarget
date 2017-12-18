<?
/* @var $this NewsController */
/* @var $model News */
$parents = $model->getParents($model->campaign_id);

$this->breadcrumbs=array(
	'Клиенты'=>array('users/index'),
	$parents['login'] => array('users/' . $parents['uid']),
	$parents['name'] => array('campaigns/' . $model->campaign_id),
	$model->name => array('news/'.$model->id),
	'Редактировать',
);

$this->menu=array(
	array('label'=>'List News', 'url'=>array('index')),
	array('label'=>'Create News', 'url'=>array('create')),
	array('label'=>'View News', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage News', 'url'=>array('admin')),
);
?>

<h1>Редактировать Новость "<b><?= $model->name; ?></b>"</h1>

<?= $this->renderPartial('_form', array('model'=>$model)); ?>