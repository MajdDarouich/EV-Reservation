<?php

namespace App\Services\Api;

use App\Enums\ChargerStatus;
use App\Models\ChargingSession;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Stripe;

class PaymentService
{
    public function createCashPayment(ChargingSession $chargingSession): Payment
    {
        return DB::transaction(function () use ($chargingSession): Payment {
            $session = $this->lockSessionForPayment($chargingSession);
            $payment = $session->payment()->first();

            if ($payment?->status === 'paid') {
                throw ValidationException::withMessages([
                    'payment' => 'This charging session has already been paid.',
                ]);
            }

            return $session->payment()->updateOrCreate([], [
                'payment_method' => 'cash',
                'status' => 'pending',
                'paid_at' => null,
            ]);
        });
    }

    public function createStripeCheckout(ChargingSession $chargingSession): StripeCheckoutSession
    {
        $session = $this->lockSessionForPayment($chargingSession);
        $payment = $session->payment()->first();

        if ($payment?->status === 'paid') {
            throw ValidationException::withMessages([
                'payment' => 'This charging session has already been paid.',
            ]);
        }

        $payment ??= $session->payment()->create([
            'payment_method' => 'stripe_wallet',
            'status' => 'pending',
        ]);
        $payment->forceFill([
            'payment_method' => 'stripe_wallet',
            'status' => 'pending',
            'paid_at' => null,
        ])->save();

        Stripe::setApiKey(config('services.stripe.secret', env('STRIPE_SECRET')));
        $amount = (int) round((float) $session->final_cost * 100);

        $stripeSession = StripeCheckoutSession::create([
            'customer_email' => $session->reservation->user->email,
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => 'EV charging session'],
                    'unit_amount' => $amount,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => config('app.url').'/payment/success',
            'cancel_url' => config('app.url').'/payment/cancelled',
            'metadata' => [
                'charging_session_id' => (string) $session->id,
                'amount' => (string) $amount,
            ],
        ]);

        $payment->update(['stripe_checkout_session_id' => $stripeSession->id]);

        return $stripeSession;
    }

    public function completeStripePayment(StripeCheckoutSession $stripeSession): Payment
    {
        if ($stripeSession->payment_status !== 'paid') {
            throw ValidationException::withMessages([
                'payment' => 'The Stripe checkout session is not paid.',
            ]);
        }

        $chargingSessionId = $stripeSession->metadata->charging_session_id ?? null;
        if (! $chargingSessionId) {
            throw ValidationException::withMessages([
                'payment' => 'Stripe checkout session is missing a charging session.',
            ]);
        }

        return DB::transaction(function () use ($stripeSession, $chargingSessionId): Payment {
            $session = ChargingSession::query()
                ->with('reservation.connector.charger')
                ->lockForUpdate()
                ->findOrFail((int) $chargingSessionId);
            $expectedAmount = (int) round((float) $session->final_cost * 100);

            if ((int) $stripeSession->amount_total !== $expectedAmount) {
                throw ValidationException::withMessages([
                    'payment' => 'The Stripe payment amount does not match the charging session cost.',
                ]);
            }

            $payment = $session->payment()->firstOrCreate([], [
                'payment_method' => 'stripe_wallet',
                'status' => 'pending',
            ]);

            if ($payment->status !== 'paid') {
                $payment->forceFill([
                    'payment_method' => 'stripe_wallet',
                    'status' => 'paid',
                    'paid_at' => now(),
                    'stripe_checkout_session_id' => $stripeSession->id,
                ])->save();
                $this->releaseCharger($session);
            }

            return $payment->refresh();
        });
    }

    public function failStripePayment(StripeCheckoutSession $stripeSession): void
    {
        $payment = Payment::query()
            ->where('stripe_checkout_session_id', $stripeSession->id)
            ->where('status', '!=', 'paid')
            ->first();

        $payment?->update(['status' => 'failed']);
    }

    public function confirmCashPayment(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment): Payment {
            $payment = Payment::query()
                ->with('chargingSession.reservation.connector.charger')
                ->lockForUpdate()
                ->findOrFail($payment->id);

            if ($payment->payment_method !== 'cash' || $payment->status === 'paid') {
                throw ValidationException::withMessages([
                    'payment' => 'This payment cannot be confirmed as cash.',
                ]);
            }

            $payment->forceFill([
                'status' => 'paid',
                'paid_at' => now(),
            ])->save();
            $this->releaseCharger($payment->chargingSession);

            return $payment->refresh();
        });
    }

    private function lockSessionForPayment(ChargingSession $chargingSession): ChargingSession
    {
        $session = ChargingSession::query()
            ->with('reservation.user')
            ->lockForUpdate()
            ->findOrFail($chargingSession->id);

        if ($session->actual_end_time === null || $session->final_cost === null) {
            throw ValidationException::withMessages([
                'charging_session' => 'Charging must be finished before payment.',
            ]);
        }

        return $session;
    }

    private function releaseCharger(ChargingSession $session): void
    {
        $session->loadMissing('reservation.connector.charger');
        $charger = $session->reservation?->connector?->charger;

        if ($charger) {
            $charger->update(['status' => ChargerStatus::AVAILABLE]);
        }
    }
}
