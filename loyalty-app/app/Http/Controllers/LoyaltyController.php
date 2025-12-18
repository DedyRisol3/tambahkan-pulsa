<?php

namespace App\Http\Controllers;

use App\Models\PulsaRedemption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LoyaltyController extends Controller
{
    /**
     * Mendapatkan daftar paket pulsa yang tersedia
     */
    public function getPulsaPackages()
    {
        return response()->json([
            'packages' => PulsaRedemption::getPackages(),
            'providers' => PulsaRedemption::getProviders()
        ]);
    }

    /**
     * Proses penukaran poin menjadi pulsa
     */
    public function redeemPulsa(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'phone_number' => 'required|string|regex:/^08[0-9]{9,12}$/',
            'provider' => 'required|string|in:' . implode(',', array_keys(PulsaRedemption::getProviders())),
            'points_used' => 'required|integer'
        ]);

        $packages = PulsaRedemption::getPackages();
        
        // Validasi paket poin
        if (!isset($packages[$validated['points_used']])) {
            return response()->json([
                'success' => false,
                'message' => 'Paket poin tidak valid'
            ], 400);
        }

        $pulsaAmount = $packages[$validated['points_used']];

        // Cek saldo poin user dari loyalty-service
        try {
            $loyaltyServiceUrl = env('VITE_LOYALTY_SERVICE_URL', 'http://localhost:8001');
            $pointsResponse = Http::get("{$loyaltyServiceUrl}/api/points/user/{$validated['user_id']}");
            
            if (!$pointsResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengecek saldo poin'
                ], 500);
            }

            $currentPoints = $pointsResponse->json()['total_points'] ?? 0;

            if ($currentPoints < $validated['points_used']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Saldo poin tidak mencukupi. Saldo Anda: ' . $currentPoints . ' poin'
                ], 400);
            }

            // Kurangi poin di loyalty-service dengan redeem
            $redeemResponse = Http::post("{$loyaltyServiceUrl}/api/redeem", [
                'user_id' => $validated['user_id'],
                'points' => $validated['points_used'],
                'order_id' => 'PULSA-' . time()
            ]);

            if (!$redeemResponse->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses penukaran poin'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungi loyalty service: ' . $e->getMessage()
            ], 500);
        }

        // Simpan request penukaran pulsa
        $redemption = PulsaRedemption::create([
            'user_id' => $validated['user_id'],
            'phone_number' => $validated['phone_number'],
            'provider' => $validated['provider'],
            'points_used' => $validated['points_used'],
            'pulsa_amount' => $pulsaAmount,
            'status' => 'pending'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permintaan penukaran pulsa berhasil! Pulsa akan diproses dalam 1x24 jam.',
            'data' => [
                'id' => $redemption->id,
                'phone_number' => $redemption->phone_number,
                'provider' => PulsaRedemption::getProviders()[$redemption->provider],
                'points_used' => $redemption->points_used,
                'pulsa_amount' => $redemption->pulsa_amount,
                'status' => $redemption->status
            ]
        ]);
    }

    /**
     * Mendapatkan riwayat penukaran pulsa user
     */
    public function getPulsaHistory($userId)
    {
        $redemptions = PulsaRedemption::forUser($userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'phone_number' => $item->phone_number,
                    'provider' => PulsaRedemption::getProviders()[$item->provider] ?? $item->provider,
                    'points_used' => $item->points_used,
                    'pulsa_amount' => $item->pulsa_amount,
                    'status' => $item->status,
                    'admin_notes' => $item->admin_notes,
                    'created_at' => $item->created_at,
                    'processed_at' => $item->processed_at
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $redemptions
        ]);
    }

    /**
     * [ADMIN] Update status penukaran pulsa
     */
    public function updatePulsaStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,rejected',
            'admin_notes' => 'nullable|string'
        ]);

        $redemption = PulsaRedemption::findOrFail($id);
        
        $redemption->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? $redemption->admin_notes,
            'processed_at' => in_array($validated['status'], ['completed', 'rejected']) ? now() : null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diupdate',
            'data' => $redemption
        ]);
    }

    /**
     * [ADMIN] Mendapatkan semua permintaan penukaran pulsa
     */
    public function getAllPulsaRedemptions(Request $request)
    {
        $status = $request->query('status');
        
        $query = PulsaRedemption::orderBy('created_at', 'desc');
        
        if ($status) {
            $query->status($status);
        }

        $redemptions = $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'phone_number' => $item->phone_number,
                'provider' => PulsaRedemption::getProviders()[$item->provider] ?? $item->provider,
                'points_used' => $item->points_used,
                'pulsa_amount' => $item->pulsa_amount,
                'status' => $item->status,
                'admin_notes' => $item->admin_notes,
                'created_at' => $item->created_at,
                'processed_at' => $item->processed_at
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $redemptions
        ]);
    }
}
