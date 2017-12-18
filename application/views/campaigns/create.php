<?
/* @var $this CampaignsController */
/* @var $model Campaigns */
$parents = $model->getParents($model->client_id);

$this->breadcrumbs=array(
	'Клиенты'=>array('users/index'),
	$parents['login'] => array('users/' . $parents['uid']),
	//$parents['name'] => array('campaigns/' . $model->campaign_id),
	'Создать рекламную кампанию'
);

$this->menu=array(
	array('label'=>'List Campaigns', 'url'=>array('index')),
	array('label'=>'Manage Campaigns', 'url'=>array('admin')),
);
?>

<h1>Создать Рекламную кампанию</h1>

<?= $this->renderPartial('_form', array('model'=>$model)); ?>