<?php
session_start();

require __DIR__ . '/../vendor/autoload.php';

use Slim\App;
$setting = require __DIR__ . '/setting.php';
$app = new App(['settings' => $setting]);


require  __DIR__ . '/container.php';
require  __DIR__ . '/routes/web.php';
require  __DIR__ . '/routes/api.php';
