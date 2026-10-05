@extends('layouts.app')

@section('title', 'Beli Paket Data & Pulsa H2H Termurah - Tokonet')

@section('content')
<div class="space-y-6">
    <!-- Top Hero / Banner Card -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900/80 via-slate-900 to-slate-950 p-6 sm:p-8 border border-indigo-500/20 shadow-2xl">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-indigo-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-0 right-1/4 w-32 h-32 bg-violet-500/10 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-400/30 text-indigo-300 text-xs font-semibold mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                    <span>Server H2H Online 24 Jam • Terhubung Okeconnect</span>
                </div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight">
                    Isi Kuota & Paket Data <span class="bg-gradient-to-r from-indigo-400 via-sky-300 to-indigo-200 bg-clip-text text-transparent">Lebih Cepat & Murah</span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-2 max-w-xl leading-relaxed">
                    Tersedia kuota Telkomsel OMG!, Indosat Freedom, XL Combo Flex, Axis Bronet, Tri Happy & Token PLN harga agen grosir.
                </p>
            </div>

            <!-- Quick Saldo & Action Box -->
            <div class="bg-slate-900/90 border border-slate-700/80 rounded-2xl p-5 sm:min-w-[280px] shadow-xl backdrop-blur">
                @auth
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs text-slate-400 font-semibold">Saldo Akun Anda</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-bold">Aktif</span>
                    </div>
                    <div class="text-2xl font-black text-emerald-400 mb-4 tracking-tight">
                        Rp {{ number_format(auth()->user()->balance, 0, ',', '.') }}
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('topup.index') }}" class="flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold py-2.5 px-3 rounded-xl transition shadow-md shadow-indigo-600/30">
                            <i data-lucide="plus-circle" class="w-4 h-4"></i> Topup
                        </a>
                        <a href="{{ route('history') }}" class="flex items-center justify-center gap-1.5 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold py-2.5 px-3 rounded-xl transition">
                            <i data-lucide="history" class="w-4 h-4"></i> Riwayat
                        </a>
                    </div>
                @else
                    <div class="text-center py-2">
                        <span class="text-xs text-slate-300 font-semibold block mb-2">Mulai Transaksi Hemat Hari Ini</span>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('login') }}" class="bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold py-2.5 rounded-xl transition text-center">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" class="bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold py-2.5 rounded-xl transition text-center shadow-md shadow-indigo-600/30">
                                Daftar
                            </a>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </div>

    <!-- Main Store Panel: Input Number & Product Selector -->
    <div class="glass-card rounded-3xl p-5 sm:p-7 shadow-xl">
        <!-- Step 1: Input Destination Number -->
        <div class="mb-6">
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                1. Masukkan Nomor HP / ID Pelanggan
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none text-slate-400">
                    <i data-lucide="smartphone" class="w-5 h-5"></i>
                </div>
                <input type="tel" id="destinationNumberInput" placeholder="Contoh: 08123456789 atau 0857..." value="{{ old('destination_number', request('phone')) }}" class="w-full bg-slate-900 border-2 border-slate-700/80 rounded-2xl pl-12 pr-32 py-3.5 text-base sm:text-lg font-bold text-white tracking-wide placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition">

                <!-- Detected Operator Badge -->
                <div id="detectedOperatorBadge" class="absolute inset-y-0 right-2 flex items-center">
                    <span class="px-3 py-1.5 rounded-xl text-xs font-bold bg-indigo-600/30 text-indigo-300 border border-indigo-500/40 flex items-center gap-1.5">
                        <span id="detectedOperatorName">Telkomsel</span>
                    </span>
                </div>
            </div>
            <p class="text-[11px] text-slate-400 mt-1.5 flex items-center gap-1">
                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-indigo-400"></i> Operator otomatis terdeteksi saat nomor diketik.
            </p>
        </div>

        <!-- Step 2: Choose Category / Operator Brand -->
        <div class="mb-6">
            <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2.5">
                2. Pilih Kategori & Operator
            </label>
            
            <!-- Type Tabs: Data, Pulsa, PLN -->
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-2 mb-3">
                <button type="button" onclick="selectType('data')" id="tab-type-data" class="type-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-indigo-600 text-white shadow-lg shadow-indigo-600/30">
                    <i data-lucide="wifi" class="w-4 h-4"></i> Paket Data Internet
                </button>
                <button type="button" onclick="selectType('pln')" id="tab-type-pln" class="type-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-slate-900 border border-slate-700 text-slate-300 hover:text-white">
                    <i data-lucide="zap" class="w-4 h-4 text-amber-400"></i> Token PLN Prabayar
                </button>
            </div>

            <!-- Brand Pills -->
            <div id="brandPillsContainer" class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
                @php
                    $brands = ['Telkomsel', 'Indosat', 'XL', 'Axis', 'Tri', 'Smartfren', 'PLN'];
                @endphp
                @foreach($brands as $b)
                    <button type="button" onclick="selectBrand('{{ $b }}')" data-brand="{{ $b }}" class="brand-pill-btn px-4 py-2 rounded-xl text-xs font-bold transition border {{ $b === 'Telkomsel' ? 'bg-indigo-600/20 border-indigo-500 text-indigo-300' : 'bg-slate-900/80 border-slate-800 text-slate-400 hover:text-slate-200' }}">
                        {{ $b }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Step 3: Product Cards Grid -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                    3. Pilih Paket / Produk
                </label>
                <span id="productCountBadge" class="text-xs text-slate-400 font-semibold">Menampilkan produk</span>
            </div>

            <!-- Grid -->
            <div id="productsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                @foreach($activeProducts as $product)
                    <div data-brand="{{ $product->category->brand ?? '' }}" data-type="{{ $product->type }}" class="product-item-card group relative bg-slate-900/90 border border-slate-800 hover:border-indigo-500/60 rounded-2xl p-4 transition-all duration-200 hover:shadow-xl hover:shadow-indigo-500/10 flex flex-col justify-between cursor-pointer" onclick="openCheckoutModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $product->price_selling }}, '{{ $product->provider_code }}', '{{ addslashes($product->description ?? '') }}')">
                        <div>
                            <div class="flex items-start justify-between gap-2 mb-2">
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg bg-slate-800 text-indigo-300 border border-slate-700">
                                    {{ $product->category->brand ?? 'H2H' }}
                                </span>
                                <span class="text-[11px] font-semibold text-emerald-400 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Ready
                                </span>
                            </div>

                            <h3 class="font-bold text-sm text-white group-hover:text-indigo-300 transition line-clamp-2 mb-1.5">
                                {{ $product->name }}
                            </h3>

                            <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed mb-3">
                                {{ $product->description ?: 'Proses instan 24 jam langsung masuk.' }}
                            </p>
                        </div>

                        <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between mt-auto">
                            <div>
                                <span class="text-[10px] text-slate-400 block">Harga</span>
                                <span class="text-base font-extrabold text-white group-hover:text-emerald-400 transition">
                                    Rp {{ number_format($product->price_selling, 0, ',', '.') }}
                                </span>
                            </div>
                            <button type="button" class="bg-indigo-600/20 group-hover:bg-indigo-600 text-indigo-300 group-hover:text-white px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 border border-indigo-500/30 group-hover:border-transparent">
                                Beli <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Empty State -->
            <div id="noProductNotice" class="hidden text-center py-12 bg-slate-900/40 rounded-2xl border border-slate-800/80">
                <i data-lucide="package-search" class="w-12 h-12 text-slate-600 mx-auto mb-3"></i>
                <p class="text-sm font-semibold text-slate-300">Tidak ada produk untuk kategori ini</p>
                <p class="text-xs text-slate-500 mt-1">Coba ganti operator atau pilih kategori lainnya.</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Checkout Confirmation -->
<div id="checkoutModal" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="glass-card w-full max-w-md rounded-3xl p-6 shadow-2xl border border-slate-700/80 relative overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between pb-4 border-b border-slate-800">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-indigo-600/20 border border-indigo-500/40 flex items-center justify-center text-indigo-400">
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                </div>
                <h3 class="font-bold text-base text-white">Konfirmasi Pembelian</h3>
            </div>
            <button type="button" onclick="closeCheckoutModal()" class="text-slate-400 hover:text-white p-1 rounded-lg">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('checkout') }}" method="POST" id="checkoutForm" class="mt-4 space-y-4">
            @csrf
            <input type="hidden" name="product_id" id="modalProductId">
            <input type="hidden" name="destination_number" id="modalDestinationInput">
            <input type="hidden" name="payment_method" value="balance">

            <div class="bg-slate-900 p-4 rounded-2xl border border-slate-800 space-y-2">
                <div class="flex justify-between text-xs">
                    <span class="text-slate-400">Nomor Tujuan</span>
                    <span id="modalDestDisplay" class="font-bold text-white tracking-wide">0812...</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-slate-400">Produk</span>
                    <span id="modalProductName" class="font-bold text-indigo-300 text-right max-w-[200px] truncate">-</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-slate-400">Deskripsi</span>
                    <span id="modalProductDesc" class="text-slate-300 text-right text-[11px] max-w-[200px] truncate">-</span>
                </div>
                <div class="flex justify-between text-sm pt-2 border-t border-slate-800">
                    <span class="font-bold text-slate-300">Total Bayar</span>
                    <span id="modalProductPrice" class="font-extrabold text-emerald-400">Rp 0</span>
                </div>
            </div>

            <!-- Payment Method Check -->
            @auth
                <div class="bg-indigo-950/40 border border-indigo-500/30 p-3.5 rounded-2xl flex items-center justify-between">
                    <div>
                        <span class="text-xs text-slate-300 font-semibold block">Metode Pembayaran</span>
                        <span class="text-xs text-indigo-300 font-bold flex items-center gap-1">
                            <i data-lucide="wallet" class="w-3.5 h-3.5"></i> Saldo Web (Rp {{ number_format(auth()->user()->balance, 0, ',', '.') }})
                        </span>
                    </div>
                    <span class="text-[10px] bg-emerald-500/20 text-emerald-300 px-2 py-1 rounded-lg font-bold">Otomatis Terpotong</span>
                </div>

                <div id="balanceInsufficientWarning" class="hidden p-3 rounded-xl bg-rose-950/70 border border-rose-500/30 text-rose-300 text-xs">
                    ⚠️ Saldo Anda tidak mencukupi. Silakan <a href="{{ route('topup.index') }}" class="underline font-bold text-white">Topup Saldo</a> terlebih dahulu.
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="button" onclick="closeCheckoutModal()" class="w-1/3 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold py-3 rounded-xl text-xs transition">
                        Batal
                    </button>
                    <button type="submit" id="btnConfirmPay" class="w-2/3 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 rounded-xl text-xs transition shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-1.5">
                        <i data-lucide="zap" class="w-4 h-4"></i> Bayar Sekarang
                    </button>
                </div>
            @else
                <div class="text-center p-4 bg-slate-900 rounded-2xl border border-slate-800">
                    <p class="text-xs text-slate-300 mb-3">Silakan masuk ke akun Anda untuk menyelesaikan transaksi.</p>
                    <a href="{{ route('login') }}" class="block w-full bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 rounded-xl text-xs transition shadow-md shadow-indigo-600/30">
                        Masuk / Daftar Akun
                    </a>
                </div>
            @endauth
        </form>
    </div>
</div>

@push('scripts')
<script>
    let currentBrand = 'Telkomsel';
    let currentType = 'data';
    const userBalance = {{ auth()->check() ? auth()->user()->balance : 0 }};

    function selectType(type) {
        currentType = type;
        document.querySelectorAll('.type-tab-btn').forEach(btn => {
            btn.className = 'type-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-slate-900 border border-slate-700 text-slate-300 hover:text-white';
        });
        const activeBtn = document.getElementById('tab-type-' + type);
        if (activeBtn) {
            activeBtn.className = 'type-tab-btn px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 bg-indigo-600 text-white shadow-lg shadow-indigo-600/30';
        }

        if (type === 'pln') {
            selectBrand('PLN');
        } else {
            if (currentBrand === 'PLN') selectBrand('Telkomsel');
        }
        filterProducts();
    }

    function selectBrand(brand) {
        currentBrand = brand;
        document.querySelectorAll('.brand-pill-btn').forEach(btn => {
            if (btn.getAttribute('data-brand') === brand) {
                btn.className = 'brand-pill-btn px-4 py-2 rounded-xl text-xs font-bold transition border bg-indigo-600/20 border-indigo-500 text-indigo-300';
            } else {
                btn.className = 'brand-pill-btn px-4 py-2 rounded-xl text-xs font-bold transition border bg-slate-900/80 border-slate-800 text-slate-400 hover:text-slate-200';
            }
        });
        filterProducts();
    }

    function filterProducts() {
        const cards = document.querySelectorAll('.product-item-card');
        let count = 0;

        cards.forEach(card => {
            const cardBrand = card.getAttribute('data-brand');
            const cardType = card.getAttribute('data-type');

            let matchBrand = (cardBrand === currentBrand) || (currentBrand === 'PLN' && cardType === 'pln');
            let matchType = (cardType === currentType);

            if (matchBrand && matchType) {
                card.classList.remove('hidden');
                count++;
            } else {
                card.classList.add('hidden');
            }
        });

        document.getElementById('productCountBadge').innerText = `${count} Paket Tersedia`;
        document.getElementById('noProductNotice').classList.toggle('hidden', count > 0);
    }

    // Live prefix detection
    const destInput = document.getElementById('destinationNumberInput');
    destInput.addEventListener('input', function() {
        const val = this.value.replace(/[^0-9]/g, '');
        let clean = val;
        if (clean.startsWith('628')) clean = '08' + clean.substring(3);
        if (clean.startsWith('8')) clean = '08' + clean.substring(1);

        if (clean.length >= 4) {
            fetch(`/api/detect-operator?phone=${clean}&type=${currentType}`)
                .then(res => res.json())
                .then(data => {
                    if (data.brand && currentType !== 'pln') {
                        document.getElementById('detectedOperatorName').innerText = data.brand;
                        selectBrand(data.brand);
                    }
                })
                .catch(() => {});
        }
    });

    function openCheckoutModal(id, name, price, code, desc) {
        const dest = destInput.value.trim();
        if (!dest) {
            alert('Silakan masukkan Nomor HP / ID Pelanggan terlebih dahulu di kolom nomor 1.');
            destInput.focus();
            return;
        }

        document.getElementById('modalProductId').value = id;
        document.getElementById('modalDestinationInput').value = dest;
        document.getElementById('modalDestDisplay').innerText = dest;
        document.getElementById('modalProductName').innerText = name;
        document.getElementById('modalProductDesc').innerText = desc || 'Pengisian H2H Otomatis';
        document.getElementById('modalProductPrice').innerText = 'Rp ' + Number(price).toLocaleString('id-ID');

        const warning = document.getElementById('balanceInsufficientWarning');
        const payBtn = document.getElementById('btnConfirmPay');
        if (warning && payBtn) {
            if (userBalance < price) {
                warning.classList.remove('hidden');
                payBtn.disabled = true;
                payBtn.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                warning.classList.add('hidden');
                payBtn.disabled = false;
                payBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }

        document.getElementById('checkoutModal').classList.remove('hidden');
        lucide.createIcons();
    }

    function closeCheckoutModal() {
        document.getElementById('checkoutModal').classList.add('hidden');
    }

    // Initial filter
    filterProducts();
</script>
@endpush
@endsection
