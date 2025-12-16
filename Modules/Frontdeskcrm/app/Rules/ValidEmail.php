<?php

namespace Modules\Frontdeskcrm\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Closure;

class ValidEmail implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        $accessKey = config('services.abstract_api.key');

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

            // 1. Check for deliverability (already in place)
            if (isset($data['deliverability']) && $data['deliverability'] === 'UNDELIVERABLE') {
                $fail('The :attribute does not appear to be a deliverable email address.');
            }

            // 2. Check for risky emails (already in place)
            if (isset($data['deliverability']) && $data['deliverability'] === 'RISKY') {
                $fail('The :attribute provider is risky. Please use a different email.');
            }

            // ======================================================
            // 3. NEW: CHECK FOR DISPOSABLE (NON-GENUINE) EMAILS
            // ======================================================
            if (isset($data['is_disposable_email']['value']) && $data['is_disposable_email']['value'] === true) {
                $fail('The :attribute appears to be a temporary or disposable email. Please use a permanent email address.');
            }
            // ======================================================

        } catch (\Exception $e) {
            Log::error('Abstract Email API call exception: ' . $e->getMessage());
            return; // Fail open on exception
        }
    }
}
