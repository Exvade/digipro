// Mesin Index Page JavaScript - Modernized for Tailwind & DataTables

const MESIN_REASONS = {
    'maintenance': [
        { value: 'Mesin Error', text: 'Mesin Error' },
        { value: 'Leaking / nozzle meleleh', text: 'Leaking / nozzle meleleh' },
        { value: 'Nozel tersumbat', text: 'Nozel tersumbat' },
        { value: 'Crane / Hoist Error', text: 'Crane / Hoist Error' },
        { value: 'Robot Error', text: 'Robot Error' },
        { value: 'MTC Error', text: 'MTC Error' },
        { value: 'Control Hot Runner Error', text: 'Control Hot Runner Error' },
        { value: 'Control Valve Gate Error', text: 'Control Valve Gate Error' },
        { value: 'Cooling Tower Mati', text: 'Cooling Tower Mati' },
        { value: 'Chiller Mati', text: 'Chiller Mati' },
        { value: 'Jig Proses Trouble', text: 'Jig Proses Trouble' },
        { value: 'Mold Trouble', text: 'Mold Trouble' },
        { value: 'Trial Material', text: 'Trial Material' },
        { value: 'Trial New Model / New Mold', text: 'Trial New Model / New Mold' },
        { value: 'Trial Over', text: 'Trial Over' }
    ],
    'stop': [
        { value: 'Cuci Barel', text: 'Cuci Barel' },
        { value: 'Material Habis', text: 'Material Habis' },
        { value: 'Telat Supply', text: 'Telat Supply' },
        { value: 'Kontaminasi Material Lain', text: 'Kontaminasi Material Lain' },
        { value: 'Drying Ulang Material', text: 'Drying Ulang Material' },
        { value: 'Sarana Packing Habis', text: 'Sarana Packing Habis' },
        { value: 'Sarana Packing Telat Supply', text: 'Sarana Packing Telat Supply' },
        { value: 'Setup Mold', text: 'Setup Mold' },
        { value: 'Set Down Mold', text: 'Set Down Mold' },
        { value: 'Setting Parameter', text: 'Setting Parameter' },
        { value: 'Dokumen Standar Tidak Tersedia', text: 'Dokumen Standar Tidak Tersedia' },
        { value: 'Tunggu Judgment Quality', text: 'Tunggu Judgment Quality' },
        { value: 'Tidak Ada Operator', text: 'Tidak Ada Operator' },
        { value: 'Man Power On The Job Training', text: 'Man Power On The Job Training' },
        { value: 'Man Power 5R', text: 'Man Power 5R' }
    ],
    'offline': [
        { value: 'Tidak ada jadwal produksi', text: 'Tidak ada jadwal produksi' }
    ]
};

$(document).ready(function() {
    // Initialize DataTable
    if ($.fn.dataTable.isDataTable('#mesinTable')) {
        $('#mesinTable').DataTable().destroy();
    }

    const mesinTable = $('#mesinTable').DataTable({
        responsive: false,
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        order: [[0, 'asc']],
        language: {
            "emptyTable": "Tidak ada data mesin yang tersedia",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ mesin",
            "infoEmpty": "Menampilkan 0 mesin",
            "infoFiltered": "(disaring dari _MAX_ total mesin)",
            "lengthMenu": "Tampilkan _MENU_ mesin",
            "loadingRecords": "Memuat data...",
            "processing": "Sedang memproses...",
            "search": "Cari:",
            "searchPlaceholder": "Cari mesin, brand, tonase...",
            "zeroRecords": "Tidak ditemukan mesin yang sesuai",
            "paginate": {
                "first": '<i class="fas fa-angle-double-left text-xs"></i>',
                "last": '<i class="fas fa-angle-double-right text-xs"></i>',
                "next": '<i class="fas fa-angle-right text-xs"></i>',
                "previous": '<i class="fas fa-angle-left text-xs"></i>'
            }
        },
        dom: "<'flex flex-col sm:flex-row items-center justify-between gap-3 mb-4'<'w-full sm:w-auto'l><'w-full sm:w-auto'f>>" +
             "<'overflow-x-auto custom-scrollbar'tr>" +
             "<'flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 pt-3 border-t border-slate-100 dark:border-slate-800'<'text-xs font-medium text-slate-500 dark:text-slate-400'i><'flex items-center gap-1'p>>",
        columnDefs: [
            {
                targets: -1, // Aksi column
                orderable: false,
                searchable: false,
                className: 'text-center'
            },
            {
                targets: [0, 3, 4], // No, Tonase, Status
                className: 'text-center'
            }
        ]
    });

    // ==========================================================================
    // KPI Cards: Counter Animation & Interactive Table Filtering
    // ==========================================================================
    initKpiCounters();
    initKpiCardFilters(mesinTable);

    function initKpiCounters() {
        $('.kpi-counter').each(function () {
            var $this = $(this);
            var isDecimal = $this.data('decimal') === true || $this.data('decimal') === 'true';
            var target = parseFloat($this.data('target')) || 0;
            if (target === 0) {
                $this.text(isDecimal ? '0.00' : '0');
                return;
            }

            var duration = 750;
            var start = 0;
            var startTime = performance.now();

            function updateCounter(currentTime) {
                var elapsed = currentTime - startTime;
                var progress = Math.min(elapsed / duration, 1);
                var ease = 1 - (1 - progress) * (1 - progress); // easeOutQuad
                var currentVal = start + (target - start) * ease;

                if (isDecimal) {
                    $this.text(currentVal.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                } else {
                    $this.text(Math.floor(currentVal).toLocaleString());
                }

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    if (isDecimal) {
                        $this.text(target.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                    } else {
                        $this.text(target.toLocaleString());
                    }
                }
            }

            requestAnimationFrame(updateCounter);
        });
    }

    function initKpiCardFilters(table) {
        $('.kpi-card').on('click', function() {
            var $card = $(this);
            var filter = $card.data('filter');
            var isAlreadyActive = $card.hasClass('active');

            // Reset active states
            $('.kpi-card').removeClass('active ring-2 ring-indigo-500/20');
            $('.kpi-card .kpi-active-indicator').addClass('hidden');

            if (isAlreadyActive && filter !== 'all') {
                // Deselect: return to default 'all' filter
                $('.kpi-card[data-filter="all"]').addClass('active ring-2 ring-indigo-500/20');
                $('.kpi-card[data-filter="all"] .kpi-active-indicator').removeClass('hidden');
                table.column(4).search('').draw();
                return;
            }

            $card.addClass('active ring-2 ring-indigo-500/20');
            $card.find('.kpi-active-indicator').removeClass('hidden');

            if (filter === 'all') {
                table.column(4).search('').draw();
            } else if (filter === 'running') {
                table.column(4).search('Running').draw();
            } else if (filter === 'stop') {
                table.column(4).search('Stand By').draw();
            } else if (filter === 'breakdown-offline') {
                // Regex search for breakdown or off
                table.column(4).search('Breakdown|Off', true, false).draw();
            }
        });
    }

    // Event listener for create button
    $(document).on('click', '.btn-create-mesin', function() {
        openCreateModal();
    });

    // Event listener for edit button
    $(document).on('click', '.btn-edit-mesin', function() {
        const mesinId = $(this).data('mesin-id');
        openEditModal(mesinId);
    });

    // Event listener for view detail button
    $(document).on('click', '.btn-view-mesin', function() {
        const data = {
            id: $(this).data('mesin-id'),
            nama: $(this).data('mesin-nama'),
            brand: $(this).data('mesin-brand'),
            tonase: $(this).data('mesin-tonase'),
            status: $(this).data('mesin-status'),
            statusText: $(this).data('mesin-status-text'),
            reason: $(this).data('mesin-reason'),
            keterangan: $(this).data('mesin-keterangan')
        };
        openViewModal(data);
    });

    // Event listener for delete button
    $(document).on('click', '.btn-delete-mesin', function() {
        const mesinId = $(this).data('mesin-id');
        const mesinName = $(this).data('mesin-name');
        deleteMesin(mesinId, mesinName);
    });

    // Event delegation for dynamic reasons in Create & Edit forms
    $(document).on('change', '#status', function() {
        const form = $(this).closest('form');
        handleStatusChange(form, $(this).val());
    });

    // AJAX Submission for Create Mesin Form
    $(document).on('submit', '#addForm', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalHtml = submitBtn.html();

        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1.5"></i>Menyimpan...');
        clearFormErrors(form);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                $('#addModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message || 'Mesin berhasil ditambahkan',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                handleAjaxErrors(xhr, form);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalHtml);
            }
        });
    });

    // AJAX Submission for Edit Mesin Form
    $(document).on('submit', '#editMesinForm', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalHtml = submitBtn.html();

        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1.5"></i>Menyimpan...');
        clearFormErrors(form);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                $('#editModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message || 'Data mesin berhasil diperbarui.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                handleAjaxErrors(xhr, form);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalHtml);
            }
        });
    });
});

// Function to handle status change and populate reasons
function handleStatusChange(form, status, selectedReason = '') {
    const $wrapper = form.find('#stop_reason_wrapper');
    const $select = form.find('#stop_reason');

    if (MESIN_REASONS[status]) {
        $select.html('<option value="">-- Pilih Alasan / Pesan --</option>');
        MESIN_REASONS[status].forEach(function(item) {
            const isSelected = item.value === selectedReason ? 'selected' : '';
            $select.append(`<option value="${item.value}" ${isSelected}>${item.text}</option>`);
        });
        $wrapper.slideDown(200);
        $select.prop('required', true);
    } else {
        $wrapper.slideUp(200);
        $select.prop('required', false).val('');
    }
}

// Function to open create modal
function openCreateModal() {
    $('#addModal').modal('show');
    
    $.ajax({
        url: window.createModalUrl || '/mesin/create',
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            $('#addModalBody').html(`
                <div class="text-center py-10">
                    <div class="inline-block w-8 h-8 border-3 border-indigo-600 border-t-transparent rounded-full animate-spin" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Memuat formulir tambah mesin...</p>
                </div>
            `);
        },
        success: function(response) {
            $('#addModalBody').html(response);
            const initialStatus = $('#addForm #status').val();
            if (initialStatus) {
                handleStatusChange($('#addForm'), initialStatus);
            }
        },
        error: function(xhr) {
            let errorMessage = 'Terjadi kesalahan saat memuat formulir.';
            if (xhr.status === 404) errorMessage = 'Rute tidak ditemukan.';
            else if (xhr.status === 401) errorMessage = 'Sesi Anda telah habis. Silakan login kembali.';
            else if (xhr.status === 403) errorMessage = 'Anda tidak memiliki hak akses untuk menambah mesin.';
            
            $('#addModalBody').html(`
                <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-medium flex items-center gap-2">
                    <i class="fas fa-exclamation-circle text-sm flex-shrink-0"></i>
                    <span>${errorMessage}</span>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" data-dismiss="modal">
                        Tutup
                    </button>
                </div>
            `);
        }
    });
}

// Function to open edit modal
function openEditModal(mesinId) {
    $('#editModal').modal('show');
    
    $.ajax({
        url: '/mesin/' + mesinId + '/edit',
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function() {
            $('#editModalBody').html(`
                <div class="text-center py-10">
                    <div class="inline-block w-8 h-8 border-3 border-indigo-600 border-t-transparent rounded-full animate-spin" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3 text-xs font-semibold text-slate-500 dark:text-slate-400">Memuat data mesin...</p>
                </div>
            `);
        },
        success: function(response) {
            $('#editModalBody').html(response);
            const form = $('#editMesinForm');
            const initialStatus = form.find('#status').val();
            const currentReason = form.data('initial-reason') || form.find('#initial_stop_reason').val() || '';
            if (initialStatus) {
                handleStatusChange(form, initialStatus, currentReason);
            }
        },
        error: function(xhr) {
            let errorMessage = 'Terjadi kesalahan saat memuat data.';
            if (xhr.status === 404) errorMessage = 'Data mesin tidak ditemukan.';
            else if (xhr.status === 401) errorMessage = 'Sesi Anda telah habis. Silakan login kembali.';
            else if (xhr.status === 403) errorMessage = 'Anda tidak memiliki hak akses untuk mengedit mesin ini.';
            
            $('#editModalBody').html(`
                <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 text-xs font-medium flex items-center gap-2">
                    <i class="fas fa-exclamation-circle text-sm flex-shrink-0"></i>
                    <span>${errorMessage}</span>
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="button" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800" data-dismiss="modal">
                        Tutup
                    </button>
                </div>
            `);
        }
    });
}

// Function to open view modal
function openViewModal(data) {
    $('#view_nama_mesin').text(data.nama || '-');
    $('#view_brand_mesin').text(data.brand || '-');
    $('#view_tonase_mesin').text(data.tonase ? `${data.tonase} Ton` : '-');
    
    // Status badge formatting
    const $statusPill = $('#view_status_pill');
    let statusBadgeHtml = '';
    
    if (data.status === 'running') {
        statusBadgeHtml = `
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-300 border border-emerald-200/80 dark:border-emerald-800/60">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span>RUNNING</span>
            </span>
        `;
    } else if (data.status === 'maintenance') {
        statusBadgeHtml = `
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-700 dark:bg-rose-950/70 dark:text-rose-300 border border-rose-200/80 dark:border-rose-800/60">
                <i class="fas fa-wrench text-[10px]"></i>
                <span>BREAKDOWN</span>
            </span>
        `;
    } else if (data.status === 'stop') {
        statusBadgeHtml = `
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/70 dark:text-amber-300 border border-amber-200/80 dark:border-amber-800/60">
                <i class="fas fa-pause text-[10px]"></i>
                <span>STAND BY</span>
            </span>
        `;
    } else {
        statusBadgeHtml = `
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                <i class="fas fa-power-off text-[10px]"></i>
                <span>OFFLINE</span>
            </span>
        `;
    }
    
    $statusPill.html(statusBadgeHtml);
    
    // Stop reason
    if (data.reason && data.reason.trim() !== '') {
        $('#view_reason_wrapper').removeClass('hidden');
        $('#view_stop_reason').text(data.reason);
    } else {
        $('#view_reason_wrapper').addClass('hidden');
    }
    
    $('#view_keterangan').text(data.keterangan && data.keterangan.trim() !== '' ? data.keterangan : 'Tidak ada keterangan tambahan.');
    
    $('#viewMesinModal').modal('show');
}

// Function to delete mesin
function deleteMesin(mesinId, mesinName) {
    Swal.fire({
        title: 'Hapus Data Mesin?',
        html: `Apakah Anda yakin ingin menghapus mesin <b>"${mesinName}"</b>? Tindakan ini tidak dapat dibatalkan.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#64748b',
        confirmButtonText: '<i class="fas fa-trash mr-1.5"></i>Ya, Hapus Mesin',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        focusCancel: true,
        customClass: {
            popup: 'rounded-2xl dark:bg-slate-900 dark:text-white border border-slate-200 dark:border-slate-800',
            confirmButton: 'rounded-xl px-4 py-2 font-bold text-xs',
            cancelButton: 'rounded-xl px-4 py-2 font-bold text-xs'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/mesin/' + mesinId,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Menghapus...',
                        text: 'Mohon tunggu sebentar',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: response.message || 'Data mesin berhasil dihapus.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Gagal', response.message || 'Terjadi kesalahan.', 'error');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Terjadi kesalahan saat menghapus data.';
                    if (xhr.status === 404) errorMessage = 'Data mesin tidak ditemukan.';
                    else if (xhr.status === 401) errorMessage = 'Sesi Anda telah habis. Silakan login kembali.';
                    else if (xhr.status === 403) errorMessage = 'Anda tidak memiliki hak akses untuk menghapus data ini.';
                    else if (xhr.responseJSON && xhr.responseJSON.message) errorMessage = xhr.responseJSON.message;

                    Swal.fire('Error', errorMessage, 'error');
                }
            });
        }
    });
}

// Helpers for validation error handling
function clearFormErrors(form) {
    form.find('.is-invalid').removeClass('is-invalid');
    form.find('.invalid-feedback').remove();
}

function handleAjaxErrors(xhr, form) {
    let errorMessage = 'Terjadi kesalahan saat menyimpan data.';
    
    if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
        const errors = xhr.responseJSON.errors;
        $.each(errors, function(field, messages) {
            const input = form.find(`[name="${field}"]`);
            input.addClass('is-invalid');
            input.closest('div').append(`<div class="invalid-feedback">${messages[0]}</div>`);
        });
        errorMessage = Object.values(errors).flat().join('<br>');
    } else if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMessage = xhr.responseJSON.message;
    }
    
    Swal.fire({
        icon: 'error',
        title: 'Validasi Gagal',
        html: errorMessage,
        customClass: {
            popup: 'rounded-2xl dark:bg-slate-900 dark:text-white border border-slate-200 dark:border-slate-800'
        }
    });
}
