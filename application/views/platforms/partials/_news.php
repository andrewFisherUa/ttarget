<?
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'platforms-stats-partner',
    'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering'),
    'dataProvider' => $report,
    'template' => '{items}',
    'rowCssClassExpression' => '$data["class"] . (!$data["is_active"] ? " inactive" : "")',
    'rowHtmlOptionsExpression' => 'array("data-campaign-id" => $data[\'campaign_id\'])',
    'afterAjaxUpdate' => 'activeTable',
    'loadingCssClass' => 'ajax-sending',
    'columns' => array(
        array(
            'name' => 'name',
            'header' => 'Кампания<i class="icon-sort' . ($sort == 'name' ? ' icon-sort-down' : ($sort == 'name.desc' ? ' icon-sort-up' : '')) . '"></i>',
            'value' => '($data["class"] == "campaign" ? $data["campaign_name"] : $data["news_name"]) . (isset($data["news_url_decoded"])  ? "<br/><br/>".$data["news_url_decoded"] : "")',
            'type' => 'raw',
            'visible' => (Yii::app()->user->role === Users::ROLE_ADMIN),
            'htmlOptions' => array('class' => 'break-word')
        ),
        array(
            'name' => 'title',
            'header' => 'Тизеры новостных блоков',
            'value' => '$data["class"] == "teaser" ? "<img src=\"/i/t/".$data["picture"]."\" class=\"teaser-image\"> <a href=\"/campaigns/".$data["campaign_id"]."#a-teasers-".$data["news_id"]."\" class=\"teaser-link\">".$data["title"]."</a>" : $data["teasers_count"]',
            'type' => 'raw',
            'htmlOptions' => array('class' => 'break-word')
        ),
        array(
            'name' => 'is_active',
            'header' => 'Статус<i class="icon-sort' . ($sort == 'is_active' ? ' icon-sort-down' : ($sort == 'is_active.desc' ? ' icon-sort-up' : '')) . '"></i>',
            'value' => 'Chtml::dropDownList("", $data["is_active"], array("1" => "Активен", "0" => "Не активен"), array("class" => "selectpicker", "onchange" => "return changeActivity($(this).val(), \'".$data["class"]."\', ".($data["id"]).")"))',
            'type' => 'raw'
        ),
        array(
            'name' => 'shows',
            'header' => 'Показы<i class="icon-sort' . ($sort == 'shows' ? ' icon-sort-down' : ($sort == 'shows.desc' ? ' icon-sort-up' : '')) . '"></i>',
        ),
        array(
            'name' => 'clicks',
            'header' => 'Переходы<i class="icon-sort' . ($sort == 'clicks' ? ' icon-sort-down' : ($sort == 'clicks.desc' ? ' icon-sort-up' : '')) . '"></i>',
        ),
        array(
            'name' => 'ctr',
            'header' => 'CTR<i class="icon-sort' . ($sort == 'ctr' ? ' icon-sort-down' : ($sort == 'ctr.desc' ? ' icon-sort-up' : '')) . '"></i>'
        ),
    ),
    'enableSorting' => true,
));
?>
<script type="text/javascript">
    jQuery(function ($) {
        activeTable();
    });
    var activeTable = function () {
        <?php if(Yii::app()->user->role === Users::ROLE_ADMIN) : ?>
            $('.teaser').hide();
            $('.campaign td:not(:nth-child(3))').on('click', function () {
                console.log($(this).parent().data('campaign-id'));
                $('.teaser[data-campaign-id=' + $(this).parent().data('campaign-id') + ']').toggle();
            });
        <?php endif; ?>
        $('.campaign:odd').addClass('odd')
        $('.campaign:even').addClass('even')
    }
    var changeActivity = function (val, type, id) {
        $.ajax({
            type: "POST",
            url: "<?= $this->createUrl('changeActivity', array('id' => $platform->id)); ?>",
            data: {
                "update_id": id,
                "type": type,
                "val": val,
                "YII_CSRF_TOKEN": "<?php echo Yii::app()->request->csrfToken;?>"
            },
            beforeSend: function () {
                $("#platforms-stats-partner").addClass("ajax-sending");
            },
            complete: function (data) {
                $("#platforms-stats-partner").removeClass("ajax-sending");
                $.fn.yiiGridView.update('platforms-stats-partner');
            }
        });//ajax
        return false;
    }
</script>