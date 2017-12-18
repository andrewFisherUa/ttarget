<? /**
 * @var $this CampaignsController
 */
$this->adaptive = true;
?>
<div>
    <?= CHtml::dropDownList('status',
        Yii::app()->request->getParam('status', ''),
        ActionsLog::getAvailableStatuses(),
        array('class' => 'pull-right input150 tableFilterSelect', 'empty' => 'Все статусы')
    ); ?>

    <?= CHtml::dropDownList(
        'source_type',
        isset($_REQUEST['source_type']) ? $_REQUEST['source_type'] : '',
        ActionsLog::model()->getAvailableSourceTypes(),
        array('empty' => 'Все источники', 'class' => 'tableFilterSelect')
    ); ?>

    <? if(Yii::app()->request->getParam('source_type') == ActionsLog::SOURCE_TYPE_TEASER){
        echo CHtml::dropDownList(
            'platform_id',
            Yii::app()->request->getParam('platform_id', ''),
            CHtml::listData(Platforms::model()->printable()->findAll(), 'id', 'server'),
            array('empty' => 'Все площадки', 'class' => 'selectpicker tableFilterSelect',  'data-live-search' => 'true')
        );
    } elseif(Yii::app()->request->getParam('source_type') == ActionsLog::SOURCE_TYPE_OFFER) {
        echo CHtml::dropDownList(
            'user_id',
            Yii::app()->request->getParam('user_id', ''),
            CHtml::listData(Users::model()->webmaster()->printable()->findAll(), 'id', 'loginEmail'),
            array('empty' => 'Все вебмастера', 'class' => 'selectpicker tableFilterSelect', 'data-live-search' => 'true')
        );
    } ?>
</div>
<?
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'date.desc';
$columns = array(
    array(
        'name' => 'source_type_name',
        'header' => 'Тип<i class="icon-sort' . ($sort == 'source_type_name' ? ' icon-sort-down' : ($sort == 'source_type_name.desc' ? ' icon-sort-up' : '')) . '"></i>',
    ),
    array(
        'name' => 'id',
        'header' => 'ID<i class="icon-sort' . ($sort == 'id' ? ' icon-sort-down' : ($sort == 'id.desc' ? ' icon-sort-up' : '')) . '"></i>',
    ),
    array(
        'name' => 'date',
        'header' => 'Дата<i class="icon-sort' . ($sort == 'date' ? ' icon-sort-down' : ($sort == 'date.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportActionsLog->itemCount ? 'весь период' : null
    ),
    array(
        'name' => 'ip',
        'header' => 'IP<i class="icon-sort' . ($sort == 'ip' ? ' icon-sort-down' : ($sort == 'ip.desc' ? ' icon-sort-up' : '')) . '"></i>',
    ),
    array(
        'name' => 'geo',
        'header' => 'ГЕО<i class="icon-sort' . ($sort == 'geo' ? ' icon-sort-down' : ($sort == 'geo.desc' ? ' icon-sort-up' : '')) . '"></i>',
    ),
//    array(
//        'name' => 'referrer_url',
//        'header' => 'Referrer URL<i class="icon-sort' . ($sort == 'referrer_url' ? ' icon-sort-down' : ($sort == 'referrer_url.desc' ? ' icon-sort-up' : '')) . '"></i>',
//    ),
    array(
        'name' => 'target_url_decoded',
        'header' => 'URL цели<i class="icon-sort' . ($sort == 'target_url_decoded' ? ' icon-sort-down' : ($sort == 'target_url_decoded.desc' ? ' icon-sort-up' : '')) . '"></i>',
    ),
    array(
        'name' => 'source_name',
        'header' => 'Источник<i class="icon-sort' . ($sort == 'source_name' ? ' icon-sort-down' : ($sort == 'source_name.desc' ? ' icon-sort-up' : '')) . '"></i>',
    ),
    array(
        'name' => 'target_name',
        'header' => 'Материал<i class="icon-sort' . ($sort == 'target_name' ? ' icon-sort-down' : ($sort == 'target_name.desc' ? ' icon-sort-up' : '')) . '"></i>',
    ),
    array(
        'name' => 'payment',
        'header' => 'Выплата<i class="icon-sort' . ($sort == 'payment' ? ' icon-sort-down' : ($sort == 'payment.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportActionsLog->total('payment')
    ),
    array(
        'name' => 'reward',
        'header' => 'Вознаграждение<i class="icon-sort' . ($sort == 'reward' ? ' icon-sort-down' : ($sort == 'reward.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportActionsLog->total('reward')
    ),
    array(
        'name' => 'debit',
        'header' => 'Зароботок<i class="icon-sort' . ($sort == 'debit' ? ' icon-sort-down' : ($sort == 'debit.desc' ? ' icon-sort-up' : '')) . '"></i>',
        'footer' => $reportActionsLog->total('debit')
    ),
    array(
        'name' => 'action_name',
        'header' => 'Цель<i class="icon-sort' . ($sort == 'action_name' ? ' icon-sort-down' : ($sort == 'action_name.desc' ? ' icon-sort-up' : '')) . '"></i>'
    ),
    array(
        'type' => 'raw',
        'value' => '
            $data["status"] == ActionsLog::STATUS_DECLINED ?
                "<a data-id=\"".$data["id"]."\" class=\"btn btn-success action-status\">"
                ."<i class=\"icon-ok\"></i></a>"
            : ($data["status"] == ActionsLog::STATUS_ACCEPTED ?
                "<a data-id=\"".$data["id"]."\" class=\"btn btn-danger action-status\">"
                ."<i class=\"icon-remove\"></i></a>"
            :
                "<a data-id=\"".$data["id"]."\" class=\"btn btn-success action-status\">"
                ."<i class=\"icon-ok\"></i></a>"
                ."<a data-id=\"".$data["id"]."\" class=\"btn btn-danger action-status\">"
                ."<i class=\"icon-remove\"></i></a>"
            )
        '
    )
);
$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'actions-details-grid',
    'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering table-blue'),
    'template' => '{items}{pager}',
    'dataProvider' => $reportActionsLog,
    'rowCssClassExpression' =>
        '( $row%2 ? $this->rowCssClass[1] : $this->rowCssClass[0] ) .
        ($data["status"] != ActionsLog::STATUS_ACCEPTED ? " disabled" : "")',
    'columns' => $columns
));
?>

<script type="text/javascript">
    $(function () {
        $(".action-status, .offer-action-status").on("click", function(e){
            var el = $(this);
            $.ajax({
                type: "POST",
                url: "<?= $this->createUrl("campaignsActions/changeActionLogStatus"); ?>",
                data: {
                    id: el.data('id'),
                    status: el.children('i').hasClass('icon-remove') ?
                        "<?= ActionsLog::STATUS_DECLINED; ?>" : "<?= ActionsLog::STATUS_ACCEPTED; ?>"
                },
                beforeSend: function(){ $("#actions-details-grid").addClass("ajax-sending");},
                complete: function(){ $("#actions-details-grid").removeClass("ajax-sending");},
                success: function (data) {
                    if(data.success) {
                        el.siblings('a.actions-status').remove();
                        if(data.status == "<?= ActionsLog::STATUS_DECLINED; ?>"){
                            el.removeClass('btn-danger').addClass('btn-success');
                            el.children('i').removeClass('icon-remove').addClass('icon-ok');
                            el.closest('tr').addClass('disabled');
                        }else{
                            el.removeClass('btn-success').addClass('btn-danger');
                            el.children('i').removeClass('icon-ok').addClass('icon-remove');
                            el.closest('tr').removeClass('disabled');
                        }
                    }else{
                        alert('Не удалось изменить статус действия.');
                    }
                }
            });
            return false;
        });

        $('#reports').on("click", function(e){
            if($('#actions-details-grid>table>tbody>tr>td.empty').length == 1){
                alert('Нет результатов');
            }else {
                document.location = '<?= $this->createUrl(Yii::app()->controller->getAction()->getId(), array_merge($_GET, array('report' => 1))); ?>'
            }
        });
    });
</script>