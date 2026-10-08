@extends('layouts.app')

@section('title', 'QR Code Generated - Digital Production System')

@push('styles')
<style>
    .qr-container {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1.25rem;
        text-align: center;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }
    .dark .qr-container {
        background: #1e293b;
        border-color: #334155;
        color: #f8fafc;
    }
    .qr-code-canvas {
        margin: 10px auto;
        border: 2px solid #6366f1;
        border-radius: 0.5rem;
        padding: 10px;
        background: white;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 150px;
    }
    @media print {
        body * { visibility: hidden; }
        .print-area, .print-area * { visibility: visible; }
        .print-area { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.3/qrcode.min.js"></script>
<script src="{{ asset('js/qrcode.js') }}"></script>
<script>
    var qrCodes = @json($qrCodes ?? []);
    var partData = @json($part ?? null);

    $(document).ready(function() {
        if (qrCodes.length > 0) {
            qrCodes.forEach(function(qrCode, index) {
                try {
                    var qrElement = document.getElementById('qrCode' + index);
                    var qrValue = qrCode.qr_text || qrCode.unique_id || JSON.stringify(qrCode.data);
                    
                    if (typeof QRCode !== 'undefined') {
                        new QRCode(qrElement, {
                            text: qrValue,
                            width: 120,
                            height: 120,
                            colorDark: "#000000",
                            colorLight: "#ffffff",
                            correctLevel: QRCode.CorrectLevel.M
                        });
                    } else if (typeof qrcode !== 'undefined') {
                        var canvas = document.createElement('canvas');
                        qrElement.appendChild(canvas);
                        qrcode.toCanvas(canvas, qrValue, { width: 120, margin: 2 });
                    }
                } catch (error) {
                    console.error('Error generating QR code:', error);
                }
            });
        }

        $('.print-all-btn').click(function() { window.print(); });

        $('.print-btn').click(function() {
            var printWindow = window.open('', '', 'height=600,width=800');
            var qrContainer = $(this).closest('.qr-container').clone();
            qrContainer.find('.no-print').remove();
            printWindow.document.write('<html><head><title>Print QR Code</title></head><body style="text-align:center;padding:20px;">');
            printWindow.document.write(qrContainer.html());
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        });

        $('.download-btn').click(function() {
            var index = $(this).data('index');
            var canvas = document.querySelector('#qrCode' + index + ' canvas');
            if (canvas) {
                var link = document.createElement('a');
                link.download = 'qr_code_' + qrCodes[index].unique_id + '.png';
                link.href = canvas.toDataURL();
                link.click();
            }
        });

        $('#downloadAll').click(function() {
            qrCodes.forEach(function(qrCode, index) {
                var canvas = document.querySelector('#qrCode' + index + ' canvas');
                if (canvas) {
                    var link = document.createElement('a');
                    link.download = 'qr_code_' + qrCode.unique_id + '.png';
                    link.href = canvas.toDataURL();
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            });
        });
    });
</script>
@endpush

@section('content-header')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
    <div>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">QR Code Generated</h1>
        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mt-1">Hasil generate QR code untuk part produk</p>
    </div>
    <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
        <a href="{{ route('menu') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Menu Utama</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <a href="{{ route('qrcode.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Generate QR</a>
        <i class="fas fa-chevron-right text-[10px] text-slate-400"></i>
        <span class="text-slate-900 dark:text-white font-bold">Results</span>
    </nav>
</div>
@endsection

@section('content')
<div class="space-y-6">
    @if(isset($part) && isset($qrCodes))
    
    <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 rounded-2xl flex items-center justify-between no-print">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-500 text-white flex items-center justify-center font-bold">
                <i class="fas fa-check text-lg"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold text-emerald-950 dark:text-emerald-200">Success!</h4>
                <p class="text-xs text-emerald-800 dark:text-emerald-300">
                    Berhasil generate {{ count($qrCodes) }} QR Code untuk part <strong>{{ $part->no_parts }} - {{ $part->nama_part }}</strong>
                </p>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 no-print">
        <div class="flex items-center gap-2">
            <button type="button" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl shadow-md shadow-indigo-500/20 flex items-center gap-2 transition print-all-btn">
                <i class="fas fa-print"></i>
                <span>Print Semua</span>
            </button>
            <button type="button" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md shadow-emerald-500/20 flex items-center gap-2 transition" id="downloadAll">
                <i class="fas fa-download"></i>
                <span>Download Semua</span>
            </button>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('qrcode.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl flex items-center gap-2 transition">
                <i class="fas fa-plus"></i>
                <span>Generate Lagi</span>
            </a>
            <a href="{{ route('menu') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl flex items-center gap-2 transition">
                <i class="fas fa-home"></i>
                <span>Menu Utama</span>
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-slate-200/80 dark:border-slate-800 no-print">
        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2 pb-4 border-b border-slate-100 dark:border-slate-800 mb-4">
            <i class="fas fa-info-circle text-indigo-600 dark:text-indigo-400"></i>
            Informasi Part
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <div class="space-y-2">
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">ID Part:</span><span class="font-bold text-slate-800 dark:text-slate-200">{{ $part->id }}</span></div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">No. Parts:</span><span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 font-bold rounded">{{ $part->no_parts }}</span></div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">Nama Part:</span><span class="font-bold text-slate-800 dark:text-slate-200">{{ $part->nama_part }}</span></div>
                <div class="flex justify-between py-1"><span class="text-slate-400">Type:</span><span class="font-bold text-slate-800 dark:text-slate-200">{{ $part->type }}</span></div>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">Quantity:</span><span class="font-bold text-slate-800 dark:text-slate-200">{{ $part->quantity }}</span></div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">Jumlah Generated:</span><span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 font-bold rounded">{{ count($qrCodes) }} QR Code</span></div>
                <div class="flex justify-between py-1 border-b border-slate-100 dark:border-slate-800"><span class="text-slate-400">Waktu Generate:</span><span class="font-bold text-slate-800 dark:text-slate-200">{{ date('d/m/Y H:i:s') }}</span></div>
                <div class="flex justify-between py-1"><span class="text-slate-400">Generated By:</span><span class="font-bold text-slate-800 dark:text-slate-200">{{ auth()->user()->nama ?? 'System' }}</span></div>
            </div>
        </div>
    </div>

    <div class="print-area">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="qrCodesGrid">
            @foreach ($qrCodes as $index => $qrCode)
                <div class="qr-container">
                    <div class="mb-2">
                        <h6 class="text-xs font-bold text-slate-900 dark:text-white">{{ $part->no_parts }}</h6>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate">{{ $part->nama_part }}</p>
                    </div>

                    <div class="qr-code-canvas" id="qrCode{{ $index }}"></div>

                    <div class="mt-2 text-[10px] text-slate-400 space-y-0.5">
                        <div class="font-mono">ID: {{ $qrCode['unique_id'] }}</div>
                        <div>{{ $qrCode['sequence'] }}/{{ count($qrCodes) }}</div>
                    </div>

                    <div class="flex items-center justify-center gap-2 mt-3 pt-2 border-t border-slate-100 dark:border-slate-800 no-print">
                        <button class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 flex items-center justify-center transition download-btn" data-index="{{ $index }}" data-unique-id="{{ $qrCode['unique_id'] }}" title="Download">
                            <i class="fas fa-download text-xs"></i>
                        </button>
                        <button class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 flex items-center justify-center transition print-btn" data-index="{{ $index }}" title="Print">
                            <i class="fas fa-print text-xs"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @else

    <div class="p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 rounded-2xl flex items-center gap-3 text-xs text-rose-800 dark:text-rose-200">
        <i class="fas fa-exclamation-triangle text-rose-500 text-lg"></i>
        <div>
            <strong>Error!</strong> Data part atau QR Code tidak ditemukan. Silakan <a href="{{ route('qrcode.index') }}" class="underline font-bold">generate ulang</a>.
        </div>
    </div>

    @endif
</div>
@endsection