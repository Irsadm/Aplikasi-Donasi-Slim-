<?php


namespace App\Controllers\api;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\CampaignCategory;


class CampaignCategoryController extends BaseController
{
    public function addCategorys($request, $response)
    {
        $categoryModel = new CampaignCategory($this->db);

        $newCategory = $request->getParam('category');

        $checkCategory = $categoryModel->find('category', $newCategory);
        // var_dump($checkCategory); die();
        if ($checkCategory != null) {
            return $this->responseDetail(400, true, 'Kategori sudah ada');
        } else {
            $categoryModel->add($newCategory);
            return $this->responseDetail(201, false, 'Berhasil menambahkan kategori baru');
        }
    }

    public function getAllCategory($request, $response)
    {
        $categoryModel = new CampaignCategory($this->db);

        $getAll['data'] = $categoryModel->getCategories();

        return $this->responseDetail(200, false, 'Data ditemukan', $getAll);
    }

    public function delete($request, $response, $args)
    {
        $categoryModel = new CampaignCategory($this->db);

        $findCategory = $categoryModel->find('id', $args['id']);

        if ($findCategory) {
            $categoryModel->hardDelete($args['id']);
            return $this->responseDetail(200, false, 'Kategori berhasil dihapus');
        } else {
            return $this->responseDetail(400, true, 'Kategori tidak ditemukan');
        }
    }
}
