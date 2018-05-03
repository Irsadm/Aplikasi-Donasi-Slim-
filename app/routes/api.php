<?php
// var_dump($app);
// $app->get('/api/user/all', 'App\Controllers\api\UserController:index');
$app->get('/activation/{token}', 'App\Controllers\api\UserController:accountActivation');
$app->group('/api', function () use ($app, $container) {
    // auth routes
    $this->post('/register', 'App\Controllers\api\UserController:registration');
    $this->post('/login', 'App\Controllers\api\UserController:login');
    $this->get('/logout', 'App\Controllers\api\UserController:logout');
    $this->post('/forgot-password', 'App\Controllers\api\UserController:forgotPassword');
    $this->post('/reset-password', 'App\Controllers\api\UserController:resetPassword');
    //category routes
    $this->post('/category/add', 'App\Controllers\api\CampaignCategoryController:addCategory');
    $this->post('/category/{id}/delete', 'App\Controllers\api\CampaignCategoryController:delete');
    $this->get('/category/all', 'App\Controllers\api\CampaignCategoryController:getAllCategory');
    // campaign routes
    $this->get('/campaign/all', 'App\Controllers\api\CampaignController:getAll');
    $this->get('/search', 'App\Controllers\api\CampaignController:searchCampaign');
    $this->get('/campaign/{id}/detail', 'App\Controllers\api\CampaignController:detailCampaign');
    $this->post('/campaign/{id}/approve', 'App\Controllers\api\CampaignController:approveCampaign');
    $this->post('/campaign/{id}/delete', 'App\Controllers\api\CampaignController:deleteCampaign');
    // donasi routes
    $this->get('/donasi/all', 'App\Controllers\api\DonasiController:index');
    $this->post('/donasi/new-donasi', 'App\Controllers\api\DonasiController:createDonasi');
    $this->get('/donasi/campaign/{id}', 'App\Controllers\api\DonasiController:getCampaignDonasi');
    $this->get('/donasi/campaign/{id}/approved', 'App\Controllers\api\DonasiController:getApprovedCampaignDonasi');
    $this->get('/donasi/user', 'App\Controllers\api\DonasiController:getUserDonasi');
    $this->get('/donasi/user/{id}/approved', 'App\Controllers\api\DonasiController:getApprovedUserDonasi');
    // $this->post('/donasi/{code}', 'App\Controllers\api\DonasiController:paymentConfirmation');
    // upload
    $this->post('/upload', 'App\Controllers\api\UploadController:upload')->setName('upload');
    // bank
    $this->post('/bank/add', 'App\Controllers\api\BankController:createBank');
    $this->post('/bank/{id}/delete', 'App\Controllers\api\BankController:delete');
    $this->put('/bank/{id}/edit', 'App\Controllers\api\BankController:edit');
    $this->get('/bank/all', 'App\Controllers\api\BankController:index');

    $app->group('/user', function () use ($app, $container) {
        //user routes
        $this->get('/all', 'App\Controllers\api\UserController:index');
        $this->post('/setting/data-diri', 'App\Controllers\api\UserController:editPersonalInformation');
        $this->post('/setting/foto-profil', 'App\Controllers\api\UserController:editProfilePhoto');
        $this->post('/setting/change-password', 'App\Controllers\api\UserController:changePassword');
        $this->post('/setting/verifikasi-akun', 'App\Controllers\api\UserController:verifikasiAkun');
        // user campaign routes
        $this->post('/new-campaign', 'App\Controllers\api\CampaignController:createCampaign');
        $this->post('/campaign/{id}/edit/deskripsi', 'App\Controllers\api\CampaignController:editDeskripsi');
        $this->post('/campaign/{id}/edit/deadline', 'App\Controllers\api\CampaignController:editDeadline');
        $this->post('/campaign/{id}/edit/cover', 'App\Controllers\api\CampaignController:editCover');
        $this->get('/campaign/all', 'App\Controllers\api\CampaignController:getUserCampaign');
        $this->get('/campaign/active', 'App\Controllers\api\CampaignController:getUserActiveCampaign');
        // donasi routes
    })->add(new \App\Middlewares\AuthMiddleware($container));
});
