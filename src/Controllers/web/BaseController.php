<?php

namespace App\Controllers\web;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Container;

abstract class BaseController
{
    protected $container;

    public function __construct(Container $container)
    {
        return $this->container = $container;
    }

    public function __get($property)
    {
        if ($this->container->{$property}) {
            return $this->container->{$property};
        }
    }

    public function paginateArray($data, $page, $per_page)
    {
        $total = count($data);
        $pages = (int) ceil($total / $per_page);
        $start = ($page - 1) * ($per_page);
         // $offset = $per_page;
        $outArray = array_slice($data, $start, $per_page);
        $result = [
                       'data'       => $outArray,
                       'pagination' =>[
                                              'total_data'   => $total,
                                              'perpage'     => $per_page,
                                              'current'       => $page,
                                              'total_page'   => $pages,
                                              'first_page'    => 1,
                       ]
                  ];
                  return $result;
    }

    public function jsonDecode($result)
    {
       $data = json_decode($result->getBody()->getContents(), true);
       return $data;
    }
}


 ?>
