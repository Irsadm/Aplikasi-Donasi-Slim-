<?php

namespace App\Controllers\web;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\Campaign;
use GuzzleHttp\Exception\BadResponseException as GuzzleException;


class CampaignController extends BaseController
{
    public function index($request, $response)
    {
        try {
            $result = $this->client->request('GET', 'campaign/all', [
                'query' => [
                    'perpage' => 6,
                    'page'  => $request->getQueryParam('page')
                ]
            ]);
        } catch (GuzzleException $e){
            $result = $e->getResponse();
        }
        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data['data']); die();
        foreach ($data['data'] as $key => $value) {
            $today = new \DateTime(date('Y-m-d H:i:s'));
            $deadline = new \DateTime($value['deadline']);
            $interval = $deadline->diff($today);
            $dayRemaining[] = $interval->format('%a');

        }
        // var_dump($_SESSION['login']); die();

        foreach ($data['data'] as $key => $value) {
            try {
                $result = $this->client->request('GET', 'donasi/campaign/' .$value['id'].'/approved');
            } catch (GuzzleException $e) {
                $result = $e->getResponse();
            }

            $userModel = new \App\Models\User($this->db);
            $co = count($data['data']);
                for ($x = 0; $x < $co; $x++)  {
                    $dataFinal[$x] = $data['data'][$x];
                $findUser[$x] = $userModel->getUser('name', $dataFinal[$x]['campaigner']);

                    if ($findUser[$x]['status'] == 4) {
                        $data['data'][$x]['verified'] = true;
                    } else {
                        $data['data'][$x]['verified'] = false;
                        
                    }
                }
                        
            // var_dump($data['data']); die();
            $donasi = json_decode($result->getBody()->getContents(), true);
            $don[] = $donasi;
            // var_dump($don); die();

        }
            $c = count($don);
            for ($i = 0; $i < $c; $i++ ) {
            foreach ($don[$i]['data'] as  $value2) {
                $valu    = $value2['nominal'];
                $subNom[$i] += $valu;
                $campaignId = $value2['campaign_id'];
                $campaigns[$i] = $campaignId;
                }
                $don[$i]['data']['total_donasi'] = $subNom[$i];
              // var_dump($don[0]['data']); die();
              $co = count($data['data']);
                for ($a = 0; $a < $co; $a++)  {
                    $dataFinal[$a] = $data['data'][$a];
                    $data['data'][$a]['deskripsi_singkat'] = substr($data['data'][$a]['deskripsi_singkat'], 0, 110);

                if ($dataFinal[$a]['id'] == $campaigns[$i]) {
                    $data['data'][$a]['total_donasi'] = $subNom[$i];

                }
                }
            }

            // var_dump($data['data'][4]); die();
        $output = [
            'data' => $data['data'],
            'pagination' => $data['pagination'],
            'deadline' => $dayRemaining,
        ];

        if ($data['code'] == 200 && !empty($data['data'])) {
            return $this->view->render($response, 'campaign/explore.twig', $output);
        } else {
            return $this->view->render($response, 'templates/response/404.twig');
        }
    }

    public function getUserCampaign($request, $response)
    {
        $campaignModel = new Campaign($this->db);
        try {
            $result = $this->client->request('GET', 'user/campaign/all', [
                'query'  => [
                    'perpage' => '3',
                    'page'      => $request->getQueryParam('page'),
                ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data['data']); die();
        foreach ($data['data'] as $key => $value) {
            $today = new \Datetime(date('Y-m-d H:i:s'));
            $deadline = new \Datetime($value['deadline']);
            $interval = $deadline->diff($today);
            $dayRemaining[] = $interval->format('%a');
        }

        $datum = count($data['data']); 
             for ($a = 0; $a < $datum; $a++) {
            $targetRupiah = $campaignModel->convertRupiah($data['data'][$a]['target_dana']);
            $data['data'][$a]['target_dana'] = $targetRupiah;
        }
        // var_dump($data['data']); die();

        foreach ($data['data'] as $key => $value) {
            try {
                $result = $this->client->request('GET', 'donasi/campaign/' .$value['id'].'/approved');
            } catch (GuzzleException $e) {
                $result = $e->getResponse();
            }

            $donasi = json_decode($result->getBody()->getContents(), true);
            $don[] = $donasi;
            // var_dump($donasi); die();
        }

        $c = count($don);
        for ($i = 0; $i < $c; $i++ ) {
        foreach ($don[$i]['data'] as  $value2) {
            $valu    = $value2['nominal'];
            $donatur[$i] = count($don[$i]['data']);
            $subNom[$i] += $valu;
            $campaignId = $value2['campaign_id'];
            $campaigns[$i] = $campaignId;
            }
            $don[$i]['data']['total_donasi'] = $subNom[$i];
            $don[$i]['data']['donatur'] = $donatur[$i];
          // var_dump($don[0]['data']); die();
          $co = count($data['data']);
          // var_dump($data['data'][0]['target_dana']); die();
            for ($a = 0; $a < $co; $a++)  {
                $dataFinal[$a] = $data['data'][$a];

          // var_dump($subNom[$i]); die();
            if ($dataFinal[$a]['id'] == $campaigns[$i]) {
                  $subNom[$i] = $campaignModel->convertRupiah($subNom[$i]);
                  $data['data'][$a]['total_donasi'] = $subNom[$i];
                  $data['data'][$a]['donatur'] = $donatur[$i];
               } 
            }
        }

        // var_dump($data); die();
        $viewData = ['data' => $data['data'], 'remaining' => $dayRemaining, 'pagination' => $data['pagination']];
        if ($data['code'] == 200) {
            return $this->view->render($response, 'users/dashboard/campaign.twig', $viewData); 
        } else {
            return $this->view->render($response, 'templates/response/404.twig');
        }

    }

    public function detailCampaign($request, $response, $args)
    {
        $campaignModel = new Campaign($this->db);
        // detail campaign
        try {
            $result = $this->client->request('GET', 'campaign/'.$args['id'].'/detail');

        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);

        // donatur campaign
        try {
            $result = $this->client->request('GET', 'donasi/campaign/' .$data['data']['id'] . '/approved');
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $donatur = json_decode($result->getBody()->getContents(), true);
        // jumlah donatur
        $jumlah_donatur = count($donatur['data']);
   
        // convert tanggal
        for ($a=0; $a < $jumlah_donatur; $a++) {
        $tanggal = $campaignModel->convertTanggal($donatur['data'][$a]['tanggal_donasi']);
        $donatur['data'][$a]['tanggal_donasi'] = $tanggal;
        // total donasi
        $total_donasi += $donatur['data'][$a]['nominal'];
        }
        // progress
        $administrasi = 5 * $total_donasi / 100;
        $administrasiRup = $campaignModel->convertRupiah($administrasi);
        $progress = round($total_donasi / $data['data']['target_dana'] * 100);
        $danaCair = $total_donasi - $administrasi;
        $danaCair = $campaignModel->convertRupiah($danaCair);
        // var_dump($progress); die();  
        // convert rupiah 
        for ($a=0; $a < $jumlah_donatur; $a++) {
        $nominal = $campaignModel->convertRupiah($donatur['data'][$a]['nominal']);
        $donatur['data'][$a]['nominal'] = $nominal;
        }
        $target_dana = $campaignModel->convertRupiah($data['data']['target_dana']);
        $data['data']['target_dana'] = $target_dana;
        // total donasi
        $total_donasi_rupiah = $campaignModel->convertRupiah($total_donasi);
        // deadline
        $today = new \DateTime(date('Y-m-d H:i:s'));
        $deadline = new \DateTime($data['data']['deadline']);
        $interval = $deadline->diff($today);
        $data['data']['deadline'] = $interval->format('%a');
        // var_dump($_SESSION['login']); die(); 
      
        // var_dump($data['data']); die();
        $view_data = [
            'data' => $data['data'],
            'donatur' => $donatur['data'],
            'jumlah_donatur' => $jumlah_donatur,
            'jumlah_donasi' => $total_donasi_rupiah,
            'progress' => $progress,
        ];

        if ($data['code'] == 200) {
            if ($_SESSION['login']['name'] == $data['data']['name']) {
                $view_data['jumlah_donasi'] = substr($total_donasi_rupiah, 3);
                $view_data['administrasi'] = substr($administrasiRup, 3);
                $view_data['dana_cair'] = substr($danaCair, 3);
                $view_data['id']  = $args['id'];
                // $view_data['admin'] = 

                return  $this->view->render($response, 'campaign/dashboard/ringkasan.twig', $view_data);
            } else {
                return  $this->view->render($response, 'campaign/detail.twig', $view_data);
            }

        } else {
            return  $this->view->render($response, 'templates/response/404.twig');
            
        }

    }

    // public function getCampaignOverview($request, $response)
    // {

    // }

    public function searchCampaign($request, $response)
    {
        try {
            $result = $this->client->request('GET', 'search', [
                'query' => [
                    'perpage' => 9,
                    'page'    => $request->getQueryParam('page'),
                    'search'  => $request->getParam('search')
                ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);
        foreach ($data['data'] as $key => $value) {
            $today = new \DateTime(date('Y-m-d H:i:s'));
            $deadline = new \DateTime($value['deadline']);
            $interval = $deadline->diff($today);
            $dayRemaining[] = $interval->format('%a');

        }

        foreach ($data['data'] as $key => $value) {
            try {
                $result = $this->client->request('GET', 'donasi/campaign/' .$value['id']);
            } catch (GuzzleException $e) {
                $result = $e->getResponse();
            }

            $userModel = new \App\Models\User($this->db);
            $co = count($data['data']);
                for ($x = 0; $x < $co; $x++)  {
                    $dataFinal[$x] = $data['data'][$x];
                $findUser[$x] = $userModel->getUser('name', $dataFinal[$x]['campaigner']);

                    if ($findUser[$x]['status'] == 3) {
                        $data['data'][$x]['verified'] = true;
                    } else {
                        $data['data'][$x]['verified'] = false;
                        
                    }
                }
                        
            // var_dump($data['data']); die();
            $donasi = json_decode($result->getBody()->getContents(), true);
            $don[] = $donasi;

        }
            $c = count($don);
            for ($i = 0; $i < $c; $i++ ) {
            foreach ($don[$i]['data'] as  $value2) {
                $valu    = $value2['nominal'];
                $subNom[$i] += $valu;
                $campaignId = $value2['campaign_id'];
                $campaigns[$i] = $campaignId;
                }
                $don[$i]['data']['total_donasi'] = $subNom[$i];
              // var_dump($don[0]['data']); die();
              $co = count($data['data']);
                for ($a = 0; $a < $co; $a++)  {
                    $dataFinal[$a] = $data['data'][$a];

                if ($dataFinal[$a]['id'] == $campaigns[$i]) {
                    $data['data'][$a]['total_donasi'] = $subNom[$i];
                }
                }
            }

            // var_dump($data['pagination']); die();
        $output = [
            'data' => $data['data'],
            'pagination' => $data['pagination'],
            'deadline' => $dayRemaining,
        ];

        if ($data['code'] == 200 && !empty($data['data'])) {
            return $this->view->render($response, 'campaign/explore.twig', $output);
        } else {
            return $this->view->render($response, 'templates/response/404.twig');
        }
    }

    public function getCreateCampaign($request, $response)
    {
          try {
            $result = $this->client->request('GET', 'user/campaign/active', [
                'query'  => [
                    'perpage' => '3',
                    'page'      => $request->getQueryParam('page'),
                ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
        $campaignActive = json_decode($result->getBody()->getContents(), true);
        $count = count($campaignActive['data']); 
        // var_dump($count); die();

         try {
            $result = $this->client->request('GET', 'category/all'. $request->getUri()->getQuery());
        } catch (GuzzleException $e){
            $result = $e->getResponse();
        }
        $category = json_decode($result->getBody()->getContents(), true);
        $locationModel = new \App\Models\Location($this->db);
        $location = $locationModel->getLocations();
        $data = [
        'category' => $category['data'],
        'location'  => $location
        ];
        // var_dump($_SESSION['login']['status ']); die();


        if (empty($_SESSION['login'])) {
            return $response->withRedirect($this->router->pathFor('login'));
        } else {
            if ($_SESSION['login']['status'] != 4 && $count < 1) {
            return $this->view->render($response, 'campaign/new-campaign.twig', $data);
            } elseif ($_SESSION['login']['status'] != 4 && $count == 1) {
            $this->flash->addMessage('warning', 'Mohon maaf Anda belum melakukan verifikasi, Anda hanya diizinkan memiliki satu Campaign Active, Silakan melakukan Verifikasi untuk bisa membuat Campaign Baru');
            return $this->response->withRedirect($this->router->pathFor('user-campaign'));
            } else {
            return $this->view->render($response, 'campaign/new-campaign.twig', $data);
            }
        }

    }

    public function createCampaign($request, $response)
    {
        $post = $request->getParams();
        $path = $_FILES['image']['tmp_name'];
        $mime = $_FILES['image']['type'];
        $name = $_FILES['image']['name'];
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
        $target = (int)str_replace('.', '', $post['target_dana']);
        try {
            $result = $this->client->request('POST', 'user/new-campaign', [
                'form_params' => [
                    'title'                     => $post['title'],
                    'deskripsi_singkat'  => $post['deskripsi_singkat'],
                    'deskripsi_lengkap' => $post['deskripsi_lengkap'],
                    'target_dana'         => $target,
                    'deadline'              => $post['deadline'],
                    'lokasi_penerima'   => $post['lokasi_penerima'],
                    'category_id'         => $post['category_id'],
                    'user_id'                => $post['user_id'],
                    'cover'                  => $image['data'],
                ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);
        if ($image['code'] == 400 && $image['message'] !=  "Foto belum dipilih") {
            $post['cover'] = "";
            $data['message']['cover'][0] = "File tidak didukung";
            $_SESSION['old'] = $request->getParams();
            // $_SESSION['errors']['cover'] = $data['message']['cover'];
        }
        // var_dump($data); die();
        if ($data['code'] == 201) {
            return $response->withRedirect($this->router->pathFor('explore'));

        } else {
            // var_dump($data['message']); die();
            foreach ($data['message'] as $key => $value ) {
                $_SESSION['errors'][$key] = $data['message'][$key];
                // var_dump($key); die();
            }
            $_SESSION['old'] = $request->getParams();
            return $response->withRedirect($this->router->pathFor('new-campaign'));
        }
    }

    public function getCampaignDashboard($request, $response, $args)
    {

        return $this->view->render($response, 'campaign/dashboard/campaign-dashboard.twig');
    }

    public function getEditCampaign($request, $response, $args)
    {
        $campaignModel = new Campaign($this->db);
        try {
            $result = $this->client->request('GET', 'campaign/'.$args['id'].'/detail');

        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }


        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data['data']['deadline']); die();
        $tanggal = $data['data']['deadline'];
        $data['data']['deadline']= $campaignModel->convertTanggal($tanggal);
        $data['id'] = $args['id'];
        return $this->view->render($response, 'campaign/dashboard/edit-campaign/edit-campaign.twig', $data);
    }

    public function editDeskripsi($request, $response, $args)
    {
        $post = $request->getParams();
        try {
            $result = $this->client->request('POST', 'user/campaign/'. $args['id'].'/edit/deskripsi', [
                'form_params' => [
                    'deskripsi_singkat' => $post['deskripsi_singkat'],
                    'deskripsi_lengkap' => $post['deskripsi_lengkap']
                ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data); die();

        if ($data['code'] == 200) {
            $this->flash->addMessage('success', 'Deskripisi Campaign berhasil diperbarui');
            return $response->withRedirect($this->router->pathFor('edit-campaign', ['id' => $args['id']]));
        } else {
            return $this->view->render('templates/response/404.twig');
        }
    }

    public function editDeadline($request, $response, $args)
    {
        $post = $request->getParams();
        // var_dump($post['deadline']); die();
        try {
            $result = $this->client->request('POST', 'user/campaign/'. $args['id'].'/edit/deadline', [
                'form_params' => [
                    'deadline' => $post['deadline']
                ]
            ]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data); die();
        if ($data['code'] == 200) {
            $this->flash->addMessage('success', $data['message']);
            return $response->withRedirect($this->router->pathFor('edit-campaign', ['id' => $args['id']]));
        } else {
            $_SESSION['errors']['deadline'] = $data['message']['deadline'];
            return $response->withRedirect($this->router->pathFor('edit-campaign', ['id' => $args['id']]));
            
        }
    }

    public function editCover($request, $response, $args)
    {
        $post = $request->getParams();
        $path = $_FILES['image']['tmp_name'];
        $mime = $_FILES['image']['type'];
        $name = $_FILES['image']['name'];
        // var_dump(); die();
        if ($_FILES['image']['name'] == null) {
             $_SESSION['errors']['cover'][] = "Silakan pilih gambar baru untuk, mengganti sampul campaign";
            return $response->withRedirect($this->router->pathFor('edit-campaign', ['id' => $args['id']]));
        } else {
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
        }

        // var_dump($image); die();
        
        if ($image['code'] == 200) {
            try {
                        $result = $this->client->request('POST', 'user/campaign/'. $args['id'].'/edit/cover', [
                            'form_params' => [
                                'cover' => $image['data']
                            ]
                        ]);
                    } catch (GuzzleException $e) {
                        $result = $e->getResponse();
                    }

            $data = json_decode($result->getBody()->getContents(), true);
            $this->flash->addMessage('success', $data['message']);
            return $response->withRedirect($this->router->pathFor('edit-campaign', ['id' => $args['id']]));
        } else {
            $_SESSION['errors']['cover'][] = "File tidak didukung";
            return $response->withRedirect($this->router->pathFor('edit-campaign', ['id' => $args['id']]));
        
        }
            
       
    }

// Admin

    public function requestApprove($request, $response)
    {
        $campaign = new Campaign($this->db);

        $campaigns = $campaign->getCampaigns(0);
        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perpage = !$request->getQueryParam('perpage') ? 1 : $request->getQueryParam('perpage');
        // var_dump($campaigns); die();

        if (!empty($campaigns)) {
            $result = $this->paginateArray($campaigns, $page, 10);
            $data['data'] = $result['data'];
            $data['pagination'] = $result['pagination'];
            $datum = count($data['data']); 
            for ($a = 0; $a < $datum; $a++) {
            $targetRupiah = $campaign->convertRupiah($data['data'][$a]['target_dana']);
            $data['data'][$a]['target_dana'] = $targetRupiah;
        }
            return $this->view->render($response, '/admin/campaign/verificationrequest.twig', $data);
        } else {
            return $this->view->render($response, '/templates/response/404admin.twig');
        }

    }

    public function activeCampaign($request, $response)
    {
        $campaign = new Campaign($this->db);

        $campaigns = $campaign->getCampaigns(1);
        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perpage = !$request->getQueryParam('perpage') ? 1 : $request->getQueryParam('perpage');
        // var_dump($campaigns); die();

        if (!empty($campaigns)) {
            $result = $this->paginateArray($campaigns, $page, 10);
            $data['data'] = $result['data'];
            $data['pagination'] = $result['pagination'];
            $datum = count($data['data']); 
            for ($a = 0; $a < $datum; $a++) {
            $targetRupiah = $campaign->convertRupiah($data['data'][$a]['target_dana']);
            $data['data'][$a]['target_dana'] = $targetRupiah;
        }
            return $this->view->render($response, '/admin/campaign/active.twig', $data);
        } else {
            return $this->view->render($response, '/templates/response/404admin.twig');
        }

    }

    public function expiredCampaign($request, $response)
    {
        $campaign = new Campaign($this->db);

        $campaigns = $campaign->getCampaigns(2);
        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perpage = !$request->getQueryParam('perpage') ? 1 : $request->getQueryParam('perpage');
        // var_dump($campaigns); die();

        if (!empty($campaigns)) {
            $result = $this->paginateArray($campaigns, $page, 10);
            $data['data'] = $result['data'];
            $data['pagination'] = $result['pagination'];
            $datum = count($data['data']); 
            for ($a = 0; $a < $datum; $a++) {
            $targetRupiah = $campaign->convertRupiah($data['data'][$a]['target_dana']);
            $data['data'][$a]['target_dana'] = $targetRupiah;
        }
            return $this->view->render($response, '/admin/campaign/expired.twig', $data);
        } else {
            return $this->view->render($response, '/templates/response/404admin.twig');
        }

    }

    public function viewCampaign($request, $response, $args)
    {
        $campaign = new Campaign($this->db);

        $findCampaign = $campaign->getById($args['id']);

        // var_dump($findCampaign); die();
        if (!empty($findCampaign)) {
            $findCampaign['deadline'] = $campaign->convertTanggal($findCampaign['deadline']);
            $findCampaign['target_dana'] = $campaign->convertRupiah($findCampaign['target_dana']);
            return $this->view->render($response, '/admin/campaign/detailcampaign.twig', ['data'=>$findCampaign]);
        }
    }

    public function approveCampaign($request, $response, $args)
    {
        $campaign = new Campaign($this->db);

        $findCampaign = $campaign->find('id', $args['id']);

        if ($findCampaign) {
            $campaign->setStatus(1, $args['id']);
            $this->flash->addMessage('success', 'Campaign telah disetujui');
            return $this->response->withRedirect($this->router->pathFor('campaign.request'));
        } else {
            return $this->view->render($response, '/templates/response/404admin.twig');
        }
    }

    public function deleteCampaign($request, $response, $args)
    {
        $campaign = new Campaign($this->db);

        $findCampaign = $campaign->find('id', $args['id']);

        if ($findCampaign) {
            $campaign->hardDelete($args['id']);
            $this->flash->addMessage('warning', 'Campaign '. $findCampaign['title'] .' telah dihapus');
            return $this->response->withRedirect($this->router->pathFor('campaign.active'));
        } else {
            return $this->view->render($response, '/templates/response/404admin.twig');
        }
    }
    





}
