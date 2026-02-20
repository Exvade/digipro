/**
 * Dashboard Inject - Optimized JavaScript
 * Essential functionality for dashboard inject with SweetAlert integration
 */

$(function() {
    // ====== GLOBAL VARIABLES ======
    let currentStream = null;
    let currentStep = 0;
    const totalSteps = 6;
    let pieChart = null;

    // ====== INITIALIZATION ======
    initializeDashboard();
    initializeEventHandlers();

    // ====== DASHBOARD INITIALIZATION ======
    function initializeDashboard() {
        // Initialize pie chart
        initializePieChart();
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();
        
        // Initialize form validation
        $('.form-control').on('blur', function() {
            if ($(this).hasClass('is-invalid')) {
                $(this).removeClass('is-invalid');
            }
        });
    }

    function initializeEventHandlers() {
        // Machine card click - Fixed event delegation
        $(document).on('click', '.mesin-item', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const mesinId = $(this).data('mesin-id');
            const nama = $(this).data('mesin-nama');
            const brand = $(this).data('mesin-brand');
            const tonase = $(this).data('mesin-tonase');
            const status = $(this).data('mesin-status');
            
            // Cukup panggil showMachineDetail
            showMachineDetail(mesinId, nama, brand, tonase, status);
        });

        // Machine card click handler - from index.blade.php
        // Machine card click handler - from index.blade.php
        $(document).on('click', '.machine-card, .machine-card-simple', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const id = $(this).data('mesin-id');
            const nama = $(this).data('mesin-nama');
            const brand = $(this).data('mesin-brand');
            const tonase = $(this).data('mesin-tonase');
            let status = $(this).data('mesin-status');
            const stopReason = $(this).data('stop-reason');

            // Jika status adalah 'stop' dan ada stop_reason, gunakan stop_reason sebagai status
            if (status === 'stop' && stopReason) {
                status = stopReason;
            }

            // Panggil fungsi detail
            if (id && nama) {
                window.showMachineDetail(id, nama, brand, tonase, status);
            }
        });

        // Step navigation
        $('#nextStepBtn').on('click', function() {
            // Jika ada ploting data dan di step 0, skip validasi step 0 dan langsung ke step 1
            if (currentStep === 0 && $('#ploting-info-section').is(':visible')) {
                currentStep = 1;
                showStep(currentStep);
                return;
            }
            
            if (!validateStep(currentStep)) return;
            if (currentStep < totalSteps - 1) {
                currentStep++;
                showStep(currentStep);
            }
        });

        $('#prevStepBtn').on('click', function() {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        });

        // Form submission
        $('#checksheetForm').on('submit', handleFormSubmission);

        // Load operators when grup is selected
        $(document).on('change', '#grup_id', function() {
            const grupId = $(this).val();
            const grupName = $(this).find('option:selected').text();
            
            // Ekstrak huruf A, B, atau C dari nama grup untuk backward compatibility
            // Format bisa: "Grup A", "A", "Grup B", dll
            let grupLetter = '';
            if (grupName && grupName !== 'Pilih Grup' && grupName.trim() !== '') {
                // Cari huruf A, B, atau C di nama grup
                const match = grupName.match(/\b([ABC])\b/i);
                if (match) {
                    grupLetter = match[1].toUpperCase();
                } else {
                    // Jika tidak ditemukan, coba ambil karakter pertama yang valid
                    const firstChar = grupName.trim().charAt(0).toUpperCase();
                    if (['A', 'B', 'C'].includes(firstChar)) {
                        grupLetter = firstChar;
                    }
                }
            }
            // Set hidden field dengan huruf A, B, atau C (atau kosong jika tidak ada grup dipilih)
            $('#grup').val(grupLetter);
            console.log('Grup dipilih:', grupName, '-> Letter:', grupLetter);
            
            if (grupId) {
                loadOperatorsByGrup(grupId);
            } else {
                $('#user_id').html('<option value="">Pilih Operator</option>');
                $('#grup').val(''); // Reset jika grup tidak dipilih
            }
        });
        
        // Variabel untuk menyimpan user_id yang akan di-set setelah operator di-load
        window.pendingUserId = null;
        window.plotingDataFilled = false;
        window.userIdRetryCount = 0;

        // Machine filter & Pagination logic
        let currentLimit = 'all';
        let currentPage = 1;
        const $machines = $('.col-custom-5');
        const $grid = $('#mesinGrid');
        const $pagination = $('#paginationControls');

        function updatePagination() {
            if (currentLimit === 'all') {
                // Tampilkan semua card dengan fadeIn untuk smooth transition
                $machines.each(function() {
                    $(this).fadeIn(400);
                });
                $pagination.addClass('d-none').removeClass('d-flex');
                $grid.removeClass('limited-view');
                return;
            }

            const limitNum = parseInt(currentLimit);
            const totalPages = Math.ceil($machines.length / limitNum);
            
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const start = (currentPage - 1) * limitNum;
            const end = start + limitNum;

            $machines.each(function(index) {
                if (index >= start && index < end) {
                    $(this).fadeIn(400);
                } else {
                    $(this).hide();
                }
            });

            // Update UI
            $pagination.removeClass('d-none').addClass('d-flex');
            $grid.addClass('limited-view');
            $('#currentPageNum').text(currentPage);
            $('#totalPageNum').text(totalPages);
            
            // Disable/Enable buttons
            $('#prevPage').prop('disabled', currentPage === 1);
            $('#nextPage').prop('disabled', currentPage === totalPages);
        }

        $('.filter-btn').on('click', function() {
            currentLimit = $(this).data('limit');
            currentPage = 1;
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            
            updatePagination();
            
            // Re-initialize tooltips for visible machines
            if (typeof initTooltips === 'function') {
                setTimeout(initTooltips, 500);
            }
        });

        $('#prevPage').on('click', function() {
            if (currentPage > 1) {
                currentPage--;
                updatePagination();
                // Smooth scroll to top of grid
                $('html, body').animate({
                    scrollTop: $grid.offset().top - 100
                }, 500);
            }
        });

        $('#nextPage').on('click', function() {
            const limitNum = parseInt(currentLimit);
            const totalPages = Math.ceil($machines.length / limitNum);
            if (currentPage < totalPages) {
                currentPage++;
                updatePagination();
                // Smooth scroll to top of grid
                $('html, body').animate({
                    scrollTop: $grid.offset().top - 100
                }, 500);
            }
        });

        // Shift is automatically retrieved from jadwal kerja in backend
        // No need to handle it in frontend anymore

        // Camera controls - FIXED: Use event delegation for dynamically loaded elements
        $(document).on('click', '.camera-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const cameraType = $(this).data('camera');
            console.log('Camera button clicked:', cameraType);
            
            // Check if button is already disabled to prevent double-click
            if ($(this).prop('disabled')) {
                console.log('Button already disabled, ignoring click');
                return;
            }
            
            openCamera(cameraType);
        });

        $(document).on('click', '.capture-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const $btn = $(this);
            
            // Jangan proses jika tombol disabled
            if ($btn.prop('disabled')) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tunggu sebentar',
                    text: 'Video kamera belum siap. Silakan tunggu.',
                    timer: 2000,
                    showConfirmButton: false
                });
                return false;
            }
            
            const cameraType = $(this).data('camera');
            capturePhoto(cameraType);
        });

        $(document).on('click', '.retake-btn', function() {
            const cameraType = $(this).data('camera');
            retakePhoto(cameraType);
        });

        // Modal events
        $('#machineDetailModal').on('show.bs.modal', function() {
            currentStep = 0;
            showStep(currentStep);
            $('#form_grup').val(window.selectedGrup || '');
            
            // Sembunyikan form checksheet dulu, akan ditampilkan jika ada ploting
            $('#checksheet-form-section').hide();
            $('#checksheet-form-card').hide();
            $('#ploting-info-section').hide();
            $('#no-ploting-info-section').hide();
            
            // Sembunyikan alert
            $('#ploting-info-alert').hide();
            $('#manual-info-alert').hide();
            
            // Reset disabled state
            $('#grup_id').prop('disabled', false).removeClass('bg-light');
            $('#user_id').prop('disabled', false).removeClass('bg-light');
            $('#part_id').prop('disabled', false).removeClass('bg-light');
            
            // Reset field grup
            $('#grup').val('');
            
            // Fetch dokumen ketika modal machine detail dibuka
            var mesinId = $('#form_mesin_id').val();
            if (!mesinId) return;
            
            // Load ploting data terlebih dahulu untuk menampilkan informasi operator yang sedang bekerja
            loadPlotingData(mesinId);
            
            // Fetch dokumen untuk mesin ini dan update modal contoh
            var docsUrl = window.dashboardInjectDocsUrl ? 
                window.dashboardInjectDocsUrl.replace('__ID__', mesinId) : 
                '/dashboard/inject/machine/' + mesinId + '/documents';
                
            fetch(docsUrl, { 
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(function(json){
                if (json.success && json.data) {
                    var d = json.data;
                    
                    // Update APD modal
                    if (d.apd && d.apd.url) {
                        $('#APDExampleModal iframe').attr('src', d.apd.url);
                        $('#APDExampleModal .modal-footer a.btn-primary').attr('href', d.apd.url).show();
                        var $alert = $('#APDExampleModal .modal-body .alert');
                        if ($alert.length) {
                            $alert.removeClass('alert-warning').addClass('alert-info');
                            $alert.find('strong').html('Petunjuk: Berikut adalah dokumen APD yang telah diupload untuk mesin ini.');
                        }
                    }
                    
                    // Update Q-Point modal
                    if (d.qpoint && d.qpoint.url) {
                        $('#qpointExampleModal iframe').attr('src', d.qpoint.url);
                        $('#qpointExampleModal .modal-footer a.btn-primary').attr('href', d.qpoint.url);
                    }
                    
                    // Update Instruksi Kerja modal
                    if (d.instruksi_kerja && d.instruksi_kerja.url) {
                        $('#instruksiKerjaExampleModal iframe').attr('src', d.instruksi_kerja.url);
                        $('#instruksiKerjaExampleModal .modal-footer a.btn-primary').attr('href', d.instruksi_kerja.url);
                    }
                    
                    // Update Limit Sampel modal
                    if (d.limit_sampel && d.limit_sampel.url) {
                        $('#limitSampelExampleModal iframe').attr('src', d.limit_sampel.url);
                        $('#limitSampelExampleModal .modal-footer a.btn-primary').attr('href', d.limit_sampel.url);
                    }
                    
                    // Update AMTPM modal
                    if (d.amptm && d.amptm.url) {
                        $('#amptmExampleModal iframe').attr('src', d.amptm.url);
                        $('#amptmExampleModal .modal-footer a.btn-primary').attr('href', d.amptm.url);
                    }
                }
            })
            .catch(function(error){
                console.error('Error fetching documents:', error);
            });

            // Auto-load ploting data untuk mesin ini
            loadPlotingData(mesinId);
        });

        $('#machineDetailModal').on('hidden.bs.modal', function() {
            resetForm();
        });
    }

    // ====== CHART INITIALIZATION ======
    function initializePieChart() {
        const ctx = document.getElementById('pieChartMesin');
        if (!ctx) return;

        const chartData = {
            labels: ['Aktif', 'Maintenance', 'Offline', 'Setup', 'Setdown'],
            datasets: [{
                data: [
                    parseInt($('[data-count="aktif"]').text()) || 0,
                    parseInt($('[data-count="maintenance"]').text()) || 0,
                    parseInt($('[data-count="tidak-aktif"]').text()) || 0,
                    parseInt($('[data-count="setup"]').text()) || 0,
                    parseInt($('[data-count="setdown"]').text()) || 0
                ],
                backgroundColor: [
                    '#28a745', // Aktif
                    '#ffc107', // Maintenance
                    '#dc3545', // Offline
                    '#17a2b8', // Setup
                    '#6c757d'  // Setdown
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        };

        pieChart = new Chart(ctx, {
            type: 'doughnut',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: {
                            padding: 10,
                            usePointStyle: true,
                            font: {
                                size: 10
                            },
                            boxWidth: 8
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }

    // ====== STEPPER LOGIC ======
    function showStep(step) {
        $('.form-step').hide();
        $('#step' + step).show();
        $('#stepNow').text(step + 1);
        const percent = Math.round(((step + 1) / totalSteps) * 100);
        $('#stepProgressBar').css('width', percent + '%').attr('aria-valuenow', percent);
        $('#stepPercent').text(percent + '%');
        
        // Button logic
        if (step === 0) {
            $('#prevStepBtn').hide();
            $('#nextStepBtn').show();
            $('#submitStepBtn').hide();
        } else if (step === totalSteps - 1) {
            $('#prevStepBtn').show();
            $('#nextStepBtn').hide();
            $('#submitStepBtn').show();
        } else {
            $('#prevStepBtn').show();
            $('#nextStepBtn').show();
            $('#submitStepBtn').hide();
        }
        
        // Jika ada ploting data dan step 0, sembunyikan form step 0
        if (step === 0 && $('#ploting-info-section').is(':visible')) {
            $('#step0').hide();
        }
    }

    function validateStep(step) {
        let valid = true;
        let errorMessage = '';

        switch(step) {
            case 0:
                // Skip validasi step 0 jika ada ploting data (data sudah terisi otomatis)
                if ($('#ploting-info-section').is(':visible')) {
                    return true;
                }
                if (!$('#user_id').val()) {
                    errorMessage = 'Pilih User!';
                    valid = false;
                } else if (!$('#part_id').val()) {
                    errorMessage = 'Pilih Part!';
                    valid = false;
                }
                break;
            case 1:
                if (!$('input[name="apd_topi"]:checked').val()) {
                    errorMessage = 'Pilih jawaban untuk topi!';
                    valid = false;
                } else if (!$('input[name="apd_sepatu"]:checked').val()) {
                    errorMessage = 'Pilih jawaban untuk sepatu safety!';
                    valid = false;
                }
                break;
            case 2:
                if (!$('input[name="quality_qpoint"]:checked').val()) {
                    errorMessage = 'Pilih jawaban untuk Q Point!';
                    valid = false;
                }
                break;
            case 3:
                if (!$('input[name="instruksi_kerja"]:checked').val()) {
                    errorMessage = 'Pilih jawaban untuk Instruksi Kerja!';
                    valid = false;
                }
                break;
            case 4:
                if (!$('input[name="limit_sampel"]:checked').val()) {
                    errorMessage = 'Pilih jawaban untuk Limit Sampel!';
                    valid = false;
                }
                break;
            case 5:
                if (!$('input[name="amptm_standar"]:checked').val()) {
                    errorMessage = 'Pilih jawaban untuk Standar AMTPM!';
                    valid = false;
                }
                break;
        }

        if (!valid) {
            Swal.fire({
                icon: 'warning',
                title: 'Validasi Gagal',
                text: errorMessage,
                confirmButtonText: 'OK'
            });
        }

        return valid;
    }

    // ====== FORM SUBMISSION ======
    function handleFormSubmission(e) {
        e.preventDefault();
        
        const form = $(this);
        
        // Pastikan step 0 tidak tersembunyi saat submit (untuk menghindari error browser)
        if ($('#step0').is(':hidden')) {
            $('#step0').show();
        }
        
        // Enable semua disabled field sebelum submit agar data terkirim
        // Simpan status disabled sebelumnya
        const wasGrupDisabled = $('#grup_id').prop('disabled');
        const wasUserDisabled = $('#user_id').prop('disabled');
        const wasPartDisabled = $('#part_id').prop('disabled');
        
        // Force enable semua field
        $('#grup_id').prop('disabled', false);
        $('#user_id').prop('disabled', false);
        $('#part_id').prop('disabled', false);
        
        // Baca nilai setelah enable (dari DOM langsung untuk memastikan)
        const grupId = document.getElementById('grup_id') ? document.getElementById('grup_id').value : $('#grup_id').val();
        const userId = document.getElementById('user_id') ? document.getElementById('user_id').value : $('#user_id').val();
        const partId = document.getElementById('part_id') ? document.getElementById('part_id').value : $('#part_id').val();
        
        console.log('Nilai field setelah enable:', {
            grupId: grupId,
            userId: userId,
            partId: partId,
            wasGrupDisabled: wasGrupDisabled,
            wasUserDisabled: wasUserDisabled,
            wasPartDisabled: wasPartDisabled
        });
        
        // Pastikan field grup terisi dengan benar sebelum submit
        if (!$('#grup').val() && $('#grup_id').val()) {
            const grupName = $('#grup_id').find('option:selected').text();
            let grupLetter = '';
            if (grupName && grupName !== 'Pilih Grup' && grupName.trim() !== '') {
                const match = grupName.match(/\b([ABC])\b/i);
                if (match) {
                    grupLetter = match[1].toUpperCase();
                } else {
                    const firstChar = grupName.trim().charAt(0).toUpperCase();
                    if (['A', 'B', 'C'].includes(firstChar)) {
                        grupLetter = firstChar;
                    }
                }
            }
            if (grupLetter) {
                $('#grup').val(grupLetter);
                console.log('Field grup diisi sebelum submit:', grupLetter);
            }
        }
        
        // Enable semua field sebelum validasi agar nilai bisa dibaca
        $('#grup_id').prop('disabled', false);
        $('#user_id').prop('disabled', false);
        $('#part_id').prop('disabled', false);
        
        // Debug: Log nilai field sebelum validasi
        console.log('Nilai sebelum validasi:', {
            user_id: $('#user_id').val(),
            grup_id: $('#grup_id').val(),
            part_id: $('#part_id').val(),
            grup: $('#grup').val(),
            ploting_visible: $('#ploting-info-section').is(':visible')
        });
        
        if (!validateForm()) {
            // Disable kembali jika validasi gagal
            if (wasGrupDisabled) $('#grup_id').prop('disabled', true);
            if (wasUserDisabled) $('#user_id').prop('disabled', true);
            if (wasPartDisabled) $('#part_id').prop('disabled', true);
            // Sembunyikan kembali step 0 jika ada ploting data
            if ($('#ploting-info-section').is(':visible')) {
                $('#step0').hide();
            }
            return;
        }
        
        // Pastikan field grup terisi lagi setelah validasi
        if (!$('#grup').val() && $('#grup_id').val()) {
            const grupName = $('#grup_id').find('option:selected').text();
            let grupLetter = '';
            if (grupName && grupName !== 'Pilih Grup' && grupName.trim() !== '') {
                const match = grupName.match(/\b([ABC])\b/i);
                if (match) {
                    grupLetter = match[1].toUpperCase();
                } else {
                    const firstChar = grupName.trim().charAt(0).toUpperCase();
                    if (['A', 'B', 'C'].includes(firstChar)) {
                        grupLetter = firstChar;
                    }
                }
            }
            $('#grup').val(grupLetter);
            console.log('Field grup diisi setelah validasi:', grupLetter);
        }
        
        // Pastikan field grup selalu terisi jika grup_id ada
        if ($('#grup_id').val() && !$('#grup').val()) {
            const grupName = $('#grup_id').find('option:selected').text();
            let grupLetter = '';
            if (grupName && grupName !== 'Pilih Grup' && grupName.trim() !== '') {
                const match = grupName.match(/\b([ABC])\b/i);
                if (match) {
                    grupLetter = match[1].toUpperCase();
                } else {
                    const firstChar = grupName.trim().charAt(0).toUpperCase();
                    if (['A', 'B', 'C'].includes(firstChar)) {
                        grupLetter = firstChar;
                    }
                }
            }
            $('#grup').val(grupLetter);
            console.log('Field grup diisi sebelum FormData:', grupLetter);
        }
        
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...').prop('disabled', true);
        $('#machineDetailModal .modal-content').addClass('loading');
        
        // Buat FormData baru setelah field di-enable dan grup terisi
        const formData = new FormData(form[0]);
        
        // Pastikan field user_id terisi dan terkirim (jika disabled, FormData mungkin tidak mengirimnya)
        const userIdValue = $('#user_id').val();
        if (userIdValue) {
            formData.set('user_id', userIdValue);
            console.log('Field user_id ditambahkan ke FormData:', userIdValue);
        } else {
            console.error('Field user_id kosong!');
        }
        
        // Pastikan field grup_id terisi dan terkirim
        const grupIdValue = $('#grup_id').val();
        if (grupIdValue) {
            formData.set('grup_id', grupIdValue);
            console.log('Field grup_id ditambahkan ke FormData:', grupIdValue);
        }
        
        // Pastikan field part_id terisi dan terkirim
        const partIdValue = $('#part_id').val();
        if (partIdValue) {
            formData.set('part_id', partIdValue);
            console.log('Field part_id ditambahkan ke FormData:', partIdValue);
        }
        
        // Hapus field grup dari FormData terlebih dahulu untuk memastikan nilai yang benar
        formData.delete('grup');
        
        // Pastikan field grup ada di FormData dengan nilai yang valid
        let grupValue = $('#grup').val();
        
        // Validasi: hanya terima nilai 'A', 'B', atau 'C'
        if (grupValue && ['A', 'B', 'C'].includes(grupValue.toUpperCase())) {
            formData.set('grup', grupValue.toUpperCase());
            console.log('Field grup ditambahkan ke FormData:', grupValue.toUpperCase());
        } else if ($('#grup_id').val()) {
            // Jika grup masih kosong atau tidak valid tapi grup_id ada, ekstrak lagi
            const grupName = $('#grup_id').find('option:selected').text();
            let grupLetter = '';
            if (grupName && grupName !== 'Pilih Grup' && grupName.trim() !== '') {
                const match = grupName.match(/\b([ABC])\b/i);
                if (match) {
                    grupLetter = match[1].toUpperCase();
                } else {
                    const firstChar = grupName.trim().charAt(0).toUpperCase();
                    if (['A', 'B', 'C'].includes(firstChar)) {
                        grupLetter = firstChar;
                    }
                }
            }
            if (grupLetter && ['A', 'B', 'C'].includes(grupLetter)) {
                formData.set('grup', grupLetter);
                $('#grup').val(grupLetter);
                console.log('Field grup ditambahkan ke FormData (fallback):', grupLetter);
            } else {
                // Jika tidak bisa mengekstrak nilai yang valid, jangan kirim field grup
                // Karena validasi backend adalah nullable, ini tidak akan menyebabkan error
                console.log('Field grup tidak dikirim karena nilai tidak valid');
            }
        } else {
            // Jika tidak ada grup_id, jangan kirim field grup
            console.log('Field grup tidak dikirim karena grup_id kosong');
        }
        
        // Debug: Log nilai semua field sebelum submit
        console.log('Nilai sebelum submit:', {
            mesin_id: $('#form_mesin_id').val(),
            grup_id: $('#grup_id').val(),
            grup: $('#grup').val(),
            user_id: $('#user_id').val(),
            part_id: $('#part_id').val(),
            apd_topi: $('input[name="apd_topi"]:checked').val(),
            apd_sepatu: $('input[name="apd_sepatu"]:checked').val(),
            quality_qpoint: $('input[name="quality_qpoint"]:checked').val(),
            instruksi_kerja: $('input[name="instruksi_kerja"]:checked').val(),
            limit_sampel: $('input[name="limit_sampel"]:checked').val(),
            amptm_standar: $('input[name="amptm_standar"]:checked').val(),
            user_disabled: $('#user_id').prop('disabled'),
            grup_disabled: $('#grup_id').prop('disabled'),
            part_disabled: $('#part_id').prop('disabled')
        });
        
        // Pastikan semua field yang diperlukan terisi
        const requiredAnswers = [
            { name: 'apd_topi', label: 'APD Topi' },
            { name: 'apd_sepatu', label: 'APD Sepatu' },
            { name: 'quality_qpoint', label: 'Quality Q Point' },
            { name: 'instruksi_kerja', label: 'Instruksi Kerja' },
            { name: 'limit_sampel', label: 'Limit Sampel' },
            { name: 'amptm_standar', label: 'Standar AMTPM' }
        ];
        
        for (let answer of requiredAnswers) {
            if (!$(`input[name="${answer.name}"]:checked`).val()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validasi Gagal',
                    text: `Silakan jawab pertanyaan untuk ${answer.label}!`,
                    confirmButtonText: 'OK'
                });
                submitBtn.html(originalText).prop('disabled', false);
                $('#machineDetailModal .modal-content').removeClass('loading');
                return;
            }
        }
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success' || response.success === true) {
                    // Clear flag dan pendingUserId setelah berhasil submit
                    window.plotingDataFilled = false;
                    window.pendingUserId = null;
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Checksheet berhasil disimpan!',
                        confirmButtonText: 'OK',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        resetForm();
                        $('#machineDetailModal').modal('hide');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message || 'Terjadi kesalahan saat menyimpan',
                        confirmButtonText: 'OK'
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan saat menyimpan checksheet';
                
                // Cek apakah ada error validasi dari Laravel
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        // Jika ada multiple errors, tampilkan semua
                        const errors = xhr.responseJSON.errors;
                        const errorList = Object.keys(errors).map(key => {
                            return Array.isArray(errors[key]) ? errors[key].join(', ') : errors[key];
                        }).join('<br>');
                        errorMessage = errorList;
                    }
                }
                
                // Log untuk debugging
                console.error('Error response:', xhr.responseJSON);
                console.error('Status:', xhr.status);
                console.log('Form data yang dikirim:', {
                    grup_id: $('#grup_id').val(),
                    grup: $('#grup').val(),
                    user_id: $('#user_id').val(),
                    part_id: $('#part_id').val(),
                    mesin_id: $('#form_mesin_id').val()
                });
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    html: errorMessage,
                    confirmButtonText: 'OK'
                });
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
                $('#machineDetailModal .modal-content').removeClass('loading');
            }
        });
    }

    function validateForm() {
        // Jika ada ploting data, pastikan field sudah terisi (tidak perlu validasi ketat karena sudah auto-fill)
        if ($('#ploting-info-section').is(':visible')) {
            // Pastikan field tidak disabled saat membaca nilai (enable dulu jika disabled)
            $('#user_id').prop('disabled', false);
            $('#grup_id').prop('disabled', false);
            $('#part_id').prop('disabled', false);
            
            // Baca nilai langsung dari DOM element untuk memastikan tidak ada cache issue
            const userSelectElement = document.getElementById('user_id');
            const grupSelectElement = document.getElementById('grup_id');
            const partSelectElement = document.getElementById('part_id');
            
            let userId = userSelectElement ? userSelectElement.value : $('#user_id').val();
            let grupId = grupSelectElement ? grupSelectElement.value : $('#grup_id').val();
            let partId = partSelectElement ? partSelectElement.value : $('#part_id').val();
            
            // Jika masih kosong, coba set lagi dari window.pendingUserId (fallback)
            if (!userId || userId === '' || userId === null) {
                if (window.pendingUserId) {
                    console.warn('User ID kosong, mencoba set dari pendingUserId:', window.pendingUserId);
                    userId = window.pendingUserId;
                    
                    // Pastikan option dengan value tersebut ada
                    if ($('#user_id option[value="' + userId + '"]').length > 0) {
                        $('#user_id').val(userId);
                        if (userSelectElement) {
                            userSelectElement.value = userId;
                        }
                        console.log('User ID di-set dari pendingUserId:', userId);
                    } else {
                        console.error('Option dengan value pendingUserId tidak ditemukan:', userId);
                        // Coba tunggu sebentar dan coba lagi (mungkin operator belum di-load)
                        const retryCount = window.userIdRetryCount || 0;
                        if (retryCount < 3) {
                            window.userIdRetryCount = retryCount + 1;
                            console.warn('Mencoba lagi setelah delay... (attempt ' + (retryCount + 1) + ')');
                            setTimeout(function() {
                                const retryUserId = window.pendingUserId;
                                if ($('#user_id option[value="' + retryUserId + '"]').length > 0) {
                                    $('#user_id').val(retryUserId);
                                    const retryUserSelectElement = document.getElementById('user_id');
                                    if (retryUserSelectElement) {
                                        retryUserSelectElement.value = retryUserId;
                                    }
                                    console.log('User ID berhasil di-set setelah retry:', retryUserId);
                                }
                            }, 300);
                        }
                    }
                } else {
                    console.error('User ID kosong dan pendingUserId juga tidak ada');
                }
            }
            
            // Baca ulang userId setelah mungkin di-set dari pendingUserId
            userId = userSelectElement ? userSelectElement.value : $('#user_id').val();
            
            console.log('Validasi ploting data:', {
                userId: userId,
                grupId: grupId,
                partId: partId,
                user_disabled: $('#user_id').prop('disabled'),
                grup_disabled: $('#grup_id').prop('disabled'),
                part_disabled: $('#part_id').prop('disabled'),
                user_options_count: $('#user_id option').length,
                user_selected_option: $('#user_id option:selected').text(),
                user_selected_value: $('#user_id option:selected').val(),
                pendingUserId: window.pendingUserId,
                plotingDataFilled: window.plotingDataFilled
            });
            
            // Validasi dengan lebih ketat - cek apakah nilai benar-benar ada dan valid
            if (!userId || userId === '' || userId === null || userId === undefined || userId === '0') {
                console.error('Validasi gagal - user_id kosong atau tidak valid:', userId);
                
                // Jika masih kosong dan ada pendingUserId, coba set sekali lagi dengan beberapa cara
                if (window.pendingUserId) {
                    console.warn('Mencoba set user_id sekali lagi dari pendingUserId sebelum error...');
                    const finalUserId = window.pendingUserId;
                    
                    // Cek apakah option sudah ada
                    const optionExists = $('#user_id option[value="' + finalUserId + '"]').length > 0;
                    console.log('Option exists:', optionExists, 'PendingUserId:', finalUserId, 'Total options:', $('#user_id option').length);
                    
                    if (optionExists) {
                        // Set dengan jQuery
                        $('#user_id').val(finalUserId);
                        
                        // Set langsung ke DOM
                        const finalUserSelectElement = document.getElementById('user_id');
                        if (finalUserSelectElement) {
                            finalUserSelectElement.value = finalUserId;
                            // Trigger change event
                            finalUserSelectElement.dispatchEvent(new Event('change', { bubbles: true }));
                        }
                        
                        // Baca ulang setelah set
                        userId = finalUserSelectElement ? finalUserSelectElement.value : $('#user_id').val();
                        console.log('User ID di-set sekali lagi, nilai sekarang:', userId);
                        
                        // Jika masih kosong, tunggu sebentar dan coba lagi (mungkin ada race condition)
                        if (!userId || userId === '') {
                            console.warn('User ID masih kosong setelah set, menunggu 200ms dan coba lagi...');
                            // Return false untuk mencegah submit, tapi tidak tampilkan error dulu
                            // Biarkan user coba submit lagi setelah field terisi
                            Swal.fire({
                                icon: 'warning',
                                title: 'Tunggu Sebentar',
                                text: 'Data operator sedang dimuat. Silakan tunggu sebentar dan coba submit lagi.',
                                confirmButtonText: 'OK',
                                timer: 2000
                            });
                            return false;
                        }
                    } else {
                        // Option belum ada, mungkin operator belum di-load
                        console.error('Option dengan value pendingUserId belum ada di select. Mungkin operator belum di-load.');
                        Swal.fire({
                            icon: 'error',
                            title: 'Validasi Gagal',
                            text: 'Data operator belum terisi dengan benar. Pastikan operator sudah di-load dari grup. Silakan refresh halaman dan coba lagi.',
                            confirmButtonText: 'OK'
                        });
                        return false;
                    }
                } else {
                    // Tidak ada pendingUserId, berarti memang tidak ada data ploting
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal',
                        text: 'Data operator belum terisi dengan benar. Silakan pilih operator terlebih dahulu.',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }
            }
            
            if (!grupId || grupId === '' || grupId === null || grupId === undefined || grupId === '0') {
                console.error('Validasi gagal - grup_id kosong atau tidak valid:', grupId);
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: 'Data grup belum terisi dengan benar. Silakan refresh halaman dan coba lagi.',
                    confirmButtonText: 'OK'
                });
                return false;
            }
            
            if (!partId || partId === '' || partId === null || partId === undefined || partId === '0') {
                console.error('Validasi gagal - part_id kosong atau tidak valid:', partId);
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: 'Data part belum terisi dengan benar. Silakan refresh halaman dan coba lagi.',
                    confirmButtonText: 'OK'
                });
                return false;
            }
            
            // Verifikasi tambahan: pastikan option dengan value tersebut benar-benar ada
            if ($('#user_id option[value="' + userId + '"]').length === 0) {
                console.error('Validasi gagal - user_id option tidak ditemukan:', userId);
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: 'Operator yang dipilih tidak ditemukan di daftar. Silakan refresh halaman dan coba lagi.',
                    confirmButtonText: 'OK'
                });
                return false;
            }
            
            console.log('Validasi ploting data berhasil');
            return true;
        }
        
        // Validasi field dasar untuk manual input (shift sudah otomatis dari backend, tidak perlu divalidasi)
        const requiredFields = ['user_id', 'part_id'];
        for (let field of requiredFields) {
            const fieldValue = $('#' + field).val();
            if (!fieldValue) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validasi Gagal',
                    text: `Pilih ${field.replace('_', ' ')}!`,
                    confirmButtonText: 'OK'
                });
                // Enable field jika disabled untuk focus
                $('#' + field).prop('disabled', false);
                $('#' + field).focus();
                return false;
            }
        }
        
        // Validasi grup_id juga diperlukan
        if (!$('#grup_id').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Validasi Gagal',
                text: 'Pilih Grup!',
                confirmButtonText: 'OK'
            });
            $('#grup_id').prop('disabled', false);
            $('#grup_id').focus();
            if ($('#ploting-info-section').is(':visible')) {
                $('#grup_id').prop('disabled', true);
            }
            return false;
        }
        
        // Validasi semua step
        for (let i = 0; i < totalSteps; i++) {
            if (!validateStep(i)) return false;
        }
        
        return true;
    }

    function resetForm() {
        $('#checksheetForm')[0].reset();
        currentStep = 0;
        showStep(currentStep);
        
        // Reset ploting info alert dan section
        $('#ploting-info-alert').hide();
        $('#manual-info-alert').show();
        $('#ploting-info-section').hide();
        $('#no-ploting-info-section').hide();
        $('#checksheet-exists-warning').hide();
        $('#checksheet-form-section').show();
        $('#checksheet-form-card').show();
        $('#ploting-operator-name').text('-');
        $('#ploting-grup-name').text('-');
        $('#ploting-part-name').text('-');
        
        // Reset disabled state
        $('#grup_id').prop('disabled', false).removeClass('bg-light');
        $('#user_id').prop('disabled', false).removeClass('bg-light');
        $('#part_id').prop('disabled', false).removeClass('bg-light');
        
        // Reset camera states
        $('.camera-preview').hide();
        $('.camera-canvas').hide();
        $('.camera-preview-container').hide();
        $('.capture-btn').hide().prop('disabled', false);
        $('.retake-btn').hide();
        $('.camera-btn').show().prop('disabled', false);
        
        // Reset video elements
        $('video.camera-preview').each(function() {
            this.srcObject = null;
            this.style.width = '';
            this.style.height = '';
        });
        
        // Stop any active camera stream
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
            currentStream = null;
        }
    }

    // ====== CAMERA FUNCTIONALITY ======
    function getAdaptiveConstraints() {
        const isPortrait = window.matchMedia('(orientation: portrait)').matches;
        const vw = Math.max(document.documentElement.clientWidth || 0, window.innerWidth || 0);
        const vh = Math.max(document.documentElement.clientHeight || 0, window.innerHeight || 0);
        
        // Adaptive resolution based on device type and orientation
        let widthIdeal, heightIdeal;
        
        if (vw <= 576) { // Mobile
            if (isPortrait) {
                widthIdeal = Math.min(720, vw);
                heightIdeal = Math.min(960, vh * 0.5);
            } else {
                widthIdeal = Math.min(1280, vw);
                heightIdeal = Math.min(720, vh * 0.4);
            }
        } else if (vw <= 991) { // Tablet
            if (isPortrait) {
                widthIdeal = Math.min(1024, vw);
                heightIdeal = Math.min(768, vh * 0.6);
            } else {
                widthIdeal = Math.min(1280, vw);
                heightIdeal = Math.min(720, vh * 0.5);
            }
        } else { // Desktop
            widthIdeal = Math.min(1920, vw * 0.8);
            heightIdeal = Math.min(1080, vh * 0.6);
        }
        
        return {
            audio: false,
            video: {
                width: { ideal: widthIdeal },
                height: { ideal: heightIdeal },
                facingMode: 'environment',
                frameRate: { ideal: 30, max: 60 }
            }
        };
    }

    function fitVideoToContainer(videoEl) {
        if (!videoEl) return;
        
        const container = videoEl.parentElement;
        if (!container) return;
        
        // Pastikan video memiliki dimensi yang valid
        if (!videoEl.videoWidth || !videoEl.videoHeight || videoEl.videoWidth <= 0 || videoEl.videoHeight <= 0) {
            console.warn('Video dimensions not available yet');
            return;
        }
        
        // Let CSS handle the sizing with object-fit: cover
        // Just ensure video is displayed and fills container
        videoEl.style.width = '100%';
        videoEl.style.height = '100%';
        videoEl.style.objectFit = 'cover';
        videoEl.style.display = 'block';
        
        console.log(`Video ready: ${videoEl.videoWidth}x${videoEl.videoHeight}`);
    }

    function openCamera(cameraType) {
        console.log('openCamera called for:', cameraType);
        
        // Check if getUserMedia is supported
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            console.error('getUserMedia not supported');
            Swal.fire({
                icon: 'error',
                title: 'Kamera Tidak Didukung',
                text: 'Browser Anda tidak mendukung akses kamera. Gunakan browser modern seperti Chrome, Firefox, atau Edge.',
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // Stop any existing stream
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
            currentStream = null;
        }
        
        // Show loading state
        const cameraBtn = $(`.camera-btn[data-camera="${cameraType}"]`);
        const originalText = cameraBtn.html();
        cameraBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Membuka Kamera...').prop('disabled', true);
        
        console.log('Requesting camera access...');
        
        navigator.mediaDevices.getUserMedia(getAdaptiveConstraints())
        .then(function(stream) {
            console.log('Camera access granted, stream received');
            currentStream = stream;
            const video = document.getElementById(`camera-${cameraType}`);
            
            if (!video) {
                console.error('Video element not found for:', cameraType);
                throw new Error('Video element not found');
            }
            
            console.log('Video element found:', video.id);
            video.srcObject = stream;
            video.style.display = 'block';
            video.play(); // Ensure video is playing
            
            // Wait for video metadata to load before sizing
            video.onloadedmetadata = function() {
                console.log(`Video loaded - dimensions: ${video.videoWidth}x${video.videoHeight}`);
                
                // Pastikan video memiliki dimensi yang valid
                if (video.videoWidth > 0 && video.videoHeight > 0) {
                    // Delay untuk memastikan video benar-benar siap
                    setTimeout(() => {
                        fitVideoToContainer(video);
                        
                        // Add responsive event listeners
                        video.addEventListener('resize', () => fitVideoToContainer(video));
                    }, 200);
                }
            };
            
            // Pastikan video siap untuk capture dengan menunggu readyState
            video.oncanplaythrough = function() {
                console.log('Video is ready for capture');
            };
            
            // Handle video errors
            video.onerror = function() {
                console.error('Video error:', video.error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error Video',
                    text: 'Terjadi kesalahan saat memuat video kamera.',
                    confirmButtonText: 'OK'
                });
            };
            
            // Update UI - disable tombol capture sampai video siap
            $(`.camera-btn[data-camera="${cameraType}"]`).hide();
            $(`.capture-btn[data-camera="${cameraType}"]`).show().prop('disabled', true);
            $(`.retake-btn[data-camera="${cameraType}"]`).hide();
            $(`#preview-${cameraType}`).hide();
            
            $(`.camera-container:has(#camera-${cameraType})`).addClass('capturing');
            
            // Pastikan tombol capture di-enable setelah video ready
            video.oncanplay = function() {
                setTimeout(() => {
                    $(`.capture-btn[data-camera="${cameraType}"]`).prop('disabled', false);
                    console.log(`Capture button enabled for ${cameraType}`);
                }, 500); // Tambahan delay untuk memastikan video benar-benar siap
            };
            
            // Success feedback
            Swal.fire({
                icon: 'success',
                title: 'Kamera Aktif',
                text: 'Kamera berhasil dibuka. Tunggu sebentar hingga video siap, lalu ambil foto.',
                timer: 2000,
                timerProgressBar: true,
                showConfirmButton: false
            });
        })
        .catch(function(err) {
            console.error('Camera access error:', err);
            console.error('Error name:', err.name);
            console.error('Error message:', err.message);
            
            // Reset button state
            cameraBtn.html(originalText).prop('disabled', false);
            
            let errorMessage = 'Tidak dapat mengakses kamera.';
            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                errorMessage = 'Akses kamera ditolak. Silakan klik ikon 🔒 di address bar dan izinkan akses kamera.';
            } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                errorMessage = 'Kamera tidak ditemukan. Pastikan kamera terhubung dengan baik.';
            } else if (err.name === 'NotSupportedError' || err.name === 'InvalidStateError') {
                errorMessage = 'Browser tidak mendukung akses kamera. Gunakan browser modern.';
            } else if (err.name === 'OverconstrainedError') {
                errorMessage = 'Kamera tidak mendukung resolusi yang diminta.';
            } else if (err.name === 'AbortError') {
                errorMessage = 'Akses kamera dibatalkan atau tidak tersedia.';
            }
            
            Swal.fire({
                icon: 'warning',
                title: 'Kamera Tidak Tersedia',
                html: `
                    <p>${errorMessage}</p>
                    <hr>
                    <p><strong>Tips:</strong></p>
                    <ul style="text-align: left; font-size: 0.9em;">
                        <li>Pastikan browser mendukung kamera (Chrome, Firefox, Edge)</li>
                        <li>Berikan izin akses kamera saat diminta</li>
                        <li>Pastikan tidak ada aplikasi lain yang menggunakan kamera</li>
                        <li>Coba refresh halaman dan izinkan akses lagi</li>
                    </ul>
                `,
                confirmButtonText: 'OK'
            });
        });
    }

    function capturePhoto(cameraType) {
        const video = document.getElementById(`camera-${cameraType}`);
        const canvas = document.getElementById(`canvas-${cameraType}`);
        const preview = document.getElementById(`${cameraType}_preview`);
        
        // Pastikan video sudah dimuat dan memiliki dimensi yang valid
        if (!video || !video.videoWidth || !video.videoHeight || video.readyState < 2) {
            Swal.fire({
                icon: 'warning',
                title: 'Video Belum Siap',
                text: 'Tunggu sebentar hingga video kamera siap.',
                confirmButtonText: 'OK',
                timer: 2000
            });
            return;
        }
        
        // Set canvas dimensions sesuai dengan video actual dimensions
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        // Konfigurasi konteks canvas untuk hasil terbaik
        const ctx = canvas.getContext('2d');
        
        // Clear canvas terlebih dahulu
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        
        // Set image smoothing untuk hasil lebih halus
        ctx.imageSmoothingEnabled = true;
        ctx.imageSmoothingQuality = 'high';
        
        // Pastikan draw image dengan dimensi yang tepat
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        // Convert to blob dengan quality lebih tinggi
        canvas.toBlob(function(blob) {
            if (!blob) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Gagal mengambil foto. Silakan coba lagi.',
                    confirmButtonText: 'OK'
                });
                return;
            }
            
            const fileInput = document.getElementById(`foto_${cameraType}`);
            const file = new File([blob], `${cameraType}_photo_${Date.now()}.jpg`, { 
                type: 'image/jpeg' 
            });
            
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            fileInput.files = dataTransfer.files;
            
            preview.src = URL.createObjectURL(blob);
            $(`#preview-${cameraType}`).show();
            
            video.style.display = 'none';
            $(`.capture-btn[data-camera="${cameraType}"]`).hide();
            $(`.retake-btn[data-camera="${cameraType}"]`).show();
            
            if (currentStream) {
                currentStream.getTracks().forEach(track => track.stop());
                currentStream = null;
            }
            
            $(`.camera-container:has(#camera-${cameraType})`).removeClass('capturing');
            
            Swal.fire({
                icon: 'success',
                title: 'Foto Berhasil Diambil!',
                text: 'Foto telah berhasil disimpan.',
                timer: 1500,
                timerProgressBar: true,
                showConfirmButton: false
            });
        }, 'image/jpeg', 0.95); // Meningkatkan quality dari 0.8 ke 0.95
    }

    // Enhanced resize handler for responsive video
    function resizeActiveVideo() {
        const cameraTypes = ['apd','quality','instruksi_kerja','limit_sampel','amptm'];
        
        cameraTypes.forEach(function(type){
            const videoEl = document.getElementById(`camera-${type}`);
            if (videoEl && videoEl.style.display !== 'none' && videoEl.srcObject) {
                // Add small delay to ensure layout is stable
                setTimeout(() => {
                    fitVideoToContainer(videoEl);
                }, 50);
            }
        });
    }
    
    // Debounced resize handler to prevent excessive calls
    let resizeTimeout;
    function debouncedResize() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(resizeActiveVideo, 150);
    }
    
    // Event listeners for responsive behavior
    window.addEventListener('resize', debouncedResize);
    window.addEventListener('orientationchange', function(){
        // Longer delay for orientation change to ensure layout is complete
        setTimeout(() => {
            if (currentStream) {
                const videoTrack = currentStream.getVideoTracks()[0];
                if (videoTrack && typeof videoTrack.applyConstraints === 'function') {
                    // Apply new constraints for orientation
                    videoTrack.applyConstraints(getAdaptiveConstraints().video)
                        .then(() => {
                            setTimeout(resizeActiveVideo, 200);
                        })
                        .catch(err => {
                            console.warn('Failed to apply constraints:', err);
                            resizeActiveVideo();
                        });
                } else {
                    resizeActiveVideo();
                }
            } else {
                resizeActiveVideo();
            }
        }, 300);
    });
    
    // Handle modal resize events
    $('#machineDetailModal').on('shown.bs.modal', function() {
        setTimeout(resizeActiveVideo, 100);
    });
    
    // Handle window focus (useful for mobile browsers)
    window.addEventListener('focus', function() {
        if (currentStream) {
            setTimeout(resizeActiveVideo, 100);
        }
    });

    function retakePhoto(cameraType) {
        document.getElementById(`foto_${cameraType}`).value = '';
        $(`#preview-${cameraType}`).hide();
        $(`.camera-btn[data-camera="${cameraType}"]`).show();
        $(`.capture-btn[data-camera="${cameraType}"]`).hide();
        $(`.retake-btn[data-camera="${cameraType}"]`).hide();
    }

    // ====== GLOBAL FUNCTIONS ======
    window.showMachineDetail = function(id, nama, brand, tonase, status) {
        console.log('showMachineDetail called with:', { id, nama, brand, tonase, status }); // Debug log

        // ✅ Validasi status mesin dipusatkan di sini
        // Definisikan pesan untuk setiap status
        const statusMessages = {
            'maintenance': {
                icon: 'warning',
                title: 'Mesin Dalam Perbaikan',
                text: `${nama} sedang dalam status Maintenance (Perbaikan). Checksheet tidak dapat diisi.`,
                color: '#f1c40f' // Yellow
            },
            'tidak aktif': {
                icon: 'error',
                title: 'Mesin Offline',
                text: `${nama} sedang dalam status Offline (Mati). Checksheet tidak dapat diisi.`,
                color: '#e74c3c' // Red
            },
            'offline': {
                icon: 'error',
                title: 'Mesin Offline',
                text: `${nama} sedang dalam status Offline (Mati). Checksheet tidak dapat diisi.`,
                color: '#e74c3c' // Red
            },
            'setup': {
                icon: 'info',
                title: 'Mesin Setup Mold',
                text: `${nama} sedang dalam proses Setup Mold. Checksheet tidak dapat diisi.`,
                color: '#3498db' // Blue (Stop)
            },
            'setdown': {
                icon: 'info',
                title: 'Mesin Setdown Mold',
                text: `${nama} sedang dalam proses Setdown Mold. Checksheet tidak dapat diisi.`,
                color: '#3498db' // Blue (Stop)
            },
            'material_habis': {
                icon: 'info',
                title: 'Material Habis',
                text: `${nama} sedang dalam status Material Habis. Checksheet tidak dapat diisi.`,
                color: '#3498db' // Blue (Stop)
            },
            'packaging_habis': {
                icon: 'info',
                title: 'Packaging Habis',
                text: `${nama} sedang dalam status Packaging Habis. Checksheet tidak dapat diisi.`,
                color: '#3498db' // Blue (Stop)
            }
        };

        // Cek apakah status mesin termasuk yang tidak bisa diisi checksheet
        if (statusMessages[status]) {
            const msg = statusMessages[status];
            Swal.fire({
                icon: msg.icon,
                title: msg.title,
                text: msg.text,
                confirmButtonColor: msg.color,
                confirmButtonText: 'OK'
            });
            return;
        }
        
        // Update status text and color in modal
        let displayStatus = status;
        let statusColorClass = 'text-info'; // Default for stop/info
        
        if (status === 'aktif' || status === 'running') {
            displayStatus = 'Running';
            statusColorClass = 'text-success';
        } else if (status === 'maintenance') {
            displayStatus = 'Maintenance';
            statusColorClass = 'text-warning';
        } else if (status === 'offline' || status === 'tidak aktif') {
            displayStatus = 'Offline';
            statusColorClass = 'text-danger';
        } else {
            // It's a stop reason
            displayStatus = status.replace(/_/g, ' ').charAt(0).toUpperCase() + status.replace(/_/g, ' ').slice(1);
            statusColorClass = 'text-info';
        }

        $('#detail-nama-mesin').text(nama);
        $('#detail-brand-mesin').text(brand);
        $('#detail-tonase-mesin').text(tonase + ' Ton');
        
        // Update status with color
        $('#detail-status-mesin')
            .text(displayStatus)
            .removeClass('text-success text-warning text-danger text-info')
            .addClass(statusColorClass);
            
        $('#form_mesin_id').val(id);
        
        console.log('Modal elements updated, showing modal...'); // Debug log
        
        // Use Bootstrap modal method
        $('#machineDetailModal').modal('show');
        
        // Fetch dokumen akan dilakukan di inline script di view (seperti dashboard-assy)
        
        console.log('Modal show command executed'); // Debug log
    };

    // Shift is now automatically retrieved from jadwal kerja in backend via accessor
    // No need for frontend shift handling

    // ====== LOAD OPERATORS BY GRUP ======
    // ====== PLOTING DATA LOADING ======
    function loadPlotingData(mesinId) {
        var tanggal = new Date().toISOString().split('T')[0]; // Format YYYY-MM-DD
        var plotingUrl = '/dashboard/inject/ploting-by-mesin?mesin_id=' + mesinId + '&tanggal=' + tanggal;
        
        fetch(plotingUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(function(json) {
            if (json.status === 'success' && json.has_ploting && json.data.length > 0) {
                // Ada ploting data, tampilkan informasi dan auto-populate form
                var plotingData = json.data;
                
                // Tampilkan alert success dan sembunyikan alert manual
                $('#ploting-info-alert').show();
                $('#manual-info-alert').hide();
                
                // Pilih ploting yang sedang aktif (on_progress) jika ada, jika tidak ambil yang pertama
                var selectedPloting = plotingData.find(p => p.is_active) || plotingData[0];
                
                if (selectedPloting) {
                    // Tampilkan informasi ploting di section khusus
                    $('#ploting-info-section').show();
                    $('#ploting-operator-name').text(selectedPloting.user_nama);
                    $('#ploting-grup-name').text(selectedPloting.grup_nama);
                    $('#ploting-part-name').text(selectedPloting.part_no + ' - ' + selectedPloting.part_nama);
                    
                    // Cek apakah checksheet sudah terisi untuk shift ini
                    if (selectedPloting.has_checksheet && selectedPloting.existing_checksheet) {
                        // Tampilkan warning bahwa checksheet sudah terisi
                        $('#checksheet-exists-warning').show();
                        $('#checksheet-shift-info').text(selectedPloting.shift_display || selectedPloting.existing_checksheet.shift_display || 'shift ini');
                        
                        // Format waktu created_at
                        const createdTime = new Date(selectedPloting.existing_checksheet.created_at);
                        const timeString = createdTime.toLocaleString('id-ID', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        $('#checksheet-created-time').text(timeString);
                        
                        // Tentukan shift selanjutnya
                        let nextShift = '';
                        if (selectedPloting.shift === 'shift_1') {
                            nextShift = 'Shift 2';
                        } else if (selectedPloting.shift === 'shift_2') {
                            nextShift = 'Shift 3';
                        } else if (selectedPloting.shift === 'shift_3') {
                            nextShift = 'Shift 1 (hari berikutnya)';
                        } else {
                            nextShift = 'shift selanjutnya';
                        }
                        $('#next-shift-info').text(nextShift);
                        
                        // Sembunyikan form checksheet karena sudah terisi untuk shift ini
                        $('#checksheet-form-section').hide();
                        $('#checksheet-form-card').hide();
                        
                        // JANGAN auto-fill data atau skip ke step selanjutnya jika checksheet sudah terisi
                        // Hanya tampilkan informasi saja
                        console.log('Checksheet sudah terisi untuk shift ini, hanya menampilkan informasi');
                        return; // Keluar dari fungsi, tidak lanjut ke auto-fill
                    } else {
                        // Sembunyikan warning jika tidak ada checksheet
                        $('#checksheet-exists-warning').hide();
                        // Tampilkan form checksheet
                        $('#checksheet-form-section').show();
                        $('#checksheet-form-card').show();
                    }
                    
                    // Ekstrak huruf A, B, atau C dari nama grup untuk backward compatibility
                    let grupLetter = '';
                    if (selectedPloting.grup_nama) {
                        const match = selectedPloting.grup_nama.match(/\b([ABC])\b/i);
                        if (match) {
                            grupLetter = match[1].toUpperCase();
                        } else {
                            const firstChar = selectedPloting.grup_nama.trim().charAt(0).toUpperCase();
                            if (['A', 'B', 'C'].includes(firstChar)) {
                                grupLetter = firstChar;
                            }
                        }
                    }
                    
                    // Validasi grupLetter sebelum set
                    if (grupLetter && ['A', 'B', 'C'].includes(grupLetter)) {
                        // Set field grup terlebih dahulu sebelum trigger change
                        $('#grup').val(grupLetter);
                        console.log('Field grup diisi dengan:', grupLetter);
                    } else {
                        console.warn('Grup letter tidak valid:', grupLetter);
                    }
                    
                    // Simpan user_id yang akan di-set setelah operator di-load
                    window.pendingUserId = selectedPloting.user_id;
                    
                    // Auto-fill grup_id dan load operators dengan callback
                    loadOperatorsByGrup(selectedPloting.grup_id, function(response) {
                        // Set grup_id setelah operator di-load
                        $('#grup_id').val(selectedPloting.grup_id);
                        
                        // Pastikan field grup terisi dengan nilai yang valid
                        if (grupLetter && ['A', 'B', 'C'].includes(grupLetter)) {
                            $('#grup').val(grupLetter);
                            console.log('Field grup diisi:', grupLetter);
                        }
                        
                        // Auto-fill part
                        $('#part_id').val(selectedPloting.part_id);
                        console.log('Part ID diisi:', selectedPloting.part_id);
                        
                        // Auto-fill operator setelah operator benar-benar di-load
                        const userId = window.pendingUserId;
                        if (userId) {
                            // Tunggu sebentar untuk memastikan DOM sudah update
                            setTimeout(function() {
                                // Pastikan option dengan value userId ada di dalam select
                                const userOption = $('#user_id option[value="' + userId + '"]');
                                if (userOption.length > 0) {
                                    // Set user_id dengan beberapa cara untuk memastikan tersimpan
                                    $('#user_id').val(userId);
                                    
                                    // Set juga langsung ke DOM element untuk memastikan
                                    const userSelectElement = document.getElementById('user_id');
                                    if (userSelectElement) {
                                        userSelectElement.value = userId;
                                        // Trigger input event untuk memastikan browser mengenali perubahan
                                        userSelectElement.dispatchEvent(new Event('change', { bubbles: true }));
                                    }
                                    
                                    // Verifikasi bahwa nilai benar-benar terisi
                                    const verifiedValue = userSelectElement ? userSelectElement.value : $('#user_id').val();
                                    console.log('User ID diisi:', userId, 'Verifikasi:', verifiedValue);
                                    
                                    if (!verifiedValue || verifiedValue !== userId.toString()) {
                                        console.warn('User ID tidak tersimpan dengan benar, mencoba lagi...');
                                        $('#user_id').val(userId);
                                        if (userSelectElement) {
                                            userSelectElement.value = userId;
                                        }
                                    }
                                    
                                    // Pastikan field tidak disabled setelah diisi
                                    $('#grup_id').prop('disabled', false);
                                    $('#user_id').prop('disabled', false);
                                    $('#part_id').prop('disabled', false);
                                    
                                    // Set flag bahwa data ploting sudah terisi
                                    window.plotingDataFilled = true;
                                    
                                    // Jangan clear pendingUserId terlalu cepat, biarkan untuk fallback di validasi
                                    // window.pendingUserId akan di-clear setelah form berhasil di-submit
                                    
                                    // Verifikasi nilai setelah di-set dengan delay kecil untuk memastikan DOM sudah update
                                    setTimeout(function() {
                                        // Baca nilai langsung dari DOM
                                        const verifiedUserId = userSelectElement ? userSelectElement.value : $('#user_id').val();
                                        const verifiedGrupId = document.getElementById('grup_id') ? document.getElementById('grup_id').value : $('#grup_id').val();
                                        const verifiedPartId = document.getElementById('part_id') ? document.getElementById('part_id').value : $('#part_id').val();
                                        
                                        console.log('Verifikasi nilai setelah auto-fill:', {
                                            user_id: verifiedUserId,
                                            grup_id: verifiedGrupId,
                                            part_id: verifiedPartId,
                                            grup: $('#grup').val(),
                                            user_option_exists: $('#user_id option[value="' + userId + '"]').length > 0,
                                            user_selected_text: $('#user_id option:selected').text(),
                                            plotingDataFilled: window.plotingDataFilled
                                        });
                                        
                                        // Double check: pastikan nilai benar-benar terisi
                                        if (!verifiedUserId || verifiedUserId === '') {
                                            console.warn('User ID masih kosong setelah di-set, mencoba set lagi...');
                                            $('#user_id').val(userId);
                                            if (userSelectElement) {
                                                userSelectElement.value = userId;
                                            }
                                        }
                                        if (!verifiedGrupId || verifiedGrupId === '') {
                                            console.warn('Grup ID masih kosong setelah di-set, mencoba set lagi...');
                                            $('#grup_id').val(selectedPloting.grup_id);
                                            const grupSelectElement = document.getElementById('grup_id');
                                            if (grupSelectElement) {
                                                grupSelectElement.value = selectedPloting.grup_id;
                                            }
                                        }
                                        if (!verifiedPartId || verifiedPartId === '') {
                                            console.warn('Part ID masih kosong setelah di-set, mencoba set lagi...');
                                            $('#part_id').val(selectedPloting.part_id);
                                            const partSelectElement = document.getElementById('part_id');
                                            if (partSelectElement) {
                                                partSelectElement.value = selectedPloting.part_id;
                                            }
                                        }
                                        
                                        // Set flag lagi setelah verifikasi
                                        window.plotingDataFilled = true;
                                    }, 200);
                                    
                                    // Sembunyikan form step 0 karena data sudah terisi otomatis
                                    // Tampilkan form checksheet untuk step-step berikutnya
                                    $('#checksheet-form-section').show();
                                    $('#checksheet-form-card').show();
                                    
                                    // Langsung skip ke step 1 (APD) karena step 0 sudah tidak perlu ditampilkan
                                    currentStep = 1;
                                    showStep(currentStep);
                                    
                                    console.log('Data ploting terisi, langsung ke step 1 (APD)');
                                } else {
                                    console.error('User ID tidak ditemukan di select options:', userId, 'Total options:', $('#user_id option').length);
                                    window.plotingDataFilled = false;
                                    // Tampilkan form checksheet meskipun user_id tidak ditemukan
                                    $('#checksheet-form-section').show();
                                    $('#checksheet-form-card').show();
                                    currentStep = 1;
                                    showStep(currentStep);
                                }
                            }, 100); // Delay kecil untuk memastikan option sudah di-load
                        } else {
                            console.error('PendingUserId tidak ada:', userId);
                        }
                    });
                }
            } else {
                // Tidak ada ploting data, sembunyikan form dan tampilkan pesan tidak ada operator
                $('#ploting-info-alert').hide();
                $('#manual-info-alert').show();
                $('#ploting-info-section').hide();
                $('#checksheet-form-section').hide();
                $('#checksheet-form-card').hide();
                $('#no-ploting-info-section').show();
                console.log('Tidak ada ploting data untuk mesin ini pada tanggal tersebut');
            }
        })
        .catch(function(error) {
            console.error('Error loading ploting data:', error);
            // Jika error, sembunyikan form dan tampilkan pesan tidak ada operator
            $('#ploting-info-alert').hide();
            $('#manual-info-alert').show();
            $('#ploting-info-section').hide();
            $('#checksheet-form-section').hide();
            $('#checksheet-form-card').hide();
            $('#no-ploting-info-section').show();
        });
    }

    function loadOperatorsByGrup(grupId, callback) {
        const $userSelect = $('#user_id');
        $userSelect.prop('disabled', true).html('<option value="">Memuat operator...</option>');
        
        $.ajax({
            url: '/dashboard/inject/operators-by-grup',
            method: 'GET',
            data: { grup_id: grupId },
            success: function(response) {
                let html = '<option value="">Pilih Operator</option>';
                
                if (response.data && response.data.length > 0) {
                    response.data.forEach(function(operator) {
                        const displayName = operator.nik 
                            ? `${operator.nama} (${operator.nik})`
                            : `${operator.nama} (${operator.username})`;
                        html += `<option value="${operator.id}">${displayName}</option>`;
                    });
                } else {
                    html = '<option value="">Tidak ada operator di grup ini</option>';
                }
                
                $userSelect.html(html).prop('disabled', false);
                
                // Panggil callback jika ada setelah operator di-load
                if (callback && typeof callback === 'function') {
                    callback(response);
                }
            },
            error: function() {
                $userSelect.html('<option value="">Error memuat operator</option>').prop('disabled', false);
                
                // Panggil callback meskipun error
                if (callback && typeof callback === 'function') {
                    callback(null);
                }
            }
        });
    }

    // ====== UTILITY FUNCTIONS ======
    function showAlert(message, type = 'info') {
        const alertConfig = {
            info: { icon: 'info', title: 'Informasi' },
            success: { icon: 'success', title: 'Berhasil' },
            warning: { icon: 'warning', title: 'Peringatan' },
            error: { icon: 'error', title: 'Error' }
        };

        const config = alertConfig[type] || alertConfig.info;
        
        Swal.fire({
            icon: config.icon,
            title: config.title,
            text: message,
            confirmButtonText: 'OK',
            timer: type === 'success' ? 2000 : undefined,
            timerProgressBar: type === 'success'
        });
    }

    // ====== AUTO-REFRESH MANAGEMENT ======
    let autoRefreshInterval = setInterval(function() {
        if (!$('#machineDetailModal').hasClass('show')) {
            // Auto-refresh disabled when modal is open
            // Uncomment below if you want auto-refresh
            // location.reload();
        }
    }, 30000);

    if (window.kabagApproved) {
        Swal.fire({
            icon: 'success',
            title: 'Approved oleh Kabag',
            text: window.kabagApproved,
            confirmButtonText: 'OK'
        });
        // Hapus agar tidak muncul lagi jika reload
        window.kabagApproved = null;
    }

});
