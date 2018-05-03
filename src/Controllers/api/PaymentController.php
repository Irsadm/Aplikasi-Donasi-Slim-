<?php


namespace App\Controllers\api;
use App\Models\Payment;


class PaymentController extends BaseController
{
    public function createPayment($request, $response)
    {
        $paymentModel = new Payment($this->db);

        $post = $request->getParam();

        $this->validation->rule
    }
}
