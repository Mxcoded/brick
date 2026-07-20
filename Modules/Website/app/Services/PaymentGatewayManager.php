<?php

namespace Modules\Website\Services;

use Illuminate\Support\Collection;
use Modules\Website\Contracts\PaymentGatewayInterface;
use Modules\Website\Drivers\PaystackGateway;
use Modules\Website\Drivers\StripeGateway;
use Modules\Website\Models\PaymentGateway;
use RuntimeException;

/**
 * Resolves the configured payment gateway driver.
 *
 * Gateways are configured in the `payment_gateways` table (active/default flags
 * + encrypted credentials). If no DB record exists yet, the manager falls back
 * to the env-based config so the application keeps working before an admin
 * configures anything in the UI.
 */
class PaymentGatewayManager
{
    /**
     * Map of gateway code => driver class.
     * Add a new gateway by registering its driver here (and a DB/config record).
     */
    protected array $drivers = [
        'paystack' => PaystackGateway::class,
        'stripe' => StripeGateway::class,
    ];

    public function driver(?string $code = null): PaymentGatewayInterface
    {
        $code = $code ?? $this->defaultCode();
        $record = $this->resolveRecord($code);

        $driverClass = $this->drivers[$record->driver] ?? $this->drivers[$code] ?? null;

        if (! $driverClass || ! class_exists($driverClass)) {
            throw new RuntimeException("Payment gateway driver [{$code}] is not registered.");
        }

        return new $driverClass($record);
    }

    /**
     * All configured gateway records (for admin listing).
     */
    public function all(): Collection
    {
        return PaymentGateway::orderBy('name')->get();
    }

    protected function defaultCode(): string
    {
        $default = PaymentGateway::where('is_default', true)
            ->where('is_active', true)
            ->first();

        return $default ? $default->code : 'paystack';
    }

    protected function resolveRecord(string $code): PaymentGateway
    {
        $record = PaymentGateway::where('code', $code)->first();

        if ($record && $record->is_active) {
            return $record;
        }

        // Fallback to env/config so the system boots before DB configuration.
        return $this->fallbackRecord($code);
    }

    protected function fallbackRecord(string $code): PaymentGateway
    {
        $credentials = match ($code) {
            'paystack' => [
                'secret' => config('services.paystack.secret'),
                'public' => config('services.paystack.public'),
            ],
            'stripe' => [
                'secret' => config('services.stripe.secret'),
                'public' => config('services.stripe.public'),
                'webhook_secret' => config('services.stripe.webhook_secret'),
            ],
            default => [],
        };

        return new PaymentGateway([
            'code' => $code,
            'driver' => $code,
            'name' => match ($code) {
                'paystack' => 'Paystack',
                'stripe' => 'Stripe',
                default => ucfirst($code),
            },
            'is_active' => true,
            'is_default' => false,
            'credentials' => $credentials,
        ]);
    }
}
