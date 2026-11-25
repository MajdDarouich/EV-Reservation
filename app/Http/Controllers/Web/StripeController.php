<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Api\PaymentService;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeController extends Controller
{
    public function __construct(private PaymentService $service) {}

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sig = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent(
                $payload,
                $sig,
                config('services.stripe.webhook_secret', env('STRIPE_WEBHOOK_SECRET'))
            );

            if ($event->type === 'checkout.session.completed') {
                $this->service->completeStripePayment($event->data->object);
            } elseif (in_array($event->type, [
                'checkout.session.async_payment_failed',
                'checkout.session.expired',
            ], true)) {
                $this->service->failStripePayment($event->data->object);
            }

        } catch (\UnexpectedValueException|SignatureVerificationException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        return response()->json(['status' => 'ok']);
    }
}
