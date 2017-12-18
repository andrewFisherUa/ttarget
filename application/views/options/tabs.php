<?
/**
 * @var OptionsController $this
 */
?>
<ul class="nav nav-tabs">
    <li class="<?= $this->action->id == 'index' ? 'active' : ''; ?>"><a href="<?= $this->createUrl('index'); ?>">Сегменты</a></li>
    <li class="<?= $this->action->id == 'constructor' ? 'active' : ''; ?>"><a href="<?= $this->createUrl('constructor'); ?>">Конструктор</a></li>
</ul>