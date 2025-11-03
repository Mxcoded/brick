<?php

namespace Modules\Frontdeskcrm\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Closure;

class ValidEmail implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // If the value is empty, it passes (to respect 'nullable' rules)
        if (empty($value)) {
            return;
        }

        $accessKey = config('services.abstract_api.key');

        // If no API key is set, skip validation
        if (empty($accessKey)) {
            Log::error('Abstract Email API key is not set. Skipping email validation.');
            return;
        }

        try {
            $response = Http::get('https://emailvalidation.abstractapi.com/v1/', [
                'api_key' => $accessKey,
                'email' => $value,
            ]);

            if ($response->failed()) {
                Log::warning('Abstract Email API call failed. Failing open.');
                return;
            }

            $data = $response->json();

            // The API returns a "deliverability" status.
            // We'll block any that are not marked as DELIVERABLE.
            if (isset($data['deliverability']) && $data['deliverability'] === 'UNDELIVERABLE') {
                $fail('The :attribute does not appear to be a deliverable email address.');
            }

            // You can also choose to block 'RISKY' emails
            // if (isset($data['deliverability']) && $data['deliverability'] === 'RISKY') {
            //     $fail('The :attribute provider is risky. Please use a different email.');
            // }

        } catch (\Exception $e) {
            Log::error('Abstract Email API call exception: ' . $e->getMessage());
            return; // Fail open on exception
        }
    }
}
