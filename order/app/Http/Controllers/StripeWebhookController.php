<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGatewayInterface;
use App\Services\OrderPaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentGatewayInterface $paymentGateway,
        private readonly OrderPaymentService $payments,
    ) {}

    public function handle(Request $request): Response
    {
        $notification = $this->paymentGateway->parseWebhook(
            $request->getContent(),
            ['Stripe-Signature' => (string) $request->header('Stripe-Signature', '')],
        );

        if ($notification !== null) {
            $this->payments->handleWebhookNotification($notification);
        }

        return response()->noContent();
    }
}
