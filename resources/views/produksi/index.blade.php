@extends('layouts.app')

@section('title', 'Scan QR Code - Digital Production System')

@push('styles')
<link href="{{ asset('css/produksi.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script src="{{ asset('js/qrcode-lib.js') }}"></script>
<script src="{{ asset('js/produksi.js') }}"></script>
@endpush

@section('content-header')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Scan QR Code</h1>
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Pemindaian QR Code Produk untuk Quality Control & Monitoring</p>
    </div>
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
        <a href="{{ route('menu') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Menu Utama</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="text-slate-900 dark:text-white font-bold">Scan QR Code</span>
    </nav>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200/80 dark:border-slate-800 shadow-sm">
        <div class="scan-section">
            <div class="max-w-2xl mx-auto space-y-6">
                <div class="p-4 bg-indigo-50/60 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 rounded-xl text-center">
                    <i class="fas fa-info-circle text-indigo-600 dark:text-indigo-400 text-lg mb-1"></i>
                    <p class="text-xs text-indigo-900 dark:text-indigo-200 font-medium">
                        <strong>Petunjuk:</strong> Scan QR code yang telah di-generate dari sistem untuk memproses produk.
                    </p>
                </div>

                <div class="p-8 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-200/60 dark:border-slate-700/60 text-center" id="scanArea">
                    <div class="w-20 h-20 rounded-full bg-indigo-100 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-qrcode text-3xl"></i>
                    </div>
                    <h4 class="text-lg font-bold text-slate-900 dark:text-white mb-1">
                        Scan QR Code
                    </h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-1">Klik tombol di bawah untuk membuka kamera scanner</p>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mb-6">Pastikan QR code terlihat jelas di dalam frame</p>
                    <button class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-extrabold rounded-xl shadow-lg shadow-indigo-500/25 inline-flex items-center gap-2 transition" id="startScanBtn">
                        <i class="fas fa-camera text-sm"></i>
                        <span>Mulai Scan Kamera</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Loading State -->
        <div class="loading hidden py-8 text-center" id="loading">
            <div class="w-10 h-10 border-4 border-indigo-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
            <p class="text-xs font-semibold text-slate-600 dark:text-slate-400">Memproses scan...</p>
        </div>

        <!-- Product Information -->
        <div class="product-info" id="productInfo"></div>

        <!-- Quality Check -->
        <div class="quality-check hidden mt-6 pt-6 border-t border-slate-100 dark:border-slate-800" id="qualityCheck">
            <h4 class="text-sm font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="fas fa-check-circle text-emerald-500"></i>
                Quality Check
            </h4>
            
            <div class="grid grid-cols-3 gap-4">
                <button class="quality-btn good p-4 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-black text-xs shadow-md shadow-emerald-500/20 flex flex-col items-center justify-center gap-2 transition" data-quality="good">
                    <i class="fas fa-thumbs-up text-lg"></i>
                    <span>GOOD</span>
                </button>
                <button class="quality-btn ng p-4 bg-rose-500 hover:bg-rose-600 text-white rounded-xl font-black text-xs shadow-md shadow-rose-500/20 flex flex-col items-center justify-center gap-2 transition" data-quality="ng">
                    <i class="fas fa-times-circle text-lg"></i>
                    <span>NG</span>
                </button>
                <button class="quality-btn retry p-4 bg-amber-500 hover:bg-amber-600 text-white rounded-xl font-black text-xs shadow-md shadow-amber-500/20 flex flex-col items-center justify-center gap-2 transition" data-quality="retry">
                    <i class="fas fa-redo text-lg"></i>
                    <span>RETRY</span>
                </button>
            </div>
        </div>

        <!-- Scan History -->
        <div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800">
            <h5 class="text-sm font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2">
                <i class="fas fa-history text-indigo-600 dark:text-indigo-400"></i>
                Riwayat Scan Terakhir
            </h5>
            <div id="scanHistory">
                <div class="text-center text-slate-400 dark:text-slate-500 py-8 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-dashed border-slate-200 dark:border-slate-700">
                    <i class="fas fa-history text-2xl mb-2"></i>
                    <p class="text-xs">Belum ada riwayat scan</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Scanner -->
<div class="modal fade" id="scannerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content dark:bg-slate-900 dark:text-white border-0 shadow-2xl rounded-2xl overflow-hidden">
            <div class="modal-header bg-indigo-600 text-white px-6 py-4">
                <h5 class="modal-title text-sm font-bold flex items-center gap-2">
                    <i class="fas fa-qrcode"></i>
                    Scan QR Code Product
                </h5>
                <button type="button" class="close text-white opacity-80 hover:opacity-100" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body p-6">
                <div class="text-center space-y-4">
                    <div class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/50 rounded-xl text-xs text-amber-900 dark:text-amber-200">
                        <i class="fas fa-exclamation-triangle text-amber-500 mr-1"></i>
                        <strong>Penting:</strong> Hanya QR code yang di-generate dari sistem yang dapat di-scan
                    </div>
                    
                    <div id="cameraContainer" class="relative rounded-xl overflow-hidden bg-black">
                        <div id="cameraLoading" class="text-center py-8 text-white" style="display: none;">
                            <div class="w-8 h-8 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                            <p class="text-xs">Menginisialisasi kamera...</p>
                        </div>
                        <div id="interactive" class="viewport min-h-[300px]"></div>
                    </div>
                    
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        <i class="fas fa-lightbulb text-amber-400 mr-1"></i>
                        Arahkan kamera ke QR code untuk scan otomatis
                    </div>
                </div>
            </div>
            <div class="modal-footer px-6 py-4 bg-slate-50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800">
                <button type="button" class="px-4 py-2 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-bold text-xs" data-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection