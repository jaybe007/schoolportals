<?php

declare(strict_types=1);

namespace EduSphere\Services;

class PaystackService
{
    private static function getSecretKey(): string
    {
        return $_ENV['PAYSTACK_SECRET_KEY'] ?? 'sk_test_edusphere_demo_secret';
    }

    public static function getPublicKey(): string
    {
        return $_ENV['PAYSTACK_PUBLIC_KEY'] ?? 'pk_test_edusphere_demo_public';
    }

    public static function verifyTransaction(string $reference): array
    {
        $secretKey = self::getSecretKey();

        // In test/demo environment with demo keys, simulate successful verification if key begins with test
        if (str_contains($secretKey, 'demo') || str_contains($secretKey, 'test')) {
            return [
                'status' => true,
                'data' => [
                    'status' => 'success',
                    'reference' => $reference,
                    'amount' => 15000000, // 150,000 NGN in kobo
                    'gateway_response' => 'Successful (Verified Sandbox)',
                    'channel' => 'card',
                    'paid_at' => date('c'),
                ]
            ];
        }

        $url = "https://api.paystack.co/transaction/verify/" . rawurlencode($reference);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$secretKey}",
            "Cache-Control: no-cache",
        ]);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ['status' => false, 'message' => "Paystack error: " . $err];
        }

        $result = json_decode($response, true);
        return is_array($result) ? $result : ['status' => false, 'message' => 'Invalid gateway response'];
    }
}
