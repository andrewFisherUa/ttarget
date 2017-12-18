<?
/* @var $this CampaignsController */
/* @var $model Campaigns */
$parents = $model->getParents($model->client_id);

$this->breadcrumbs=array(
	'Клиенты'=>array('users/index'),
	$parents['login'] => array('users/' . $parents['uid']),
	//$parents['name'] => array('campaigns/' . $model->campaign_id),
	$model->name => array('campaigns/'. $model->id),
	'Редактировать'
);

$this->menu=array(
	array('label'=>'List Campaigns', 'url'=>array('index')),
	array('label'=>'Create Campaigns', 'url'=>array('create')),
	array('label'=>'View Campaigns', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage Campaigns', 'url'=>array('admin')),
);
?>

<h1>Редактировать Рекламную кампанию "<b><?= $model->name; ?></b>"</h1>

<?= $this->renderPartial('_form', array('model'=>$model)); ?>