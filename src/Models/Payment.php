<?php


namespace App\Models;


class Payment extends AbstractModel
{
    protected $table = 'payments';

    public function create($data)
    {

        $data = [
            'donasi_id' => $data['donasi_id'],
            'bank_id'   => $data['bank_id'],
            'image'   => $data['image'],
            'status'    => $data['status']
        ];

        $this->createData($data);

        return $this->db->lastInsertId();
    }



}
