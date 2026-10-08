<?php
$autoload = getenv('CNCE_TEST_AUTOLOAD') ?: dirname(__DIR__).'/vendor/autoload.php';
$loader = require $autoload;
$loader->addPsr4('WebEtDesign\\MediaBundle\\', dirname(__DIR__).'/src', true);
