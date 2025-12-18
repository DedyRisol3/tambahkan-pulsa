<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RajaOngkirController extends Controller
{
    /**
     * Menampilkan daftar provinsi dari API Raja Ongkir (Kommerce)
     */
    public function index()
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'key' => config('rajaongkir.api_key'),
        ])->get('https://rajaongkir.komerce.id/api/v1/destination/province');

        $provinces = [];

        if ($response->successful()) {
            $provinces = $response->json()['data'] ?? [];
        }

        return view('rajaongkir', compact('provinces'));
    }

    /**
     * Mengambil data kota berdasarkan ID provinsi
     *
     * @param int $provinceId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCities($provinceId)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'key' => config('rajaongkir.api_key'),
        ])->get("https://rajaongkir.komerce.id/api/v1/destination/city/{$provinceId}");

        if ($response->successful()) {
            return response()->json($response->json()['data'] ?? []);
        }

        return response()->json([], $response->status());
    }

    /**
     * Mengambil data kecamatan berdasarkan ID kota
     *
     * @param int $cityId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDistricts($cityId)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'key' => config('rajaongkir.api_key'),
        ])->get("https://rajaongkir.komerce.id/api/v1/destination/district/{$cityId}");

        if ($response->successful()) {
            return response()->json($response->json()['data'] ?? []);
        }

        return response()->json([], $response->status());
    }

    /**
     * Menghitung ongkos kirim berdasarkan data yang diberikan
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkOngkir(Request $request)
    {
        try {
            $apiKey = config('rajaongkir.api_key');
            
            // Cek API key
            if (empty($apiKey)) {
                \Log::error('RajaOngkir API Key kosong!');
                return response()->json([
                    'error' => 'API Key tidak dikonfigurasi',
                    'message' => 'Silakan set RAJAONGKIR_API_KEY di file .env'
                ], 500);
            }

            $response = Http::asForm()->withHeaders([
                'Accept' => 'application/json',
                'key'    => $apiKey,
            ])->post('https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
                'origin'      => 3855,
                'destination' => $request->input('district_id'),
                'weight'      => $request->input('weight'),
                'courier'     => $request->input('courier'),
            ]);

            // Log response untuk debugging
            \Log::info('RajaOngkir Response', [
                'status' => $response->status(),
                'body' => $response->json(),
                'request' => [
                    'district_id' => $request->input('district_id'),
                    'weight' => $request->input('weight'),
                    'courier' => $request->input('courier'),
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                
                // Jika data kosong, coba cek format response lain
                if (empty($data) && isset($response->json()['rajaongkir']['results'])) {
                    $data = $response->json()['rajaongkir']['results'];
                }
                
                return response()->json($data);
            }

            // Log error
            \Log::error('RajaOngkir Error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'error' => 'Gagal menghitung ongkir',
                'status' => $response->status(),
                'detail' => $response->json()
            ], $response->status());
            
        } catch (\Exception $e) {
            \Log::error('RajaOngkir Exception: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}