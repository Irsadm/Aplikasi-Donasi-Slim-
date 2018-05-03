<?php

namespace App\Controllers\web;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\User;
use App\Models\Donasi;
use App\Models\Campaign;


class AdminController extends BaseController
{
    public function getLogin($request, $response)
    {
        return $this->view->render($response, 'admin/login.twig');
    }

    public function login($request, $response)
    {
        $post = $request->getParams();
        $user = new User($this->db);

        $findUser = $user->find('email', $post['email']);
        // var_dump($findUser); die();

        if (empty($findUser) || $findUser['is_admin'] != 1) {
            $_SESSION['errors'] = "Silahkan periksa kembali email dan password Anda";
            return $this->response->withRedirect($this->router->pathFor('admin.login.page'));
        } else {
            $checkPassword = password_verify($post['password'], $findUser['password']);
            if ($checkPassword) {
            $_SESSION['login'] = $findUser;
            return $this->response->withRedirect($this->router->pathFor('dashboard'));
            } else {
                $_SESSION['errors'] = "Password yang Anda masukan salah";
                return $this->response->withRedirect($this->router->pathFor('admin.login.page'));
            }            
        }
    }

    public function index(Request $request, Response $response)
    {
        return $this->view->render($response, 'admin/index.twig');
    }

    public function verificationRequest($request, $response)
    {
        $user = new User($this->db);

        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        if (empty($perpage)) {
            $perpage = 10;
        } else {
            $perpage = $request->getQueryParam('perpage');
        }

        $getUser = $user->getAllUser(0)->setPaginate($page, $perpage);
        // var_dump($getUser['pagination']); die();


        if ($getUser) {
            $data['data'] = $getUser['data'];
            $data['pagination'] = $getUser['pagination'];
            // var_dump($data);
            return $this->view->render($response, '/admin/user/verificationrequest.twig', $data);

        } else {

            return $this->responseDetail(400, true, 'Data tidak ditemukan', null);
        }
    }

    public function verifiedUser($request, $response)
    {
        $user = new User($this->db);

        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        if (empty($perpage)) {
            $perpage = 10;
        } else {
            $perpage = $request->getQueryParam('perpage');
        }

        $getUser = $user->getAllUser(4)->setPaginate($page, $perpage);
        // var_dump($getUser['pagination']); die();


        if ($getUser) {
            $data['data'] = $getUser['data'];
            $data['pagination'] = $getUser['pagination'];
            // var_dump($data);
            return $this->view->render($response, '/admin/user/verified.twig', $data);

        } else {

            return $this->view->render($response, '/admin/user/verified.twig', $data);
        }
    }


    public function verifyUser($request, $response, $args)
    {
        $user = new User($this->db);
        $findUser = $user->find('id', $args['id']);

        if (!empty($findUser)) {
            $user->setStatus(4, $args['id']);
            $this->flash->addMessage('success', ucwords($findUser['name']). ' telah diverifikasi');
            return $this->response->withredirect($this->router->pathFor('user.request'));
        } else {
            return $this->view->render('templates/response/404.twig');
        }
    }

    public function deleteUser($request, $response, $args)
    {
        $user = new User($this->db);

        $findUser = $user->find('id', $args['id']);
        if(!empty($findUser))
        {
            $user->hardDelete($args['id']);
            $this->flash->addMessage('success', ucwords($findUser['name']) .' telah dihapus');
            if ($findUser['status'] == 4) {
                return $this->response->withRedirect($this->router->pathFor('user.verified'));
            } else {
                return $this->response->withRedirect($this->router->pathFor('user.request'));
            }
        } else {
            return $this->flash->addMessage('Danger', 'Data tidak ditemukan');
        }
    }

    public function donationConfirmationRequest($request, $response)
    {
        $donation = new Donasi($this->db);

        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perpage = !$request->getQueryParam('perpage') ? 1 : $request->getQueryParam('perpage');
        // $data = $donation->allDonasi()->setPaginate($page, $perpage);
        $findDonasi = $donation->unapproveDonasi()->setPaginate($page, 10);
        // var_dump($findDonasi); die();    
        if ($findDonasi) {
            $data['data'] = $findDonasi['data'];
            $data['pagination'] = $findDonasi['pagination'];
            $datum = count($data['data']);
            for ($a = 0; $a < $datum; $a++) {
            $nominal = $donation->convertRupiah($data['data'][$a]['nominal']);
            $data['data'][$a]['nominal'] = $nominal;
            }

            return $this->view->render($response, '/admin/donation/verificationrequest.twig', $data);

        } else {
            return $this->view->render($response, '/templates/response/404.twig');
        }
        
    }

    public function verifyDonation($request, $response, $args)
    {
        $donation = new Donasi($this->db);
        $findDonasi = $donation->find('id', $args['id']);

        if (!empty($findDonasi)) {
            $donation->setStatus(1, $args['id']);
            $this->flash->addMessage('success',  ' Donasi telah diverifikasi');
            return $this->response->withredirect($this->router->pathFor('donation.request'));
        } else {
            return $this->view->render('templates/response/404.twig');
        }
    }

    public function paidDonation($request, $response)
    {
        $donation = new Donasi($this->db);

        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perpage = !$request->getQueryParam('perpage') ? 1 : $request->getQueryParam('perpage');

        $findDonasi = $donation->getApprovedDonasi('status', 1)->setPaginate($page, 10);
        if ($findDonasi) {
            $data['data'] = $findDonasi['data'];
            $data['pagination'] = $findDonasi['pagination'];
            $datum = count($data['data']);
            for ($a = 0; $a < $datum; $a++) {
            $nominal = $donation->convertRupiah($data['data'][$a]['nominal']);
            $data['data'][$a]['nominal'] = $nominal;
            }

            return $this->view->render($response, '/admin/donation/paid.twig', $data);
        } else {
            return $this->view->render($response, '/templates/response/404.twig');
        }

    }

    public function cancelledDonation($request, $response)
    {
        $donation = new Donasi($this->db);

        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryPaaram('page');
        $perpage = !$request->getQueryParam('perpage') ? 1 : $request->getQueryPaaram('perpage');

        $findDonasi = $donation->getDonasi('status', 3)->setPaginate($page, 10);

        if ($findDonasi) {
            $data['data'] = $findDonasi['data'];
            $data['pagination'] = $findDonasi['pagination'];
            $datum = count($data['data']); 
            for ($a = 0; $a < $datum; $a++) {
            $nominal = $donation->convertRupiah($data['data'][$a]['nominal']);
            $data['data'][$a]['nominal'] = $nominal;
            }

            return $this->view->render($response, '/admin/donation/cancelled.twig', $data);
        } else {
            return $this->view->render($response, '/templates/response/404.twig');
        }
    }

}


