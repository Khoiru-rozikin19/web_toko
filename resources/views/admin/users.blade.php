@extends('layouts.admin')

@section('title', 'Kelola Pengguna')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-white tracking-tight">Manajemen Pengguna</h1>
            <p class="text-xs sm:text-sm text-slate-400">Kelola akun pengguna dan sesuaikan saldo web</p>
        </div>

        <!-- Search -->
        <form action="{{ route('admin.users') }}" method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/HP/email..." class="bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-3.5 py-2 rounded-xl text-xs font-semibold">Cari</button>
        </form>
    </div>

    <div class="glass-sidebar rounded-2xl overflow-hidden border border-slate-800 shadow-xl">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs sm:text-sm">
                <thead class="bg-slate-900 text-slate-400 uppercase text-[11px] font-bold border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4">Nama / Role</th>
                        <th class="py-3 px-4">Kontak (HP / Email)</th>
                        <th class="py-3 px-4">Saldo Web</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Terdaftar</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 text-slate-300">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-900/50">
                            <td class="py-3.5 px-4">
                                <span class="font-bold text-white block">{{ $user->name }}</span>
                                <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded {{ $user->isAdmin() ? 'bg-amber-500/20 text-amber-300' : 'bg-slate-800 text-slate-400' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                <span class="block text-slate-200">{{ $user->phone ?: '-' }}</span>
                                <span class="text-[11px] text-slate-400">{{ $user->email }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-extrabold text-emerald-400 text-sm">
                                Rp {{ number_format($user->balance, 0, ',', '.') }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $user->is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 text-xs">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button type="button" onclick="openBalanceModal({{ $user->id }}, '{{ addslashes($user->name) }}', {{ $user->balance }})" class="bg-indigo-600/20 hover:bg-indigo-600 text-indigo-300 hover:text-white px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1 mx-auto border border-indigo-500/30">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Ubah Saldo
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-8 text-slate-500">Tidak ada data pengguna</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-900/50">
                {{ $users->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Ubah Saldo -->
<div id="balanceModal" class="fixed inset-0 z-50 hidden bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="glass-sidebar w-full max-w-md rounded-3xl p-6 shadow-2xl border border-slate-700 relative">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="font-bold text-sm text-white">Sesuaikan Saldo Pengguna</h3>
            <button onclick="closeBalanceModal()" class="text-slate-400 hover:text-white"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>

        <form id="balanceForm" method="POST" class="mt-4 space-y-4">
            @csrf
            <div>
                <span class="text-xs text-slate-400 block">Pengguna:</span>
                <span id="modalUserName" class="font-bold text-white text-sm block">User</span>
                <span class="text-xs text-emerald-400 font-bold block mt-0.5">Saldo Sekarang: <span id="modalCurrentBalance">Rp 0</span></span>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Aksi</label>
                <select name="action" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                    <option value="add">Tambah Saldo (+)</option>
                    <option value="subtract">Kurangi Saldo (-)</option>
                    <option value="set">Setel Ulang Saldo (=)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Nominal (Rp)</label>
                <input type="number" name="amount" min="0" required placeholder="50000" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Alasan / Catatan</label>
                <input type="text" name="notes" required placeholder="Penyesuaian saldo manual / bonus" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
            </div>

            <div class="flex gap-2 pt-2">
                <button type="button" onclick="closeBalanceModal()" class="w-1/2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-semibold py-2.5 rounded-xl text-xs">Batal</button>
                <button type="submit" class="w-1/2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-2.5 rounded-xl text-xs shadow-md shadow-indigo-600/30">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openBalanceModal(userId, userName, currentBalance) {
        document.getElementById('balanceForm').action = `/admin/users/${userId}/balance`;
        document.getElementById('modalUserName').innerText = userName;
        document.getElementById('modalCurrentBalance').innerText = 'Rp ' + Number(currentBalance).toLocaleString('id-ID');
        document.getElementById('balanceModal').classList.remove('hidden');
        lucide.createIcons();
    }
    function closeBalanceModal() {
        document.getElementById('balanceModal').classList.add('hidden');
    }
</script>
@endpush
@endsection
