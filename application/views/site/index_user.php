<?
/**
 * @var Campaigns $modelC
 * @var Users $model
 * @var SiteController $this
 */
Yii::app()->clientScript->registerCoreScript('jquery');
$this->pageTitle = Yii::app()->name;
?>
<div class="page-title-row  page-title-row-big ">
    <h1 class="page-title">
        Активные рекламные кампании <input type="text" value="" class="main-search">
    </h1>
</div>

<div class="page-content">
    <div class="campaign-user">
        <?
        $sort = isset($_GET['Campaigns_sort']) ? $_GET['Campaigns_sort'] : '';
        Yii::app()->controller->widget(
            'zii.widgets.grid.CGridView',
            array(
                'id' => 'campaigns-grid',
                'dataProvider' => $modelC->searchForUser(Yii::app()->user->id, true),
                'template' => '{items}{pager}',
                'cssFile' => false,
                'rowCssClassExpression' => '$data->getGlobalIsActive() ? "row-open":"inactive"',
                'htmlOptions' => array('class' => 'table table-striped table-bordered table-hover table-shadow table-campaign tu-' . $modelC->id),
                'columns' => array(
                    array(
                        'name' => 'name',
                        'type' => 'raw',
                        'header' => 'Название кампании<i class="icon-sort' . ($sort == 'name' ? ' icon-sort-down'
                            : ($sort == 'name.desc' ? ' icon-sort-up' : '')) . '"></i>',
                        'value' => '"<a href=\'". Yii::app()->createUrl("/campaigns/".$data->id)."\'>".$data->name."</a>"'

                    ),
                    array(
                        'name' => 'id',
                        'header' => 'ID<i class="icon-sort'.($sort == 'id'?' icon-sort-down':($sort == 'id.desc'?' icon-sort-up':'')).'"></i>',
                        'headerHtmlOptions' => array('class' => 'min'),
                    ),
                    array(
                        'name' => 'clicks',
                        'header' => 'Переходов/Действий<i class="icon-sort' . ($sort == 'clicks'
                            ? ' icon-sort-down'
                            : ($sort == 'clicks.desc' ? ' icon-sort-up' : '')) . '"></i>',
                        'class' => 'DataColumn',
                        'evaluateHtmlOptions' => true,
                        'htmlOptions' => array(
                            'colspan' => '$data->getGlobalIsActive() ? "1":"2"'
                        ),
                        'value' => '$data->getGlobalIsActive() ? $data->totalDone():"Неактивна"',
                        'headerHtmlOptions' => array('class' => 'min'),
                    ),
                    array(
                        'name' => 'days_left',
                        'header' => 'Осталось дней<i class="icon-sort' . ($sort == 'days_left' ? ' icon-sort-down'
                            : ($sort == 'days_left.desc' ? ' icon-sort-up' : '')) . '"></i>',
                        'headerHtmlOptions' => array('class' => 'min'),
                    ),

                ),
            )
        );
        ?>
    </div>
</div>
<script type="text/javascript">
    $(".main-search").keyup(function (event) {
        if (event.keyCode == 13) {
            var q = $(this).val();
            $.fn.yiiGridView.update('campaigns-grid', {data: {Campaigns: {name: q}} });
            $('.modal-backdrop').remove();
            $('.main-search').focusout().blur();

            return false;
        }
    });
</script>