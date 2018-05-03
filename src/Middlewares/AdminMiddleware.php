<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\User;
use App\Models\Token;

class AdminMiddleware extends BaseMiddleware
{
    public function __invoke($request, $response, $next)
    {
        $token = $request->getHeader('Authorization')[0];
        $tokenModel = new Token($this->container->db);
        $userModel  = new User($this->container->db);

        $auth  = $tokenModel->find('token', $token);
        $findUser = $userModel->find('id', $auth['user_id']);
        $now = date('Y-m-d H:i:s');
        
        if ($_SESSION['login']['is_admin'] != 1) {
             $this->container->flash->addMessage('warning', 'Silakan login');
             return $this->container->response->withRedirect($this->container->router->pathFor('admin.login.page'));
        }

        $response = $next($request, $response);
        
        return $response;

    }
}
