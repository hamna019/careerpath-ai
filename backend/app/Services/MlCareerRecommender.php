<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MlCareerRecommender
{
    public function rank(array $answers, ?array $profile = null): ?array
    {
        $url = rtrim((string) config('services.ml.url'), '/');
        if ($url === '') {
            return null;
        }

        try {
            $response = Http::timeout((float) config('services.ml.timeout', 3))
                ->connectTimeout(1)
                ->acceptJson()
                ->post("{$url}/predict", [
                    'answers' => (object) array_map('array_values', $answers),
                    'profile' => $profile ?: null,
                ]);

            if (!$response->successful()) {
                Log::warning('ML service returned an error', ['status' => $response->status()]);
                return null;
            }

            $data = $response->json();
            if (empty($data['recommendations']) || !is_array($data['recommendations'])) {
                return null;
            }

            return $data;
        } catch (\Throwable $e) {
            Log::warning('ML service unavailable, using built-in matcher', ['error' => $e->getMessage()]);
            return null;
        }
    }
}
