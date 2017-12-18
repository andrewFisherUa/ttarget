<?php


class ResqueCommand extends RResqueCommand {


    public function actionIndex()
    {
        echo <<<EOD
This is the command for the yii-resque component. Usage:

    ./yiic resque <command>

Available commands are:

    start
    stop --quit=[0|1]

EOD;
    }

    public function actionStart()
    {
        parent::actionStart('app', 5, 1);
        parent::actionStart('stat', 5, 7);
        parent::actionStartrecurring('app');
    }
}