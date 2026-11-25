<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChargingSession;
use App\Models\Payment;
use App\Services\Api\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $service) {}

    public function cash(Request $request, ChargingSession $chargingSession): JsonResponse
    {
        abort_unless($chargingSession->reservation?->user_id === $request->user()->id, 403);

        return response()->json([
            'payment' => $this->service->createCashPayment($chargingSession),
            'message' => 'Cash payment is pending staff confirmation.',
        ], 201);
    }

    public function stripe(Request $request, ChargingSession $chargingSession): JsonResponse
    {
        abort_unless($chargingSession->reservation?->user_id === $request->user()->id, 403);
        $stripeSession = $this->service->createStripeCheckout($chargingSession);

        return response()->json([
            'checkout_url' => $stripeSession->url,
            'checkout_session_id' => $stripeSession->id,
        ]);
    }

    public function confirmCash(Payment $payment): JsonResponse
    {
        return response()->json([
            'payment' => $this->service->confirmCashPayment($payment),
        ]);
    }
}
