<?php

namespace App\Models;

class User extends AbstractModel
{
    protected $table = 'users';

    public function add(array $data)
    {
        $data = [
            'name'  => $data['name'],
            'email'  => $data['email'],
            'password'  => password_hash($data['password'], PASSWORD_BCRYPT),
            'phone'       => $data['phone'],

        ];

        $this->createData($data);
        return $this->db->lastInsertId();
    }

    public function getAllUser($status)
    {
        $qb  = $this->db->createQueryBuilder();
        $this->query = $qb->select('u.id', 'u.name', 'l.kota_kabupaten as lokasi', 'u.email', 'u.biografi', 'u.phone', 'u.status', 'u.foto_verifikasi','u.foto_profil', 'u.created_at')
        ->from($this->table, 'u')
        ->leftjoin('u', 'locations', 'l', 'u.lokasi_id = l.id')
        ->where('u.deleted = 0')
        ->andWhere('u.status ='.$status);


        return $this;

    }

    public function getUser($column, $val)
    {
        $param = ':'.$column;

        $qb = $this->db->createQueryBuilder();
        $qb->select('u.id', 'u.name', 'l.kota_kabupaten as lokasi', 'u.email', 'u.phone', 'u.biografi', 'u.status', 'u.foto_profil', 'u.created_at')
        ->from($this->table, 'u')
        ->leftjoin('u', 'locations', 'l', 'u.lokasi_id = l.id')
        ->where('u.'.$column.'='.$param)
        ->setParameter($param, $val);

        $query = $qb->execute();
        return $query->fetch();
    }

    public function setPassword($password, $id)
    {
        $data = [
            'password'  => password_hash($password, PASSWORD_BCRYPT),

        ];

        $this->updateData($data, 'id', $id );
    }

    public function setStatus($status, $id)
    {
        $data = [
            'status'  => $status,

        ];

        $this->updateData($data, 'id', $id );
    }

    public function checkDuplicate($name, $email)
    {
        $checkUsername = $this->find('name', $name);
        $checkEmail = $this->find('email', $email);

        if ($checkUsername && $checkEmail) {
            return 3;
        } elseif ($checkEmail) {
            return 2;
        } elseif ($checkUsername ) {
            return 1;
        }

        return false;
    }

    public function getUserByToken($token)
    {
        $token = new \App\Models\Token($this->db);
        $findToken = $token->find('token', $token);
        $findUser  = $this->find('id', $findToken['user_id']);

        return $findUser;
    }



}





 ?>
