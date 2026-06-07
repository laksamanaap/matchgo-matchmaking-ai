<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MidtransSnapService
{
    public function createToken(string $orderId, int $grossAmount, array $customer, array $itemDetails = []): array
    {
        $serverKey = config('services.midtrans.server_key');

        if (! $serverKey) {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum diatur di file .env.');
        }

        $baseUrl = config('services.midtrans.is_production')
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => $customer,
        ];

        if ($itemDetails !== []) {
            $payload['item_details'] = $itemDetails;
        }

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->post($baseUrl . '/snap/v1/transactions', $payload);

        if ($response->failed()) {
            throw new RuntimeException($response->json('error_messages.0') ?? 'Gagal membuat transaksi Midtrans.');
        }

        return $response->json();
    }

    public function refundDemo(string $orderId, int $amount, string $reason): array
    {
        $refundKey = 'RF-' . now()->format('YmdHisv') . '-' . substr(md5($orderId), 0, 6);

        if (! config('services.midtrans.refund_demo_call_api')) {
            return [
                'status_code' => '200',
                'status_message' => 'Demo refund simulated.',
                'refund_key' => $refundKey,
                'amount' => $amount,
                'reason' => $reason,
                'source' => 'local_demo',
            ];
        }

        $serverKey = config('services.midtrans.server_key');

        if (! $serverKey) {
            throw new RuntimeException('MIDTRANS_SERVER_KEY belum diatur di file .env.');
        }

        $baseUrl = config('services.midtrans.is_production')
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';

        $response = Http::withBasicAuth($serverKey, '')
            ->acceptJson()
            ->post($baseUrl . '/v2/' . urlencode($orderId) . '/refund', [
                'refund_key' => $refundKey,
                'amount' => $amount,
                'reason' => $reason,
            ]);

        if ($response->failed()) {
            throw new RuntimeException($response->json('status_message') ?? $response->json('error_messages.0') ?? 'Gagal membuat refund Midtrans.');
        }

        return array_merge($response->json(), [
            'refund_key' => $response->json('refund_key') ?? $refundKey,
            'source' => 'midtrans_sandbox',
        ]);
    }
}
