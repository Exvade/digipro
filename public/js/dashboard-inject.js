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
            
            showMachineDetail(mesinId, nama, brand, tonase, status);
        });

        // Alternative click handler for machine cards
        $(document).on('click', '.machine-card', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Machine card inner clicked!'); // Debug log
            
            const $mesinItem = $(this).closest('.mesin-item');
            if ($mesinItem.length) {
                $mesinItem.trigger('click');
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
        $('#machineDetailModal').on('show.bs.modal', function() {
            currentStep = 0;
            showStep(currentStep);
            $('#form_grup').val(window.selectedGrup || '');
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
            labels: ['Aktif', 'Maintenance', 'Tidak Aktif'],
            datasets: [{
                data: [
                    parseInt($('[data-count="aktif"]').text()) || 0,
                    parseInt($('[data-count="maintenance"]').text()) || 0,
                    parseInt($('[data-count="tidak-aktif"]').text()) || 0
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

    // ====== FORM SUBMISSION ======
    function handleFormSubmission(e) {
        e.preventDefault();
        
        if (!validateForm()) return;
        
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...').prop('disabled', true);
        $('#machineDetailModal .modal-content').addClass('loading');
        
        $.ajax({
            url: $(this).attr('action'),
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
                $('#machineDetailModal .modal-content').removeClass('loading');
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
    window.showMachineDetail = function(id, nama, brand, tonase, status) {
        console.log('showMachineDetail called with:', { id, nama, brand, tonase, status }); // Debug log
        
        $('#detail-nama-mesin').text(nama);
        $('#detail-brand-mesin').text(brand);
        $('#detail-tonase-mesin').text(tonase + ' Ton');
        $('#detail-status-mesin').text(status.charAt(0).toUpperCase() + status.slice(1));
        $('#form_mesin_id').val(id);
        
        console.log('Modal elements updated, showing modal...'); // Debug log
        
        // Use Bootstrap modal method
        $('#machineDetailModal').modal('show');
        
        console.log('Modal show command executed'); // Debug log
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