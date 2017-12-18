<?
/* @var $this SiteController */
/* @var $error array */

$this->pageTitle=Yii::app()->name . ' - Ошибка ' . $code;
$this->breadcrumbs=array(
	'Ошибка ' . $code,
);
?>

<h2>Ошибка <?= $code; ?></h2>

<div class="error">
<?= CHtml::encode($message); ?>
</div>