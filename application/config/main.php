<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
    'basePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..',
    'runtimePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'runtime',
    'name' => 'Тематический Таргетинг',
    'sourceLanguage' => 'ru',
    'language' => 'ru',

    // preloading 'log' component
    'preload' => array('log'),

    // autoloading model and component classes
    'import' => array(
        'application.models.*',
        'application.models.campaigns.*',
        'application.models.news.*',
        'application.models.redis.*',
        'application.models.teasers.*',
        'application.models.report.*',
        'application.models.chart.*',
        'application.models.excel.Abstract.*',
        'application.models.excel.*',
        'application.models.offers.*',
        'application.models.connection.*',
        'application.models.session.*',
        'application.models.rtb.*',

        'application.components.*',
        'application.helpers.*',

        'application.jobs.*',
        'application.jobs.campaigns.*',
        'application.jobs.news.*',
        'application.jobs.platforms.*',
        'application.jobs.teasers.*',
        'application.jobs.users.*',
        'application.jobs.stat.*',
        'application.jobs.actions.*',
        'application.jobs.offers.*',
        'application.jobs.session.*',

        'ext.CAdvancedArBehavior',
        'ext.ESaveRelatedBehavior',
        'ext.DView',
        'ext.pChart.CPChart',
        'ext.yiiext.validators.*',
        'ext.yiiext.behaviors.*',
        'ext.phpexcel.Classes.PHPExcel',
        'ext.yii-mail.YiiMailMessage',
        'ext.xmlrpc.*',
        'ext.Google.message.*',
    ),

    'aliases' => array(
        'client_css' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . 'htdocs' .
            DIRECTORY_SEPARATOR . 's' .
            DIRECTORY_SEPARATOR . 'css',

    ),

    'modules' => array(),

    // application components
    'components' => array(
        'redis' => array(
            'class' => 'application.components.RedisClient',
            'host' => '/var/run/redis/redis.sock',
            'port' => 0,
        ),
        'resque' => array(
            'class' => 'ext.yii-resque.RResque',
            'prefix' => 'ttarget',
            'path' => dirname(__FILE__) . '/../../lib/resque',
            'server' => 'unix:/var/run/redis/redis.sock',
        ),
        'authManager' => array(
            'class' => 'PhpAuthManager',
            'defaultRoles' => array('guest'),
        ),
        'user' => array(
            // enable cookie-based authentication
            'allowAutoLogin' => true,
            'class' => 'WebUser',
            'loginUrl' => '/login',
        ),
        // uncomment the following to enable URLs in path-format
        'urlManager' => array(
            'urlFormat' => 'path',
            'showScriptName' => false,
            'rules' => array(
                'gii' => 'gii',
                'gii/<controller:\w+>' => 'gii/<controller>',
                'gii/<controller:\w+>/<action:\w+>' => 'gii/<controller>/<action>',

                '/' => 'site/index',
                '<action:(login|logout|error)>' => 'site/<action>',
                '<view:(about)>' => 'site/page',

                'campaigns/creatives/<id:\d+>' => 'campaignsCreatives/index',
                'RTBReceiver/creatives/view/<id:\d+>' => 'RTBReceiver/view/',
                'RTBReceiver/creatives/click/<id:\d+>' => 'RTBReceiver/click/',
                'RTBReceiver/RTBReceiver' => 'RTBReceiver',

                '<controller:\w+>' => '<controller>/index',
                '<controller:\w+>/<id:\d+>' => '<controller>/view',
                //'<controller:\w+>/<id:\d+>' => '<controller>/click',
                '<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ),
        ),
        'cache' => array(
            'class' => 'RedisCache',
            'host' => '/var/run/redis/redis.sock',
            'port' => 0,
        ),
        'db' => array(
            'connectionString' => 'mysql:host=localhost;dbname=teaser_db',
            'emulatePrepare' => true,
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8',
            'schemaCacheID' => 'cache',
            'schemaCachingDuration' => 3600,

            // включаем профайлер
            'enableProfiling' => true,
            // показываем значения параметров
            'enableParamLogging' => true,
        ),
        'mysqli' => array(
            'class' => 'application.components.MysqliWrapper',
            'username' => 'root',
            'password' => '',
            'database' => 'teaser_db'
        ),
        'errorHandler' => array(
            // use 'site/error' action to display errors
            'errorAction' => 'site/error',
        ),
        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class' => 'CFileLogRoute',
                    'levels' => 'error, warning',
                ),
            ),
        ),
        'mail' => array(
            'class' => 'ext.yii-mail.YiiMail',
            'transportType' => 'php',
            'transportOptions' => array(
                'host' => '',
                'username' => '',
                'password' => '',
                'port' => '465',
                'encryption' => 'tls',
            ),
            'viewPath' => 'application.views.mail',
            'logging' => true,
            'dryRun' => false
        ),
        'image' => array(
            'class' => 'ext.image.CImageComponent',
            // GD or ImageMagick
            'driver' => 'GD',
            // ImageMagick setup path
            'params' => array(),
        ),
        'xmlrpc' => array(
            'class' => 'ext.xmlrpc.CXMLRPCComponent',
        ),
    ),

    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params' => array(
        // this is used in contact page
        'tmpPath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . 'tmp',
        'teaserImageBaseUrl' => 'http://tt.ttarget.ru/i/t',
        'teaserLinkBaseUrl' => 'http://tt.ttarget.ru/go?',
        'offerLinkBaseUrl' => 'http://tt.ttarget.ru/og?',
        'shortLinkBaseUrl' => 'http://g.ttarget.ru/',
        'linkSecret' => 'anysecretasdfg,jmvgqewr89',
        'clientCodeSecret' => 'anysecret',
        'userImageHeight' => 50,
        'userImageWidth' => 50,

        'offerImageBaseUrl' => '/i/t',
        'offerImageThumbMaxWidth' => 200,
        'offerImageThumbMaxHeight' => 100,
        'docTmpUrl' => '/tmp',
        'teaserImageHeight' => 200,
        'teaserImageWidth' => 200,
        'CRTFactor' => 10,
        'VAT' => 18,
        'PlatformDefaultUserPassword' => 'asd12345',
        'PlatformBillingMinimalWithdrawal' => 500,
        'PlatformRequestAlertTime' => 72000, //20h
        'CampaignNotifyDaysLeft' => 1,
        'CampaignNotifyClicksLeft' => 100,
        'adminEmail' => 'root@localhost',
        'billingEmail' => 'root@localhost',
        'notifyEmail' => 'root@localhost',
        'registrationEmail' => 'root@localhost',
        'rtbCreativeFileUploadsPath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . 'htdocs' .
            DIRECTORY_SEPARATOR . 'i' .
            DIRECTORY_SEPARATOR . 'creatives',
        'rtbCreativeTypeImageMaxFilesize' => 35840, //35 Кб
        'rtbCreativeTypeImageTypesAllowed' => array('image/jpeg', 'image/png', 'image/gif'),
        'rtbCreativeTypeAudioMaxFilesize' => 1048576, //1 Мб
        'rtbCreativeTypeAudioTypesAllowed' => array('application/octet-stream'),
        'rtbCreativeTypeVideoMaxFilesize' => 2097152, //2 Мб
        'rtbCreativeTypeVideoTypesAllowed' => array('application/octet-stream'),
        'GoogleAPIConfigFilename' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'google.ini',
        'YandexRTBName' => '',
        'YandexRTBPassword' => '',
        'YandexRTBUrl' => 'https://bayan2cdn.xmlrpc.http.yandex.net:35999',
        'YandexRTBClickUrl' => 'http://tt.ttarget.ru/RTBReceiver/action/',
        'YandexRTBShowUrl' => 'http://tt.ttarget.ru/RTBReceiver/show/',
        'GoogleRTBClickUrl' => 'http://tt.ttarget.ru/RTBGoogleReceiver/action/',
        'GoogleRTBShowUrl' => 'http://tt.ttarget.ru/RTBGoogleReceiver/show/',

        'teaserTemplatePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . 'models' .
            DIRECTORY_SEPARATOR . 'teasers' .
            DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'default.html',
        'imageBasePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . 'htdocs' .
            DIRECTORY_SEPARATOR . 'i' .
            DIRECTORY_SEPARATOR . 't',
        'logoBasePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . 'htdocs' .
            DIRECTORY_SEPARATOR . 'i' .
            DIRECTORY_SEPARATOR . 'c',
        'docTmpPath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . '..' .
            DIRECTORY_SEPARATOR . 'htdocs' .
            DIRECTORY_SEPARATOR . 'tmp',
    ),
);
