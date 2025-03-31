<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SewidanFakeGatewayController extends Controller
{
    /**
     * Step 1: Checkout - Generate fake payment link
     */
    public function checkout(Request $request)
    {
        $transactionId = uniqid('txn_');
        $amount = $request->input('amount', 100);
        $userName = $request->input('user_name');

        // Fake payment URL
        $paymentUrl = route('fake-gateway.pay',[
            'transaction_id' => $transactionId, 
            'amount' => $amount,
            'user_name' => $userName,
        ]);

        return response()->json([
            'message' => 'Payment link generated successfully',
            'transaction_id' => $transactionId,
            'payment_url' => $paymentUrl,
        ]);
    }

    /**
     * Step 2: Show Payment Form
     */
    public function showPaymentForm(Request $request)
    {
        $transactionId = $request->query('transaction_id');
        $amount = $request->query('amount');
        $userName = $request->query('user_name');

        return view('fake-gateway', compact('transactionId', 'amount','userName'));
    }

    /**
     * Step 3: Process Payment
     */
    public function processPayment(Request $request,$transactionId)
    {
        $successRedirect = route('api.payment.success', ['transaction_id' => $transactionId,'status' => 'success']);
        $failureRedirect = route('api.payment.failure',['transaction_id' => $transactionId,'status' => 'failure']);

        // Fake payment logic (any card ending in 1111 = success)
        if (substr($request->card_number, -4) == '1111') {
            return redirect()->to($successRedirect)->with('message', 'Payment Successful');
        }

        return redirect()->to($failureRedirect)->with('message', 'Payment Failed');
    }
}
