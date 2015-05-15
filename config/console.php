<?php

$config = [
    'id' => 'parse-statistic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'log',
    ],
    'controllerNamespace' => 'app\commands',
    'components' => [
        'formatter' => [
            'dateFormat' => 'yyyy-MM-dd',
        ],
        'parser' => [
            'class' => 'app\components\parser',
        ],
        /*'errorHandler' => [
            'errorAction' => 'parse/error',
        ],*/
    ],
];

return \yii\helpers\ArrayHelper::merge($config, require(__DIR__ . '/local.php'));