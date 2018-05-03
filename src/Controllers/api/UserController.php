<?php

namespace App\Controllers\api;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Extensions\Mailer;
use App\Models\User;
use App\Models\Token;



class UserController extends BaseController
{
    public function index(Request $request, Response $response)
    {
        $user = new User($this->db);

        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        if (empty($perpage)) {
            $perpage = 10;
        } else {
            $perpage = $request->getQueryParam('perpage');
        }

        $getUser = $user->getAllUser()->setPaginate($page, $perpage);
        // var_dump($getUser['pagination']); die();


        if ($getUser) {
            return $this->responseDetail(200, false, 'Data tersedia', [
                'data'  => $getUser['data'],
                'pagination' => $getUser['pagination']
            ]);

        } else {

            return $this->responseDetail(400, true, 'Data tidak ditemukan', null);
        }

    }

    public function registration($request, $response)
    {
        $mailer  = new Mailer();
        $user     = new User($this->db);
        $post     = $request->getParams();

        if ($post['status'] == 1) {
            $this->validation->rule('required', ['name', 'email', 'phone']);

            $this->validation->rule('email', 'email')->message('Format alamat email tidak benar');

            $this->validation->labels([
                'name'  => 'Nama',
                'email'  => 'Email',
                'phone' => 'Nomor Handphone'
            ]);

            if ($this->validation->validate()) {
                $data = [
                    'name'      => $post['name'],
                    'email'      => $post['email'],
                    'phone'     => $post['phone'],
                    'password' => null,
                ];

                $newUser = $user->add($data);
                $findUser['data'] = $user->getUser('id', $newUser);
                
                return $this->responseDetail(201, false, 'User baru berhasil ditambahkan', $findUser); 

            } else {
                return $this->responseDetail(400, true, $this->validation->errors());
            }

        } else {
            $this->validation->labels([
                'name' => 'Nama',
                'password' => 'Password',
                'confirm_password' => 'Konfirmasi Password',
                'email'   => 'Email'
            ]);
            $this->validation
                 ->rule('required', ['name', 'email', 'password', 'confirm_password']);
            $this->validation->rule('email', 'email')->message('Format alamat email tidak benar');
            $this->validation->rule('alphaNum', 'name');
            $this->validation->rule('lengthMax',
                [
                    'name','email', 'password'
                ], 30);
            $this->validation->rule('lengthMin', ['name', 'password'], 5);
            $this->validation->rule('equals', 'confirm_password', 'password');

            if ($this->validation->validate()) {
                $base = $request->getUri()->getBaseUrl();
                $checkDuplicate = $user->checkDuplicate($request->getParam('name'), $request->getParam('email'));

                if ($checkDuplicate == 3) {
                    return $this->responseDetail(400, true, 'Nama dan email sudah digunakan');
                } elseif ($checkDuplicate == 1) {
                    return $this->responseDetail(400, true, 'Nama sudah digunakan');
                } elseif ($checkDuplicate == 2) {
                    return $this->responseDetail(400, true, 'Email sudah digunakan');
                } else {
                    $data = [
                        'name'     => $post['name'],
                        'email'    => $post['email'],
                        'password' => $post['password'],
                    ];
                    $registerModel = new \App\Models\Register($this->db);
                    $newUser = $user->add($data);
                    $token = md5(openssl_random_pseudo_bytes(8));
                    $setToken = $registerModel->registerToken($newUser, $token);
                    $findToken = $registerModel->find('id',$setToken);
                    $keyToken = $findToken['token'];
                    $activateUrl = '<a href = '.$base ."/activation/".$keyToken.'><h3>AKTIFKAN AKUN</h3></a>';

                    $findUser = $user->find('id', $newUser);

                    $content = '<html><head></head>
                    <body style="font-family: Verdana;font-size: 12.0px;">
                    <table border="0" cellpadding="0" cellspacing="0" style="max-width: 600.0px;">
                    <tbody><tr><td><table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tbody><tr><td align="left">
                    </td></tr></tbody></table></td></tr><tr height="16"></tr><tr><td>
                    <table bgcolor="#337A" border="0" cellpadding="0" cellspacing="0"
                    style="min-width: 332.0px;max-width: 600.0px;border: 1.0px solid rgb(224,224,224);
                    border-bottom: 0;" width="100%">
                    <tbody><tr><td colspan="3" height="42px"></td></tr>
                    <tr><td width="32px"></td>
                    <td style="font-family: Roboto-Regular , Helvetica , Arial , sans-serif;font-size: 24.0px;
                    color: rgb(255,255,255);line-height: 1.25;">Aktivasi Akun Donasi App</td>
                    <td width="32px"></td></tr>
                    <tr><td colspan="3" height="18px"></td></tr></tbody></table></td></tr>
                    <tr><td><table bgcolor="#FAFAFA" border="0" cellpadding="0" cellspacing="0"
                    style="min-width: 332.0px;max-width: 600.0px;border: 1.0px solid rgb(240,240,240);
                    border-bottom: 1.0px solid rgb(192,192,192);border-top: 0;" width="100%">
                    <tbody><tr height="16px"><td rowspan="3" width="32px"></td><td></td>
                    <td rowspan="3" width="32px"></td></tr>
                    <tr><td><p>Yang terhormat '.$findUser['name'].',</p>
                    <p>Terima kasih telah mendaftar di donasiapp
                    Untuk mengaktifkan akun Anda, silakan klik tautan di bawah ini.</p>
                    <div style="text-align: center;"><p>
                    <strong style="text-align: center;font-size: 24.0px;font-weight: bold;">
                    '.$activateUrl.'</strong></p></div>
                    <tr height="32px"></tr></tbody></table></td></tr>
                    <tr height="16"></tr>
                    <tr><td style="max-width: 600.0px;font-family: Roboto-Regular , Helvetica , Arial , sans-serif;
                    font-size: 10.0px;color: rgb(188,188,188);line-height: 1.5;"></td>
                    </tr><tr><td></td></tr></tbody></table></body></html>';

                    $mail = [
                    'subject'   => ' Donasi App- Verifikasi Email',
                    'from'      =>  'donasiapp@gmail.com',
                    'to'        =>  $findUser['email'],
                    'sender'    =>  'Do-donasi',
                    'receiver'  =>  $findUser['name'],
                    'content'   =>  $content,
                    ];
                    $mailer->send($mail);
                    return  $this->responseDetail(201, false, 'Pendaftaran berhasil.
                    silakan cek email anda untuk mengaktifkan akun');
                }
            } else {
                $errors = $this->validation->errors();
                return $this->responseDetail(401, true, $errors);
            }
        }
    }

    // Login
    public function login($request, $response)
    {
        $user = new User($this->db);
        $this->validation->rule('required', ['email', 'password']);
        $this->validation->rule('email', 'email')->message('Format alamat email tidak benar');
        $this->validation->labels([
            'email'          => 'Email',
            'Password'    => 'Password'
        ]);
        $login = $user->find('email', $request->getParam('email'));
        $getUser = $user->getUser('email', $request->getParam('email'));
        // var_dump($getUser); die();
        if ($this->validation->validate()) {
            if (empty($login)) {
                return $this->responseDetail(401, true, 'Email belum terdaftar ');
            } else {
                $checkPassword = password_verify($request->getParam('password'), $login['password']);

                if ($checkPassword) {
                    $token = new Token($this->db);

                    $token->loginToken($login['id']);
                    $getToken = $token->find('user_id', $login['id']);
                    $key = [
                        'key_token' => $getToken['token']
                    ];

                    return $this->responseDetail(200, false, 'Login berhasil', [
                        'data' => $getUser,
                        'key'  => $key
                    ]);
                } else {
                    return $this->responseDetail(401, true, 'Password salah');
                }
            }
        } else {
            return $this->responseDetail(400, true, $this->validation->errors());
        }

    }

    public function logout($request, $response)
    {
        $user  = new User($this->db);
        $tokenModel = new Token($this->db);
        $token = $request->getHeader('Authorization')[0];
        $userId = $tokenModel->getUserId($token);
        $findToken = $tokenModel->find('user_id', $userId);
        $tokenModel->hardDelete($findToken['id']);
        return $this->responseDetail(200, false, 'Logout berhasil');

    }

    public function accountActivation($request, $response, $args)
    {
        $userModel = new User($this->db);
        $registerModel = new \App\Models\Register($this->db);
        $now = date('Y-m-d H:i:s');
        $token = $args['token'];
        $findToken = $registerModel->find('token', $token);
        $userId = $findToken['user_id']; 
        $findUser = $userModel->find('id', $userId);
        $emailUser = $findUser['email'];
        // var_dump($findToken); die();

        if ($findToken == true ) {
            $activation = $userModel->setStatus(1, $userId);
            $registerModel->setStatus(1, $findToken['id']);
            return $this->view->render($response, 'auth/activation.twig', ['email' => $emailUser]);
  
       } else {
            return $this->view->render($response, 'templates/response/404.twig');

       }
    }
    //
    public function editPersonalInformation($request, $response)
    {
        $user = new User($this->db);
        $tokenModel = new \App\Models\Token($this->db);
        $auth = $request->getHeader('Authorization')[0];
        $getUserId = $tokenModel->getUserId($auth);
        $post = $request->getParams();
        $findUser = $user->find('id', $getUserId);
        // die(return $this->responseDetail(200, false, 'ok', $findUser));
        if (!$findUser) {
            return $this->responseDetail(400, true, 'Data tidak ditemukan');
        } else {
            $this->validation->rule('required', 'phone');
            $this->validation->rule('numeric', 'phone');
            $this->validation->rule('lengthMin', 'phone', 9);
            $this->validation->rule('lengthMax', 'phone', 20);

            $this->validation->labels(['phone' => 'Nomor Handphone']);


            if ($this->validation->validate()) {
                $data = [
                    'phone'     => $post['phone'],
                    'lokasi_id' => $post['lokasi_id'],
                    'biografi'  => $post['biografi'],
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $user->updateData($data, 'id', $getUserId);
                $getUser['data'] = $user->getUser('id', $getUserId);
                return $this->responseDetail(200, false, 'Data berhasil diperbaharui',$getUser);
            } else {
                return $this->responseDetail(400, true, $this->validation->errors());
            }
        }
    }

    public function editProfilePhoto($request, $response)
    {
        $user = new User($this->db);
        $tokenModel = new \App\Models\Token($this->db);
        $auth = $request->getHeader('Authorization')[0];
        $getUser = $tokenModel->getUserId($auth);
        $findUser = $user->find('id', $getUser);
        // $imageUpload = $request->getUploadedFiles();

        if (!$findUser) {
            return $this->responseDetail(401, true, 'Anda tidak diizinkan untuk mengkakses halaman ini');
        } else {
            $datas = [
                'foto_profil' => $request->getParam('foto_profil')
            ];
            $user->updateData($datas, 'id', $getUser);
            $getData['data'] = $user->getUser('id',$getUser);
            return $this->responseDetail(200, false, 'Foto profil berhasil diperbarui', $getData);
        }
    }

    public function changePassword($request, $response)
    {
        $user = new User($this->db);
        $tokenModel = new Token($this->db);
        $auth = $request->getHeader('Authorization')[0];
        $userId = $tokenModel->getUserId($auth);

        $findUser = $user->find('id', $userId);
        $currentPassword = $findUser['password'];
        $verify  = password_verify($request->getParam('password'), $currentPassword);
        // var_dump($password); die();
            $this->validation->rule('required', ['password', 'new_password', 'confirm_password']);
            $this->validation->rule('equals', 'confirm_password', 'new_password');
            $this->validation->rule('lengthMin', 'new_password', 5);
            $this->validation->labels([
                    'password'              => 'Password',
                    'new_password'       => 'Password Baru',
                    'confirm_password'  => 'Konfirmasi Password'
                 ]);

        if ($this->validation->validate()) {
            if ($verify) {
                $user->setPassword($request->getParam('new_password'), $userId);
                return $this->responseDetail(200, false, 'Password berhasil diganti');
            } else {
                return $this->responseDetail(400, true, 'Password lama tidak sesuai');
            }
        } else {
                return $this->responseDetail(400, true, $this->validation->errors());
        }
    }


    public function forgotPassword($request, $response)
    {
        $userModel = new User($this->db);
        $registerModel = new \App\Models\Register($this->db);
        $token = $request->getHeader('Authorization')[0];

        $mailer = new \App\Extensions\Mailer;

        $email = $request->getParam('email');
        $base = $request->getUri()->getBaseUrl();

        $findUser = $userModel->find('email', $email);
        $userId = $findUser['id'];
        $this->validation->rule('required', 'email');
        $this->validation->rule('email', 'email')->message('Format alamat email tidak benar');
        $this->validation->label('Email');
        if ($this->validation->validate()) { 
            if (!$findUser) {
                return $this->responseDetail(400, true, 'Email belum terdaftar');
            } else {
                $token = str_shuffle('n3wPa55wo012d').substr(md5(microtime()), rand(0, 26), 37);
                $registerModel->registerToken($findUser['id'], $token);
                $resetUrl = '<a href ='.$base ."/password/reset/".$token.'> <h3>RESET PASSWORD</h3></a>';

                $content =  '<html><head></head>
                <body style="margin: 0;padding: 0; font-family: Verdana;font-size: 12.0px;">
                <table border="0" cellpadding="0" cellspacing="0" style="max-width: 600.0px;">
                <tbody><tr><td><table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody><tr><td align="left">
                </td></tr></tbody></table></td></tr><tr height="16"></tr><tr><td>
                <table bgcolor="#337AB7" border="0" cellpadding="0" cellspacing="0"
                 style="min-width: 332.0px;max-width: 600.0px;border: 1.0px solid rgb(224,224,224);
                 border-bottom: 0;" width="100%">
                <tbody><tr><td colspan="3" height="42px"></td></tr>
                <tr><td width="32px"></td>
                <td style="font-family: Roboto-Regular , Helvetica , Arial , sans-serif;font-size: 24.0px;
                color: rgb(255,255,255);line-height: 1.25;">Setel Ulang Sandi Reporting App</td>
                <td width="32px"></td></tr>
                <tr><td colspan="3" height="18px"></td></tr></tbody></table></td></tr>
                <tr><td><table bgcolor="#FAFAFA" border="0" cellpadding="0" cellspacing="0"
                 style="min-width: 332.0px;max-width: 600.0px;border: 1.0px solid rgb(240,240,240);
                 border-bottom: 1.0px solid rgb(192,192,192);border-top: 0;" width="100%">
                <tbody><tr height="16px"><td rowspan="3" width="32px"></td><td></td>
                <td rowspan="3" width="32px"></td></tr>
                <tr><td><p>Yang terhormat '.$findUser["name"].',</p>
                <p>Baru-baru ini Anda meminta untuk menyetel ulang kata sandi akun Reporting App Anda.
                  Untuk mengubah kata sandi akun Anda, silakan ikuti tautan di bawah ini.</p>
                  <div style="text-align: center;"><p>'.$resetUrl.'</p></div>
                 <p>Jika tautan tidak bekerja, Anda dapat menyalin atau mengetik kembali
                tautan berikut.</p>
                <p>'.$base."/password/reset/".$token.'</p>
                <p>Jika Anda tidak seharusnya menerima email ini, mungkin pengguna lain
                memasukkan alamat email Anda secara tidak sengaja saat mencoba menyetel
                ulang sandi. Jika Anda tidak memulai permintaan ini, Anda tidak perlu
                melakukan tindakan lebih lanjut dan dapat mengabaikan email ini dengan aman.</p>
                <p> <br />Terima kasih, <br /><br /> Admin Reporting App</p></td></tr>
                <tr height="32px"></tr></tbody></table></td></tr>
                <tr height="16"></tr>
                <tr><td style="max-width: 600.0px;font-family: Roboto-Regular , Helvetica , Arial , sans-serif;
                font-size: 10.0px;color: rgb(188,188,188);line-height: 1.5;"></td>
                </tr><tr><td></td></tr></tbody></table></body></html>';
                $mail = [
                'subject'   =>  'Setel Ulang Sandi',
                'from'      =>  'donasiapp@gmail.com',
                'to'        =>  $findUser['email'],
                'sender'    =>  'Donasi App Account Recovery',
                'receiver'  =>  $findUser['name'],
                'content'   =>  $content,
                ];
                $mailer->send($mail);
                return $this->responseDetail(200, false, 'Silakan cek email anda untuk mengubah password');
            }
         } else {
            return $this->responseDetail(400, true, $this->validation->errors());
         }
    }


    public function resetPassword($request, $response)
    {
        $userModel = new User($this->db);
        $registerModel = new \App\Models\Register($this->db);
        $password = $request->getParam('password');
        $token = $request->getParam('token');

        $this->validation->rule('required', ['password', 'confirm_password']);
        $this->validation->rule('equals', 'confirm_password', 'password');
        $this->validation->rule('lengthMin', 'password', 5);
        $this->validation->labels([
            'password'              => 'Password',
            'confirm_password' => 'Konfirmasi Password'
        ]);


            $findToken = $registerModel->find('token', $token);
            // var_dump($findToken); die();
        if ($this->validation->validate()) {
            $findUser  = $userModel->find('id', $findToken['user_id']);
            $now = date('Y-m-d H:i:s');      
            $userModel->setPassword($password, $findToken['user_id']);
            $registerModel->hardDelete($findToken['id']);
            return $this->responseDetail(200, false, 'Password berhasil diubah');

        } else {
            return $this->responseDetail(400, true, $this->validation->errors());
        }

    }

    public function verifikasiAkun($request, $response)
    {
        $user = new User($this->db);
        $tokenModel = new \App\Models\Token($this->db);
        $auth = $request->getHeader('Authorization')[0];
        $getUser = $tokenModel->getUserId($auth);
        $findUser = $user->find('id', $getUser);
        // $imageUpload = $request->getUploadedFiles();

        if (!$findUser) {
            return $this->responseDetail(401, true, 'Anda tidak diizinkan untuk mengkakses halaman ini');
        } else {
            $datas = [
                'foto_verifikasi' => $request->getParam('foto_verifikasi')
            ];
            $user->updateData($datas, 'id', $getUser);
            $getData['data'] = $user->getUser('id',$getUser);
            return $this->responseDetail(200, false, 'Permintaan verifikasi akun telah terkirim', $getData);
        }
    }


}
