/**
 * Dashboard Assy - Optimized JavaScript
 * Essential functionality for dashboard assy with SweetAlert integration
 */

$(function() {
    // ====== GLOBAL VARIABLES ======
    let currentStream = null;
    let pieChart = null;
    let currentStep = 0;
    const totalSteps = 6;

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
        // Meja card click - Fixed event delegation
        $(document).on('click', '.meja-item', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const mejaId = $(this).find('.meja-card').data('meja-id');
            const nama = $(this).find('.meja-card').data('meja-nama');
            const status = $(this).find('.meja-card').data('meja-status');
            
            showMejaDetail(mejaId, nama, status);
        });

        // Alternative click handler for meja cards
        $(document).on('click', '.meja-card', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Meja card inner clicked!'); // Debug log
            
            const $mejaItem = $(this).closest('.meja-item');
            if ($mejaItem.length) {
                $mejaItem.trigger('click');
            }
        });

        // Step navigation
        $('#nextStepBtn').on('click', function() {
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

        // Camera controls
        $('.camera-btn').on('click', function() {
            const cameraType = $(this).data('camera');
            openCamera(cameraType);
        });

        $('.capture-btn').on('click', function() {
            const cameraType = $(this).data('camera');
            capturePhoto(cameraType);
        });

        $('.retake-btn').on('click', function() {
            const cameraType = $(this).data('camera');
            retakePhoto(cameraType);
        });

        // Modal events
        $('#mejaDetailModal').on('show.bs.modal', function() {
            currentStep = 0;
            showStep(currentStep);
        });

        $('#mejaDetailModal').on('hidden.bs.modal', function() {
            resetForm();
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
    }

    function validateStep(step) {
        let valid = true;
        let errorMessage = '';

        switch(step) {
            case 0:
                if (!$('#user_id').val()) {
                    errorMessage = 'Pilih User!';
                    valid = false;
                } else if (!$('#shift').val()) {
                    errorMessage = 'Pilih Shift!';
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

    // ====== CHART INITIALIZATION ======
    function initializePieChart() {
        const ctx = document.getElementById('pieChartMeja');
        if (!ctx) return;

        const chartData = {
            labels: ['Aktif', 'Proses', 'Tidak Aktif'],
            datasets: [{
                data: [
                    window.countAktif || 0,
                    window.countMaintenance || 0,
                    window.countTidakAktif || 0
                ],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#dc3545'
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
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }

    // ====== FORM SUBMISSION ======
    function handleFormSubmission(e) {
        e.preventDefault();
        
        if (!validateForm()) return;
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...').prop('disabled', true);
        $('#mejaDetailModal .modal-content').addClass('loading');
        
        $.ajax({
            url: window.checksheetStoreUrl,
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Checksheet berhasil disimpan!',
                        confirmButtonText: 'OK',
                        timer: 2000,
                        timerProgressBar: true
                    }).then(() => {
                        resetForm();
                        $('#mejaDetailModal').modal('hide');
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
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage,
                    confirmButtonText: 'OK'
                });
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
                $('#mejaDetailModal .modal-content').removeClass('loading');
            }
        });
    }

    function validateForm() {
        const requiredFields = ['user_id', 'shift', 'part_id'];
        for (let field of requiredFields) {
            if (!$('#' + field).val()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validasi Gagal',
                    text: `Pilih ${field.replace('_', ' ')}!`,
                    confirmButtonText: 'OK'
                });
                $('#' + field).focus();
                return false;
            }
        }
        
        for (let i = 0; i < totalSteps; i++) {
            if (!validateStep(i)) return false;
        }
        
        return true;
    }

    function resetForm() {
        $('#checksheetForm')[0].reset();
        currentStep = 0;
        showStep(currentStep);
        
        // Reset camera states
        $('.camera-preview').hide();
        $('.camera-canvas').hide();
        $('.camera-preview-container').hide();
        $('.capture-btn').hide();
        $('.retake-btn').hide();
        $('.camera-btn').show();
        
        // Stop any active camera stream
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
            currentStream = null;
        }
    }

    // ====== CAMERA FUNCTIONALITY ======
    function openCamera(cameraType) {
        if (currentStream) {
            currentStream.getTracks().forEach(track => track.stop());
        }
        
        navigator.mediaDevices.getUserMedia({ 
            video: { 
                width: { ideal: 1280 },
                height: { ideal: 720 },
                facingMode: 'environment'
            } 
        })
        .then(function(stream) {
            currentStream = stream;
            const video = document.getElementById(`camera-${cameraType}`);
            video.srcObject = stream;
            video.style.display = 'block';
            
            $(`.camera-btn[data-camera="${cameraType}"]`).hide();
            $(`.capture-btn[data-camera="${cameraType}"]`).show();
            $(`.retake-btn[data-camera="${cameraType}"]`).hide();
            $(`#preview-${cameraType}`).hide();
            
            $(`.camera-container:has(#camera-${cameraType})`).addClass('capturing');
        })
        .catch(function(err) {
            console.error('Camera access error:', err);
            Swal.fire({
                icon: 'warning',
                title: 'Kamera Tidak Tersedia',
                text: 'Tidak dapat mengakses kamera. Silakan gunakan upload file.',
                confirmButtonText: 'OK'
            });
        });
    }

    function capturePhoto(cameraType) {
        const video = document.getElementById(`camera-${cameraType}`);
        const canvas = document.getElementById(`canvas-${cameraType}`);
        const preview = document.getElementById(`${cameraType}_preview`);
        
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0);
        
        canvas.toBlob(function(blob) {
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
        }, 'image/jpeg', 0.8);
    }

    function retakePhoto(cameraType) {
        document.getElementById(`foto_${cameraType}`).value = '';
        $(`#preview-${cameraType}`).hide();
        $(`.camera-btn[data-camera="${cameraType}"]`).show();
        $(`.capture-btn[data-camera="${cameraType}"]`).hide();
        $(`.retake-btn[data-camera="${cameraType}"]`).hide();
    }

    // ====== GLOBAL FUNCTIONS ======
    window.showMejaDetail = function(id, nama, status) {
        console.log('showMejaDetail called with:', { id, nama, status }); // Debug log
        
        $('#detail-nama-meja').text(nama);
        $('#detail-status-meja').text(status.charAt(0).toUpperCase() + status.slice(1));
        $('#form_meja_id').val(id);
        
        console.log('Modal elements updated, showing modal...'); // Debug log
        
        // Use Bootstrap modal method
        $('#mejaDetailModal').modal('show');
        
        console.log('Modal show command executed'); // Debug log
    };

    // ====== FILTER FUNCTIONALITY ======
    window.filterMeja = function(status) {
        console.log('filterMeja called:', status); // Debug log
        
        // Update active button
        $('.btn-group .btn').removeClass('active');
        event.target.classList.add('active');
        
        // Filter meja items
        $('.meja-item').each(function() {
            const mejaStatus = $(this).data('status');
            if (status === 'all' || mejaStatus === status) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    };

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
        if (!$('#mejaDetailModal').hasClass('show')) {
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