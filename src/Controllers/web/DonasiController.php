<?php

namespace App\Controllers\web;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Exception\BadResponseException as GuzzleException;
use App\Models\Donasi;

class DonasiController extends BaseController
{
    public function index($request, $response)
    {
        // var_dump($this->client); die();
        try {
            $result = $this->client->request('GET', 'donasi/all'. $request->getUri()->getQuery());
        } catch (GuzzleException $e){
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data);
    }

    public function getDonasiPage($request, $response, $args)
    {
          // $campaignModel = new Campaign($this->db);
        // detail campaign
        $donasiModel = new Donasi($this->db);
        try {
            $result = $this->client->request('GET', 'campaign/'.$args['id'].'/detail');

        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);
        // $findDonasi = 
        if ($data['code'] == 200) {
            $viewData = ['data' => $data['data']];
            return $this->view->render($response, 'donasi/donasi.twig', $viewData);
        } else {
            return $this->view->render($response, 'templates/response/404.twig');
        }
    }

    public function donasi($request, $response)
    {
        $post = $request->getParams();
        $id = $post['campaign_id'];
        $donasiModel = new Donasi($this->db);
        // var_dump($id); die();

        if (!empty($_SESSION['login'])) {
            $userId = $_SESSION['login']['id'];

            if ($post['phone'] != $_SESSION['login']['phone'] || empty($_SESSION['login']['phone'])) {
                try {
                    $result = $this->client->request('POST', 'user/setting/data-diri', [
                        'form_params' => [
                            'phone' => $post['phone']
                        ]
                    ]);
                } catch (GuzzleException $e) {
                    $result = $e->getResponse();
                }

                $phone = json_decode($result->getBody()->getContents(), true);
                // var_dump($phone); die();
                if ($phone['code'] == 400) {
                    $_SESSION['errors']['phone'] = $phone['message']['phone'];
                    $_SESSION['old'] = $post;
                    return $response->withRedirect($this->router->pathFor('get-donasi', ['id' => $id]));
                } else {
                    $_SESSION['old'] = $post;
                    $_SESSION['login']['phone'] = $post['phone'];
                }
            }

        } else {
            try {
               $result = $this->client->request('POST', 'register', [
                'form_params' => [
                    'status'   => '1',
                    'name'   => $post['name'],
                    'email'   => $post['email'],
                    'phone'   => $post['phone']
                ]
               ]);
            } catch (GuzzleException $e) {
                $result  = $e->getResponse();
            }

            $user = json_decode($result->getBody()->getContents(), true);
            if ($user['code'] == 400) {
                foreach ($user['message'] as $key => $value) {
                    $_SESSION['errors'][$key] = $user['message'][$key];
                }
                return $response->withRedirect($this->router->pathFor('get-donasi', ['id' => $id]));
            } else {
                $_SESSION['old'] = $post;
            }
            // var_dump($user); die();
            $userId = $user['data']['id'];
        }
        //convert nominal
        $num = (int)str_replace('.', '', $post['nominal']);
        try {
            $result = $this->client->request('POST', 'donasi/new-donasi', [
            'form_params' => [
                 'campaign_id' => $post['campaign_id'],
                 'nominal'        => $num,
                 'user_id'         => $userId,
                 'komentar'      => $post['komentar'],
            ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        
        // var_dump($num); die();
        $date = date('Y-m-d H:i:s');
        $time = strtotime($date);
        $endTime = date("Y-m-d H:i:s", strtotime('+24 Hours', $time));
        $batasTransfer = $donasiModel->convertTanggal($endTime, true);


        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data); die(); 
        if ($data['code'] == 200) {
            $_SESSION['donasi'] = $data['data'];
            $data['data'] = $data['data'][0];
            // var_dump($data['data']['nominal']); die();
            $convert = $donasiModel->convertRupiah($data['data']['nominal']);
            $data['data']['nominal'] = $convert;
            // var_dump($data['data']); die();
            $data['data']['batas_transfer'] = $batasTransfer;
            return $this->view->render($response, 'donasi/summary.twig', $data);
            // return $response->withRedirect($this->router->pathFor('get-summary-donasi'));
        } else {
            $_SESSION['errors']['nominal'] = $data['message']['nominal'];
            $_SESSION['old'] = $post;
            return $response->withRedirect($this->router->pathFor('get-donasi', ['id' => $id]));
            
        }
    }

    public function getUserDonasi($request, $response)
    {
        $donasiModel = new Donasi($this->db);
        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perpage = !$request->getQueryParam('perpage') ? 5 : $request->getQueryParam('perpage');
        $id = $_SESSION['login']['id'];
        // total donasi user yang berhasil
        try {
            $result = $this->client->request('GET', 'donasi/user/' .$id.'/approved');
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $approvedDonasi = json_decode($result->getBody()->getContents(), true);
        $donasi = count($approvedDonasi['data']);
        for ($a = 0; $a < $donasi; $a++) {
            $total_donasi += $approvedDonasi['data'][$a]['nominal'];
            $total = $donasiModel->convertRupiah($total_donasi);
        }
        // mengambil semua donasi user 
        try {
            $result = $this->client->request('GET', 'donasi/user', [
                'query' => [
                    'page' => $request->getQueryParam('page'),
                    'perpage' => '10'
                ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data); die();
        $count = count($data['data']);
        for ($a = 0; $a < $count; $a++) {
            $tanggal = $donasiModel->convertTanggal($data['data'][$a]['tanggal_donasi']);
            $data['data'][$a]['tanggal_donasi'] = $tanggal;
            $nominal = substr($donasiModel->convertRupiah($data['data'][$a]['nominal']), 3);
            $data['data'][$a]['nominal'] = $nominal;
        }
        $viewData = ['data' => $data['data'], 'pagination' => $data['pagination'], 'total_donasi' => $total];

        if ($data['code'] == 200) {
            return $this->view->render($response, 'users/dashboard/donasi.twig', $viewData);
        } else {
            return $this->view->render($response, 'templates/response/404.twig');
        }
    }

    public function getDonasiCampaign($request, $response, $args)
    {
        $donasiModel = new Donasi($this->db);
        try {
            $result = $this->client->request('GET', 'donasi/campaign/'.$args['id'], [
                'query' => [
                    'page' => $request->getQueryParam('page'),
                    'perpage' => 10
                ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $donatur = json_decode($result->getBody()->getContents(), true);
        // var_dump($donatur); die();

        try {
          $result = $this->client->request('GET', 'donasi/campaign/'. $args['id'].'/approved', [
            'query' => [
                // 'page' => $request->getQueryParam('page'),
                // 'perpage' => 10
            ]
          ]);  
        } catch (GuzzleException $e) {
           $result = $e->getResponse();
        }
        $approvedDonasi = json_decode($result->getBody()->getContents(), true);
        // var_dump($approvedDonasi['data']); die();
        $data['donatur'] = $donatur['data'];
        $data['pagination'] = $donatur['pagination'];
        $donasi = count($data['donatur']);
        for ($a = 0; $a < $donasi; $a++) {
            $rupiah = substr($donasiModel->convertRupiah($data['donatur'][$a]['nominal']), 3);
            $data['donatur'][$a]['nominal'] = $rupiah;
            // $tanggal = $donasiModel->convertTanggal($data['data'][$a]['tanggal_donasi']);
            // $tanggal = date('dd [.\t-] mm [.-] YY', $data['data'][$a]['tanggal_donasi']);
            // $data['data'][$a]['tanggal_donasi'] = $tanggal;
        }

        $c = count($approvedDonasi['data']);
        for ($a = 0; $a < $c; $a++) {
            $total_donasi += $approvedDonasi['data'][$a]['nominal'];
            $total = $donasiModel->convertRupiah($total_donasi);
        }

        try {
            $result = $this->client->request('GET', 'campaign/'. $args['id']. '/detail');
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        // var_dump($total); die();

        $campaign = json_decode($result->getBody()->getContents(), true);



        $data['total_donasi'] = $total;
        $data['total_donatur'] = $c;
        $data['id'] = $args['id'];
        $data['data']['cover']  = $campaign['data']['cover'];
        $data['data']['lokasi']  = $campaign['data']['lokasi'];
        $data['data']['category'] = $campaign['data']['category'];
        $data['data']['title'] = $campaign['data']['title'];
        $data['id'] = $args['id'];
        // var_dump($data); die();

        // var_dump($data['data']); die(); 


        if ($donatur['code'] == 200) {
            return $this->view->render($response, 'campaign/dashboard/donasi.twig', $data);
        } else {
            return $this->view->render($response, 'templates/response/404.twig');
        }
    }

    public function getSummary($request, $response)
    {
        $data = $_SESSION['donasi'];
        var_dump($data); die();

        return $this->view->render($response, 'donasi/summary.twig', $data);
    }

    public function getKonfirmasi($request, $response)
    {
        return $this->view->render($response, 'donasi/konfirmasi.twig');
    }
}
