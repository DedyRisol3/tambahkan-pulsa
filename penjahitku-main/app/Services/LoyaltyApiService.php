<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LoyaltyApiService
{
    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('LOYALTY_SERVICE_URL'), '/');
        
        // Validasi baseUrl
        if (empty($this->baseUrl)) {
            Log::error('LOYALTY_SERVICE_URL is not set in .env file');
        }
    }

    /**
     * Grant points to user after successful order
     * 
     * @param int $userId
     * @param int $orderId
     * @param float $orderTotal Total harga order untuk kalkulasi poin
     */
    public function grantPoints($userId, $orderId, $orderTotal = 0)
    {
        try {
            // Build URL - pastikan format benar
            $url = $this->baseUrl . '/api/points/grant';
            
            // Log untuk debugging
            Log::info('Calling Loyalty Service', [
                'url' => $url,
                'user_id' => $userId,
                'order_id' => $orderId,
                'order_total' => $orderTotal,
                'base_url' => $this->baseUrl
            ]);

            $response = Http::timeout(10)->post($url, [
                'user_id' => $userId,
                'order_id' => $orderId,
                'order_total' => $orderTotal
            ]);

            // Log response untuk debugging
            Log::info('Loyalty Service Response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $url
            ]);

            // Cek jika request berhasil
            if ($response->successful()) {
                return $response->json();
            }

            // Jika request gagal, return error response
            $errorBody = $response->json() ?? $response->body();
            Log::error('Loyalty Service Error Response', [
                'status' => $response->status(),
                'body' => $errorBody,
                'url' => $url
            ]);

            return [
                'status' => 'error',
                'message' => 'Loyalty service returned error: ' . $response->status(),
                'error' => $errorBody
            ];
        } catch (\Exception $e) {
            // Tangani exception (connection error, timeout, dll)
            Log::error('Loyalty API Exception: ' . $e->getMessage(), [
                'user_id' => $userId,
                'order_id' => $orderId,
                'base_url' => $this->baseUrl,
                'url' => $url ?? 'N/A',
                'exception' => $e
            ]);
            return [
                'status' => 'error',
                'message' => 'Failed to connect to loyalty service: ' . $e->getMessage()
            ];
        }
    }
}
