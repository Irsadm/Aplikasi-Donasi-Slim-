<?php

namespace App\Controllers\api;

use App\Models\Bank;

class BankController extends BaseController
{
    public function index($request, $response)
    {
        $bankModel = new Bank($this->db);

        $getAll= $bankModel->getAllBank();
        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $perpage = !$request->getQueryParam('perpage') ? 3 : $request->getQueryParam('perpage');
        $data = $this->paginateArray($getAll, $page, $perpage);
        if (empty($getAll) || empty($data['data'])) {
            return $this->responseDetail(404, true, 'Data tidak ditemukan');
        } else {
            return $this->responseDetail(200, false, 'Data ditemukan', $data);
        }

    }

    public function createBank($request, $response)
    {
        $bankModel = new Bank($this->db);
        $post = $request->getParams();

        $this->validation->rule('required', ['nama', 'no_rekening', 'atas_nama', 'image']);

        $this->validation->labels([
            'nama' => 'Nama',
            'no_rekening' => 'No Rekening',
            'atas_nama' => 'Atas Nama',
            'image' => 'Gambar',
        ]);

        // var_dump($post); die();

        if ($this->validation->validate()) {
            $data = [
                'nama'          => $post['nama'],
                'no_rekening' => $post['no_rekening'],
                'atas_nama'   => $post['atas_nama'],
                'image'          => $post['image']

            ];

            $newBank = $bankModel->create($data);
            $getBank['data'] = $bankModel->findBank($newBank);

            return $this->responseDetail(201, false, 'Berhasil', $getBank);
        } else {
            return $this->responseDetail(400, true, $this->validation->errors());
        }
    }

    public function edit($request, $response, $args)
    {
       $bankModel = new Bank($this->db);
       $id = $args['id'];
       $post = $request->getParams();
       $getBank = $bankModel->findBank($id);

       if ($getBank == true) {
        $this->validation->rule('required', ['nama', 'no_rekening', 'atas_nama', 'image']);

        $this->validation->labels([
            'nama'            => 'Nama',
            'no_rekening'   => 'No Rekening',
            'atas_nama'     => 'Atas Nama',
            'image'            => 'Gambar',
        ]);

        if ($this->validation->validate()) {
            $data = [
             'nama'          => $post['nama'],
             'no_rekening' => $post['no_rekening'],
             'atas_nama'   => $post['atas_nama'],
             'image'         => $post['image'],
            ];
            $update = $bankModel->updateData($data, 'id', $id);
            return $this->responseDetail(200, false, 'Data berhasil diperbaharui');
        } else {
           return $this->responseDetail(400, true, $this->validation->errors());  
        }

       } else {
           return $this->responseDetail(404, true, 'Data tidak ditemukan');  
       }
    }

    public function delete($request, $response, $args)
    {
        $bankModel = new Bank($this->db);
        $id = $args['id'];

        $findBank = $bankModel->findBank($id);

        if ($findBank == true) {
            $bankModel->hardDelete($id);

            return $this->responseDetail(200, false, 'Data berhasil dihapus');
        } else {
            return $this->responseDetail(404, true, 'Data tidak ditemukan');
        }
    }
}
