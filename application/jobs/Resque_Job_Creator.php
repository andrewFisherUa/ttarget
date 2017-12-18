<?php
/**
 * Created by JetBrains PhpStorm.
 * User: User
 * Date: 16.12.13
 * Time: 20:44
 */
class Resque_Job_Creator
{
    public static function createJob($className, $args) {

        // $className is you job class name, the second arguments when enqueuing a job
        // $args are the arguments passed to your jobs

        // Instanciate your class, and return the instance

        return new $className();
    }
}