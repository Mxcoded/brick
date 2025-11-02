<?php

namespace Modules\Frontdeskcrm\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Closure;

class ValidPhoneNumber implements ValidationRule
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

        $accessKey = config('services.numverify.key');

        // If no API key is set, skip validation to avoid breaking the form
        if (empty($accessKey)) {
            // You might want to log this error
            // Log::error('Numverify API key is not set.');
            return;
        }

        try {
            $response = Http::get('http://apilayer.net/api/validate', [
                'access_key' => $accessKey,
                'number' => $value,
            ]);

            if ($response->failed()) {
                // API call failed, fail open (pass) so we don't block the user
                // You could also $fail('Could not validate phone number.')
                return;
            }

            $data = $response->json();

            // Check if the API response says the number is valid
            if (!isset($data['valid']) || $data['valid'] === false) {
                $fail('The provided :attribute does not appear to be a valid phone number.');
            }
        } catch (\Exception $e) {
            // An exception occurred, fail open (pass) to not block the user
            // Log::error('Numverify API call failed: ' . $e->getMessage());
            return;
        }
    }
}
