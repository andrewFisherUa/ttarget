<?php
/**
 * Yii Resque Component
 *
 * Yii component to work with php resque
 *
 * PHP version 5
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author        Rolies Deby <rolies106@gmail.com>
 * @copyright     Copyright 2012, Rolies Deby <rolies106@gmail.com>
 * @link          http://www.rolies106.com/
 * @package       yii-resque
 * @since         0.1
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

class RResque extends CApplicationComponent
{
    /**
     * @var string Redis server address
     */
    public $server = 'localhost';

    /**
     * @var string Redis port number
     */
    public $port = '6379';

    /**
     * @var int Redis database index
     */
    public $database = 0;

    /**
     * @var string Redis password auth
     */
    public $password = '';


    public $prefix = '';

    /**
     * @var string Путь к папке с resque
     */
    public $path = '';

    /**
     * Initializes the connection.
     */
    public function init()
    {
        parent::init();

        if (!$this->path)
        {
            throw new Exception("Resque path didn't set");
        }

        if(!class_exists('RResqueAutoloader', false)) {
            # Turn off our amazing library autoload
            spl_autoload_unregister(array('YiiBase','autoload'));

            # Include Autoloader library
            include(dirname(__FILE__) . '/RResqueAutoloader.php');

            # Run request autoloader
            RResqueAutoloader::register($this->path);

            # Give back the power to Yii
            spl_autoload_register(array('YiiBase','autoload'));
        }

        $host = $this->server;
        if (strpos($this->server, 'unix:') === false) {
            $host .= ':' . $this->port;
        }

        Resque::setBackend($host, $this->database, $this->password);
        if ($this->prefix) {
          $this->redis()->prefix($this->prefix);
        }
        
    }

    /**
     * Create a new job and save it to the specified queue.
     *
     * @param string $queue The name of the queue to place the job in.
     * @param string $class The name of the class that contains the code to execute the job.
     * @param array $args Any optional arguments that should be passed when the job is executed.
     *
     * @return string
     */
    public function createJob($queue, $class, $args = array())
    {
        return Resque::enqueue($queue, $class, $args);
    }

    /**
     * Create a new scheduled job and save it to the specified queue.
     *
     * @param int $in Second count down to job.
     * @param string $queue The name of the queue to place the job in.
     * @param string $class The name of the class that contains the code to execute the job.
     * @param array $args Any optional arguments that should be passed when the job is executed.
     *
     * @return string
     */
    public function enqueueJobIn($in, $queue, $class, $args = array())
    {
        return ResqueScheduler\ResqueScheduler::enqueueIn($in, $queue, $class, $args);
    }

    /**
     * Create a new scheduled job and save it to the specified queue.
     *
     * @param timestamp $at UNIX timestamp when job should be executed.
     * @param string $queue The name of the queue to place the job in.
     * @param string $class The name of the class that contains the code to execute the job.
     * @param array $args Any optional arguments that should be passed when the job is executed.
     *
     * @return string
     */
    public function enqueueJobAt($at, $queue, $class, $args = array())
    {
        return ResqueScheduler\ResqueScheduler::enqueueAt($at, $queue, $class, $args);
    }

    /**
     * Get delayed jobs count
     *
     * @return int
     */
    public function getDelayedJobsCount()
    {
        return (int)Resque::redis()->zcard('delayed_queue_schedule');
    }

    /**
     * Check job status
     *
     * @param string $token Job token ID
     *
     * @return string Job Status
     */
    public function status($token)
    {
        $status = new Resque_Job_Status($token);
        return $status->get();
    }

    /**
     * Return Redis
     *
     * @return object Redis instance
     */
    public function redis()
    {
        return Resque::redis();
    }

   /**
     * Get queues
     *
     * @return object Redis instance
     */
    public function getQueues()
    {
        return $this->redis()->zRange('delayed_queue_schedule', 0, -1);
    }
}
