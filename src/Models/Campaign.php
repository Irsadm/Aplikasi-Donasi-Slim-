<?php

namespace App\Models;

class Campaign extends AbstractModel
{
    protected $table = 'campaigns';

    public function create($data)
    {
        $data = [
            'title'               => $data['title'],
            'category_id'         => $data['category_id'],
            'lokasi_penerima'     => $data['lokasi_penerima'],
            'user_id'             => $data['user_id'],
            'target_dana'         => $data['target_dana'],
            'deadline'            => $data['deadline'],
            'deskripsi_singkat'   => $data['deskripsi_singkat'],
            'deskripsi_lengkap'   => $data['deskripsi_lengkap'],
            'cover'                    => $data['cover']
        ];

        $this->createData($data);
        return $this->db->lastInsertId();
    }

    public function getCampaigns($status)
    {
        $qb = $this->db->createQueryBuilder();

         $qb->select('c.id', 'c.deadline', 'c.title', 'cc.category', 'u.name as campaigner', 'lo.kota_kabupaten as lokasi', 'c.target_dana', 'c.deskripsi_singkat', 'c.cover')
        ->from($this->table, 'c')
        ->leftjoin('c', 'campaign_category', 'cc', 'cc.id = c.category_id')
        ->leftjoin('c', 'locations', 'lo', 'lo.id = c.lokasi_penerima')
        ->leftjoin('c', 'users', 'u', 'u.id = c.user_id')
        ->where('c.status ='.$status);

        $result = $qb->execute();
        return $result->fetchAll();

        // return $this;
    }

    public function findUserCampaign($userId, $status)
    {
        $qb = $this->db->createQueryBuilder();
         $qb->select('c.id', 'c.title', 'c.deadline', 'cc.category', 'u.name as campaigner', 'lo.kota_kabupaten as lokasi', 'c.target_dana', 'c.deskripsi_singkat', 'c.cover')
           ->from($this->table, 'c')
           ->leftjoin('c', 'campaign_category', 'cc', 'cc.id = c.category_id')
           ->leftjoin('c', 'locations', 'lo', 'lo.id = c.lokasi_penerima')
           ->leftjoin('c', 'users', 'u', 'u.id = c.user_id')
           // ->leftjoin('c', 'media', 'm', 'm.campaign_id = c.id')
           ->where('c.user_id = :id')
           ->andWhere('c.status = :status')
           ->setParameter(':status', $status)
           ->setParameter(':id', $userId);

           $result = $qb->execute();

           return $result->fetchAll();

    }

    public function getAllCampaigns($userId)
    {
        $qb = $this->db->createQueryBuilder();
         $qb->select('c.id', 'c.title', 'c.deadline', 'cc.category', 'u.name as campaigner', 'lo.kota_kabupaten as lokasi', 'c.target_dana', 'c.deskripsi_singkat', 'c.cover')
           ->from($this->table, 'c')
           ->leftjoin('c', 'campaign_category', 'cc', 'cc.id = c.category_id')
           ->leftjoin('c', 'locations', 'lo', 'lo.id = c.lokasi_penerima')
           ->leftjoin('c', 'users', 'u', 'u.id = c.user_id')
           // ->leftjoin('c', 'media', 'm', 'm.campaign_id = c.id')
           ->where('c.user_id = :id')
           ->setParameter(':id', $userId);

           $result = $qb->execute();

           return $result->fetchAll();

    }

    public function getById($id)
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select('c.id', 'c.title', 'ct.category', 'c.cover', 'u.name', 'c.deadline', 'c.deskripsi_singkat', 'c.deskripsi_lengkap', 'c.target_dana', 'l.kota_kabupaten as lokasi')
           ->from($this->table, 'c')
           ->leftjoin('c', 'locations', 'l', 'l.id = c.lokasi_penerima')
           ->leftjoin('c', 'users', 'u', 'u.id = c.user_id')
           ->leftjoin('c', 'campaign_category', 'ct', 'ct.id = c.category_id')
           ->where('c.id = :id')
           ->setParameter(':id', $id);

           $result = $qb->execute();

           return $result->fetch();


    }

    public function search($param)
    {
        $qb = $this->db->createQueryBuilder();
         $qb->select('c.id', 'c.deskripsi_singkat', 'c.deskripsi_lengkap', 'c.title', 'cc.category', 'u.name as campaigner', 'lo.kota_kabupaten as lokasi', 'c.target_dana', 'c.deskripsi_singkat', 'c.cover')
        ->from($this->table, 'c')
        ->where('c.title LIKE :param')
        ->orWhere('c.deskripsi_singkat LIKE :param')
        ->orWhere('c.deskripsi_lengkap LIKE :param')
        ->setParameter(':param','%'.$param.'%')
        ->leftjoin('c', 'campaign_category', 'cc', 'cc.id = c.category_id')
        ->leftjoin('c', 'locations', 'lo', 'lo.id = c.lokasi_penerima')
        ->leftjoin('c', 'users', 'u', 'u.id = c.user_id');

        $result = $qb->execute();
        return $result->fetchAll();
    }

    public function setStatus($status, $id)
    {
        $data = [
            'status'  => $status,

        ];

        $this->updateData($data, 'id', $id );
    }
}
