<?php

namespace Modules\Website\Contracts;

interface PaymentGatewayInterface
{
    /**
     * Unique, stable gateway code (e.g. "paystack", "stripe").
     */
    public function code(): string;

    /**
     * Header the gateway sends its webhook signature in.
     */
    public function webhookSignatureHeader(): string;

    /**
     * Initialise a charge and return the decoded gateway response.
     */
    public function initialize(string $email, float $amount, string $reference, string $callbackUrl, array $metadata = []): array;

    /**
     * Verify a transaction by its reference; return the decoded gateway response.
     */
    public function verify(string $reference): array;

    /**
     * Refund a previously successful transaction; return the decoded gateway response.
     */
    public function refund(string $transactionReference, float $amount, string $reason = '', ?array $metadata = null): array;

    /**
     * Verify the HMAC signature of a raw webhook payload.
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool;

    /**
     * Process a parsed webhook event and update local records accordingly.
     */
    public function handleWebhook(array $event): void;
}
