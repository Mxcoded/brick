<?php

namespace Modules\Frontdeskcrm\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // Added for logging errors
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
        if (empty($value)) {
            return; // Passes if 'nullable' is set
        }

        $accessKey = config('services.numverify.key');

        if (empty($accessKey)) {
            Log::error('Numverify API key is not set. Skipping phone validation.');
            return; // Fail open if API key is missing
        }

        // --- NEW LOGIC TO FORMAT THE NUMBER ---
        $numberToValidate = $value;

        // 1. Remove all spaces, dashes, or parentheses
        $numberToValidate = preg_replace('/[\s\-\(\)]+/', '', $numberToValidate);

        // 2. Check if it's a local Nigerian number (starts with 0, e.g., 080...)
        if (preg_match('/^0[7-9][0-1][0-9]{8}$/', $numberToValidate)) {
            // Remove the leading '0' and add '+234'
            $numberToValidate = '+234' . substr($numberToValidate, 1);
        }
        // 3. (Optional) Handle if they type 80... without the 0
        else if (preg_match('/^[7-9][0-1][0-9]{8}$/', $numberToValidate)) {
            $numberToValidate = '+234' . $numberToValidate;
        }

        // If it doesn't start with '+', the API will likely reject it.
        if (strpos($numberToValidate, '+') !== 0) {
            $fail('The :attribute must be an international format (e.g., +234 809...)');
            return;
        }
        // --- END OF NEW LOGIC ---

        try {
            $response = Http::get('http://apilayer.net/api/validate', [
                'access_key' => $accessKey,
                'number' => $numberToValidate, // Use the formatted number
            ]);

            if ($response->failed()) {
                Log::warning('Numverify API call failed. Failing open.');
                return;
            }

            $data = $response->json();

            // Check if the API response says the number is valid
            if (isset($data['valid']) && $data['valid'] === false) {
                $fail('The provided :attribute does not appear to be a valid phone number.');
            }

            // Handle API error response (e.g., invalid key)
            if (isset($data['success']) && $data['success'] === false) {
                Log::error('Numverify API error: ' . ($data['error']['info'] ?? 'Unknown error'));
                // Fail open to not block the user
            }
        } catch (\Exception $e) {
            Log::error('Numverify API call exception: ' . $e->getMessage());
            return; // Fail open on exception
        }
    }
}
