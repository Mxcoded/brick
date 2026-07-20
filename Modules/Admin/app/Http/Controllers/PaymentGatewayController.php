<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Website\Models\PaymentGateway;

class PaymentGatewayController extends Controller
{
    /**
     * Per-gateway credential fields shown in the admin form.
     */
    protected array $credentialFields = [
        'paystack' => [
            'secret' => 'Secret Key',
            'public' => 'Public Key',
        ],
        'stripe' => [
            'secret' => 'Secret Key',
            'public' => 'Publishable Key',
            'webhook_secret' => 'Webhook Secret',
        ],
    ];

    public function index()
    {
        $gateways = PaymentGateway::orderBy('name')->get();

        return view('admin::payment-gateways.index', compact('gateways'));
    }

    public function create()
    {
        $codes = array_keys($this->credentialFields);
        $credentialFields = $this->credentialFields;

        return view('admin::payment-gateways.create', compact('codes', 'credentialFields'));
    }

    public function store(Request $request)
    {
        $available = array_keys($this->credentialFields);

        $validated = $request->validate([
            'code' => ['required', 'string', 'in:'.implode(',', $available), 'unique:payment_gateways,code'],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
            'credentials' => ['array'],
            'settings' => ['nullable', 'array'],
        ]);

        $code = $validated['code'];

        $gateway = PaymentGateway::create([
            'code' => $code,
            'name' => $validated['name'],
            'driver' => $code,
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'is_default' => (bool) ($validated['is_default'] ?? false),
            'credentials' => $this->filterCredentials($code, $validated['credentials'] ?? []),
            'settings' => $validated['settings'] ?? null,
        ]);

        $this->syncDefault($gateway);

        return redirect()->route('admin.payment-gateways.index')
            ->with('success', 'Payment gateway created.');
    }

    public function edit(PaymentGateway $paymentGateway)
    {
        $codes = array_keys($this->credentialFields);
        $credentialFields = $this->credentialFields;

        return view('admin::payment-gateways.edit', [
            'gateway' => $paymentGateway,
            'codes' => $codes,
            'credentialFields' => $credentialFields,
        ]);
    }

    public function update(Request $request, PaymentGateway $paymentGateway)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'is_default' => ['boolean'],
            'credentials' => ['array'],
            'settings' => ['nullable', 'array'],
        ]);

        $credentials = $this->filterCredentials($paymentGateway->code, $validated['credentials'] ?? []);

        // Preserve existing credential values when a field is left blank on edit.
        foreach ($credentials as $key => $value) {
            if ($value === '' || $value === null) {
                $credentials[$key] = ($paymentGateway->credentials[$key] ?? '');
            }
        }

        $paymentGateway->update([
            'name' => $validated['name'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'is_default' => (bool) ($validated['is_default'] ?? false),
            'credentials' => $credentials,
            'settings' => $validated['settings'] ?? null,
        ]);

        $this->syncDefault($paymentGateway);

        return redirect()->route('admin.payment-gateways.index')
            ->with('success', 'Payment gateway updated.');
    }

    public function destroy(PaymentGateway $paymentGateway)
    {
        $paymentGateway->delete();

        return redirect()->route('admin.payment-gateways.index')
            ->with('success', 'Payment gateway deleted.');
    }

    public function setDefault(PaymentGateway $paymentGateway)
    {
        PaymentGateway::where('id', '!=', $paymentGateway->id)->update(['is_default' => false]);
        $paymentGateway->update(['is_default' => true, 'is_active' => true]);

        return redirect()->route('admin.payment-gateways.index')
            ->with('success', $paymentGateway->name.' is now the default gateway.');
    }

    public function toggleActive(PaymentGateway $paymentGateway)
    {
        $paymentGateway->update(['is_active' => ! $paymentGateway->is_active]);

        return redirect()->route('admin.payment-gateways.index')
            ->with('success', 'Gateway status updated.');
    }

    /**
     * Keep only the credential fields relevant to the selected driver.
     */
    protected function filterCredentials(string $code, array $credentials): array
    {
        $allowed = $this->credentialFields[$code] ?? [];

        return collect($credentials)
            ->only(array_keys($allowed))
            ->map(fn ($value) => $value === null ? '' : $value)
            ->toArray();
    }

    /**
     * Ensure at most one default, and a default is always active.
     */
    protected function syncDefault(PaymentGateway $gateway): void
    {
        if ($gateway->is_default) {
            PaymentGateway::where('id', '!=', $gateway->id)->update(['is_default' => false]);
            $gateway->update(['is_active' => true]);
        } elseif (! PaymentGateway::where('is_default', true)->exists()) {
            $gateway->update(['is_default' => true]);
        }
    }
}
