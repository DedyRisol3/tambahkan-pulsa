<x-app-layout> {{-- Menggunakan layout app.blade.php --}}

    {{-- Judul Halaman Spesifik --}}
    <x-slot name="title">
        Checkout - PenjahitKu
    </x-slot>

    {{-- Slot CSS Tambahan (Gaya checkout + Gaya Loader RajaOngkir) --}}
    <x-slot name="styles">
        <style>
            .checkout-section {
                padding: 3rem 0;
            }

            .checkout-section h1 {
                font-size: 2.5rem;
                color: var(--dark-color);
                margin-bottom: 2rem;
                border-bottom: 2px solid var(--grey-color);
                padding-bottom: 1rem;
            }

            .checkout-layout {
                display: grid;
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            @media (min-width: 768px) {
                .checkout-layout {
                    grid-template-columns: 2fr 1fr;
                }
            }

            .checkout-form {
                background: var(--white-color);
                padding: 2rem;
                border-radius: 8px;
                border: 1px solid var(--grey-color);
            }

            .checkout-form h2 {
                font-size: 1.5rem;
                color: var(--primary-color);
                margin-bottom: 1.5rem;
            }

            .form-group {
                margin-bottom: 1rem;
            }

            .form-group label {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: 600;
            }

            /* Style input/select/textarea pakai class Tailwind */
            .order-summary {
                background: var(--white-color);
                border-radius: 8px;
                padding: 2rem;
                border: 1px solid var(--grey-color);
                position: sticky;
                top: 100px;
            }

            .order-summary h2 {
                font-size: 1.5rem;
                margin-bottom: 1.5rem;
                border-bottom: 2px solid var(--grey-color);
                padding-bottom: 1rem;
            }

            .summary-item {
                display: flex;
                justify-content: space-between;
                margin-bottom: 0.5rem;
                font-size: 0.9rem;
            }

            .summary-item-name {
                color: #555;
            }

            .summary-item-price {
                color: #333;
            }

            .summary-total {
                display: flex;
                justify-content: space-between;
                font-size: 1.3rem;
                font-weight: 700;
                color: var(--primary-color);
                border-top: 2px solid var(--grey-color);
                padding-top: 1rem;
                margin-top: 1.5rem;
            }

            /* Kelas untuk pesan error validasi */
            .text-red-500 {
                color: #ef4444;
            }

            .text-sm {
                font-size: 0.875rem;
            }

            /* == STYLE DARI RAJAONGKIR (LOADER) == */
            .loader {
                border: 4px solid #f3f3f3;
                border-top: 4px solid #4f46e5;
                border-radius: 50%;
                width: 30px;
                height: 30px;
                animation: spin 1s linear infinite;
                margin: 0 auto;
                display: none
            }

            @keyframes spin {
                0% {
                    transform: rotate(0deg)
                }

                100% {
                    transform: rotate(360deg)
                }
            }

            /* == AKHIR STYLE RAJAONGKIR == */
        </style>
    </x-slot>

    {{-- Konten Utama Halaman Checkout --}}
    <div class="checkout-section">
        <div class="container">
            <h1>Checkout</h1>

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <strong class="block font-semibold mb-2">Silakan perbaiki kesalahan berikut:</strong>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (isset($cartItems) && count($cartItems) > 0)
                <form class="checkout-layout" id="checkout-form" method="POST" action="{{ route('checkout.store') }}">
                    @csrf

                    {{-- Kolom 1: Form Alamat & Pengiriman --}}
                    <div class="checkout-form">
                        <h2>Detail Pengiriman</h2>
                        <div class="form-group">
                            <label for="name">Nama Lengkap</label>
                            <input type="text" id="name" name="name" required
                                class="border-gray-300 rounded-md shadow-sm w-full"
                                value="{{ old('name', Auth::user()->name ?? '') }}">
                            @error('name')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required
                                class="border-gray-300 rounded-md shadow-sm w-full"
                                value="{{ old('email', Auth::user()->email ?? '') }}">
                            @error('email')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="phone">Nomor Telepon</label>
                            <input type="tel" id="phone" name="phone" required
                                class="border-gray-300 rounded-md shadow-sm w-full" value="{{ old('phone') }}"
                                placeholder="Contoh: 08123456789">
                            @error('phone')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- =============================================== --}}
                        {{-- === BLOK RAJAONGKIR DIMASUKKAN DI SINI === --}}
                        {{-- =============================================== --}}

                        <h2>Opsi Pengiriman</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

                            <div class="form-group">
                                <label for="province" class="block text-sm font-medium text-gray-700 mb-1">Provinsi
                                    Tujuan</label>
                                <select id="province" name="province_id"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base bg-gray-200 border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow">
                                    <option value="">-- Pilih Provinsi --</option>
                                    @foreach ($provinces as $province)
                                        <option value="{{ $province['id'] }}">{{ $province['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-1">Kota /
                                    Kabupaten Tujuan</label>
                                <select id="city" name="city_id"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base bg-gray-200 border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm disabled:bg-gray-50 disabled:cursor-not-allowed">
                                    <option value="">-- Pilih Kota / Kabupaten --</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="district" class="block text-sm font-medium text-gray-700 mb-1">Kecamatan
                                    Tujuan</label>
                                <select id="district" name="district_id"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base bg-gray-200 border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm disabled:bg-gray-50 disabled:cursor-not-allowed">
                                    <option value="">-- Pilih Kecamatan --</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="weight" class="block text-sm font-medium text-gray-700 mb-1">Berat Barang
                                    (gram)</label>
                                <input type="number" name="weight" id="weight" min="1000"
                                    placeholder="Masukkan berat (gram)"
                                    class="mt-1 block w-full pl-3 pr-3 py-2 text-base bg-gray-200 border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow"
                                    value="1000">
                            </div>
                        </div>

                        <div class="form-group mb-8">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Kurir</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">

                                <div class="flex items-center">
                                    <input type="radio" name="courier" id="courier-1" value="sicepat"
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="courier-1" class="ml-2 block text-sm text-gray-900">SICEPAT</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="courier" id="courier-2" value="jnt"
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="courier-2" class="ml-2 block text-sm text-gray-900">J&T</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="courier" id="courier-3" value="ninja"
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="courier-3" class="ml-2 block text-sm text-gray-900">Ninja
                                        Express</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="courier" id="courier-4" value="jne"
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="courier-4" class="ml-2 block text-sm text-gray-900">JNE</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="courier" id="courier-5" value="anteraja"
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="courier-5" class="ml-2 block text-sm text-gray-900">Anteraja</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="courier" id="courier-6" value="pos"
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="courier-6" class="ml-2 block text-sm text-gray-900">POS
                                        Indonesia</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="courier" id="courier-7" value="tiki"
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="courier-7" class="ml-2 block text-sm text-gray-900">Tiki</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="courier" id="courier-8" value="wahana"
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="courier-8" class="ml-2 block text-sm text-gray-900">Wahana</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" name="courier" id="courier-9" value="lion"
                                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="courier-9" class="ml-2 block text-sm text-gray-900">Lion
                                        Parcel</label>
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-center mb-8 flex-col items-center">
                            <button type="button"
                                class="btn-check w-full md:w-auto px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed">
                                Hitung Ongkos Kirim
                            </button>
                            <div class="loader mt-4" id="loading-indicator"></div>
                        </div>

                        <div
                            class="mt-8 p-6 bg-indigo-50 border border-indigo-200 rounded-lg results-container hidden">
                            <h2 class="text-xl font-semibold text-indigo-800 mb-4 text-center">Hasil Perhitungan Ongkos
                                Kirim</h2>
                            <div class="space-y-3" id="results-ongkir">
                            </div>
                        </div>


                        {{-- Input tersembunyi untuk payment_method --}}
                        <input type="hidden" name="payment_method" value="xendit">

                        {{-- Input tersembunyi untuk menyimpan data ongkir yg dipilih --}}
                        <input type="hidden" name="shipping_cost" id="shipping_cost" value="0">
                        <input type="hidden" name="shipping_etd" id="shipping_etd" value="">

                    </div>

                    {{-- Kolom 2: Ringkasan Pesanan --}}
                    <div class="order-summary" id="order-summary">
                        <h2>Ringkasan Pesanan</h2>

                        <div id="summary-items-list">
                            @foreach ($cartItems as $item)
                                <div class="summary-item">
                                    <span class="summary-item-name">{{ $item['name'] ?? 'N/A' }}
                                        (x{{ $item['quantity'] ?? 0 }})
                                    </span>
                                    <span class="summary-item-price">Rp
                                        {{ number_format(($item['priceRaw'] ?? 0) * ($item['quantity'] ?? 0), 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Baris untuk Ongkos Kirim (diupdate oleh JS) --}}
                        <div class="summary-item" id="summary-shipping-row" style="display: none;">
                            <span class="summary-item-name">Ongkos Kirim</span>
                            <span class="summary-item-price" id="summary-shipping-cost">Rp 0</span>
                        </div>

                        <div class="summary-item">
                            <span class="summary-item-name">Poin Anda</span>
                            <span class="summary-item-price">{{ $pointBalance }} poin</span>
                        </div>

                        {{-- Info perhitungan poin --}}
                        <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 12px; margin: 12px 0; font-size: 0.85rem;">
                            <p style="margin: 0; color: #0369a1;">
                                <strong>💎 Info Poin:</strong><br>
                                • Setiap belanja Rp {{ number_format(config('loyalty.rupiah_per_point', 5000), 0, ',', '.') }} = 1 poin<br>
                                • 1 poin = Rp {{ number_format(config('loyalty.point_to_currency', 50), 0, ',', '.') }} diskon<br>
                                • Poin juga bisa ditukar pulsa di Loyalty App!
                            </p>
                        </div>

                        @if ($pointBalance > 0)
                            <div class="mt-4">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" id="use_points" name="use_points" value="1">
                                    <span>Gunakan poin untuk diskon</span>
                                </label>

                                <input type="number" min="1" max="{{ $pointBalance }}" id="points_used"
                                    name="points_used" class="mt-2 border-gray-300 rounded-md shadow-sm w-full"
                                    placeholder="Masukkan jumlah poin" max="{{ $pointBalance }}" disabled>

                                <p class="mt-2 text-green-600 font-semibold">
                                    Diskon: <span id="discount_value">Rp 0</span>
                                </p>
                            </div>
                        @endif

                        <div class="summary-total">
                            <span>Total</span>
                            <span id="summary-total-price">Rp {{ number_format($totalPrice, 0, ',', '.') }}</span>

                            {{-- Simpan total asli (subtotal) untuk kalkulasi JS --}}
                            <span id="summary-subtotal-raw" data-subtotal="{{ $totalPrice }}"
                                style="display: none;"></span>
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-top: 2rem;">
                            Lanjut ke Pembayaran
                        </button>
                    </div>

                </form>
            @else
                <div class="empty-cart-message">
                    <p>Keranjang Anda kosong.</p>
                    <a href="{{ route('home') }}" class="btn btn-primary">Kembali ke Katalog</a>
                </div>
            @endif
        </div>
    </div>

    {{-- Slot JavaScript Tambahan --}}
    <x-slot name="scripts">

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.0/jquery.min.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const user = @json(Auth::user());
                if (user) {
                    const nameInput = document.getElementById('name');
                    const emailInput = document.getElementById('email');
                    if (nameInput && !nameInput.value) nameInput.value = user.name || '';
                    if (emailInput && !emailInput.value) emailInput.value = user.email || '';
                }
            });
        </script>

        <script>
            $(document).ready(function() {

                // Fungsi format Rupiah
                function formatCurrency(amount) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    }).format(amount);
                }

                $('select[name="province_id"]').on('change', function() {
                    let provinceId = $(this).val();
                    if (provinceId) {
                        jQuery.ajax({
                            url: `/cities/${provinceId}`,
                            type: "GET",
                            dataType: "json",
                            success: function(response) {
                                $('select[name="city_id"]').empty().append(
                                    `<option value="">-- Pilih Kota / Kabupaten --</option>`);
                                $.each(response, function(index, value) {
                                    $('select[name="city_id"]').append(
                                        `<option value="${value.id}">${value.name}</option>`
                                    );
                                });
                                $('select[name="city_id"]').prop('disabled', false).removeClass(
                                    'disabled:bg-gray-50 disabled:cursor-not-allowed');
                            }
                        });
                    } else {
                        $('select[name="city_id"]').empty().append(
                                `<option value="">-- Pilih Kota / Kabupaten --</option>`).prop('disabled', true)
                            .addClass('disabled:bg-gray-50 disabled:cursor-not-allowed');
                    }
                    $('select[name="district_id"]').empty().append(
                        `<option value="">-- Pilih Kecamatan --</option>`).prop('disabled', true).addClass(
                        'disabled:bg-gray-50 disabled:cursor-not-allowed');
                });

                $('select[name="city_id"]').on('change', function() {
                    let cityId = $(this).val();
                    if (cityId) {
                        jQuery.ajax({
                            url: `/districts/${cityId}`,
                            type: "GET",
                            dataType: "json",
                            success: function(response) {
                                $('select[name="district_id"]').empty().append(
                                    `<option value="">-- Pilih Kecamatan --</option>`);
                                $.each(response, function(index, value) {
                                    $('select[name="district_id"]').append(
                                        `<option value="${value.id}">${value.name}</option>`
                                    );
                                });
                                $('select[name="district_id"]').prop('disabled', false).removeClass(
                                    'disabled:bg-gray-50 disabled:cursor-not-allowed');
                            }
                        });
                    } else {
                        $('select[name="district_id"]').empty().append(
                                `<option value="">-- Pilih Kecamatan --</option>`).prop('disabled', true)
                            .addClass('disabled:bg-gray-50 disabled:cursor-not-allowed');
                    }
                });


                let isProcessing = false;

                // Tombol "Hitung Ongkos Kirim"
                $('.btn-check').click(function(e) {
                    e.preventDefault();
                    if (isProcessing) return;

                    let token = $("meta[name='csrf-token']").attr("content");
                    let district_id = $('select[name=district_id]').val();
                    let courier = $('input[name=courier]:checked').val();
                    let weight = $('#weight').val();

                    if (!district_id || !courier || !weight || weight < 1) {
                        alert(
                            'Harap lengkapi semua data (Provinsi, Kota, Kecamatan, Berat, dan Kurir) terlebih dahulu!'
                        );
                        return;
                    }

                    isProcessing = true;
                    $('#loading-indicator').show();
                    $('.btn-check').prop('disabled', true).text('Memproses...');

                    $.ajax({
                        url: "/check-ongkir",
                        type: "POST",
                        dataType: "JSON",
                        data: {
                            _token: token,
                            district_id: district_id,
                            courier: courier,
                            weight: weight,
                        },
                        beforeSend: function() {
                            $('.results-container').addClass('hidden').removeClass('block');
                            updateShippingCost(0, "", "");
                        },
                        success: function(response) {
                            $('#results-ongkir').empty();
                            $('.results-container').removeClass('hidden').addClass('block');

                            if (response && response.length > 0) {
                                $.each(response, function(index, value) {
                                    let resultHtml = `
                                        <div class="flex justify-between items-center p-3 bg-white rounded-xl shadow border border-gray-200">
                                            <div class="flex items-center">
                                                <input type="radio" name="shipping_option" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                                       data-cost="${value.cost}"
                                                       data-etd="${value.etd}"
                                                       data-service="${value.service} - ${value.description}">
                                                <label class="ml-3">
                                                    <span class="text-lg font-medium text-gray-800">${value.service} - ${value.description} (${value.etd})</span>
                                                    <br>
                                                    <span class="text-lg font-bold text-indigo-700">${formatCurrency(value.cost)}</span>
                                                </label>
                                            </div>
                                        </div>
                                    `;
                                    $('#results-ongkir').append(resultHtml);
                                });
                            } else {
                                $('#results-ongkir').html(
                                    '<p class="text-center text-red-500">Opsi pengiriman tidak ditemukan.</p>'
                                );
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Ongkir Error:', xhr.responseJSON || xhr.responseText);
                            let errorMsg = "Terjadi kesalahan saat menghitung ongkir.";
                            
                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.message) {
                                    errorMsg += " " + xhr.responseJSON.message;
                                }
                                if (xhr.responseJSON.error) {
                                    errorMsg += " " + xhr.responseJSON.error;
                                }
                            }
                            
                            alert(errorMsg);
                            $('#results-ongkir').html(
                                '<p class="text-center text-red-500">' + errorMsg + '</p>'
                            );
                        },
                        complete: function() {
                            $('#loading-indicator').hide();
                            $('.btn-check').prop('disabled', false).text('Hitung Ongkos Kirim');
                            isProcessing = false;
                        }
                    });
                });

                function updateShippingCost(cost, etd, service) {
                    let selectedCost = parseFloat(cost);
                    let subtotal = parseFloat($('#summary-subtotal-raw').data('subtotal'));
                    let newTotal = subtotal + selectedCost;

                    if (selectedCost > 0) {
                        $('#summary-shipping-row').show();
                        $('#summary-shipping-cost').text(formatCurrency(selectedCost));
                    } else {
                        $('#summary-shipping-row').hide();
                    }

                    $('#summary-total-price').text(formatCurrency(newTotal));

                    $('#shipping_cost').val(selectedCost);
                    $('#shipping_etd').val(etd);
                }

                $(document).on('change', 'input[name="shipping_option"]', function() {
                    let cost = $(this).data('cost');
                    let etd = $(this).data('etd');
                    let service = $(this).data('service');

                    updateShippingCost(cost, etd, service);
                });

            });
        </script>

        <script>
            document.getElementById('use_points').addEventListener('change', function() {
                let input = document.getElementById('points_used');
                input.disabled = !this.checked;
                if (!this.checked) {
                    input.value = "";
                    document.getElementById('discount_value').innerText = "Rp 0";
                }
            });

            document.getElementById('points_used').addEventListener('input', function() {
                let rate = {{ config('loyalty.point_to_currency', 1000) }};
                let discount = (this.value || 0) * rate;

                document.getElementById('discount_value').innerText =
                    "Rp " + discount.toLocaleString('id-ID');

                // Update total harga di UI
                let subtotal = Number(document.getElementById('summary-subtotal-raw').dataset.subtotal);
                let shipping = Number(document.getElementById('shipping_cost').value || 0);
                let newTotal = subtotal + shipping - discount;

                document.getElementById('summary-total-price').innerText =
                    "Rp " + newTotal.toLocaleString('id-ID');
            });
        </script>

    </x-slot>

</x-app-layout>
