<?php

namespace App\Controllers\api;

use App\Models\Donasi;
use App\Models\Token;
use App\Models\Payment;

class DonasiController extends BaseController
{
    public function index($request, $response)
    {
        $donasiModel = new Donasi($this->db);
        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perpage = !$request->getQueryParam('perpage') ? 1 : $request->getQueryParam('perpage');
        $data = $donasiModel->allDonasi()->setPaginate($page, $perpage);
        foreach ($data['data'] as $key => $val) {
            $sum += $val['nominal'];
        }
        // var_dump($sum); die();
        // $data['data']['total'] = $sum;
        if (!empty($data)) {
            return $this->responseDetail(200, false, 'Data tersedia', $data);

        } else {
            return $this->responseDetail(400, true, 'Data tidak ditemukan');
        }
    }

    public function createDonasi($request, $response)
    {   
        $donasiModel = new Donasi($this->db);
        $this->validation->rule('min', 'nominal', '20000')->message('Minimal donasi Rp 20.000');
        $this->validation->rule('required', 'nominal')
             ->label('Nominal donasi');
        if ($this->validation->validate()) {
            $data = [
                'campaign_id' => $request->getParam('campaign_id'),
                'user_id' => $request->getParam('user_id'),
                'nominal' => $request->getParam('nominal'),
                'komentar' => $request->getParam('komentar')
            ];

            $newDonasi = $donasiModel->create($data);
            $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
            $perpage = !$request->getQueryParam('perpage') ? 5 : $request->getQueryParam('perpage');
            $findDonasi['data'] = $donasiModel->getDonasi('id', $newDonasi)->setPaginate($page, $perpage);
            // var_dump($findDonasi); die();
            return $this->responseDetail(200, false, 'Donasi Berhasil', $findDonasi['data']);
        } else {
            return $this->responseDetail(400, true, $this->validation->errors());
        }
    }

    public function getUserDonasi($request, $response)
    {
        $donasiModel = new Donasi($this->db);
        $tokenModel = new Token($this->db);
        $token = $request->getHeader('Authorization')[0];
        $userId = $tokenModel->getUserId($token);
        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perpage = !$request->getQueryParam('perpage') ? 5 : $request->getQueryParam('perpage');
        $findDonasi = $donasiModel->getDonasi('user_id', $userId, 1)->setPaginate($page, $perpage);

        if (!empty($findDonasi)) {
            return $this->responseDetail(200, false, 'Data ditemukan', $findDonasi);
        } else {

            return $this->responseDetail(400, true, 'Data tidak ditemukan');
        }

    }

    public function getApprovedCampaignDonasi($request, $response, $args)
    {
        $donasiModel = new Donasi($this->db);
        $campaignModel = new \App\Models\Campaign($this->db);
        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perpage = !$request->getQueryParam('perpage') ? 10: $request->getQueryParam('perpage');
        $findCampaign = $campaignModel->find('id', $args['id']);
        $findDonasi = $donasiModel->getApprovedDonasi('campaign_id', $args['id'])->setPaginate($page, $perpage);

        if (!empty($findDonasi['data'])) {
            return $this->responseDetail(200, false, 'Data ditemukan', $findDonasi);
        } else {
            return $this->responseDetail(400, true, 'Data tidak ditemukan');
        }

    }    

    public function getApprovedUserDonasi($request, $response, $args)
    {
        $donasiModel = new Donasi($this->db);
        $campaignModel = new \App\Models\Campaign($this->db);
        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perpage = !$request->getQueryParam('perpage') ? 10: $request->getQueryParam('perpage');
        $findCampaign = $campaignModel->find('id', $args['id']);
        $findDonasi = $donasiModel->getApprovedDonasi('user_id', $args['id'])->setPaginate($page, $perpage);

        if (!empty($findDonasi['data'])) {
            return $this->responseDetail(200, false, 'Data ditemukan', $findDonasi);
        } else {
            return $this->responseDetail(400, true, 'Data tidak ditemukan');
        }

    }

    public function getCampaignDonasi($request, $response, $args)
    {
        $donasiModel = new Donasi($this->db);
        $campaignModel = new \App\Models\Campaign($this->db);
        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perpage = !$request->getQueryParam('perpage') ? 5: $request->getQueryParam('perpage');
        $findCampaign = $campaignModel->find('id', $args['id']);
        $findDonasi = $donasiModel->getDonasi('campaign_id', $args['id'])->setPaginate($page, $perpage);

        if (!empty($findDonasi['data'])) {
            return $this->responseDetail(200, false, 'Data ditemukan', $findDonasi);
        } else {
            return $this->responseDetail(400, true, 'Data tidak ditemukan');
        }


    }

    public function getUnapproveDonasi($request, $response)
    {
        $donasiModel = new Donasi($this->db);
        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perpage = !$request->getQueryParam('perpage') ? 5 : $request->getQueryParam('perpage');
        $getDonasi = $donasiModel->unappoveDonasi()->setPaginate($page,$perpage);
    }

    // public function paymentConfirmation($request, $response)
    // {
    //     $donasiModel = new Donasi($this->db);
    //     $paymentModel = new Payment($this->db);
    //     $code = $request->getParam('code');
    //     $findDonasi = $donasiModel->find('code', $code);

    //     if ($findDonasi) {
    //         $data = [
    //             'donasi_id' => $findDonasi['code'],
    //             'bank_id'   => $request->getParam('bank_id'),
    //             'image'     => $request->getParam('image'),
    //             'status'     => 1
    //         ];
    //         $create = $paymentModel->create($data);
    //         $findKonfirmasi['data'] = $paymentModel->find('id', $create);

    //         return $this->responseDetail(200, false, 'Konfirmasi Sedekah berhasil Berhasil', $findKonfirmasi['data']);
    //     } else {
    //          return $this->responseDetail(400, true, 'Data tidak ditemukan');
    //     }
    // }
    
}

