@extends('layouts.app')

@section('title', 'Generate QR Code - Digital Production System')

@push('styles')
<link href="{{ asset('css/qrcode.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script src="{{ asset('js/qrcode.js') }}"></script>
@endpush

@section('content-header')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Generate QR Code</h1>
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Sistem Pembuatan QR Code Produk</p>
    </div>
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
        <a href="{{ route('menu') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Menu Utama</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="text-slate-900 dark:text-white font-bold">Generate QR Code</span>
    </nav>
</div>
@endsection

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <div class="flex items-center gap-3 pb-4 border-b border-slate-100 dark:border-slate-800 mb-6">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                <i class="fas fa-qrcode text-lg"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white">Generate QR Code untuk Part</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Pilih part dan tentukan jumlah label yang ingin dicetak</p>
            </div>
        </div>

        <form action="{{ route('qrcode.generate') }}" method="POST" id="qrForm" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="part_id" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Pilih Part <span class="text-rose-500">*</span></label>
                    <select id="part_id" name="part_id" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition">
                        <option value="">Pilih Part...</option>
                        @foreach($parts as $part)
                            <option value="{{ $part->id }}" data-no-parts="{{ $part->no_parts }}" data-nama-part="{{ $part->nama_part }}">
                                {{ $part->no_parts }} - {{ $part->nama_part }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="quantity" class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Jumlah QR Code <span class="text-rose-500">*</span></label>
                    <input type="number" id="quantity" name="quantity" min="1" max="100" value="1" required class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent outline-none transition">
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-1">Maksimal 100 QR code per proses generate</p>
                </div>
            </div>

            <div class="p-4 bg-indigo-50/60 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 rounded-xl flex items-start gap-3">
                <i class="fas fa-info-circle text-indigo-600 dark:text-indigo-400 mt-0.5"></i>
                <div class="text-xs text-indigo-900 dark:text-indigo-200 font-medium leading-relaxed">
                    <strong>Informasi:</strong> QR code akan berisi data part yang dipilih dan dapat di-scan menggunakan aplikasi scan product.
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-xs shadow-md shadow-indigo-500/20 flex items-center gap-2 transition">
                    <i class="fas fa-qrcode"></i>
                    <span>Generate QR Code</span>
                </button>
                <a href="{{ route('menu') }}" class="px-5 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs flex items-center gap-2 transition">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800" id="previewSection" style="display: none;">
            <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-4">Preview QR Code</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="p-6 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200/60 dark:border-slate-700/60 text-center">
                    <div id="qrPreview" class="flex justify-center mb-3"></div>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400" id="qrText"></p>
                </div>
                <div class="p-6 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200/60 dark:border-slate-700/60">
                    <h5 class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-3">Informasi Part</h5>
                    <div id="partInfo">
                        <p class="text-xs text-slate-400 dark:text-slate-500">Pilih part untuk melihat informasi</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection