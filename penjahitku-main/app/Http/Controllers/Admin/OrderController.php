<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\LoyaltyApiService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $orders = Order::latest()->paginate(15);
        return view('admin.pesanan.index', compact('orders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        abort(404);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $orderId): View
    {
        $order = Order::with('items.product')->findOrFail($orderId);
        return view('admin.pesanan.show', compact('order'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $orderId): View
    {
        // Redirect saja ke halaman show
        return redirect()->route('admin.pesanan.show', ['orderId' => $orderId]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $orderId, LoyaltyApiService $loyalty): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|string|in:pending,paid,shipped,completed,cancelled',
            ]);
            
            $order = Order::findOrFail($orderId);
            $oldStatus = $order->status;
            $order->status = $validated['status'];
            $order->save();

            // Handle status "paid" - berikan poin ke user
            if ($order->status == 'paid' && $oldStatus != 'paid') {
                try {
                    // Kirim poin ke Loyalty Service (dengan total harga untuk kalkulasi poin)
                    $response = $loyalty->grantPoints($order->user_id, $order->id, $order->total_price);

                    // Cek response loyalty service
                    if (($response['status'] ?? null) === 'success') {
                        return back()->with('success', 'Pembayaran dikonfirmasi dan poin berhasil diberikan.');
                    }

                    if (($response['status'] ?? null) === 'duplicate') {
                        return back()->with('warning', 'Pembayaran dikonfirmasi. Poin sudah pernah diberikan sebelumnya.');
                    }

                    // Jika ada error dari loyalty service
                    if (($response['status'] ?? null) === 'error') {
                        $errorMessage = $response['message'] ?? 'Unknown error';
                        $baseUrl = env('LOYALTY_SERVICE_URL', 'NOT SET');
                        
                        Log::error('Loyalty Service Error: ' . $errorMessage, [
                            'response' => $response,
                            'base_url' => $baseUrl
                        ]);
                        
                        // Pesan error yang lebih informatif
                        if (str_contains($errorMessage, '404')) {
                            $errorMessage = 'Loyalty service tidak ditemukan (404). Pastikan: 1) Loyalty service sedang berjalan, 2) LOYALTY_SERVICE_URL di .env benar (saat ini: ' . $baseUrl . ')';
                        }
                        
                        return back()->with('error', 'Pembayaran dikonfirmasi, tetapi pemberian poin gagal: ' . $errorMessage);
                    }

                    // Jika tidak ada status yang jelas, tampilkan pesan kegagalan terkontrol
                    Log::warning('Unexpected loyalty service response', ['response' => $response]);
                    return back()->with('error', 'Pembayaran dikonfirmasi, tetapi pemberian poin gagal. Response tidak valid.');
                } catch (\Exception $e) {
                    // Tangani exception yang tidak terduga
                    Log::error('Error updating order status to paid: ' . $e->getMessage(), [
                        'order_id' => $orderId,
                        'user_id' => $order->user_id,
                        'exception' => $e
                    ]);
                    return back()->with('error', 'Pembayaran dikonfirmasi, tetapi terjadi error saat memberikan poin: ' . $e->getMessage());
                }
            }

            // Untuk status lainnya (shipped, completed, cancelled, pending)
            $statusMessages = [
                'shipped' => 'Status pesanan berhasil diubah menjadi Shipped (Sedang Dikirim).',
                'completed' => 'Status pesanan berhasil diubah menjadi Completed (Selesai).',
                'cancelled' => 'Status pesanan berhasil dibatalkan.',
                'pending' => 'Status pesanan dikembalikan ke Pending.',
            ];

            $message = $statusMessages[$order->status] ?? 'Status pesanan berhasil diperbarui!';

            return redirect()->route('admin.pesanan.show', ['orderId' => $orderId])
                ->with('success', $message);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Tangani validation error
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // Tangani semua exception lainnya
            Log::error('Error updating order status: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'status' => $request->input('status'),
                'exception' => $e
            ]);
            
            return back()->with('error', 'Terjadi error saat mengupdate status pesanan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     * Menghapus pesanan dari database.
     */
    // === PASTIKAN METHOD INI BENAR ===
    public function destroy(string $orderId): RedirectResponse // Terima $orderId
    {
        // 1. Cari order berdasarkan ID
        $order = Order::findOrFail($orderId);

        // 2. Hapus pesanan (dan item-itemnya karena onDelete('cascade') di migration item)
        try {
            $order->delete();
            // 3. Redirect kembali ke daftar pesanan dengan pesan sukses
            return redirect()->route('admin.pesanan.index')->with('success', 'Pesanan berhasil dihapus!');
        } catch (\Exception $e) {
            // Tangani error jika gagal menghapus
            Log::error('Gagal menghapus pesanan: ' . $e->getMessage());
            return redirect()->route('admin.pesanan.index')->with('error', 'Gagal menghapus pesanan.');
        }
    }
    // === AKHIR METHOD DESTROY ===
}
