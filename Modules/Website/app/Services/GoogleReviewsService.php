<?php

namespace Modules\Website\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Modules\Website\Models\Settings;

class GoogleReviewsService
{
    public function fetch(): array
    {
        $settings = Settings::getAllCached();
        $placeId = $settings['google_place_id'] ?? null;
        $apiKey = $settings['google_api_key'] ?? null;

        if (! $placeId || ! $apiKey) {
            return ['rating' => null, 'count' => 0, 'reviews' => []];
        }

        return Cache::remember('google_reviews', now()->addHours(24), function () use ($placeId, $apiKey) {
            try {
                $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
                    'place_id' => $placeId,
                    'fields' => 'rating,user_ratings_total,reviews',
                    'key' => $apiKey,
                ]);

                if (! $response->successful()) {
                    return ['rating' => null, 'count' => 0, 'reviews' => []];
                }

                $result = $response->json('result') ?? [];

                $reviews = array_map(function ($r) {
                    return [
                        'author' => $r['author_name'] ?? 'Anonymous',
                        'photo' => $r['profile_photo_url'] ?? null,
                        'rating' => $r['rating'] ?? 5,
                        'text' => $r['text'] ?? '',
                        'time' => $r['relative_time_description'] ?? null,
                        'timestamp' => $r['time'] ?? 0,
                    ];
                }, $result['reviews'] ?? []);

                usort($reviews, fn ($a, $b) => $b['timestamp'] - $a['timestamp']);

                return [
                    'rating' => $result['rating'] ?? null,
                    'count' => $result['user_ratings_total'] ?? 0,
                    'reviews' => $reviews,
                ];
            } catch (\Exception $e) {
                return ['rating' => null, 'count' => 0, 'reviews' => []];
            }
        });
    }
}
