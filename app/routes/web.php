<?php
// var_dump($container);
$app->get('/', 'App\Controllers\web\HomeController:index')->setName('home');
$app->get('/explore', 'App\Controllers\web\CampaignController:index')->setName('explore');
$app->get('/search', 'App\Controllers\web\CampaignController:searchCampaign')->setName('search');
$app->get('/login', 'App\Controllers\web\UserController:getLogin')->setName('login');
$app->get('/register', 'App\Controllers\web\UserController:getRegister')->setName('register');
$app->get('/forgot-password', 'App\Controllers\web\UserController:getForgotPassword')->setName('forgot-password');
$app->post('/forgot-password', 'App\Controllers\web\UserController:forgotPassword')->setName('post.forgot-password');
$app->get('/password/reset/{token}', 'App\Controllers\web\UserController:getResetPassword')->setName('reset-password');
$app->post('/password/reset', 'App\Controllers\web\UserController:resetPassword')->setName('post.reset-password');
// auth routes
$app->post('/login', 'App\Controllers\web\UserController:login')->setName('post-login');
$app->post('/register', 'App\Controllers\web\UserController:register')->setName('post-register');
$app->get('/logout', 'App\Controllers\web\UserController:logout')->setName('user-logout');

// user routes
$app->get('/user/all', 'App\Controllers\web\UserController:userAll');
$app->get('/user/dashboard', 'App\Controllers\web\UserController:getDashboard')->setName('user-dashboard');
$app->get('/user/pengaturan', 'App\Controllers\web\UserController:getPengaturan')->setName('pengaturan');
$app->post('/user/pengaturan/profil', 'App\Controllers\web\UserController:editPersonalInformation')->setName('edit-profil');
$app->post('/user/pengaturan/password', 'App\Controllers\web\UserController:changePassword')->setName('edit-password');
$app->post('/user/pengaturan/foto', 'App\Controllers\web\UserController:changeProfilePhoto')->setName('edit-foto');
$app->post('/user/pengaturan/verifikasi', 'App\Controllers\web\UserController:verifikasiAkun')->setName('verifikasi-akun');




//campaign routes
$app->get('/create-campaign', 'App\Controllers\web\CampaignController:getCreateCampaign')->setName('new-campaign');
$app->post('/create-campaign', 'App\Controllers\web\CampaignController:createCampaign')->setName('post-new-campaign');
$app->get('/campaign/{id}/detail', 'App\Controllers\web\CampaignController:detailCampaign')->setName('detail-campaign');
$app->post('/campaign/{id}/approve', 'App\Controllers\web\CampaignController:approveCampaign');
$app->post('/campaign/{id}/delete', 'App\Controllers\web\CampaignController:deleteCampaign');
// user campaign routes
$app->get('/user/campaign/{id}/donasi', 'App\Controllers\web\DonasiController:getDonasiCampaign')->setName('donasi-campaign');
$app->get('/user/campaign/all', 'App\Controllers\web\CampaignController:getUserCampaign')->setName('user-campaign');
$app->get('/user/campaign/dashboard', 'App\Controllers\web\CampaignController:getCampaignDashboard');
$app->get('/user/campaign/{id}/edit', 'App\Controllers\web\CampaignController:getEditCampaign')->setName('edit-campaign');
$app->post('/user/campaign/{id}/edit/deskripisi', 'App\Controllers\web\CampaignController:editDeskripsi')->setName('edit-deskripsi');
$app->post('/user/campaign/{id}/edit/deadline', 'App\Controllers\web\CampaignController:editDeadline')->setName('edit-deadline');
$app->post('/user/campaign/{id}/edit/cover', 'App\Controllers\web\CampaignController:editCover')->setName('edit-cover');

// donasi routes
$app->get('/donasi/all', 'App\Controllers\web\DonasiController:index');
$app->get('/user/donasi', 'App\Controllers\web\DonasiController:getUserDonasi')->setName('user-donasi');
$app->get('/campaign/{id}/donasi', 'App\Controllers\web\DonasiController:getDonasiPage')->setName('get-donasi');
$app->post('/donasi', 'App\Controllers\web\DonasiController:donasi')->setName('donasi');
$app->get('/summary', 'App\Controllers\web\DonasiController:getSummary')->setName('get-summary-donasi');
$app->get('/konfirmasi', 'App\Controllers\web\DonasiController:getKonfirmasi')->setName('get-konfirmasi-donasi');



// admin login
$app->get('/secret/admin/login', 'App\Controllers\web\AdminController:getLogin')->setName('admin.login.page');
$app->post('/secret/admin/login', 'App\Controllers\web\AdminController:login')->setName('admin.login');

//admin user
$app->group('/secret/admin', function () use ($app, $container) {
        $this->get('/dashboard', 'App\Controllers\web\AdminController:index')->setName('dashboard');
        $this->get('/users/request', 'App\Controllers\web\AdminController:verificationRequest')->setName('user.request');
        $this->get('/users/verified', 'App\Controllers\web\AdminController:verifiedUser')->setName('user.verified');
        $this->get('/users/{id}/delete', 'App\Controllers\web\AdminController:deleteUser')->setName('user.delete');
        $this->post('/users/{id}/verify', 'App\Controllers\web\AdminController:verifyUser')->setName('user.verify');

        //admin donation
        $this->get('/donations/request', 'App\Controllers\web\AdminController:donationConfirmationRequest')->setName('donation.request');
        $this->post('/donations/{id}/verify', 'App\Controllers\web\AdminController:verifyDonation')->setName('donation.verify');
        $this->get('/donations/paid', 'App\Controllers\web\AdminController:paidDonation')->setName('donation.paid');
        $this->get('/donations/cancelled', 'App\Controllers\web\AdminController:cancelledDonation')->setName('donation.cancelled');

        //admin campaign
        $this->get('/campaigns/request', 'App\Controllers\web\CampaignController:requestApprove')->setName('campaign.request');
        $this->get('/campaigns/active', 'App\Controllers\web\CampaignController:activeCampaign')->setName('campaign.active');
        $this->post('/campaigns/{id}/verify', 'App\Controllers\web\CampaignController:approveCampaign')->setName('campaign.verify');
        $this->get('/campaigns/{id}/delete', 'App\Controllers\web\CampaignController:deleteCampaign')->setName('campaign.delete');
        $this->get('/campaigns/{id}/view', 'App\Controllers\web\CampaignController:viewCampaign')->setName('campaign.view');
        $this->get('/campaigns/expired', 'App\Controllers\web\CampaignController:expiredCampaign')->setName('campaign.expired');

        // admin category
        $this->get('/categories', 'App\Controllers\web\CampaignCategoryController:index')->setName('category.index');
        $this->post('/categories/add', 'App\Controllers\web\CampaignCategoryController:addCategory')->setName('category.add');
        $this->post('/categories/{id}/edit', 'App\Controllers\web\CampaignCategoryController:editCategory')->setName('category.edit');
        $this->get('/categories/{id}/delete', 'App\Controllers\web\CampaignCategoryController:deleteCategory')->setName('category.delete');
})->add(new \App\Middlewares\AdminMiddleware($container));


 ?>
