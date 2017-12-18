<?
$parents = $model->getParents($model->news_id);

$this->breadcrumbs=array(
	'Клиенты'=>array('users/index'),
	$parents['login'] => array('users/' . $parents['uid']),
	$parents['cname'] => array('campaigns/' . $parents['cid']),
	$parents['name'] => array('news/' . $model->news_id),
	'Создать тизер',
);
?>

<h1>Создать Тизер</h1>

<?= $this->renderPartial('_form', array('model'=>$model)); ?>
