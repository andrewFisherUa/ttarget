<?php

/**
 * Чистит таблицу фиксирующую скликивания по ip (ip_blacklist)
 * Запускается по cron с частотой IpLog::OBSOLESCENCE
 */
class FlushLogCommand extends CConsoleCommand
{
    public function actionIndex()
    {
        IpLog::model()->deleteOld();
        BounceLog::flush();
    }
}