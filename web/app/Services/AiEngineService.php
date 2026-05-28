<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SIHADIR — AI Engine HTTP Client
 *
 * Bridges PHP Laravel with the Python Flask AI Engine.
 * All AI-related operations (recognize, register, health) go through here.
 */
class AiEngineService
{
    private string $baseUrl;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.ai_engine.url', env('AI_ENGINE_URL', 'http://ai-engine:5000')), '/');
        $this->timeout = (int) config('services.ai_engine.timeout', env('AI_ENGINE_TIMEOUT', 30));
    }

    /**
     * Check AI engine health.
     *
     * @return array{status: string, model_loaded: bool, uptime_seconds: int}
     */
    public function health(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/api/health");

            if ($response->successful()) {
                return $response->json();
            }

            return ['status' => 'error', 'message' => 'AI Engine tidak merespons.'];
        } catch (\Exception $e) {
            Log::error('[AiEngine] Health check failed: ' . $e->getMessage());
            return ['status' => 'offline', 'message' => $e->getMessage()];
        }
    }

    /**
     * Recognize a face from uploaded image file.
     *
     * @param  string  $imagePath  Absolute path to image file
     * @param  string  $sessionId  Optional session identifier for logging
     * @return array{
     *   status: string,
     *   student_id: int|null,
     *   confidence: float,
     *   processing_time_ms: int,
     *   liveness_score: float,
     *   message: string
     * }
     */
    public function recognize(string $imagePath, string $sessionId = ''): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->attach('image', file_get_contents($imagePath), basename($imagePath))
                ->post("{$this->baseUrl}/api/recognize", [
                    'session_id' => $sessionId,
                ]);

            if ($response->successful()) {
                $result = $response->json();
                Log::info('[AiEngine] Recognize: ' . json_encode($result));
                return $result;
            }

            Log::warning('[AiEngine] Recognize HTTP error: ' . $response->status());
            return [
                'status'           => 'error',
                'student_id'       => null,
                'confidence'       => 0.0,
                'message'          => "AI Engine error ({$response->status()}): " . $response->body(),
                'processing_time_ms' => 0,
                'liveness_score'   => 0.0,
            ];
        } catch (\Exception $e) {
            Log::error('[AiEngine] Recognize exception: ' . $e->getMessage());
            return [
                'status'           => 'error',
                'student_id'       => null,
                'confidence'       => 0.0,
                'message'          => 'Gagal menghubungi AI Engine: ' . $e->getMessage(),
                'processing_time_ms' => 0,
                'liveness_score'   => 0.0,
            ];
        }
    }

    /**
     * Register a student's face embedding.
     *
     * @param  string  $imagePath   Absolute path to face image
     * @param  int     $studentId   Student database ID
     * @param  string  $studentName Student's full name
     * @return array{status: string, embedding_id: int|null, message: string}
     */
    public function registerFace(string $imagePath, int $studentId, string $studentName): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->attach('image', file_get_contents($imagePath), basename($imagePath))
                ->post("{$this->baseUrl}/api/register", [
                    'student_id'   => $studentId,
                    'student_name' => $studentName,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [
                'status'       => 'error',
                'embedding_id' => null,
                'message'      => "AI Engine error: " . $response->body(),
            ];
        } catch (\Exception $e) {
            Log::error('[AiEngine] Register exception: ' . $e->getMessage());
            return [
                'status'       => 'error',
                'embedding_id' => null,
                'message'      => 'Gagal menghubungi AI Engine: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a student's face embedding from AI engine.
     */
    public function deleteFace(int $studentId): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->delete("{$this->baseUrl}/api/register/{$studentId}");

            return $response->json() ?? ['status' => 'error', 'message' => 'No response body'];
        } catch (\Exception $e) {
            Log::error('[AiEngine] Delete face exception: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
