<?php


namespace App\Models;


class Donasi extends AbstractModel
{
    protected $table = 'donasi';

    public function create($data)
    {
        $code = "D-".date('ymdhis');
        $data = [
            'campaign_id' => $data['campaign_id'],
            'user_id'     => $data['user_id'],
            'nominal'     => $data['nominal'],
            'komentar'     => $data['komentar'],
            'code' => $code,
        ];

        $this->createData($data);

        return $this->db->lastInsertId();
    }

    public function allDonasi()
    {
        $qb = $this->db->createQueryBuilder();
        $this->query = $qb->select('c.title', 'u.name', 'd.komentar', 'd.nominal')
            ->from($this->table, 'd')
            ->leftjoin('d', 'campaigns', 'c', 'c.id = d.campaign_id')
            ->leftjoin('d', 'users', 'u', 'u.id = d.user_id');

            return $this;

    }

    public function unapproveDonasi()
    {
        $qb = $this->db->createQueryBuilder();
        $this->query = $qb->select('d.id', 'c.title', 'u.name', 'd.komentar', 'd.nominal', 'd.code')
               ->from($this->table, 'd')
               ->leftjoin('d', 'campaigns', 'c', 'c.id = d.campaign_id')
               ->leftjoin('d', 'users', 'u', 'u.id = d.user_id')
               ->where('d.status = 0');

               return $this;
    }

    public function getDonasi($column, $value)
    {
        $param = ':'.$column;
        $qb = $this->db->createQueryBuilder();
        $this->query = $qb->select('c.title', 'd.status', 'd.campaign_id as id', 'u.name', 'd.komentar', 'd.nominal', 'd.created_at as tanggal_donasi', 'd.code')
            ->from($this->table, 'd')
            ->leftjoin('d', 'campaigns', 'c', 'c.id = d.campaign_id')
            ->leftjoin('d', 'users', 'u', 'u.id = d.user_id')
            ->where('d.'.$column. '='.$param)
            ->setParameter($param, $value);

            return $this;
    }

    public function getApprovedDonasi($column, $value)
    {
        $param = ':'.$column;
        $qb = $this->db->createQueryBuilder();
        $this->query = $qb->select('c.title', 'd.campaign_id', 'd.status', 'u.name', 'd.komentar', 'd.nominal', 'd.created_at as tanggal_donasi')
            ->from($this->table, 'd')
            ->leftjoin('d', 'campaigns', 'c', 'c.id = d.campaign_id')
            ->leftjoin('d', 'users', 'u', 'u.id = d.user_id')
            ->where('d.'.$column. '='.$param)
            ->andWhere('d.status = 1')
            ->setParameter($param, $value);

            return $this;
    }

    public function setStatus($status, $id)
    {
        $data = [
            'status'  => $status,

        ];

        $this->updateData($data, 'id', $id );
    }

}
