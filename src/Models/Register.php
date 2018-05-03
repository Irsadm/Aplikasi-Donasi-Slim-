<?php

namespace App\Models;


class Register extends AbstractModel
{
    protected $table = 'registers';
    protected $column = ['id', 'user_id', 'token', 'expired_at'];

    public function registerToken($id, $token)
    {
        $now = date('Y-m-d H:i:s');
        $expired = strtotime('+7 days', strtotime($now));
        $data = [
            'user_id' => $id,
            'token'   => $token,
            'expired_date' => date('Y-m-d H:i:s', $expired)
        ];

        $this->createData($data);
        return $this->db->lastInsertId();
    }

    public function findToken($id)
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select('*')
           ->from($this->table)
           ->where('id ='.$id);

           $result = $qb->execute();
           return $result->fetch();
    }

    public function setStatus($status, $id)
    {
      $data['status'] = $status;
      $this->updateData($data, 'id', $id); 
    }


    public function update(array $data, $column, $value)
    {
          $columns = [];
          $paramData = [];
          $qb = $this->db->createQueryBuilder();
          $qb->update($this->table);
          foreach ($data as $key => $values) {
              $columns[$key] = ':'.$key;
              $paramData[$key] = $values;
              $qb->set($key, $columns[$key]);
          }
          $qb->where( $column.'='. $value)
             ->setParameters($paramData)
             ->execute();
    }

    public function getUserId($token)
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select('*')
           ->from($this->table)
           ->where('token = :token')
           ->setParameter(':token', $token);

           $result = $qb->execute();

           return $result->fetch()['user_id'];
    }
}
