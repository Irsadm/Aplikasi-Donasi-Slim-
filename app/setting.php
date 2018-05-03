<?php

return [
        'displayErrorDetails' => true,
        'determineRouteBeforeAppMiddleware' => false,
        'db' => [
            'host'   => 'localhost',
            'name'   => 'donasi_db',
            'user'   => 'root',
            'pass'   => '13136767',
            'driver' => 'pdo_mysql',
        ],

        'view' =>  [
            'path' => __DIR__ . '/../views',
            'twig' => [
                'cache' => false,
                'debug' => true
            ]
        ],

        'lang' => [
            'default' => 'id'
        ],

        'local' =>  [
            'path' => __DIR__ . '/../public/assets/images',
        ],

        'guzzle' => [
            'base_uri' => 'http://localhost/Project/DonasiApp2/public/api/',
            'headers'  => [
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'Authorization' => @$_SESSION['key']['key_token'],
            ],

        ],

        // 'base_url' => 'http://localhost/,


]


 ?>
