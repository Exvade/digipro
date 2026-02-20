/**
 * Ploting Page JavaScript
 * Menggabungkan semua fungsi untuk halaman ploting
 */

// Initialize runtime calculators for on_progress plotings
function initializeRuntimeCalculators() {
    document.querySelectorAll('[id^="runtime-"]').forEach(function(element) {
        const plotingId = element.getAttribute('data-ploting-id');
        const startTimeStr = element.getAttribute('data-start-time');
        const shiftEndTimeStr = element.getAttribute('data-shift-end-time');
        
        if (!startTimeStr) return;
        
        const startTime = new Date(startTimeStr);
        const shiftEndTime = shiftEndTimeStr ? new Date(shiftEndTimeStr) : null;
        const runtimeElement = element;
        let intervalId = null;
        let isShiftExpired = false;
        
        function updateRuntime() {
            const now = new Date();
            
            // Cek status ploting dari server untuk melihat apakah sudah selesai (target tercapai atau shift berakhir)
            // Panggil API runtime untuk mendapatkan status terbaru
            const routeRuntime = window.Laravel.routes.plotingRuntime;
            const csrfToken = window.Laravel.routes.csrfToken;
            
            // Cek status ploting secara berkala (setiap 10 detik) untuk mendeteksi auto-finish
            if (!window.lastRuntimeCheck || (now - window.lastRuntimeCheck) > 10000) {
                window.lastRuntimeCheck = now;
                
                $.ajax({
                    url: `${routeRuntime}${plotingId}/runtime`,
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    success: function(response) {
                        // Jika target sudah tercapai atau shift berakhir, ploting akan otomatis selesai
                        if (response.target_reached || response.shift_expired) {
                            // Reload halaman untuk update status ploting
                            if (response.target_reached) {
                                console.log('Target shift tercapai, ploting otomatis selesai');
                            }
                            if (response.shift_expired) {
                                console.log('Shift berakhir, ploting otomatis selesai');
                            }
                            // Bisa juga reload halaman atau update UI tanpa reload
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        }
                    },
                    error: function() {
                        // Silent fail
                    }
                });
            }
            
            // Cek apakah shift sudah berakhir
            // Hanya auto-finish jika start_time dilakukan SEBELUM shift berakhir
            // Jika start_time dilakukan SETELAH shift berakhir, tidak auto-finish (biarkan manual finish)
            if (shiftEndTime && now >= shiftEndTime && !isShiftExpired) {
                // Pastikan start_time dilakukan sebelum shift berakhir
                // Jika start_time setelah shift berakhir, tidak auto-finish
                if (startTime > shiftEndTime) {
                    // Start dilakukan setelah shift berakhir, tidak auto-finish
                    // Tampilkan runtime normal sampai sekarang
                    const diffMs = now - startTime;
                    const diffSeconds = Math.floor(diffMs / 1000);
                    const hours = Math.floor(diffSeconds / 3600);
                    const minutes = Math.floor((diffSeconds % 3600) / 60);
                    const seconds = diffSeconds % 60;
                    
                    const timeString = String(hours).padStart(2, '0') + ':' + 
                                      String(minutes).padStart(2, '0') + ':' + 
                                      String(seconds).padStart(2, '0');
                    
                    const displayElement = runtimeElement.querySelector('.runtime-display');
                    if (displayElement) {
                        displayElement.textContent = timeString + ' (Shift Berakhir)';
                    }
                    
                    runtimeElement.classList.remove('badge-info');
                    runtimeElement.classList.add('badge-warning');
                    const spinnerIcon = runtimeElement.querySelector('.fa-spinner');
                    if (spinnerIcon) {
                        spinnerIcon.classList.remove('fa-spinner', 'fa-spin');
                        spinnerIcon.classList.add('fa-exclamation-triangle');
                    }
                    return;
                }
                
                // Start dilakukan sebelum shift berakhir, auto-finish ketika shift berakhir
                isShiftExpired = true;
                
                // Hentikan interval
                if (intervalId) {
                    clearInterval(intervalId);
                    intervalId = null;
                }
                
                // Hitung runtime sampai shift berakhir
                const diffMs = shiftEndTime - startTime;
                const diffSeconds = Math.floor(diffMs / 1000);
                const hours = Math.floor(diffSeconds / 3600);
                const minutes = Math.floor((diffSeconds % 3600) / 60);
                const seconds = diffSeconds % 60;
                
                const timeString = String(hours).padStart(2, '0') + ':' + 
                                  String(minutes).padStart(2, '0') + ':' + 
                                  String(seconds).padStart(2, '0');
                
                const displayElement = runtimeElement.querySelector('.runtime-display');
                if (displayElement) {
                    displayElement.textContent = timeString;
                }
                
                // Update badge untuk menunjukkan shift sudah berakhir
                runtimeElement.classList.remove('badge-info');
                runtimeElement.classList.add('badge-warning');
                const spinnerIcon = runtimeElement.querySelector('.fa-spinner');
                if (spinnerIcon) {
                    spinnerIcon.classList.remove('fa-spinner', 'fa-spin');
                    spinnerIcon.classList.add('fa-clock');
                }
                
                // Otomatis finish ploting ketika shift berakhir (hanya jika start sebelum shift berakhir)
                autoFinishPloting(plotingId);
                
                return;
            }
            
            // Jika shift belum berakhir, hitung runtime normal
            if (!isShiftExpired) {
                const diffMs = now - startTime;
                const diffSeconds = Math.floor(diffMs / 1000);
                const hours = Math.floor(diffSeconds / 3600);
                const minutes = Math.floor((diffSeconds % 3600) / 60);
                const seconds = diffSeconds % 60;
                
                const timeString = String(hours).padStart(2, '0') + ':' + 
                                  String(minutes).padStart(2, '0') + ':' + 
                                  String(seconds).padStart(2, '0');
                
                const displayElement = runtimeElement.querySelector('.runtime-display');
                if (displayElement) {
                    displayElement.textContent = timeString;
                }
            }
        }
        
        updateRuntime();
        intervalId = setInterval(updateRuntime, 1000);
    });
}

// Filter tanggal dan shift logic
function initializeFilters() {
    const tanggalSelect = document.getElementById('filterTanggal');
    const modeSelect = document.getElementById('filterTanggalMode');
    const shiftSelect = document.getElementById('filterShift');
    const tanggalParam = document.getElementById('tanggalParam');
    const shiftParam = document.getElementById('shiftParam');
    const applyBtn = document.getElementById('applyFilter');
    const filterStatus = document.getElementById('filterStatus');

    if (!tanggalSelect || !modeSelect) {
        return;
    }

    const todayYmd = window.plotingTodayYmd || '';
    const tomorrowYmd = window.plotingTomorrowYmd || '';
    const currentYmd = window.plotingCurrentYmd || todayYmd;

    // Initialize mode based on currentYmd
    if (currentYmd === todayYmd) {
        modeSelect.value = 'today';
        if (tanggalSelect) tanggalSelect.style.display = 'none';
    } else if (currentYmd === tomorrowYmd) {
        modeSelect.value = 'tomorrow';
        if (tanggalSelect) tanggalSelect.style.display = 'none';
    } else {
        modeSelect.value = 'custom';
        if (tanggalSelect) {
            tanggalSelect.style.display = 'inline-block';
            tanggalSelect.value = currentYmd;
        }
    }

    function updateParams() {
        if (tanggalParam && tanggalSelect) {
            tanggalParam.value = tanggalSelect.value || '';
        }
        if (shiftParam && shiftSelect) {
            shiftParam.value = shiftSelect ? shiftSelect.value : '';
        }
    }

    if (tanggalSelect) {
        tanggalSelect.addEventListener('change', function() {
            updateParams();
            if (filterStatus) filterStatus.style.display = 'block';
        });
    }
    
    if (modeSelect) {
        modeSelect.addEventListener('change', function() {
            if (this.value === 'today') {
                if (tanggalSelect) {
                    tanggalSelect.style.display = 'none';
                    tanggalSelect.value = todayYmd;
                }
                updateParams();
            } else if (this.value === 'tomorrow') {
                if (tanggalSelect) {
                    tanggalSelect.style.display = 'none';
                    tanggalSelect.value = tomorrowYmd;
                }
                updateParams();
            } else {
                if (tanggalSelect) {
                    tanggalSelect.style.display = 'inline-block';
                    if (!tanggalSelect.value) tanggalSelect.value = todayYmd;
                }
                updateParams();
            }
            if (filterStatus) filterStatus.style.display = 'block';
        });
    }
    
    if (shiftSelect) {
        shiftSelect.addEventListener('change', function() {
            updateParams();
            if (filterStatus) filterStatus.style.display = 'block';
        });
    }
    
    if (applyBtn) {
        applyBtn.addEventListener('click', function() {
            updateParams();
        });
    }

    // Initialize params to defaults on load
    updateParams();
}

// Initialize DataTables
function initializeDataTables() {
    // Support multiple tables: plotingTable, plotingTableInject, plotingTableAssy
    const tableSelectors = ['#plotingTable', '#plotingTableInject', '#plotingTableAssy'];
    let initializedTables = [];
    
    tableSelectors.forEach(function(selector) {
        if ($(selector).length === 0) {
            return; // Skip if table doesn't exist
        }
        
        // Destroy existing DataTable instance if it exists
        if ($.fn.dataTable.isDataTable(selector)) {
            $(selector).DataTable().destroy();
        }
        
        // Initialize DataTable
        var table = $(selector).DataTable({
        "paging": true,
        "lengthChange": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "responsive": true,
        "pageLength": 10,
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
        "order": [[0, 'desc']],
        "language": {
            "emptyTable": "Tidak ada data plooting produksi",
            "zeroRecords": "Tidak ada data yang sesuai dengan filter",
            "lengthMenu": "Tampilkan _MENU_ data per halaman",
            "info": "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            "infoEmpty": "Menampilkan 0 sampai 0 dari 0 data",
            "infoFiltered": "(difilter dari _MAX_ total data)",
            "search": "Cari:",
            "paginate": {
                "first": "Pertama",
                "last": "Terakhir",
                "next": "Selanjutnya",
                "previous": "Sebelumnya"
            }
        },
        "columnDefs": [
            {
                "targets": [4],
                "orderable": false,
                "searchable": false,
                "width": "600px"
            },
            {
                "targets": [0],
                "type": "date",
                "width": "120px"
            },
            {
                "targets": [3],
                "type": "num",
                "width": "100px"
            }
        ],
        "drawCallback": function(settings) {
            initializeRuntimeCalculators();
            $('[data-toggle="tooltip"]').tooltip();
        },
        "initComplete": function() {
            initializeRuntimeCalculators();
            $('[data-toggle="tooltip"]').tooltip();
        }
        });
        
        initializedTables.push(table);
        console.log('✅ DataTable initialized for ' + selector);
    });
    
    return initializedTables.length > 0 ? initializedTables[0] : null;
}

// Set Laravel routes and variables
function initializeLaravelRoutes() {
    window.Laravel = window.Laravel || {};
    window.Laravel.routes = {
        operatorsByGroup: window.plotingRoutes?.operatorsByGroup || '/ploting/operators',
        plotingStart: window.plotingRoutes?.plotingStart || '/ploting/',
        plotingFinish: window.plotingRoutes?.plotingFinish || '/ploting/',
        plotingRuntime: window.plotingRoutes?.plotingRuntime || '/ploting/',
        csrfToken: window.plotingRoutes?.csrfToken || ''
    };
}

// Function untuk update status filter
function updateFilterStatus() {
    const tanggal = $('#filterTanggal').val() || $('#tanggalParam').val();
    
    // Tampilkan "Filter Aktif" jika ada filter tanggal yang diterapkan
    if (tanggal) {
        $('#filterStatus').show();
    } else {
        $('#filterStatus').hide();
    }
}

// Function untuk load operators berdasarkan grup
function loadOperators(grupId, $userSelect, currentUserId = null) {
    const urlOperators = window.plotingRoutes ? window.plotingRoutes.operatorsByGroup : window.Laravel.routes.operatorsByGroup;
    
    console.log('Memuat operator untuk grup:', grupId);
    $userSelect.prop('disabled', true).html('<option><i class="fas fa-spinner fa-spin"></i> Memuat operator...</option>');
    if (!grupId) {
        $userSelect.html('<option value="">Pilih Operator</option>').prop('disabled', false);
        return;
    }

    // Ambil tanggal dari filter (default hari ini)
    const tanggal = $('#filterTanggal').val() || $('#tanggalParam').val() || window.plotingCurrentYmd || window.plotingTodayYmd;
    
    // Ambil bagian dari form context
    const $form = $userSelect.closest('form');
    const $bagianInput = $form.find('.bagian-input');
    const bagian = $bagianInput.val() || 'inject'; // Default ke inject
    
    // Ambil filter shift jika ada
    const filterShift = $('#filterShift').val() || '';
    
    const params = { 
        grup_id: grupId,
        tanggal: tanggal,
        shift: filterShift, // Tambahkan filter shift
        bagian: bagian // Tambahkan filter bagian
    };

    $.get(urlOperators, params)
        .done(function (response) {
            console.log('Respons server:', response);
            let html = '<option value="">Pilih Operator</option>';
            const data = response.data || response;
            const grupShift = response.grup_shift;

            if (Array.isArray(data)) {
                data.forEach(item => {
                    const sel = currentUserId && currentUserId == item.id ? 'selected' : '';
                    // Tampilkan shift info - jika grup memiliki shift spesifik, tampilkan sebagai shift grup
                    let shiftInfo = '';
                    if (item.shift) {
                        if (grupShift && grupShift !== 'all') {
                            shiftInfo = ` - Shift ${item.shift.charAt(0).toUpperCase() + item.shift.slice(1)} (Grup)`;
                        } else {
                            shiftInfo = ` - Shift ${item.shift.charAt(0).toUpperCase() + item.shift.slice(1)}`;
                        }
                    }
                    // Tampilkan indicator jika operator tidak memiliki jadwal
                    const noJadwalIndicator = !item.has_jadwal ? ' ⚠️' : '';
                    html += `<option value="${item.id}" ${sel} data-shift="${item.shift || ''}">${item.nama} (${item.nik})${shiftInfo}${noJadwalIndicator}</option>`;
                });

                if (data.length === 0) {
                    html = '<option value="">Tidak ada operator di grup ini' + (filterShift ? ' untuk shift ' + filterShift : '') + '</option>';
                    if (response.message) {
                        console.log('No operators found:', response.message);
                    }
                } else if (response.message) {
                    console.log('Operators loaded:', response.message);
                    // Tampilkan notifikasi jika shift otomatis dari grup
                    if (grupShift && grupShift !== 'all') {
                        console.log(`Shift otomatis dari grup: ${grupShift}`);
                    }
                }
            } else {
                console.error('Invalid server response format');
            }

            $userSelect.html(html);
        })
        .fail((xhr) => {
            console.log('Error AJAX:', xhr);
            $userSelect.html('<option>Gagal memuat</option>');
            let errorMsg = 'Gagal memuat data operator.';
            try {
                const response = JSON.parse(xhr.responseText);
                errorMsg = response.message || errorMsg;
            } catch {}
            console.error('Failed to load operators:', errorMsg);
        })
        .always(() => $userSelect.prop('disabled', false));
}

// Function untuk reload operator berdasarkan filter yang aktif
function reloadOperatorsWithFilter() {
    const tanggal = $('#filterTanggal').val() || $('#tanggalParam').val() || window.plotingCurrentYmd || window.plotingTodayYmd;
    
    if (!tanggal) {
        return;
    }
    
    updateFilterStatus();
    
    // Clear timer sebelumnya
    if (window.filterDebounceTimer) {
        clearTimeout(window.filterDebounceTimer);
    }
    
    // Debounce untuk mencegah terlalu banyak request
    window.filterDebounceTimer = setTimeout(() => {
        let reloadCount = 0;
        $('.grup-select').each(function () {
            const $grup = $(this);
            const $userSelect = $grup.closest('form').find('.operator-select');
            const currentUserId = $userSelect.data('current-user') || null;

            if ($grup.val()) {
                loadOperators($grup.val(), $userSelect, currentUserId);
                reloadCount++;
            }
        });
        
        if (reloadCount > 0) {
            console.log(`Auto-filter applied for ${tanggal}. Reloaded ${reloadCount} operator groups.`);
        }
    }, 500);
}

// Autosave runtime setiap 30 detik saat on_progress
function autosaveRuntime() {
    const routeRuntime = window.Laravel.routes.plotingRuntime;
    const csrfToken = window.Laravel.routes.csrfToken;
    
    $('[id^="runtime-"]').each(function() {
        const $badge = $(this);
        const plotingId = $badge.data('ploting-id');
        if (!plotingId) return;

        const url = `${routeRuntime}${plotingId}/runtime`;
        $.ajax({
            url,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
        }).done((res) => {
            // Optional: could update UI based on res.run_time
        }).fail((xhr) => {
            // Silent fail to avoid noise
        });
    });
}

// Initialize when DOM is ready
$(document).ready(function() {
    // Pastikan jQuery dan DataTables sudah di-load
    if (typeof $ === 'undefined') {
        console.error('jQuery tidak ditemukan!');
        return;
    }
    
    if (typeof $.fn.dataTable === 'undefined') {
        console.error('DataTables tidak ditemukan! Pastikan script DataTables sudah di-load.');
        return;
    }
    
    console.log('Memulai inisialisasi ploting...');
    
    // Initialize routes first
    initializeLaravelRoutes();
    
    // Initialize filters
    initializeFilters();
    
    // Initialize DataTables
    var table = initializeDataTables();
    
    if (table) {
        console.log('✅ DataTables berhasil diinisialisasi!');
    } else {
        console.error('❌ DataTables gagal diinisialisasi!');
    }
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize filter status
    updateFilterStatus();
    
    // Ensure runtime calculators are initialized
    setTimeout(function() {
        initializeRuntimeCalculators();
    }, 500);
    
    // Event delegation untuk grup select (support multiple tables)
    $(document).on('change', '.grup-select', function() {
        const $grup = $(this);
        const $userSelect = $grup.closest('form').find('.operator-select');
        const currentUserId = $userSelect.data('current-user') || null;
        
        // Langsung load operator ketika grup dipilih
        loadOperators($grup.val(), $userSelect, currentUserId);
    });

    // Initialize existing grup selects
    $('.grup-select').each(function () {
        const $grup = $(this);
        const $userSelect = $grup.closest('form').find('.operator-select');
        const currentUserId = $userSelect.data('current-user') || null;

        if ($grup.val()) {
            loadOperators($grup.val(), $userSelect, currentUserId);
        }
    });

    // Event handler untuk perubahan filter tanggal
    $('#filterTanggal').on('change', function() {
        reloadOperatorsWithFilter();
    });

    // Event handler untuk perubahan filter shift
    $('#filterShift').on('change', function() {
        // Reload semua operator select yang sudah memiliki grup dipilih
        reloadOperatorsWithFilter();
    });

    // Event handler untuk tombol terapkan filter
    $('#applyFilter').on('click', function() {
        const tanggal = $('#filterTanggal').val() || $('#tanggalParam').val();
        
        if (!tanggal) {
            console.log('Please select date first');
            return;
        }
        
        // Reload operator dengan filter tanggal baru
        reloadOperatorsWithFilter();
    });

    // Event delegation untuk tambah operator (support multiple tables)
    $(document).on('click', '.add-operator-btn', function() {
        const $form = $(this).closest('.add-operator-form');
        const grupId = $form.find('select[name="grup_id"]').val();
        const userId = $form.find('select[name="user_id"]').val();
        const operatorName = $form.find('select[name="user_id"] option:selected').text();
        const grupName = $form.find('select[name="grup_id"] option:selected').text();

        if (!grupId) {
            console.log('Please select group first');
            $form.find('select[name="grup_id"]').focus();
            return;
        }
        if (!userId) {
            console.log('Please select operator first');
            $form.find('select[name="user_id"]').focus();
            return;
        }

        // Cek duplikat
        const $table = $form.closest('table');
        let operatorExists = false;
        $table.find('tbody tr').each(function () {
            const existingOperator = $(this).find('strong').text().trim();
            if (existingOperator === operatorName) {
                operatorExists = true;
                return false;
            }
        });
        if (operatorExists) {
            console.log(`Operator ${operatorName} already assigned`);
            return;
        }

        $('#confirmModalLabel').text('Konfirmasi Tambah Operator');
        $('#confirmModalBody').html(`
            <div class="mb-3">
                <p class="mb-2">Apakah Anda yakin ingin menambahkan operator berikut?</p>
                <div class="card bg-light">
                    <div class="card-body py-2">
                        <strong>Grup:</strong> ${grupName}<br>
                        <strong>Operator:</strong> ${operatorName}
                    </div>
                </div>
            </div>
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle mr-2"></i>
                <small>Operator akan ditugaskan dengan status "Waiting".</small>
            </div>
        `);

        $('#confirmAction').removeClass().addClass('btn btn-success').text('Ya, Tambahkan')
            .off('click').on('click', function () {
                $('#confirmModal').modal('hide');
                const $btn = $form.find('.add-operator-btn');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');
                $form.submit();
            });

        $('#confirmModal').modal('show');
    });

    // Event delegation untuk update operator (support multiple tables)
    $(document).on('click', '.update-btn', function() {
        const $form = $(this).closest('.update-form');
        const operatorName = $form.data('operator-name');
        const newUserId = $form.find('select[name="user_id"]').val();
        const newOperatorName = $form.find('select[name="user_id"] option:selected').text();
        const grupName = $form.find('select[name="grup_id"] option:selected').text();

        if (!newUserId) {
            console.log('Please select replacement operator');
            return;
        }

        const currentUserId = $form.find('select[name="user_id"]').data('current-user');
        if (newUserId == currentUserId) {
            console.log('New operator is the same as current operator');
            return;
        }

        let operatorExists = false;
        const $table = $form.closest('table');
        $table.find('tbody tr').each(function () {
            const existingOperator = $(this).find('strong').text().trim();
            const $currentForm = $(this).find('.update-form');
            if ($currentForm.length > 0 && $currentForm[0] === $form[0]) return true;
            if (existingOperator === newOperatorName) {
                operatorExists = true;
                return false;
            }
        });
        if (operatorExists) {
            console.log(`Operator ${newOperatorName} already assigned`);
            return;
        }

        $('#confirmModalLabel').text('Konfirmasi Update Operator');
        $('#confirmModalBody').html(`
            <div class="mb-3">
                <p class="mb-2">Apakah Anda yakin ingin mengubah operator?</p>
                <div class="card bg-light">
                    <div class="card-body py-2">
                        <div class="row">
                            <div class="col-6"><strong>Lama:</strong> ${operatorName}</div>
                            <div class="col-6"><strong>Baru:</strong> ${newOperatorName}</div>
                        </div>
                        <hr class="my-2">
                        <strong>Grup:</strong> ${grupName}
                    </div>
                </div>
            </div>
            <div class="alert alert-warning mb-0">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <small>Update hanya bisa jika status masih "Waiting".</small>
            </div>
        `);

        $('#confirmAction').removeClass().addClass('btn btn-warning').text('Ya, Update')
            .off('click').on('click', function () {
                $('#confirmModal').modal('hide');
                const $btn = $form.find('.update-btn');
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Mengupdate...');
                $form.submit();
            });

        $('#confirmModal').modal('show');
    });

    // Event delegation untuk start button (support multiple tables)
    $(document).on('click', '.start-btn', function() {
        const plotingId = $(this).data('ploting-id');
        const operatorName = $(this).data('operator-name');
        const isDocComplete = $(this).data('doc-complete');
        const missingDocs = $(this).data('missing-docs');
        const routeStart = window.Laravel.routes.plotingStart;
        const csrfToken = window.Laravel.routes.csrfToken;

        if (!plotingId) {
            console.log('Ploting ID not found');
            return;
        }

        // Cek kelengkapan dokumen
        if (isDocComplete === false || isDocComplete === 'false') {
            Swal.fire({
                icon: 'error',
                title: 'Dokumen Belum Lengkap!',
                html: `Dokumen berikut belum diupload untuk mesin/meja ini: <br><strong class="text-danger">${missingDocs}</strong><br><br>Mohon lengkapi dokumen di halaman Pengaturan Dokumen sebelum memulai produksi.`,
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#d33'
            });
            return;
        }

        $('#confirmModalLabel').text('Konfirmasi Mulai Produksi');
        $('#confirmModalBody').html(`
            <p>Mulai produksi untuk operator <strong>${operatorName}</strong>?</p>
            <div class="alert alert-info"><small>Setelah mulai, operator tidak bisa diubah.</small></div>
        `);

        $('#confirmAction').removeClass().addClass('btn btn-success').text('Mulai')
            .off('click').on('click', function () {
                const url = `${routeStart}${plotingId}/start`;
                console.log('Mengirim ke URL:', url);
                const form = $(`
                    <form action="${url}" method="POST">
                        <input type="hidden" name="_token" value="${csrfToken}">
                    </form>
                `);
                $('body').append(form);
                $('#confirmModal').modal('hide');
                form.submit();
            });

        $('#confirmModal').modal('show');
    });

    // Event delegation untuk finish button (support multiple tables)
    $(document).on('click', '.finish-btn', function() {
        const plotingId = $(this).data('ploting-id');
        const operatorName = $(this).data('operator-name');
        const routeFinish = window.Laravel.routes.plotingFinish;
        const csrfToken = window.Laravel.routes.csrfToken;

        if (!plotingId) {
            console.log('Ploting ID not found');
            return;
        }

        $('#confirmModalLabel').text('Konfirmasi Selesai Produksi');
        $('#confirmModalBody').html(`
            <p>Selesaikan produksi untuk <strong>${operatorName}</strong>?</p>
            <div class="alert alert-success"><small>Produksi akan ditandai selesai.</small></div>
        `);

        $('#confirmAction').removeClass().addClass('btn btn-success').text('Selesai')
            .off('click').on('click', function () {
                const url = `${routeFinish}${plotingId}/finish`;
                console.log('Mengirim ke URL:', url);
                const form = $(`
                    <form action="${url}" method="POST">
                        <input type="hidden" name="_token" value="${csrfToken}">
                    </form>
                `);
                $('body').append(form);
                $('#confirmModal').modal('hide');
                form.submit();
            });

        $('#confirmModal').modal('show');
    });

    // Jalankan autosave setiap 30s
    setInterval(autosaveRuntime, 30000);
});

// Function untuk otomatis finish ploting ketika shift berakhir
function autoFinishPloting(plotingId) {
    if (!plotingId) return;
    
    const routeFinish = window.Laravel?.routes?.plotingFinish || window.plotingRoutes?.plotingFinish || '/ploting/';
    const csrfToken = window.Laravel?.routes?.csrfToken || window.plotingRoutes?.csrfToken || '';
    
    if (!csrfToken) {
        console.error('CSRF token tidak ditemukan');
        return;
    }
    
    const url = `${routeFinish}${plotingId}/finish`;
    
    console.log('Auto-finish ploting:', plotingId);
    
    // Kirim AJAX request untuk finish
    $.ajax({
        url: url,
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        success: function(response) {
            console.log('Ploting otomatis selesai:', response);
            // Reload halaman untuk update UI
            setTimeout(function() {
                window.location.reload();
            }, 1000);
        },
        error: function(xhr) {
            console.error('Error auto-finish ploting:', xhr);
            // Jika error, tetap reload untuk sync dengan backend
            setTimeout(function() {
                window.location.reload();
            }, 2000);
        }
    });
}
