<?php
// session_start();
// use \Psr\Http\Message\ServerRequestInterface as Request;
// use \Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../app/bootstrap.php';

//
// require '../vendor/autoload.php';
//
// $setting = include __DIR__ . '/../app/setting.php';
//
// $app = new \Slim\App($setting);
//
// include __DIR__ . '/../app/container.php';
// include __DIR__ . '/../app/routing.php';


// var_dump($container->client); die();
$app->run();
