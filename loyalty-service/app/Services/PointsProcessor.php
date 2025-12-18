<?php

namespace App\Services;

use App\Models\PointBalance;
use App\Models\PointHistory;
use Illuminate\Support\Facades\DB;

class PointsProcessor
{
    /**
     * Konfigurasi konversi poin
     * 
     * Rate: Setiap Rp 5.000 belanja = 1 poin
     * Contoh: Belanja Rp 500.000 = 100 poin = bisa tukar pulsa Rp 5.000
     * Ini setara dengan cashback 1%
     */
    const RUPIAH_PER_POINT = 5000;
    const MIN_POINTS = 1; // Minimum poin yang diberikan per transaksi

    /**
     * GRANT POINTS — pemberian poin ketika order berhasil
     */
    public function grant($request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'order_id' => 'required|integer',
            'order_total' => 'nullable|numeric|min:0'
        ]);

        $userId = $request->user_id;
        $orderId = $request->order_id;
        $orderTotal = $request->order_total ?? 0;

        // Anti duplikasi berdasarkan order_id
        if (PointHistory::where('order_id', $orderId)->exists()) {
            return response()->json([
                'status' => 'duplicate',
                'message' => 'Poin untuk order ini sudah diberikan sebelumnya.'
            ]);
        }

        // Hitung poin berdasarkan total belanja
        // Rumus: total_belanja / RUPIAH_PER_POINT (dibulatkan ke bawah)
        $pointsEarned = max(self::MIN_POINTS, floor($orderTotal / self::RUPIAH_PER_POINT));

        DB::transaction(function () use ($userId, $orderId, $orderTotal, $pointsEarned) {
            // Simpan history earn
            PointHistory::create([
                'user_id'    => $userId,
                'type'       => 'earn',
                'points'     => $pointsEarned,
                'order_id'   => $orderId,
                'description' => 'Poin dari belanja Rp ' . number_format($orderTotal, 0, ',', '.') . ' (Order #' . $orderId . ')'
            ]);

            // Update saldo poin user
            $balance = PointBalance::firstOrCreate(
                ['user_id' => $userId],
                ['total_points' => 0]
            );

            $balance->increment('total_points', $pointsEarned);
        });

        return response()->json([
            'status' => 'success',
            'points_earned' => $pointsEarned,
            'order_total' => $orderTotal,
            'conversion_rate' => 'Rp ' . number_format(self::RUPIAH_PER_POINT, 0, ',', '.') . ' = 1 poin',
            'message' => 'Poin berhasil ditambahkan: ' . $pointsEarned . ' poin dari belanja Rp ' . number_format($orderTotal, 0, ',', '.')
        ]);
    }

    /**
     * REDEEM POINTS — menukarkan poin
     */
    public function redeem($request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'redeem_type' => 'required|string',
            'points' => 'required|integer|min:1'
        ]);

        $userId = $request->user_id;
        $redeemType = $request->redeem_type;
        $pointsToRedeem = $request->points;

        $balance = PointBalance::where('user_id', $userId)->first();

        // Jika user belum punya saldo
        if (!$balance) {
            return response()->json([
                'status' => 'error',
                'message' => 'User belum memiliki poin.'
            ]);
        }

        // Jika poin tidak cukup
        if ($balance->total_points < $pointsToRedeem) {
            return response()->json([
                'status' => 'error',
                'message' => 'Poin tidak mencukupi untuk ditukarkan.'
            ]);
        }

        DB::transaction(function () use ($userId, $redeemType, $pointsToRedeem, $balance) {
            // Kurangi saldo poin
            $balance->decrement('total_points', $pointsToRedeem);

            // Simpan history redeem
            PointHistory::create([
                'user_id'     => $userId,
                'type'        => 'redeem',
                'points'      => $pointsToRedeem,
                'redeem_type' => $redeemType,
                'description' => 'Redeem via ' . $redeemType
            ]);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Redeem berhasil.'
        ]);
    }

    /**
     * GET USER POINT BALANCE
     */
    public function getUserPoints($userId)
    {
        $balance = PointBalance::where('user_id', $userId)->first();

        return response()->json([
            'user_id' => $userId,
            'total_points' => $balance ? $balance->total_points : 0
        ]);
    }
}
