<?php

class CPChart
{
    public function __construct()
    {
        include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "pChart" . DIRECTORY_SEPARATOR . "pData.php");
        include_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . "pChart" . DIRECTORY_SEPARATOR . "pChart.php");
    }

    public function Cpath($file)
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . $file;
    }
}