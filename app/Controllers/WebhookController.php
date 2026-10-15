<?php

declare(strict_types=1);

namespace EduSphere\Controllers;

use EduSphere\Core\Request;
use EduSphere\Core\Response;
use EduSphere\Services\PaystackService;
use Throwable;

class WebhookController
{
    /**
     * Paystack Webhook Handler
     * POST /webhook/paystack
     */
    public function handlePaystack(Request $request): void
    {
        $rawBody = file_get_contents('php://input');
        if (empty($rawBody)) {
            http_response_code(400);
            Response::json(['status' => false, 'message' => 'Empty webhook payload']);
            return;
        }

        $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? null;
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;

        try {
            $result = PaystackService::processWebhookEvent($rawBody, $signature, $ip);
            Response::json([
                'status' => true,
                'message' => 'Webhook received and processed idempotently',
                'result' => $result,
            ]);
        } catch (Throwable $e) {
            http_response_code(400);
            Response::json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
