<?php

namespace App\Models;


class Token extends AbstractModel
{
    protected $table = 'tokens';
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

    public function loginToken($id)
    {
        $data = [
            'user_id'      => $id,
            'token'        => md5(openssl_random_pseudo_bytes(8)),
            'login_at'     => date('Y-m-d H:i:s'),
            'expired_date' => date('Y-m-d H:i:s', strtotime('+2 hour'))
        ];

        $findUserId = $this->find('user_id', $id);

        if ($findUserId  && $findUserId['expired_date'] < strtotime('now')) {
            $data = array_reverse($data);
            $pop  = array_pop($data);

            $this->update($data, 'user_id', $id);
        } else {
            $this->createData($data);
        }
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
