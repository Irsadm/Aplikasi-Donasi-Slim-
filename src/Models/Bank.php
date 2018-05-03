<?php

namespace App\Models;

class Bank extends AbstractModel
{
    protected $table = 'bank';

    public function create($data)
    {
        $data = [
            'nama'  => $data['nama'],
            'no_rekening'  => $data['no_rekening'],
            'atas_nama'  => $data['atas_nama'],
            'image'         => $data['image'],
            'method'         => 0,
        ];

         $this->createData($data);

        return $this->db->lastInsertId();
    }

    public function findBank($id)
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select('*')
             ->from($this->table)
             ->where('id = :id')
             ->setParameter(':id', $id);

             $result = $qb->execute();

             return $result->fetch();

    }

    public function getAllBank()
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select('*')
             ->from($this->table);

             $result = $qb->execute();

             return $result->fetchAll();
    }
}