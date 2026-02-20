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
    const docsUrlTemplate = window.mejaDocumentsUrl || null;

    // ====== INITIALIZATION ======
    initializeDashboard();
    initializeEventHandlers();

    // ====== DASHBOARD INITIALIZATION ======
    function initializeDashboard() {
        // Initialize pie chart
        initializePieChart();
        
        // Initialize tooltips
        $('[data-toggle="tooltip"]').tooltip();

        if (typeof window.startNotifPolling === 'function') {
            window.startNotifPolling();
        }
        
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
            
            const $card = $(this).find('.meja-card, .meja-card-simple');
            const mejaId = $card.data('meja-id');
            const nama = $card.data('meja-nama');
            const status = $card.data('meja-status');
            
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

        // Load operators when grup is selected
        $(document).on('change', '#grup_id', function() {
            const grupId = $(this).val();
            const grupName = $(this).find('option:selected').text();
            
            // Ekstrak huruf A, B, atau C dari nama grup untuk backward compatibility
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
            // Set hidden field dengan huruf A, B, atau C (atau kosong jika tidak ada grup dipilih)
            $('#grup').val(grupLetter);
            console.log('Grup dipilih (Assy):', grupName, '-> Letter:', grupLetter);
            
            if (grupId) {
                loadOperatorsByGrup(grupId);
            } else {
                $('#user_id').html('<option value="">Pilih Operator</option>');
                $('#grup').val(''); // Reset jika grup tidak dipilih
            }
            
            // Reset checksheet warning saat grup berubah
            $('#checksheet-exists-warning-assy').hide();
        });
        
        // Check checksheet when user is selected
        $(document).on('change', '#user_id', function() {
            const userId = $(this).val();
            const mejaId = $('#form_meja_id').val();
            
            if (userId && mejaId) {
                checkChecksheetExists(mejaId, userId);
            } else {
                $('#checksheet-exists-warning-assy').hide();
            }
        });

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
            
            // Reset disabled state
            $('#grup_id').prop('disabled', false).removeClass('bg-light');
            $('#user_id').prop('disabled', false).removeClass('bg-light');
            $('#part_id').prop('disabled', false).removeClass('bg-light');
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
            labels: ['Aktif', 'Tidak Aktif'],
            datasets: [{
                data: [
                    window.countAktif || 0,
                    window.countTidakAktif || 0
                ],
                backgroundColor: [
                    '#28a745', // Green for Aktif
                    '#dc3545'  // Red for Tidak Aktif
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
        
        const form = $(this);
        
        // Pastikan step 0 tidak tersembunyi saat submit
        if ($('#step0').is(':hidden')) {
            $('#step0').show();
        }
        
        // Enable semua disabled field sebelum submit agar data terkirim
        const wasGrupDisabled = $('#grup_id').prop('disabled');
        const wasUserDisabled = $('#user_id').prop('disabled');
        const wasPartDisabled = $('#part_id').prop('disabled');
        
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
            $('#grup').val(grupLetter);
        }
        
        $('#grup_id').prop('disabled', false);
        $('#user_id').prop('disabled', false);
        $('#part_id').prop('disabled', false);
        
        if (!validateForm()) {
            // Disable kembali jika validasi gagal
            if (wasGrupDisabled) $('#grup_id').prop('disabled', true);
            if (wasUserDisabled) $('#user_id').prop('disabled', true);
            if (wasPartDisabled) $('#part_id').prop('disabled', true);
            // Sembunyikan kembali step 0 jika ada ploting data
            if ($('#ploting-info-section-assy').is(':visible')) {
                $('#step0').hide();
            }
            return;
        }
        
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Show loading state
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...').prop('disabled', true);
        $('#mejaDetailModal .modal-content').addClass('loading');
        
        // Buat FormData baru setelah field di-enable dan grup terisi
        const formData = new FormData(form[0]);
        
        // Pastikan field user_id terisi dan terkirim
        const userIdValue = $('#user_id').val();
        if (userIdValue) {
            formData.set('user_id', userIdValue);
        }
        
        // Pastikan field grup_id terisi dan terkirim
        const grupIdValue = $('#grup_id').val();
        if (grupIdValue) {
            formData.set('grup_id', grupIdValue);
        }
        
        // Pastikan field part_id terisi dan terkirim
        const partIdValue = $('#part_id').val();
        if (partIdValue) {
            formData.set('part_id', partIdValue);
        }
        
        // Hapus field grup dari FormData terlebih dahulu untuk memastikan nilai yang benar
        formData.delete('grup');
        
        // Pastikan field grup ada di FormData dengan nilai yang valid
        let grupValue = $('#grup').val();
        
        // Validasi: hanya terima nilai 'A', 'B', atau 'C'
        if (grupValue && ['A', 'B', 'C'].includes(grupValue.toUpperCase())) {
            formData.set('grup', grupValue.toUpperCase());
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
            }
        }
        
        // Validasi tambahan: pastikan user_id terisi
        if (!userIdValue || userIdValue === '') {
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal',
                text: 'Operator harus dipilih!',
                confirmButtonText: 'OK'
            });
            submitBtn.html(originalText).prop('disabled', false);
            $('#mejaDetailModal .modal-content').removeClass('loading');
            $('#user_id').prop('disabled', false);
            $('#user_id').focus();
            return;
        }
        
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
                $('#mejaDetailModal .modal-content').removeClass('loading');
                return;
            }
        }
        
        $.ajax({
            url: window.checksheetStoreUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success' || response.success === true) {
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
                
                if (xhr.responseJSON) {
                    if (xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON.errors) {
                        const errors = xhr.responseJSON.errors;
                        const errorList = Object.keys(errors).map(key => {
                            return Array.isArray(errors[key]) ? errors[key].join(', ') : errors[key];
                        }).join('<br>');
                        errorMessage = errorList;
                    }
                }
                
                console.error('Error response:', xhr.responseJSON);
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    html: errorMessage,
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
                $('#' + field).prop('disabled', false);
                $('#' + field).focus();
                return false;
            }
        }
        
        if (!$('#grup_id').val()) {
            Swal.fire({
                icon: 'warning',
                title: 'Validasi Gagal',
                text: 'Pilih Grup!',
                confirmButtonText: 'OK'
            });
            $('#grup_id').prop('disabled', false);
            $('#grup_id').focus();
            return false;
        }
        
        for (let i = 0; i < totalSteps; i++) {
            if (!validateStep(i)) return false;
        }
        
        return true;
    }

    function resetForm() {
        // Reset ploting info sections
        $('#no-ploting-info-section-assy').hide();
        $('#ploting-info-section-assy').hide();
        $('#checksheet-exists-warning-assy').hide();
        $('#checksheet-form-section-assy').show();
        $('#checksheet-form-card-assy').show();
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
        console.log('showMejaDetail called with:', { id, nama, status }); 
        
        if ( status === 'tidak_aktif') {
            Swal.fire({
                icon: 'error',
                title: 'Mohon maaf , Anda Tidak dapat Mengisi Checksheet',
                text: `${nama} sedang ${status}.`,
                confirmButtonColor: '#d33',
                confirmButtonText: 'OK'
            });
            return;
        }
        $('#detail-nama-meja').text(nama);
        $('#detail-status-meja').text(status.charAt(0).toUpperCase() + status.slice(1));
        $('#form_meja_id').val(id);
        
        $('#mejaDetailModal').modal('show');
        
        fetchMejaDocuments(id);
        loadPlotingData(id);
    };

    // ====== LOAD PLOTING DATA ======
    function loadPlotingData(mejaId) {
        // Reset states
        $('#ploting-info-section-assy').hide();
        $('#no-ploting-info-section-assy').hide();
        $('#checksheet-exists-warning-assy').hide();
        $('#checksheet-form-section-assy').hide();
        $('#checksheet-form-card-assy').hide();
        
        // Reset form fields
        $('#grup_id').val('').trigger('change');
        $('#user_id').val('').trigger('change');
        $('#part_id').val('').trigger('change');
        $('#grup_id').prop('disabled', false).removeClass('bg-light');
        $('#user_id').prop('disabled', false).removeClass('bg-light');
        $('#part_id').prop('disabled', false).removeClass('bg-light');
        
        window.plotingDataFilled = false;
        window.pendingUserId = null;

        const tanggal = new Date().toISOString().split('T')[0];

        $.ajax({
            url: '/dashboard/assy/ploting-by-meja',
            method: 'GET',
            data: { 
                meja_id: mejaId,
                tanggal: tanggal
            },
            success: function(json) {
                console.log('Ploting data loaded:', json);
                
                if (json.status === 'success' && json.has_ploting && json.data.length > 0) {
                    const selectedPloting = json.data[0]; 
                    
                    $('#ploting-info-section-assy').show();
                    $('#ploting-operator-name-assy').text(selectedPloting.user_nama);
                    $('#ploting-grup-name-assy').text(selectedPloting.grup_nama);
                    $('#ploting-part-name-assy').text(`${selectedPloting.part_no} - ${selectedPloting.part_nama}`);
                    
                    $('#grup_id').val(selectedPloting.grup_id);
                    
                    if ($(`#user_id option[value="${selectedPloting.user_id}"]`).length === 0) {
                        $('#user_id').append(new Option(selectedPloting.user_nama, selectedPloting.user_id));
                    }
                    $('#user_id').val(selectedPloting.user_id);
                    
                    $('#part_id').val(selectedPloting.part_id);
                    
                    $('#grup_id').prop('disabled', true).addClass('bg-light');
                    $('#user_id').prop('disabled', true).addClass('bg-light');
                    $('#part_id').prop('disabled', true).addClass('bg-light');
                    
                    window.plotingDataFilled = true;
                    window.pendingUserId = selectedPloting.user_id; 
                    
                    if (selectedPloting.has_checksheet) {
                        $('#checksheet-exists-warning-assy').show();
                        $('#checksheet-shift-info-assy').text(selectedPloting.shift_display);
                        $('#checksheet-created-time-assy').text(selectedPloting.existing_checksheet.created_at);
                        
                        $('#checksheet-form-section-assy').hide();
                        $('#checksheet-form-card-assy').hide();
                    } else {
                        $('#checksheet-form-section-assy').show();
                        $('#checksheet-form-card-assy').show();
                    }
                    
                } else {
                    $('#no-ploting-info-section-assy').show();
                    $('#checksheet-form-section-assy').hide();
                    $('#checksheet-form-card-assy').hide();
                }
            },
            error: function(xhr) {
                console.error('Error loading ploting data:', xhr);
                $('#no-ploting-info-section-assy').show();
                $('#checksheet-form-section-assy').hide();
                $('#checksheet-form-card-assy').hide();
            }
        });
    }

    function fetchMejaDocuments(mejaId) {
        if (!docsUrlTemplate) {
            return;
        }

        const docsUrl = docsUrlTemplate.replace('__ID__', mejaId);

        fetch(docsUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
            .then(function(response) {
                return response.json();
            })
            .then(function(json) {
                const data = (json && json.data) ? json.data : {};
                updateDocumentModals(data);
            })
            .catch(function(error) {
                console.error('Error fetching documents:', error);
            });
    }

    function updateDocumentModals(documents) {
        updateSingleDocumentModal('#APDExampleModal', documents.apd, {
            updateAlert: true,
            hideSecondaryButton: true
        });
        updateSingleDocumentModal('#qpointExampleModal', documents.qpoint);
        updateSingleDocumentModal('#instruksiKerjaExampleModal', documents.instruksi_kerja);
        updateSingleDocumentModal('#limitSampelExampleModal', documents.limit_sampel);
        updateSingleDocumentModal('#amptmExampleModal', documents.amptm);
    }

    function updateSingleDocumentModal(modalSelector, documentData = null, options = {}) {
        if (!modalSelector || !documentData || !documentData.url) {
            return;
        }

        const $modal = $(modalSelector);
        if ($modal.length === 0) {
            return;
        }

        const $iframe = $modal.find('iframe');
        const $downloadButton = $modal.find('.modal-footer a.btn-primary');
        const $secondaryButton = $modal.find('.modal-footer button.btn-secondary');

        if ($iframe.length) {
            $iframe.attr('src', documentData.url);
        }

        if ($downloadButton.length) {
            $downloadButton.attr('href', documentData.url).show();
        }

        if (options.hideSecondaryButton && $secondaryButton.length) {
            $secondaryButton.hide();
        }

        if (options.updateAlert) {
            const $alert = $modal.find('.modal-body .alert');
            if ($alert.length) {
                $alert.removeClass('alert-warning').addClass('alert-info');
                $alert.find('strong').html('Petunjuk: Berikut adalah dokumen APD yang telah diupload untuk meja ini.');
            }
        }
    }

    // ====== FILTER FUNCTIONALITY ======
    window.filterMeja = function(status) {
        console.log('filterMeja called:', status); 
        
        $('.btn-group .btn').removeClass('active');
        event.target.classList.add('active');
        
        $('.meja-item').each(function() {
            const mejaStatus = $(this).data('status');
            if (status === 'all' || mejaStatus === status) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    };

    // ====== CHECK CHECKSHEET EXISTS ======
    function checkChecksheetExists(mejaId, userId) {
        const tanggal = new Date().toISOString().split('T')[0];
        
        $.ajax({
            url: '/dashboard/assy/check-checksheet',
            method: 'POST',
            data: {
                meja_id: mejaId,
                user_id: userId,
                tanggal: tanggal
            },
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success && response.has_checksheet && response.existing_checksheet) {
                    $('#checksheet-exists-warning-assy').show();
                    $('#checksheet-shift-info-assy').text(response.shift_display || response.existing_checksheet.shift_display || 'shift ini');
                    
                    const createdTime = new Date(response.existing_checksheet.created_at);
                    const timeString = createdTime.toLocaleString('id-ID', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    $('#checksheet-created-time-assy').text(timeString);
                    
                    let nextShift = '';
                    if (response.shift === 'shift_1') {
                        nextShift = 'Shift Siang';
                    } else if (response.shift === 'shift_2') {
                        nextShift = 'Shift Malam';
                    } else if (response.shift === 'shift_3') {
                        nextShift = 'Shift Pagi (hari berikutnya)';
                    } else {
                        nextShift = 'shift selanjutnya';
                    }
                    $('#next-shift-info-assy').text(nextShift);
                    
                    $('#checksheet-form-section-assy').hide();
                    $('#checksheet-form-card-assy').hide();
                } else {
                    $('#checksheet-exists-warning-assy').hide();
                    $('#checksheet-form-section-assy').show();
                    $('#checksheet-form-card-assy').show();
                }
            },
            error: function(xhr) {
                console.error('Error checking checksheet:', xhr);
                $('#checksheet-exists-warning-assy').hide();
                $('#checksheet-form-section-assy').show();
                $('#checksheet-form-card-assy').show();
            }
        });
    }

    // ====== LOAD OPERATORS BY GRUP ======
    function loadOperatorsByGrup(grupId) {
        const $userSelect = $('#user_id');
        $userSelect.prop('disabled', true).html('<option value="">Memuat operator...</option>');
        
        $.ajax({
            url: '/dashboard/assy/operators-by-grup',
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
            },
            error: function() {
                $userSelect.html('<option value="">Error memuat operator</option>').prop('disabled', false);
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