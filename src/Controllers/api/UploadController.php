<?php

namespace App\Controllers\api;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Extensions\Mailer;
use App\Models\User;
use App\Models\Token;

class UploadController extends BaseController
{
    // public function cekDimensiMin($lokasi_file)
    // {
    //     $lebar_min       = 700;
    //     $tinggi_min      = 500;
    //     $lokasi_gambar   = $lokasi_file;
    //     $hasil           = 0;

    //     $ukuran_asli = GetImageSize($lokasi_gambar);

    //     if ( $ukuran_asli[0] < $lebar_min  || $ukuran_asli[1] < $tinggi_min ) {
    //         $hasil = 1;
    //     }

    //     return $hasil;
    // }

    public function upload($request, $response)
    {
        if (!empty($request->getUploadedFiles()['image'])) {
               $storage = new \Upload\Storage\FileSystem('assets/images/image');
               $image = new \Upload\File('image', $storage);
               $image->setName(uniqid('img-'. date(Ymd). '-'));
               $imageValidation = $image->addValidations([
                new \Upload\Validation\Mimetype(['image/png', 'image/gif', 'image/jpg', 'image/jpeg']),
                new \Upload\Validation\Size('1M')
               ]);
                $imageName = $image->getNameWithExtension();

             if ($imageValidation->isValid()) {
                $image->upload();
                $data = array('data' => $imageName);

                $flysystem = $this->flysystem;
                $current_path = __DIR__. '/../../../public/assets/images/image/'. $imageName;
                $new_path = 'images/new_image_resize/'. $imageName;

                list($width, $height) = getimagesize($current_path);

                // var_dump($current_path); die();
                // $percent = 0.5;
                $fixHeight = 256;
                $prosentase = $fixHeight / $height;
                // $newWidth = $width * $prosentase;
                $newHeight = $fixHeight;
                // $fixwidth = ;
                $newWidth = 409;



                // $newwidth = $with * $percent;
                // $newheight = $height * $percent;

                $thum = imagecreatetruecolor($newWidth, $newHeight);

                $source = imagecreatefromjpeg($current_path);

                imagecopyresized($thum, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

                ob_start();
                imagejpeg($thum);
                $new_content = ob_get_contents();
                ob_end_clean();

                $flysystem->write('new_image_resize/' . $imageName, $new_content);

                return $this->responseDetail(200, false, 'Unggah foto berhasil', $data);

            } else {
                foreach ($imageValidation->getErrors() as $value) {
                            $val = $value;
                        }
                        return   $this->responseDetail(400, true, $val);
            }
        } else {
               
            return $this->responseDetail(400, true, 'Foto belum dipilih');
        }
    }

       // public function upload($request, $response)
       // {
       //      $content = file_get_contents('php://input');
       //      // $content = $_FILES['file'];
       //      // $content = $request->getUploadedFiles()['file'];
       //      // var_dump($content); die();
       //      $flysystem = $this->flysystem;
       //      if (!file_exists($this->path_image . '/new_image')) {
       //          mkdir($this->path_image.'/new_image',0755);
       //      }
       //      if (!file_exists($this->path_image . '/new_image_resize')) {
       //          mkdir($this->path_image.'/new_image_resize',0755);
       //      }
       //      $name_file = date("YmdHis").".jpg";
       //      $flysystem->write('new_image/'. $name_file, $content);


       //      $current_path = __DIR__. '/../../../public/assets/images/new_image/'. $name_file;
       //      $new_path = 'images/new_image_resize/'. $name_file;

       //      list($width, $height) = getimagesize($current_path);

       //      // var_dump($current_path); die();
       //      // $percent = 0.5;
       //      $fixwidth = 300;
       //      $prosentase = $fixwidth/$width;
       //      $newWidth = $fixwidth;
       //      $newHeight = $height * $prosentase;



       //      // $newwidth = $with * $percent;
       //      // $newheight = $height * $percent;

       //      $thum = imagecreatetruecolor($newWidth, $newHeight);

       //      $source = imagecreatefromjpeg($current_path);

       //      imagecopyresized($thum, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

       //      ob_start();
       //      imagejpeg($thum);
       //      $new_content = ob_get_contents();
       //      ob_end_clean();

       //      $flysystem->write('new_image_resize/' . $name_file, $new_content);
       //      $data = ['data' => $name_file];
       //      return $this->responseDetail(200, false, 'Foto berhasil diunggah', $data);

       // }

}
