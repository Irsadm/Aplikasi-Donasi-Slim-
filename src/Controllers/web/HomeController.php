<?php

namespace App\Controllers\web;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Exception\BadResponseException as GuzzleException;



class HomeController extends BaseController
{
    public function index(Request $request, Response $response)
    {
        // var_dump($this->client); die();
        try {
            $result = $this->client->request('GET', 'category/all'. $request->getUri()->getQuery());
        } catch (GuzzleException $e){
            $result = $e->getResponse();
        }

        // var_dump($this->flysystem); die();
        $data = json_decode($result->getBody()->getContents(), true);
        return $response->withRedirect($this->router->pathFor('explore'));    
    }
}
