<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sig = $request->header('Stripe-Signature');
        try {
            $event = Webhook::constructEvent(
                $payload,
                $sig,
                env('STRIPE_WEBHOOK_SECRET')
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
        if ($event->type === 'checkout.session.completed') {

            // NOTE: Mark order as paid or process payment here
        }
        return response()->json(['status' => 'ok']);
    }

    public function checkout(Request $request)
    {
        $user = auth()->user();

        Stripe::setApiKey(env('STRIPE_SECRET'));
        $session = Session::create([
            'customer_email' => 'majddarouich2005@gmail.com',
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => 'Wallet Recharge'],
                    'unit_amount' => $request->amount * 100,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('stripe.success'),
            'cancel_url' => route('stripe.cancel'),
            'metadata' => ['user_id' => 1, 'amount' => $request->amount * 100]
        ]);
        return redirect($session->url);
    }
}
