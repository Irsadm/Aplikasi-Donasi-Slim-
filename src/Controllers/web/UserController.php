<?php

namespace App\Controllers\web;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Guzzlehttp\Exception\BadResponseException as GuzzleException;
use App\Models\User;

class UserController extends BaseController
{
    public function userAll(Request $request, Response $response)
    {
        try {
            $result = $this->client->request('GET', 'user/all'. $request->getUri()->getQuery());
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);

        // var_dump($data); die();

        if ($data['code'] == 200) {
            $_SESSION['key']   = $data['key'];
            $_SESSION['login'] = $data['data'];
            $this->flash->addMessage('success', 'Selamat datang' . $_SESSION['login']['name']);
            $response->withRedirect($this->router->pathFor('login'));

        } else {
            if (is_array($data['message'])) {
                $_SESSION['errors']['email'] = $data['message']['email'];
                $_SESSION['errors']['password'] = $data['message']['email'];
                $_SESSION['old'] = $request->getQueryParams();
                $response->withRedirect($this->router->pathFor('login'));
            } else {
                $this->flash->addMessage('warning', $this->validation->errors());
            }
        }
    }

    public function getLogin($request, $response)
    {
        return $this->view->render($response, 'auth/login.twig');
    }

    public function login($request, $response)
    {
        try {
            $result = $this->client->request('POST', 'login',
            [form_params => [
                'email'   => $request->getParam('email'),
                'password' =>  $request->getParam('password')
            ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data); die();
          if ($data['code'] == 200) {
            $_SESSION['key']   = $data['key'];
            $_SESSION['login'] = $data['data'];
            $this->flash->addMessage('success', 'Selamat datang ' . $_SESSION['login']['name']);
            // var_dump($_SESSION['login']); die();
            return $response->withRedirect($this->router->pathFor('explore'));

        } else {
            if (is_array($data['message'])) {
                $_SESSION['errors']['email'] = $data['message']['email'];
                $_SESSION['errors']['password'] = $data['message']['password'];
                $_SESSION['old'] = $request->getQueryParams();
                return $response->withRedirect($this->router->pathFor('login'));
            } else {
                $this->flash->addMessage('warning', $data['message']);
                return $response->withRedirect($this->router->pathFor('login'));
            }
        }
    }

    public function getRegister($request, $response)
    {
        // var_dump($this->flash); die();
        return $this->view->render($response, 'auth/register.twig');
    }

    public function register(Request $request, Response $response)
    {
        // var_dump($this->client); die();
        try {
            $result = $this->client->request('POST', 'register', [
                'form_params' => [
                    'name'        => $request->getParam('name'),
                    'email'       => $request->getParam('email'),
                    'password'    => $request->getParam('password'),
                    'confirm_password'    => $request->getParam('confirm_password')
                ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);
        
        if ($data['code'] == 201) {
            $this->flash->addMessage('success', $data['message']);
            return $response->withRedirect($this->router->pathFor('register'));
        }  else {
            if (is_array($data['message'])) {
               $_SESSION['errors']['email'] = $data['message']['email'];
               $_SESSION['errors']['name'] = $data['message']['name'];
               $_SESSION['errors']['password'] = $data['message']['password'];
               $_SESSION['errors']['confirm_password'] = $data['message']['confirm_password'];
               $_SESSION['old'] = $request->getParams();
                return $response->withRedirect($this->router->pathFor('register'));
            } else {
                $this->flash->addMessage('warning', $data['message']);
                $_SESSION['old'] = $request->getParams();
                return $response->withRedirect($this->router->pathFor('register'));
           
            }
            // var_dump($e); die();
            // var_dump($this->flash); die();
        }
    }

    public function getForgotPassword($request, $response)
    {
        return $this->view->render($response, 'auth/forgot-password.twig');
    }

    public function forgotPassword($request, $response)
    {
        try {

            $result = $this->client->request('POST', 'forgot-password', [
                'form_params' => [
                    'email' => $request->getParam('email')
                ]
            ]);

        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data); die();

        if ($data['code'] == 200 ) {
            $this->flash->addMessage('success', 'Silkan cek email Anda, kami telah mengirimkan petunjuk untuk merubah password Anda');
            return $response->withRedirect($this->router->pathFor('forgot-password'));
        } else {
            if (is_array($data['message'])) {
                $_SESSION['errors']['email'] = $data['message']['email'];
                $_SESSION['old']= $request->getParams();
                return $response->withRedirect($this->router->pathFor('forgot-password'));
            } else {

                $this->flash->addMessage('warning', $data['message']);
                return $response->withRedirect($this->router->pathFor('forgot-password'));
            }
        }
    }

    public function getDashboard($request, $response)
    {
        $id = $_SESSION['login']['id'];
        // $data['data'] = $_SESSION['login'];
        $userModel = new User($this->db);

        // all user donasi
        try {
            $result = $this->client->request('GET', 'donasi/user');
        } catch (GuzzleException $e) {
            $result = $e->getResponse();            
        }
        $allDonasi = $this->jsonDecode($result);
        $allUserDonasi = count($allDonasi['data']);

        // approved donasi
        try {
            $result = $this->client->request('GET', 'donasi/user/'. $id. '/approved');
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $approvedDonasi = $this->jsonDecode($result);
        $c = count($approvedDonasi['data']);
        for ($a = 0; $a < $c; $a++) {
            $sumDonasi += $approvedDonasi['data'][$a]['nominal'];
            $donasi_rupiah = $userModel->convertRupiah($sumDonasi);

        }

        // user campaign
        try {
            $result = $this->client->request('GET', 'user/campaign/all');
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $userCampaignAll = $this->jsonDecode($result);
        $userCampaign = count($userCampaignAll['data']);

        $data = [
            'donasi' => $allUserDonasi,
            'total_donasi' => $donasi_rupiah,
            'campaign' => $userCampaign,
            'data' => $_SESSION['login']
        ];
        // var_dump($data); die();

        if(!empty($id)) {
            return $this->view->render($response, 'users/dashboard/ringkasan.twig', $data);
        } else {
            return $this->view->render($response, 'templates/response/404.twig');
        }
    }

    public function getPengaturan($request, $response)
    {
        $data = ['data' => $_SESSION['login']];
        // var_dump($data); die();
        if (!empty($data['data'])) {
            return $this->view->render($response, 'users/dashboard/pengaturan/pengaturan.twig', $data);
        } else {
            return $this->view->render($response, 'templates/response/404.twig');
        }
    }

    public function editPersonalInformation($request, $response)
    {
        $post = $request->getParams();
        try {
            $result = $this->client->request('POST', 'user/setting/data-diri', [
                'form_params' => [
                    'phone'      => $post['phone'],
                    'biografi'    => $post['biografi'],
                    'lokasi_id'   => $post['lokasi_id']
                ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data); die();

        if ($data['code'] == 200) {
            $this->flash->addMessage('success', 'Profil berhasil diperbarui');
            $_SESSION['login'] = $data['data'];
            return $response->withRedirect($this->router->pathFor('pengaturan'));
        } else {
            $_SESSION['errors']['phone'] = $data['message']['phone'];
            return $response->withRedirect($this->router->pathFor('pengaturan'));
        }

    }

    public function changeProfilePhoto($request, $response)
    {
        $post = $request->getParam();
        $path = $_FILES['image']['tmp_name'];
        $mime = $_FILES['image']['type'];
        $name = $_FILES['image']['name'];
        $userModel = new User($this->db);

        try {
            $cover = $this->client->request('POST', 'upload', [
                'multipart' => [
                    [
                    'name'         => 'image',
                    'filename'     => $name,
                    'Mime-Type'  => $mime,
                    'contents'      => fopen(realpath($path), 'rb')
                    ]
                ]
            ]);
        } catch (GuzzleException $e) {
            $cover = $e->getResponse();
        }
        $image = json_decode($cover->getBody()->getContents(), true);
        // var_dump($image); die();
        if ($image['code'] == 200) {
            try {
                $result = $this->client->request('POST', 'user/setting/foto-profil', [
                    'form_params' => [
                        'foto_profil' => $image['data'],
                    ]
                ]);
            } catch (GuzzleException $e) {
                $result = $e->getResponse();
            }

            $data = $this->jsonDecode($result);
            // var_dump($data); die();
            $_SESSION['login'] = $data['data'];
            // var_dump($_SESSION['login']); die();
            $this->flash->addMessage('success', $data['message']);
            return $response->withRedirect($this->router->pathFor('pengaturan'));
        } else {
            // $_SESSION['errors']['foto'] = "";
            $this->flash->addMessage('danger', "File tidak didukung");
            return $response->withRedirect($this->router->pathFor('pengaturan'));

        }
    }

    public function verifikasiAkun($request, $response)
    {
        $post = $request->getParam();
        $path = $_FILES['image']['tmp_name'];
        $mime = $_FILES['image']['type'];
        $name = $_FILES['image']['name'];
        $userModel = new User($this->db);

        try {
            $cover = $this->client->request('POST', 'upload', [
                'multipart' => [
                    [
                    'name'         => 'image',
                    'filename'     => $name,
                    'Mime-Type'  => $mime,
                    'contents'      => fopen(realpath($path), 'rb')
                    ]
                ]
            ]);
        } catch (GuzzleException $e) {
            $cover = $e->getResponse();
        }
        $image = json_decode($cover->getBody()->getContents(), true);
        // var_dump($image); die();
        if ($image['code'] == 200) {
            try {
                $result = $this->client->request('POST', 'user/setting/verifikasi-akun', [
                    'form_params' => [
                        'foto_verifikasi' => $image['data'],
                    ]
                ]);
            } catch (GuzzleException $e) {
                $result = $e->getResponse();
            }

            $data = $this->jsonDecode($result);
            $_SESSION['login'] = $data['data'];

            $this->flash->addMessage('success', $data['message']);
            // var_dump($data); die();
            return $response->withRedirect($this->router->pathFor('pengaturan'));
        } else {
            // $_SESSION['errors']['foto'] = "";
            $this->flash->addMessage('danger', "File tidak didukung");
            return $response->withRedirect($this->router->pathFor('pengaturan'));

        }


    }

    public function changePassword($request, $response)
    {
        $post = $request->getParams();
        try {
            $result = $this->client->request('POST', 'user/setting/change-password', [
                'form_params' => [
                    'password'     => $post['password'],
                    'new_password' => $post['new_password'],
                    'confirm_password' => $post['confirm_password']
                ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump(is_array($data['message'])); die();

        if ($data['code'] == 200) {
            $this->flash->addMessage('success', 'Password berhasil diperbarui');
            return $response->withRedirect($this->router->pathFor('pengaturan'));
        } else {
            if (is_array($data['message'])) {
                $_SESSION['errors']['password']              = $data['message']['password'];
                $_SESSION['errors']['new_password']       = $data['message']['new_password'];
                $_SESSION['errors']['confirm_password']  = $data['message']['confirm_password'];
                $_SESSION['old'] = $post;
                return $response->withRedirect($this->router->pathFor('pengaturan'));
            } else {
                $_SESSION['errors']['password'][] = $data['message'];
                $_SESSION['old'] = $post;
                return $response->withRedirect($this->router->pathFor('pengaturan'));
            }
        }

    }

    public function getResetPassword($request, $response, $args)
    {
        $registerModel = new \App\Models\Register($this->db);
        $token = $args['token'];

        $findToken = $registerModel->find('token', $token);

        if ($findToken == true) {

            $this->view->render($response,'auth/reset-password.twig', ['token' => $token ]);
        } else {
            $this->view->render($response,'templates/response/404.twig');
        }
    }

    public function resetPassword($request, $response)
    {
        try {
            $result = $this->client->request('POST', 'reset-password', [
                'form_params' => [
                    'password'               => $request->getParam('password'),
                    'confirm_password'  => $request->getParam('confirm_password'),
                    'token'                     => $request->getParam('token')
                ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $token = $request->getParam('token');
        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data); die();
        // $token = $args['token'];
        if ($data['code'] == 200) {
            $this->flash->addMessage('success', $data['message']);
            return $response->withRedirect($this->router->pathFor('login'));
        } else {
            $_SESSION['errors']['password'] = $data['message']['password'];
            $_SESSION['errors']['confirm_password'] = $data['message']['confirm_password'];
            $_SESSION['old'] = $request->getParams();
            return $this->view->render($response, 'auth/reset-password.twig',  ['token' => $token]);
        }

    }

    public function logout($request, $response)
    {
        session_destroy();
        return $this->response->withRedirect($this->router->pathFor('explore'));
    }
}



 ?>
