<?php
namespace App\Controllers\api;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Models\Campaign;
use App\Models\User;
use App\Models\Token;

class CampaignController extends BaseController
{
    public function getAll($request, $response)
    {
        $campaignModel = new Campaign($this->db);

        $page = !$request->getParam('page') ? 1 : $request->getParam('page');
        $perpage = !$request->getParam('perpage') ? 5 : $request->getParam('perpage');

        $data = $campaignModel->getCampaigns(1);
        if (!empty($data)) {
            $result = $this->paginateArray($data, $page, $perpage);
            return $this->responseDetail(200, false, 'Data ditemukan', $result);
        } else {
            return $this->responseDetail(404, true, 'Data tidak ditemukan');
        }

    }

    public function createCampaign($request, $response)
    {
        $campaign = new Campaign($this->db);
        $user    = new User($this->db);
        $tokenModel = new Token($this->db);
        $auth  = $request->getHeader('Authorization')[0];
        $findUser = $tokenModel->find('token', $auth);
        $userId   = $findUser['user_id'];
        $now = date('Y-m-d H:i:s');

        if (!$auth) {
            return $this->responseDetail(401, true, 'Anda Belum terdaftar, silahkan melakukan pendaftaran terlebih dahulu');
        } else {
            $this->validation->rule(
                'required', [
                'title',
                'target_dana',
                'deadline',
                'deskripsi_singkat',
                'deskripsi_lengkap',
                'lokasi_penerima',
                'category_id',
                'cover',
                ]
            );

            $this->validation->rule('numeric', 'target_dana')->message('{field} harus berupa angka(nominal)');
            $this->validation->rule('min', 'target_dana', '1000000')->message('Minimal target dana Rp 1.000.000');
            $this->validation->rule(
                'dateAfter', 'deadline', $now
            )->message('{field} tidak valid');

            $this->validation->labels([
                'title' => 'Judul',
                'target_dana' => 'Target Dana',
                'deadline' => 'Deadline',
                'deskripsi_singkat' => 'Deskripsi Singkat',
                'lokasi_penerima' => 'Lokasi',
                'category_id' => 'Kategori',
                'cover' => 'Gambar Sampul',
                'deskripsi_lengkap' => 'Deskripsi Lengkap']);
            if ($this->validation->validate()) {
                $data = [
                    'title'             => $request->getParam('title'),
                    'target_dana'       => $request->getParam('target_dana'),
                    'deadline'          => $request->getParam('deadline'),
                    'lokasi_penerima'       => $request->getParam('lokasi_penerima'), 
                    'target_dana'  => $request->getParam('target_dana'),
                    'deskripsi_singkat' => $request->getParam('deskripsi_singkat'),
                    'deskripsi_lengkap' => $request->getParam('deskripsi_lengkap'),
                    'category_id'  => $request->getParam('category_id'),
                    'cover'  => $request->getParam('cover'),
                    'user_id'  => $userId,

                ];

                $create = $campaign->create($data);
                $findCampaign['data'] = $campaign->find('id', $create);
                // var_dump($findCampaign); die();
                return $this->responseDetail(201, false, 'Campaign baru berhasil dibuat', $findCampaign);
            } else {
                return $this->responseDetail(400, true, $this->validation->errors());
            }
        }

    }

    public function editDeskripsi($request, $response, $args)
    {

        $campaign = new Campaign($this->db);
        $user     = new User($this->db);
        $token    = new Token($this->db);
        $auth     = $request->getHeader('Authorization')[0];
        $userId   = $token->getUserId($auth);

        $findCampaign = $campaign->find('id', $args['id']);

        if ($findCampaign == true) {
            $data = [
                'deskripsi_singkat' => $request->getParam('deskripsi_singkat'),
                'deskripsi_lengkap' => $request->getParam('deskripsi_lengkap')
            ];
            $update = $campaign->updateData($data, 'id', $args['id']);

            return $this->responseDetail(200, false, 'Pembaharuan berhasil dilakukan');
        } else {
            return $this->responseDetail(404, true, 'Data tidak ditemukan');
        }


    }

    public function editDeadline($request, $response, $args)
    {
        $campaign = new Campaign($this->db);
        $user     = new User($this->db);
        $token    = new Token($this->db);
        $auth     = $request->getHeader('Authorization')[0];
        $userId   = $token->getUserId($auth);

        $findCampaign = $campaign->find('id', $args['id']);
        
        // var_dump($findCampaign); die();
        if ($findCampaign == true) {
            $this->validation->rule('required', 'deadline');
            $now = date('Y-m-d H:i:s');
            $this->validation->rule('dateAfter', 'deadline', $now)
                 ->message('{field} yang anda tentukan tidak valid');
            $this->validation->label('Deadline');
            if ($this->validation->validate()){
                $data = [
                    'deadline' => $request->getParam('deadline'),
                ];
                $update = $campaign->updateData($data, 'id', $args['id']);
                return $this->responseDetail(200, false, 'Deadline campaign berhasil diperbarui');
            } else {
                // var_dump($this->validation->errors()); die();
                return $this->responseDetail(400, true, $this->validation->errors());
            }


        } else {
            return $this->responseDetail(404, true, 'Data tidak ditemukan');
        }


    }

    public function editCover($request, $response, $args)
    {
        $campaign = new Campaign($this->db);
        $user     = new User($this->db);
        $token    = new Token($this->db);
        $auth     = $request->getHeader('Authorization')[0];
        $userId   = $token->getUserId($auth);
        $post  = $request->getParams();

        $findCampaign = $campaign->find('id', $args['id']);
        
        // var_dump($findCampaign); die();
        if ($findCampaign == true) {
                $data = [
                    'cover' => $post['cover'],
                ];
                $update = $campaign->updateData($data, 'id', $args['id']);
                return $this->responseDetail(200, false, 'Sampul campaign berhasil diperbarui');

        } else {
            return $this->responseDetail(404, true, 'Data tidak ditemukan');
        }

    }

    public function getUserCampaign($request, $response)
    {
        $token    = $request->getHeader('Authorization')[0];
        $userModel = new User($this->db);
        $tokenModel = new Token($this->db);
        $campaignModel = new Campaign($this->db);

        $userId = $tokenModel->getUserid($token);
        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perpage = !$request->getQueryParam('perpage') ? 5 : $request->getQueryParam('perpage');
        $data = $campaignModel->getAllCampaigns($userId);
        if (!empty($data)) {
            // foreach ($data as $camp) {
            //     if (!empty($new[$camp['id']])) {
            //         $currentValue = (array) $new[$camp['id']]['cover'];
            //         $new[$camp['id']]['cover'] = array_unique(array_merge($currentValue, (array) $camp['cover']));
            //     } else {
            //         $new[$camp['id']] = $camp;
            //     }
            // }
            $result = $this->paginateArray($data, $page, $perpage);
            return $this->responseDetail(200, false, 'Data ditemukan', $result);
        } else {
            return $this->responseDetail(404, true, 'Data tidak ditemukan');
        }

    }

    public function getUserActiveCampaign($request, $response)
    {
        $token    = $request->getHeader('Authorization')[0];
        $userModel = new User($this->db);
        $tokenModel = new Token($this->db);
        $campaignModel = new Campaign($this->db);

        $userId = $tokenModel->getUserid($token);
        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perpage = !$request->getQueryParam('perpage') ? 5 : $request->getQueryParam('perpage');
        $data = $campaignModel->findUserCampaign($userId, 1);
        if (!empty($data)) {
            // foreach ($data as $camp) {
            //     if (!empty($new[$camp['id']])) {
            //         $currentValue = (array) $new[$camp['id']]['cover'];
            //         $new[$camp['id']]['cover'] = array_unique(array_merge($currentValue, (array) $camp['cover']));
            //     } else {
            //         $new[$camp['id']] = $camp;
            //     }
            // }
            $result = $this->paginateArray($data, $page, $perpage);
            return $this->responseDetail(200, false, 'Data ditemukan', $result);
        } else {
            return $this->responseDetail(404, true, 'Data tidak ditemukan');
        }

    }

    public function detailCampaign($request, $response, $args)
    {
        $campaign = new Campaign($this->db);
        $id = $args['id'];

        $data['data'] = $campaign->getById($id);
        // var_dump($data); die();
        if (!empty($data['data'])) {
            return $this->responseDetail(200, false, 'Data ditemukan', $data);
        } else {
            return $this->responseDetail(404, true, 'Data tidak ditemukan');
        }
    }

    public function approveCampaign($request, $response, $args)
    {

        $campaign = new Campaign($this->db);
        $user     = new User($this->db);
        $token    = new Token($this->db);
        $auth     = $request->getHeader('Authorization')[0];
        $userId   = $token->getUserId($auth);

        $findCampaign = $campaign->find('id', $args['id']);
        // var_dump($findCampaign); die();
        if ($findCampaign == true) {
            $data = [
                'status' => 1
            ];
            $update = $campaign->updateData($data, 'id', $args['id']);

            return $this->responseDetail(200, false, 'Campaign disetujui');
        } else {
            return $this->responseDetail(404, true, 'Data tidak ditemukan');
        }
    }

    public function deleteCampaign($request, $response, $args)
    {

        $campaign = new Campaign($this->db);
        $user     = new User($this->db);
        $token    = new Token($this->db);
        $auth     = $request->getHeader('Authorization')[0];
        $userId   = $token->getUserId($auth);

        $findCampaign = $campaign->find('id', $args['id']);

        if ($findCampaign == true) {

            $delete = $campaign->hardDelete($args['id']);

            return $this->responseDetail(200, false, 'Campaign berhasil dihapus');
        } else {
            return $this->responseDetail(404, true, 'Data tidak ditemukan');
        }


    }

    public function searchCampaign($request, $response)
    {
        $campaignModel = new Campaign($this->db);
        $searchParam   = $request->getQueryParam('search');

        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perpage = !$request->getQueryParam('perpage') ? 5 : $request->getQueryParam('perpage') ;


        $data = $campaignModel->search($searchParam);
        // var_dump($data); die();

            if ($data == null) {
                return $this->responseDetail(404, true, 'Tidak ditemukan hasil');
            } else {
                // foreach ($data as $camp) {
                //     if (!empty($new[$camp['id']])) {
                //         $currentValue = (array) $new[$camp['id']]['cover'];
                //         $new[$camp['id']]['cover'] = array_unique(array_merge($currentValue, (array) $camp['cover']));
                //     } else {
                //         $new[$camp['id']] = $camp;
                //     }
                // }
                $result = $this->paginateArray($data, $page, $perpage);
                return $this->responseDetail(200, false, 'Data ditemukan', $result);
            }
    }
}
