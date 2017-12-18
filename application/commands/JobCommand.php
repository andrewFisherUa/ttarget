<?php

/**
 * Комманды для работы с тасками resque
 */
class JobCommand extends CConsoleCommand
{
    public function actionIndex()
    {
        echo $this->getHelp();
    }

    /**
     * Выполняет таск
     *
     * @param $job
     * @param string $json_args
     */
    public function actionPerform($job, $json_args = '{}')
    {
        $job = new $job();
        $job->args = json_decode($json_args, true);
        $job->perform();
    }

    /**
     * Создает задачу на выполнение
     *
     * @param $job
     * @param $queue
     * @param string $json_args
     */
    public function actionCreate($job, $queue = 'stat', $json_args = '{}')
    {
        Yii::app()->resque->createJob($queue, $job, json_decode($json_args, true));
    }
}