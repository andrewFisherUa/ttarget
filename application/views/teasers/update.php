<?
$parents = $model->getParents($model->news_id);

$this->breadcrumbs=array(
	'Клиенты'=>array('users/index'),
	$parents['login'] => array('users/' . $parents['uid']),
	$parents['cname'] => array('campaigns/' . $parents['cid']),
	$parents['name'] => array('news/' . $model->news_id),
	$model->title => array('teasers/'.$model->id),
	'Редактировать',
);

$this->menu=array(
	array('label'=>'List Teasers', 'url'=>array('index')),
	array('label'=>'Create Teasers', 'url'=>array('create')),
	array('label'=>'View Teasers', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage Teasers', 'url'=>array('admin')),
);
?>

<h1> Редактировать Тизер "<b><?= $model->title; ?></b>" </h1>
<?= $this->renderPartial('_form', array('model'=>$model,'width'=>$width,'height'=>$height,)); ?>