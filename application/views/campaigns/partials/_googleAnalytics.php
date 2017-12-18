<?
/**
 * @var CampaignsController $this
 * @var Campaigns $campaign
 */
?>

<div class="navbar navbar-white">
    <div class="navbar-inner">
        <ul class="nav">
            <li><a href="<?= $this->createUrl('statistics', array('id' => $campaign->id)); ?>">Ttarget</a></li>
            <li class="active"><a href="<?= $this->createUrl('googleAnalytics', array('id' => $campaign->id)); ?>">Google Analytics</a></li>
        </ul>
    </div>
</div>
<? if($authUrl) { ?>
    <div class="well-small">
        Кампания не привязана к аккаунту Google Analytics. <br/>
        <a href="<?= $authUrl; ?>" class="btn">Привязать кампанию</a>
    </div>
<? }elseif($profiles !== null){ ?>
    <div class="well-small">
        <?= CHtml::form(); ?>
        <? if(!empty($profiles)) : ?>
            <p>Выберите представление:</p>
            <?= CHtml::radioButtonList('profile', key($profiles), $profiles, array('labelOptions' => array('class' => 'inline'))); ?>
            <div class="spacer"></div>
        <? else : ?>
            <p>Для связанного аккаунта Google Analytics нет доступных представлений.</p>
        <? endif; ?>
        <button name="cancel" class="btn btn-close" type="submit"><i class="icon-14 icon-close-white"></i>Отменить</button>
        <? if(!empty($profiles)) : ?>
            <button class="btn btn-primary" type="submit"><i class="icon-14 icon-ok-sign"></i>Сохранить</button>
        <? endif; ?>
        <?= CHtml::endForm(); ?>
    </div>
<? }else{ ?>
    <div class="spacer"></div>
    <a href="<?= $this->createUrl(Yii::app()->controller->action->id, array('id' => $campaign->id, 'cancel' => 1)); ?>"
       class="btn btn-primary">Отвязать кампанию</a>
<? } ?>

<? if($error !== null) { ?>
    <div class="spacer"></div>
    <div class="well-small alert-danger"><?= $error; ?></div>
<? } ?>

<? if(!empty($reports)) : ?>
    <? if(isset($reports['campaign'])) : ?>
        <h3>Площадки</h3>
        <?
        $reportCampaign = new CReportDataProvider($reports['campaign'], 'ga:campaign');
        $sort = isset($_GET['sort']) ? $_GET['sort'] : 'ga:campaign';
        $this->widget('zii.widgets.grid.CGridView', array(
            'id' => 'campaign-grid',
            'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering table-blue'),
            'template' => '{items}{pager}',
            'dataProvider' => $reportCampaign,
            'columns' => array(
                array(
                    'name' => 'ga:campaign',
                    'header' => 'Площадка<i class="icon-sort' . ($sort == 'ga:campaign' ? ' icon-sort-down' : ($sort == 'ga:campaign.desc' ? ' icon-sort-up' : '')) . '"></i>',
                    'footer' => $reportCampaign->itemCount ? 'весь период' : null
                ),
                array(
                    'name' => 'ga:sessions',
                    'header' => 'Сеансы<i class="icon-sort' . ($sort == 'ga:sessions' ? ' icon-sort-down' : ($sort == 'ga:sessions.desc' ? ' icon-sort-up' : '')) . '"></i>',
                    'footer' => $reportCampaign->total('ga:sessions')
                ),
                array(
                    'name' => 'ga:percentNewSessions',
                    'header' => 'Новые сеансы, %<i class="icon-sort' . ($sort == 'ga:percentNewSessions' ? ' icon-sort-down' : ($sort == 'ga:percentNewSessions.desc' ? ' icon-sort-up' : '')) . '"></i>',
                    'footer' => $reportCampaign->total('ga:percentNewSessions')
                ),
                array(
                    'name' => 'ga:newUsers',
                    'header' => 'Новые пользователи<i class="icon-sort' . ($sort == 'ga:newUsers' ? ' icon-sort-down' : ($sort == 'ga:newUsers.desc' ? ' icon-sort-up' : '')) . '"></i>',
                    'footer' => $reportCampaign->total('ga:newUsers')
                ),
                array(
                    'name' => 'ga:bounceRate',
                    'header' => 'Показатель отказов<i class="icon-sort' . ($sort == 'ga:bounceRate' ? ' icon-sort-down' : ($sort == 'ga:bounceRate.desc' ? ' icon-sort-up' : '')) . '"></i>',
                    'footer' => $reportCampaign->total('ga:bounceRate')
                ),
                array(
                    'name' => 'ga:pageviewsPerSession',
                    'header' => 'Страниц/сеанс<i class="icon-sort' . ($sort == 'ga:pageviewsPerSession' ? ' icon-sort-down' : ($sort == 'ga:pageviewsPerSession.desc' ? ' icon-sort-up' : '')) . '"></i>',
                    'footer' => $reportCampaign->total('ga:pageviewsPerSession')
                ),
                array(
                    'name' => 'ga:avgSessionDuration',
                    'header' => 'Сред. длительность сеанса <i class="icon-sort' . ($sort == 'ga:avgSessionDuration' ? ' icon-sort-down' : ($sort == 'ga:avgSessionDuration.desc' ? ' icon-sort-up' : '')) . '"></i>',
                    'footer' => $reportCampaign->total('ga:avgSessionDuration')
                ),
            ),
        ));
        ?>
    <? endif; ?>

    <h3>Тизеры</h3>
    <?
    $reportKeyword = new CReportDataProvider($reports['keyword'], 'ga:keyword');
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'ga:keyword';
    $this->widget('zii.widgets.grid.CGridView', array(
        'id' => 'keyword-grid',
        'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering table-blue'),
        'template' => '{items}{pager}',
        'dataProvider' => $reportKeyword,
        'columns' => array(
            array(
                'name' => 'ga:keyword',
                'header' => 'Тизер<i class="icon-sort' . ($sort == 'ga:keyword' ? ' icon-sort-down' : ($sort == 'ga:keyword.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportKeyword->itemCount ? 'весь период' : null
            ),
            array(
                'name' => 'ga:pageviews',
                'header' => 'Просмотры страниц<i class="icon-sort' . ($sort == 'ga:pageviews' ? ' icon-sort-down' : ($sort == 'ga:pageviews.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportKeyword->total('ga:pageviews')
            ),
            array(
                'name' => 'ga:uniquePageviews',
                'header' => 'Уникальные просмотры страниц<i class="icon-sort' . ($sort == 'ga:uniquePageviews' ? ' icon-sort-down' : ($sort == 'ga:uniquePageviews.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportKeyword->total('ga:uniquePageviews')
            ),
            array(
                'name' => 'ga:avgTimeOnPage',
                'header' => 'Средняя длительность просмотра страницы<i class="icon-sort' . ($sort == 'ga:avgTimeOnPage' ? ' icon-sort-down' : ($sort == 'ga:avgTimeOnPage.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportKeyword->total('ga:avgTimeOnPage')
            ),
        ),
    ));
    ?>

    <h3>Страны</h3>
    <?
    $reportCountry = new CReportDataProvider($reports['country']);
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'ga:country';
    $this->widget('zii.widgets.grid.CGridView', array(
        'id' => 'country-grid',
        'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering table-blue'),
        'template' => '{items}{pager}',
        'dataProvider' => $reportCountry,
        'columns' => array(
            array(
                'name' => 'ga:country',
                'header' => 'Страна<i class="icon-sort' . ($sort == 'ga:country' ? ' icon-sort-down' : ($sort == 'ga:country.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCountry->itemCount ? 'весь период' : null
            ),
            array(
                'name' => 'ga:sessions',
                'header' => 'Сеансы<i class="icon-sort' . ($sort == 'ga:sessions' ? ' icon-sort-down' : ($sort == 'ga:sessions.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCountry->total('ga:sessions')
            ),
            array(
                'name' => 'ga:percentNewSessions',
                'header' => 'Новые сеансы, %<i class="icon-sort' . ($sort == 'ga:percentNewSessions' ? ' icon-sort-down' : ($sort == 'ga:percentNewSessions.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCountry->total('ga:percentNewSessions')
            ),
            array(
                'name' => 'ga:newUsers',
                'header' => 'Новые пользователи<i class="icon-sort' . ($sort == 'ga:newUsers' ? ' icon-sort-down' : ($sort == 'ga:newUsers.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCountry->total('ga:newUsers')
            ),
            array(
                'name' => 'ga:bounceRate',
                'header' => 'Показатель отказов<i class="icon-sort' . ($sort == 'ga:bounceRate' ? ' icon-sort-down' : ($sort == 'ga:bounceRate.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCountry->total('ga:bounceRate')
            ),
            array(
                'name' => 'ga:pageviewsPerSession',
                'header' => 'Страниц/сеанс<i class="icon-sort' . ($sort == 'ga:pageviewsPerSession' ? ' icon-sort-down' : ($sort == 'ga:pageviewsPerSession.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCountry->total('ga:pageviewsPerSession')
            ),
            array(
                'name' => 'ga:avgSessionDuration',
                'header' => 'Сред. длительность сеанса <i class="icon-sort' . ($sort == 'ga:avgSessionDuration' ? ' icon-sort-down' : ($sort == 'ga:avgSessionDuration.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCountry->total('ga:avgSessionDuration')
            ),
        ),
    ));
    ?>

    <h3>Города</h3>
    <?
    $reportCity = new CReportDataProvider($reports['city']);
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'ga:city';
    $this->widget('zii.widgets.grid.CGridView', array(
        'id' => 'city-grid',
        'htmlOptions' => array('class' => 'table table-bordered table-shadow table-centering table-blue'),
        'template' => '{items}{pager}',
        'dataProvider' => $reportCity,
        'columns' => array(
            array(
                'name' => 'ga:city',
                'header' => 'Страна<i class="icon-sort' . ($sort == 'ga:city' ? ' icon-sort-down' : ($sort == 'ga:city.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCity->itemCount ? 'весь период' : null
            ),
            array(
                'name' => 'ga:sessions',
                'header' => 'Сеансы<i class="icon-sort' . ($sort == 'ga:sessions' ? ' icon-sort-down' : ($sort == 'ga:sessions.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCity->total('ga:sessions')
            ),
            array(
                'name' => 'ga:percentNewSessions',
                'header' => 'Новые сеансы, %<i class="icon-sort' . ($sort == 'ga:percentNewSessions' ? ' icon-sort-down' : ($sort == 'ga:percentNewSessions.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCity->total('ga:percentNewSessions')
            ),
            array(
                'name' => 'ga:newUsers',
                'header' => 'Новые пользователи<i class="icon-sort' . ($sort == 'ga:newUsers' ? ' icon-sort-down' : ($sort == 'ga:newUsers.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCity->total('ga:newUsers')
            ),
            array(
                'name' => 'ga:bounceRate',
                'header' => 'Показатель отказов<i class="icon-sort' . ($sort == 'ga:bounceRate' ? ' icon-sort-down' : ($sort == 'ga:bounceRate.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCity->total('ga:bounceRate')
            ),
            array(
                'name' => 'ga:pageviewsPerSession',
                'header' => 'Страниц/сеанс<i class="icon-sort' . ($sort == 'ga:pageviewsPerSession' ? ' icon-sort-down' : ($sort == 'ga:pageviewsPerSession.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCity->total('ga:pageviewsPerSession')
            ),
            array(
                'name' => 'ga:avgSessionDuration',
                'header' => 'Сред. длительность сеанса <i class="icon-sort' . ($sort == 'ga:avgSessionDuration' ? ' icon-sort-down' : ($sort == 'ga:avgSessionDuration.desc' ? ' icon-sort-up' : '')) . '"></i>',
                'footer' => $reportCity->total('ga:avgSessionDuration')
            ),
        ),
    ));
    ?>
<? endif; ?>