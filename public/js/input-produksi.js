// ============================================================
// INPUT PRODUKSI OPERATOR - JAVASCRIPT (FIXED)
// ============================================================

console.log('🚀 Input Produksi JS loaded');

$(document).ready(function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    console.log('✅ Input Produksi JS initialized');
});

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

// Global error handler
window.addEventListener('error', function(event) {
    console.error('💥 Global error:', event.error);
});

window.addEventListener('unhandledrejection', function(event) {
    console.error('💥 Unhandled promise rejection:', event.reason);
});