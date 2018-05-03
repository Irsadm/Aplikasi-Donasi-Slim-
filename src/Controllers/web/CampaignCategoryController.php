<?php

namespace App\Controllers\web;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Exception\BadResponseException as GuzzleException;
use App\Models\CampaignCategory;


class CampaignCategoryController extends BaseController
{
    public function index($request, $response)
    {
        $categoryModel = new CampaignCategory($this->db);
        $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
        $data = $categoryModel->getCategories();
        // var_dump($data); die();
        $getAll= $this->paginateArray($data, $page, 10);
        // var_dump($getAll); die();

        return $this->view->render($response, 'admin/category/categories.twig', $getAll);
    }

    public function addCategory($request, $response)
    {
        $categoryModel = new CampaignCategory($this->db);

        $newCategory = $_POST['category'];

        $checkCategory = $categoryModel->find('category', $newCategory);
        // var_dump($checkCategory); die();
        if ($checkCategory != null) {
            $this->flash->addMessage('warning', 'Kategori sudah ada');
            return $this->response->withRedirect($this->router->pathFor('category.index')); 
        } else {
            $categoryModel->add($newCategory);
            $this->flash->addMessage('success', 'Kategori berhasil ditambahkan');
            return $this->response->withRedirect($this->router->pathFor('category.index'));
        }
    }

    public function editCategory($request, $response, $args)
    {
        $categoryModel = new CampaignCategory($this->db);
        $findCategory = $categoryModel->find('id', $args['id']);
        $newCategory = $_POST['category'];

        
        // var_dump($checkCategory); die();
        if ($findCategory != null) {
            $checkCategory = $categoryModel->find('category', $newCategory);
            if ($checkCategory != null) {
            $this->flash->addMessage('warning', 'Kategori sudah ada');
            return $this->response->withRedirect($this->router->pathFor('category.index')); 
            } else {
                $data = [
                    'category' => $newCategory
                ];
                $categoryModel->updateData($data, 'id', $args['id']);
                $this->flash->addMessage('success', 'Kategori berhasil diperbarui');
                return $this->response->withRedirect($this->router->pathFor('category.index'));
            }
        } else {
            $this->flash->addMessage('warning', 'Kategori tidak ditemukan');
            return $this->response->withRedirect($this->router->pathFor('category.index'));
            
        }
    }

    public function deleteCategory($request, $response, $args)
    {
        $categoryModel = new CampaignCategory($this->db);
        $findCategory = $categoryModel->find('id', $args['id']);

        
        // var_dump($checkCategory); die();
        if ($findCategory != null) {
            $categoryModel->hardDelete($args['id']);
            $this->flash->addMessage('success', 'Kategori berhasil dihapus');
            return $this->response->withRedirect($this->router->pathFor('category.index'));
            
        } else {
            $this->flash->addMessage('warning', 'Kategori tidak ditemukan');
            return $this->response->withRedirect($this->router->pathFor('category.index'));
            
        }
    }
}
