<? /* @var $this Controller */ ?>
<? $this->beginContent('//layouts/main'); ?>
<div class="span-19">
	<div id="content">
		<?= $content; ?>
	</div><!-- content -->
</div>
<? /*<div class="span-5 last">
	<div id="sidebar">
	<?
		$this->beginWidget('zii.widgets.CPortlet', array(
			'title'=>'Operations',
		));
		$this->widget('zii.widgets.CMenu', array(
			'items'=>$this->menu,
			'htmlOptions'=>array('class'=>'operations'),
		));
		$this->endWidget();
	?>
	</div><!-- sidebar -->
</div> */ ?>
<? $this->endContent(); ?>