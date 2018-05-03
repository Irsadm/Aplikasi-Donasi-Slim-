<?php

use Slim\Container;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

$container = $app->getContainer();

$container['db'] = function ($c) {
    $setting = $c->get('settings')['db'];
    $config = new \Doctrine\DBAL\Configuration();
    $connectionParams = array(
        'dbname'   => $setting['name'],
        'user'     => $setting['user'],
        'password' => $setting['pass'],
        'host'     => $setting['host'],
        'driver'   => $setting['driver'],
    );
        $connection = \Doctrine\DBAL\DriverManager::getConnection
        ($connectionParams, $config);
        return $connection;

};

$container['view'] = function (Container $container) {
    $settings = $container->get('settings')['view'];

    $view = new \Slim\Views\Twig($settings['path'], $settings['twig']);
    $view->addExtension(new Slim\Views\TwigExtension(
    $container->router,
    $container->request->getUri()
    ));

    $view->getEnvironment()->addGlobal('flash', $container->flash);

    if (@$_SESSION['login']) {
        $view->getEnvironment()->addGlobal('login', $_SESSION['login']);
    }  

    if (@$_SESSION['donasi']) {
        $view->getEnvironment()->addGlobal('donasi', $_SESSION['donasi']);
    }  

    // $view->addExtension(new Twig_Extension_Debug());
    if (@$_SESSION['old']){
        $view->getEnvironment()->addGlobal('old', $_SESSION['old']);
        unset($_SESSION['old']);
    }

    if (@$_SESSION['errors']){
        $view->getEnvironment()->addGlobal('errors', $_SESSION['errors']);
        unset($_SESSION['errors']);
    }


    return $view;
};

$container['validation'] = function ($c) {
    $settings = $c->get('settings')['lang'];
    $lang = $settings['default'];
    $param = $c['request']->getParams();
    $langDir = ('../vendor/vlucas/valitron/lang');
    return new \Valitron\Validator($param, [], $lang, $langDir);

};

$container['flash'] = function (Container $container) {
    return new \Slim\Flash\Messages;
};

$container['csrf'] = function (Container $container) {
    return new \Slim\Csrf\Guard;
};

$container['client'] = function (Container $container) {
    $settings = $container->get('settings')['guzzle'];
    // $settings = require __DIR__.'/settings.php';

    return new GuzzleHttp\Client([
        'base_uri' => $settings['base_uri'],
        'headers'  => $settings['headers']
    ]);
};

$container['flysystem'] = function ($container) {
    $settings = $container->get('settings')['local'];
    $adapter = new Local($settings['path']);
    $filesystem = new Filesystem($adapter);
    return $filesystem;
};

$container['path_image'] = function ($container) {
    $settings = $container->get('settings')['local'];
    return $settings['path'];
};



 ?>
