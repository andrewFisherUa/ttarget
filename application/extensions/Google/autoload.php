<?php

function google_api_php_client_autoload($className) {
  $classPath = explode('_', $className);
  if ($classPath[0] != 'Google') {
    return;
  }
  unset($classPath[0]);
  if (count($classPath) > 3) {
    // Maximum class file path depth in this project is 3.
    $classPath = array_slice($classPath, 0, 3);
  }
  $filePath = Yii::getPathOfAlias('application.extensions.Google.API.src') . '/' . implode('/', $classPath) . '.php';
//  var_dump($filePath, file_exists($filePath)); exit();
  if (file_exists($filePath)) {
    require_once($filePath);
  }
}

//spl_autoload_register('google_api_php_client_autoload');
Yii::registerAutoloader('google_api_php_client_autoload');