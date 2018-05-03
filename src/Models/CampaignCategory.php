<?php


namespace App\Models;


class CampaignCategory extends AbstractModel
{
    protected $table = 'campaign_category';

    public function add($category)
    {
        $data = [
            'category' => $category
        ];

        $this->createData($data);

        return $this->db->lastInsertId();
    }

    public function getCategories()
    {
        $qb = $this->db->createQueryBuilder();

        $qb->select('*')
           ->from($this->table);

           $result = $qb->execute();

           return $result->fetchAll();
    }



}
