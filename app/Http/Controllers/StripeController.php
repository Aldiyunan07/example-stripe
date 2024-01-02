<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class StripeController extends Controller
{
    public function stripe(Request $request){
        $stripe = new \Stripe\StripeClient(config('stripe.stripe_sk'));

        $response = $stripe->checkout->sessions->create([
                        'line_items' => [
                            [
                                'price_data' => [
                                    'currency' => 'usd',
                                    'product_data' => [
                                        'name' => $request->name
                                    ],
                                    'unit_amount' => $request->price*100,
                                ],
                                'quantity' => $request->quantity,
                            ],
                        ],
                        'mode' => 'payment',
                        'success_url' => route('success').'?session_id={CHECKOUT_SESSION_ID}',
                        'cancel_url' => route('cancel'),
        ]);
        if (isset($response->id) && $response->id != '') {
            session()->put('product', $request->name);
            session()->put('quantity', $request->quantity);
            session()->put('price', $request->price);
            return redirect($response->url);
        }else{
            return redirect()->route('cancel');
        }
    }

    public function success(Request $request){
        if(isset($request->session_id)){
            $stripe = new \Stripe\StripeClient(config('stripe.stripe_sk'));
            $response = $stripe->checkout->sessions->retrieve($request->session_id);
            $payment = new Payment();
            $payment->payment_id = $response->id;
            $payment->product = session()->get('product');
            $payment->quantity = session()->get('quantity');
            $payment->amount = session()->get('price');
            $payment->currency = $response->currency;
            $payment->customer_name = $response->customer_details->name;
            $payment->customer_email = $response->customer_details->email;
            $payment->customer_email = $response->customer_details->email;
            $payment->payment_status = $response->status;
            $payment->customer_phone = "081221997131";
            $payment->payment_method = "Stripe";
            $payment->save();
            return "Payment is successfull";
            session()->forget('produc');
            session()->forget('quantity');
            session()->forget('price');
        }else{
            return redirect()->route('cancel');
        }
    }

    public function cancel(Request $request){
        return "payment is cancel";
    }
}
