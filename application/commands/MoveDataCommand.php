<?php

/**
 * Команда для переноса статистики из старых полей и таблиц
 * !!! ЗАПУСТИТЬ 1 РАЗ НА ПРОДАКШЕНЕ !!!
 */
class MoveDataCommand extends CConsoleCommand
{
    private $news = array();
    private $platforms = array();
    private $teasers = array();

    /**
     * Перенос кликов
     */
    public function actionClicks()
    {
        $command = Yii::app()->db->createCommand();
        $command->select('news_id, click_date, from_id, COUNT(news_id) as amount, teasers_id');
        $command->from('clicks');
        $command->where('is_real = 1');
        $command->group('news_id, click_date, from_id, teasers_id');

        $clicks = $command->queryAll();
        foreach ($clicks as $click) {

            $teaser     = $this->getTeaser($click['teasers_id']);
            $news       = $this->getNews($click['news_id']);
            $platform   = $this->getPlatform($click['from_id']);

            if (!$news || !$platform || !$teaser) continue;

            $sql = ReportHandler::createUpdateClicksSql($teaser, $news, $platform, $click['amount'], $click['click_date']);

            // Если платформа внешняя, тогда учитываем только общее количество кликов
            if ($platform->is_external) {
                $sql .= News::createUpdateClicksSql($news->id, $click['amount']);
                $sql .= Campaigns::createUpdateClicksSql($news->campaign_id, $click['amount']);
            } else {
                $sql .= News::createUpdateClicksWithoutExternalsSql($news->id, $click['amount']);
                $sql .= Campaigns::createUpdateClicksWithoutExternalsSql($news->campaign_id, $click['amount']);
            }

            // Отправляем все завпросы одной пачкой
            Yii::app()->mysqli->multiQuery($sql);

            $mysqli = Yii::app()->mysqli->client();
            while ($mysqli->more_results() && $mysqli->next_result());
        }

        Yii::app()->mysqli->client()->close();
    }

    /**
     * Перенос показов
     */
    public function actionShows()
    {
        $command = Yii::app()->db->createCommand();
        $command->from('shows');
        $shows = $command->queryAll();

        foreach ($shows as $show) {

            $teaser     = $this->getTeaser($show['teasers_id']);
            $news       = $this->getNews($show['news_id']);
            $platform   = $this->getPlatform($show['from_id']);

            if (!$news || !$platform) continue;

            $sql  = ReportHandler::createUpdateShowsSql($teaser, $news, $platform, $show['count'], $show['showdate']);
            $sql .= News::createUpdateShowsSql($news->id, $show['count']);
            $sql .= Campaigns::createUpdateShowsSql($news->campaign_id, $show['count']);

            // Отправляем все завпросы одной пачкой
            Yii::app()->mysqli->multiQuery($sql);

            $mysqli = Yii::app()->mysqli->client();
            while ($mysqli->more_results() && $mysqli->next_result());
        }

        Yii::app()->mysqli->client()->close();
    }

    /**
     * Переносит поддельные клики
     */
    public function actionFakeClicks()
    {
        $command = Yii::app()->db->createCommand();
        $command->from('fake_clicks');
        $clicks = $command->queryAll();

        $platform   = $this->getPlatform(Platforms::DELETED_PLATFORM_ID);

        foreach ($clicks as $click) {

            $news = $this->getNews($click['news_id']);

            if (!$news) continue;

            ReportHandler::updateFakeClicks($news, $platform, $click['count'], $click['click_date']);
            $news->updateCounters(array('fake_clicks' => $click['count']), 'id = :id', array(':id' => $news->id));
            $news->campaign->updateCounters(array('fake_clicks' => $click['count']), 'id = :id', array(':id' => $news->campaign_id));
        }

    }

    /**
     * Переносит данные по скликиваниям
     */
    public function actionClickfraud()
    {
        $command = Yii::app()->db->createCommand();
        $command->select('news_id, click_date, ip, from_id, COUNT(news_id) as amount');
        $command->from('clicks');
        $command->where('is_real = 0');
        $command->group('news_id, click_date, ip, from_id');

        $clicks = $command->queryAll();
        foreach ($clicks as $click) {

            $ip = sprintf('%u', ip2long($click['ip']));
            if (!$ip) continue;

            ReportDailyClickfraud::model()->incrClicks($ip, $click['news_id'], $click['from_id'], $click['amount'], $click['click_date']);
        }
    }

    private function getTeaser($teaser_id)
    {
        if (!isset($this->teasers[$teaser_id])) {
            $this->teasers[$teaser_id] = Teasers::model()->findByPk($teaser_id);
        }

        return $this->teasers[$teaser_id];
    }

    private function getNews($news_id)
    {
        if (!isset($this->news[$news_id])) {
            $this->news[$news_id] = News::model()->findByPk($news_id);
        }

        return $this->news[$news_id];
    }

    private function getPlatform($platform_id)
    {
        if (!isset($this->platforms[$platform_id])) {
            $this->platforms[$platform_id] = Platforms::model()->findByPk($platform_id);
        }

        return $this->platforms[$platform_id];
    }
}