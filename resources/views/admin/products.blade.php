@extends('layouts.admin')

@section('title', 'Kelola Produk & Harga')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Manajemen Produk & Harga</h1>
            <p class="text-xs sm:text-sm text-slate-400">Atur harga modal Okeconnect, harga jual, dan status produk</p>
        </div>

        <button type="button" onclick="document.getElementById('addProductModal').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md shadow-indigo-600/30">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah Produk Baru
        </button>
    </div>

    <!-- Category Filters -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2">
        <a href="{{ route('admin.products') }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold border transition {{ !request('category_id') ? 'bg-indigo-600 text-white border-indigo-500' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white' }}">
            Semua Kategori
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('admin.products', ['category_id' => $cat->id]) }}" class="px-3.5 py-1.5 rounded-xl text-xs font-bold border transition {{ request('category_id') == $cat->id ? 'bg-indigo-600 text-white border-indigo-500' : 'bg-slate-900 border-slate-800 text-slate-400 hover:text-white' }}">
                {{ $cat->brand }} ({{ $cat->type }})
            </a>
        @endforeach
    </div>

    <div class="glass-sidebar rounded-2xl overflow-hidden border border-slate-800 shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-900 text-slate-400 uppercase text-[11px] font-bold border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Kode Provider</th>
                        <th class="py-3 px-4">Nama Produk / Operator</th>
                        <th class="py-3 px-4">Harga Modal</th>
                        <th class="py-3 px-4">Harga Jual</th>
                        <th class="py-3 px-4">Estimasi Profit</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 text-slate-300">
                    @forelse($products as $p)
                        <tr class="hover:bg-slate-900/50">
                            <td class="py-3 px-4 font-mono font-bold text-indigo-400">
                                {{ $p->provider_code }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="font-bold text-white block">{{ $p->name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $p->category->brand ?? '-' }} • {{ $p->type }}</span>
                            </td>
                            <td class="py-3 px-4 text-slate-400">
                                Rp {{ number_format($p->price_original, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 font-extrabold text-white">
                                Rp {{ number_format($p->price_selling, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4 font-bold text-emerald-400">
                                +Rp {{ number_format($p->price_selling - $p->price_original, 0, ',', '.') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $p->status === 'active' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                                    {{ $p->status }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <button type="button" onclick="openEditProductModal({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->price_original }}, {{ $p->price_selling }}, '{{ $p->status }}', '{{ addslashes($p->description ?? '') }}')" class="p-1.5 text-indigo-400 hover:text-indigo-300 hover:bg-indigo-600/20 rounded-lg inline-flex" title="Edit Harga">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-8 text-slate-500">Tidak ada data produk</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-900/50">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Edit Produk -->
<div id="editProductModal" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="glass-sidebar w-full max-w-md rounded-3xl p-6 shadow-2xl border border-slate-700 relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="font-bold text-sm text-white">Edit Produk & Harga</h3>
            <button onclick="document.getElementById('editProductModal').classList.add('hidden')" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>

        <form id="editProductForm" method="POST" class="mt-4 space-y-3">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Produk</label>
                <input type="text" name="name" id="editProdName" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Harga Modal (Rp)</label>
                    <input type="number" name="price_original" id="editProdOriginal" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Harga Jual (Rp)</label>
                    <input type="number" name="price_selling" id="editProdSelling" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Status</label>
                <select name="status" id="editProdStatus" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                    <option value="active">Active (Tersedia)</option>
                    <option value="empty">Empty (Gangguan/Habis)</option>
                    <option value="inactive">Inactive (Sembunyikan)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Deskripsi Produk</label>
                <textarea name="description" id="editProdDesc" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white"></textarea>
            </div>

            <div class="flex gap-2 pt-2">
                <button type="button" onclick="document.getElementById('editProductModal').classList.add('hidden')" class="w-1/2 bg-slate-800 text-slate-300 py-2.5 rounded-xl text-xs font-semibold">Batal</button>
                <button type="submit" class="w-1/2 bg-indigo-600 hover:bg-indigo-500 text-white py-2.5 rounded-xl text-xs font-bold shadow-md shadow-indigo-600/30">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Produk Baru -->
<div id="addProductModal" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="glass-sidebar w-full max-w-md rounded-3xl p-6 shadow-2xl border border-slate-700 relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="font-bold text-sm text-white">Tambah Produk H2H Baru</h3>
            <button onclick="document.getElementById('addProductModal').classList.add('hidden')" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>

        <form action="{{ route('admin.products.store') }}" method="POST" class="mt-4 space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Kategori / Operator</label>
                <select name="category_id" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->type }})</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Kode Provider</label>
                    <input type="text" name="provider_code" required placeholder="Contoh: TD5" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white uppercase">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Tipe Produk</label>
                    <select name="type" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                        <option value="data">Paket Data</option>
                        <option value="pulsa">Pulsa Reguler</option>
                        <option value="pln">Token PLN</option>
                        <option value="game">Voucher Game</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Nama Produk</label>
                <input type="text" name="name" required placeholder="Telkomsel Flash 5 GB 30 Hari" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Harga Modal (Rp)</label>
                    <input type="number" name="price_original" required placeholder="45000" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Harga Jual (Rp)</label>
                    <input type="number" name="price_selling" required placeholder="47500" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Status</label>
                <select name="status" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                    <option value="active">Active</option>
                    <option value="empty">Empty</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Deskripsi</label>
                <textarea name="description" rows="2" placeholder="Detail kuota utama, masa aktif..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white"></textarea>
            </div>

            <div class="flex gap-2 pt-2">
                <button type="button" onclick="document.getElementById('addProductModal').classList.add('hidden')" class="w-1/2 bg-slate-800 text-slate-300 py-2.5 rounded-xl text-xs font-semibold">Batal</button>
                <button type="submit" class="w-1/2 bg-indigo-600 hover:bg-indigo-500 text-white py-2.5 rounded-xl text-xs font-bold shadow-md shadow-indigo-600/30">Tambah Produk</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openEditProductModal(id, name, original, selling, status, desc) {
        document.getElementById('editProductForm').action = `/admin/products/${id}`;
        document.getElementById('editProdName').value = name;
        document.getElementById('editProdOriginal').value = original;
        document.getElementById('editProdSelling').value = selling;
        document.getElementById('editProdStatus').value = status;
        document.getElementById('editProdDesc').value = desc;
        document.getElementById('editProductModal').classList.remove('hidden');
        lucide.createIcons();
    }
</script>
@endpush
@endsection
