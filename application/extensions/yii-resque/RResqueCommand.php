<?php
/**
 * RResque Command
 *
 * This is a console command for manage RResque workers
 *
 * @author Rolies106 <rolies106@gmail.com>
 * @version 0.1.0
 */
class RResqueCommand extends CConsoleCommand
{
    public $defaultAction = 'index';

    public function actionIndex()
    {
        echo <<<EOD
This is the command for the yii-resque component. Usage:

    ./yiic rresque <command>

Available commands are:

    start --queue=[queue_name | *] --interval=[int] --verbose=[0|1] --count=[int] --loghandler=[string] --loghandlertarget=[string]
    startrecurring --queue=[queue_name | *] --interval=[int] --verbose=[0|1] --loghandler=[string] --loghandlertarget=[string]
    stop --quit=[0|1]

EOD;
    }

    public function actionStart($queue = '*', $interval = 5, $count = 5, $verbose = 1, $loghandler = false, $loghandlertarget = false)
    {
        if (!isset(Yii::app()->resque)) {
            echo 'resque component cannot be found on your console.php configuration';
            die();
        }
        $resquePath = Yii::app()->resque->path;

        $server = (isset(Yii::app()->resque->server)) ? Yii::app()->resque->server : 'localhost';
        $port = (isset(Yii::app()->resque->port)) ? Yii::app()->resque->port : '6379';
        $db = (isset(Yii::app()->resque->database)) ? Yii::app()->resque->database : '3';
        $prefix = Yii::app()->resque->prefix;

        $host  = (strpos($server, 'unix:') !== false) ? $server : $server . ':' . $port;

        $appInclude = Yii::getPathOfAlias('application.config') . '/resque.php';

        $command = 'nohup sh -c "APP_INCLUDE='. $appInclude .
                               ' QUEUE=' . $queue .
                               ' COUNT=' . $count .
                               ' REDIS_NAMESPACE=' . $prefix.
                               ' REDIS_BACKEND=' . $host .
                               ' REDIS_DATABASE=' . $db .
                               ' INTERVAL=' . $interval .
                               ' VERBOSE=' . $verbose .
                               ' LOGHANDLER=' . $loghandler .
                               ' LOGHANDLERTARGET=' . $loghandlertarget .
                               ' php ' . $resquePath.'/bin/resque" >> ' . dirname(__FILE__) . '/../../../runtime/yii_resque_log.log 2>&1 &';

        exec($command, $return);
    }

    public function actionStartrecurring($queue = '*', $interval = 5, $verbose = 1, $count = 1, $loghandler = false, $loghandlertarget = false)
    {
        if (!isset(Yii::app()->resque)) {
            echo 'resque component cannot be found on your console.php configuration';
            die();
        }

        $resquePath = Yii::app()->resque->path;

        $server = (isset(Yii::app()->resque->server)) ? Yii::app()->resque->server : 'localhost';
        $port = (isset(Yii::app()->resque->port)) ? Yii::app()->resque->port : '6379';
        $db = (isset(Yii::app()->resque->database)) ? Yii::app()->resque->database : '3';
        $auth = (isset(Yii::app()->resque->password)) ? Yii::app()->resque->password : '';
        $prefix = Yii::app()->resque->prefix;

        $host  = (strpos($server, 'unix:') !== false) ? $server : $server . ':' . $port;

        $appInclude = Yii::getPathOfAlias('application.config') . '/resque.php';

        $command = 'nohup sh -c "APP_INCLUDE='. $appInclude .
                                ' RESQUE_PHP=' . $resquePath. '/lib/Resque.php'.
                                ' QUEUE=' . $queue .
                                ' COUNT=' . $count .
                                ' REDIS_NAMESPACE='.$prefix.
                                ' REDIS_BACKEND=' . $host .
                                ' REDIS_DATABASE=' . $db .
                                ' INTERVAL=' . $interval .
                                ' VERBOSE=' . $verbose .
                                ' LOGHANDLER=' . $loghandler .
                                ' LOGHANDLERTARGET=' . $loghandlertarget .
                                ' php ' . $resquePath . '/bin/resque-scheduler.php" >> ' . dirname(__FILE__) . '/../../../runtime/yii_resque_scheduler_log.log 2>&1 &';

        exec($command, $return);
    }

    public function actionStop($quit = null)
    {
        $quit_string = $quit ? '-s QUIT': '-9';
        exec("ps ux  | grep resque | grep -v grep | awk {'print $2'} | xargs kill $quit_string ");
    }
}
