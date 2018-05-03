<?php

namespace App\Middlewares;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\User;
use App\Models\Token;

class AuthMiddleware extends BaseMiddleware
{
    public function __invoke($request, $response, $next)
    {
        $token = $request->getHeader('Authorization')[0];
        $tokenModel = new Token($this->container->db);
        $userModel  = new User($this->container->db);

        $auth  = $tokenModel->find('token', $token);
        $findUser = $userModel->find('id', $auth['user_id']);
        $now = date('Y-m-d H:i:s');
        if (!$auth || $auth['expired_date'] < $now) {
            $data['code']  = 401;
            $data['message'] = "Anda harus login";

            return $response->withHeader('Content-type', 'application/json')->withJson($data, $data['code']);
        }

        $response = $next($request, $response);
        $add = strtotime('+50 minutes', strtotime($now));

        $addTime['expired_date'] = date('Y-m-d H:i:s', $add);

        $tokenModel->updateData($addTime, 'id', $auth['id']);
        return $response;

    }
}
