/* Planning History Page JavaScript */

$(document).ready(function() {
    // Inisialisasi DataTable untuk history table
    var historyTable;
    if ($('#historyTable').length > 0) {
        // Destroy existing DataTable instance if it exists
        if ($.fn.dataTable.isDataTable('#historyTable')) {
            $('#historyTable').DataTable().destroy();
        }
        
        // Initialize DataTable
        // DataTables akan otomatis menangani empty state dengan benar
        historyTable = $('#historyTable').DataTable({
            "paging": false,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "destroy": true,
            "order": [],
            "language": {
                "emptyTable": "Belum ada data planning produksi untuk tanggal yang dipilih",
                "zeroRecords": "Tidak ada data yang sesuai dengan filter"
            }
        });
    }

    // =========================================
    // Tab Filter Bagian (All, Inject, Assy) - History Page
    // =========================================
    $('#bagianTabsHistory .nav-link').on('click', function(e) {
        e.preventDefault();
        const bagian = $(this).data('bagian');
        
        if (historyTable) {
            if (bagian === 'all') {
                // Show all rows
                historyTable.search('').columns().search('').draw();
            } else if (bagian === 'inject') {
                // Filter untuk inject (badge-primary)
                historyTable.column(3).search('Inject', true, false).draw();
            } else if (bagian === 'assy') {
                // Filter untuk assy (badge-info / Assembly)
                historyTable.column(3).search('Assembly', true, false).draw();
            }
        }
        
        // Update active tab
        $('#bagianTabsHistory .nav-link').removeClass('active');
        $(this).addClass('active');
    });

    // Modal Cetak Functions
    // Toggle custom date input
    $('#tanggalFilter').change(function() {
        if ($(this).val() === 'custom') {
            $('#customDateGroup').show();
        } else {
            $('#customDateGroup').hide();
        }
    });
    
    // Pesan sukses flash dari Laravel
    if (typeof window.successMessage !== 'undefined' && window.successMessage) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: window.successMessage,
            showConfirmButton: false,
            timer: 2000
        });
    }
});

function submitCetak() {
    const tanggalFilter = document.getElementById('tanggalFilter').value;
    const customDate = document.getElementById('customDate').value;
    const bagianFilter = document.getElementById('bagianFilter').value;
    const baseUrl = '/planning/cetak';
    
    let tanggalValue = '';
    
    // Set parameter tanggal
    if (tanggalFilter === 'custom') {
        if (!customDate) {
            alert('Silakan pilih tanggal spesifik!');
            return;
        }
        tanggalValue = customDate;
    } else {
        tanggalValue = tanggalFilter;
    }
    
    // Buat URL dengan parameter yang benar
    const url = baseUrl + "?tanggal=" + encodeURIComponent(tanggalValue) + "&bagian=" + encodeURIComponent(bagianFilter);
    
    // Buka di tab baru
    window.open(url, '_blank');
    
    // Tutup modal
    $('#modalCetak').modal('hide');
}

