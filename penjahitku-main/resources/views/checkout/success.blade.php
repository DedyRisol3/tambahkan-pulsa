<x-app-layout> {{-- Menggunakan layout app.blade.php --}}

    {{-- Judul Halaman Spesifik --}}
    <x-slot name="title">
        Pesanan Diterima - PenjahitKu
    </x-slot>

     {{-- Slot CSS Tambahan --}}
    <x-slot name="styles">
        <style>
             /* Section utama dibuat flex untuk menengahkan box */
             .thank-you-section {
                flex-grow: 1;
                display: flex;
                justify-content: center;
                align-items: center;
                text-align: center;
                padding: 3rem 1.5rem;
            }
            .thank-you-box {
                background: var(--white-color);
                padding: 3rem;
                border-radius: 8px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                border: 1px solid var(--grey-color);
                max-width: 600px; /* Batasi lebar box */
                width: 100%;
            }
            .thank-you-box .icon {
                font-size: 4rem;
                color: var(--primary-color);
                margin-bottom: 1.5rem;
            }
            .thank-you-box h1 {
                font-size: 2.5rem;
                color: var(--dark-color);
                margin-bottom: 1rem;
                font-weight: 600;
            }
            .thank-you-box p {
                font-size: 1.2rem;
                color: #555;
                margin-bottom: 2rem;
            }
            
            /* === STYLE INSTRUKSI PEMBAYARAN SUDAH DIHAPUS === */
        </style>
    </x-slot>

    {{-- Konten Utama Halaman Terima Kasih --}}
    <div class="thank-you-section">
        <div class="thank-you-box">
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Terima Kasih!</h1>

            {{-- Baca Flash Session dari Backend --}}
            @if (session('lastOrder'))
                @php 
                    $orderData = session('lastOrder');
                    $rupiahPerPoint = config('loyalty.rupiah_per_point', 5000);
                    $estimatedPoints = floor($orderData['totalPrice'] / $rupiahPerPoint);
                @endphp
                <p id="thank-you-message">
                    Pesanan Anda (<strong>#{{ $orderData['orderId'] }}</strong>) telah berhasil kami terima.<br>
                    Total belanja Anda: 
                    <strong style="font-size: 1.4rem; color: var(--secondary-color);">
                        Rp {{ number_format($orderData['totalPrice'], 0, ',', '.') }}
                    </strong>
                </p>
                
                {{-- Info poin yang akan didapat --}}
                @if($estimatedPoints > 0)
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px; padding: 16px; margin: 20px 0; text-align: center;">
                    <p style="margin: 0; font-size: 0.9rem; opacity: 0.9;">💎 Setelah pembayaran dikonfirmasi, Anda akan mendapat:</p>
                    <p style="margin: 8px 0 0 0; font-size: 1.8rem; font-weight: bold;">+{{ $estimatedPoints }} Poin</p>
                    <p style="margin: 4px 0 0 0; font-size: 0.8rem; opacity: 0.8;">Tukarkan poin dengan pulsa di Loyalty App!</p>
                </div>
                @endif

                {{-- Pesan yang lebih generik --}}
                <p style="font-size: 1rem; color: #777;">Kami sedang menunggu konfirmasi pembayaran Anda. Silakan cek email/WhatsApp Anda untuk detail selanjutnya.</p>
            @else
                <p id="thank-you-message">Pesanan Anda telah berhasil kami buat.<br>Tim kami akan segera memprosesnya.</p>
            @endif

            {{-- === BLOK INSTRUKSI PEMBAYARAN BCA SUDAH DIHAPUS === --}}

            <a href="{{ route('home') }}" class="btn btn-primary" style="margin-top: 2rem;">Kembali ke Beranda</a>
        </div>
    </div>

    {{-- Slot JavaScript Tambahan (Hanya update counter) --}}
    <x-slot name="scripts">
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                 // Update counter header menjadi 0
                 const cartCountEl = document.getElementById('cart-count-header');
                 if(cartCountEl) cartCountEl.textContent = 0;
            });
        </script>
    </x-slot>

</x-app-layout>