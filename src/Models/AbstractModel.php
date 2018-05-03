<?php

namespace App\Models;


abstract class AbstractModel
{
    protected $table;
    protected $db;
    protected $qb;
    protected $column;

    public function __construct($db)
    {
        $this->db = $db;
        $this->qb = $db->createQueryBuilder();
    }

    public function getAll()
    {
        $this->qb->select('*')->from($this->table)->where('deleted=0');

        $query = $this->qb->execute();

        return $query->fetchAll();

    }

    public function find($column, $value)
    {
        $param = ':'.$column;
        $qb = $this->db->createQueryBuilder();
        $qb->select('*')
            ->from($this->table)
            ->setParameter($param, $value)
            ->where($column . ' = '. $param);
        $result = $qb->execute();
        return $result->fetch();
    }

    public function findNotDelete($column, $value)
    {
        $param = ':' .$column;
        $this->qb
             ->select($this->column)
             ->from($this->table)
             ->where($column . '=' .$param. ' AND deleted = 0')
             ->setParameter($param, $value);
        $result = $this->qb->execute();

        return $result->fetch();

    }

    public function createData(array $data)
    {
        $valuesColumn = [];
        $valuesData   = [];

        foreach ($data as $dataKey => $dataValue) {

                $valuesColumn[$dataKey] = ':' .$dataKey;
                $valuesData[$dataKey]   = $dataValue;

        }
        $this->qb->insert($this->table)
             ->values($valuesColumn)
             ->setParameters($valuesData)
             ->execute();
    }

    public function updateData(array $data, $column, $value)
    {
        $columns = [];
        $paramData   = [];

        $this->qb->update($this->table);

        foreach ($data as $key => $values) {

                $columns[$key] = ':' .$key;
                $paramData[$key]   = $values;

                $this->qb->set($key, $columns[$key]);

        }

        $this->qb->where($column. '='. $value)
             ->setParameters($paramData);

            return $this->qb->execute();
    }

    public function softDelete($column, $value)
    {
        $this->qb
             ->update($this->table)
             ->set('deleted', 1)
             ->where($column. '=' .$value)
             ->execute();

    }

    public function showAll()
    {
        $this->qb
             ->select($this->column)
             ->from($this->table);

        $result = $this->qb->execute();
        return $result->fetchALl();

    }

    public function restore($id)
    {
        $this->qb
             ->update($this->table)
             ->set('deleted', 0)
             ->where('id =' .$id)
             ->execute();
    }

    public function hardDelete($id)
    {
        $this->qb
             ->delete($this->table)
             ->where('id =' .$id)
             ->execute();
    }

    public function getArchive()
    {
        $this->qb->select($this->column)
             ->from($this->table)
             ->where('deleted = 1');
        $result = $this->qb->execute();

        return $result->fetchAll();

    }

    // pagination

    public function fetchAll()
    {
            return $this->query->execute()->fetchAll();
    }

    public function setPaginate($page, $limit)
    {
        // count totoal custom query
        $total = count($this->fetchAll());
        // count total pages
        $pages = (int) ceil($total / $limit);
        $range = $limit * ($page - 1);
        $data  = $this->query->setFirstResult($range)->setMaxResults($limit);
        $data  = $this->fetchAll();
        $result = [
            'data'  => $data,
            'pagination' => [
                'total_data' => $total,
                'perpage'   => $limit,
                'current'   => $page,
                'total_page' => $pages,
                'first_page' => 1,

            ]
        ];
        return $result;

    }

    public function convertTanggal($tanggal, $waktu)
    {
        $datetime = new \DateTime($tanggal);
        $date = $datetime->format('Y-m-d');
        $time = $datetime->format('H:i:s');
        $bulan = [1=> 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember' ];
        $split   = explode('-', $date);
        if ($waktu == true) {
             $viewTime  = substr($time, 0, 5);
             } else {
              $viewTime = null;
             }
        return (int)$split[2] . ' ' . $bulan[ (int)$split[1]]. ' ' . $split[0] .' ' . $viewTime;

    }

    public function convertRupiah($angka)
    {
        $hasil = "Rp ". number_format($angka, 0, ',', '.');
        return $hasil;  
    }

    public function __destruct()
    {
        $this->db->close();
    }


}
