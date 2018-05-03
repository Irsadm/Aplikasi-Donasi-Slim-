<?php

namespace App\Models;


class Media extends AbstractModel
{
    protected $table = 'media';

    public function add($data)
    {
        $data = [
            'campaign_id' => $data['campaign_id'],
            'url'   => $data['url'],
            'media_type'   => $data['url'],
        ];
        $this->createData($data);

        return $this->db->lastInsertId();

    }

    public function getByCampaignId($campaignId)
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select('*')
           ->from($this->table)
           ->where('campaign_id = :campaign_id')
           ->setParameter(':campaign_id', $campaignId);


           $result = $qb->execute();

           return $result->fetchAll();



    }



}
