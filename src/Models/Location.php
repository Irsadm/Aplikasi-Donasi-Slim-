<?php

namespace App\Models;

class Location extends AbstractModel
{
    protected $table ='locations';

    public function getLocations()
    {
        $qb = $this->db->createQueryBuilder();

        $qb->select('*')
             ->from($this->table);

             $result = $qb->execute();
             return $result->fetchAll();

    }

}