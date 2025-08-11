/* Checksheet Create Page JavaScript */

$(document).ready(function() {
    // Camera functionality variables
    let cameraStreams = {};
    let currentCamera = null;

    // Camera functionality
    function initializeCamera() {
        // Handle camera button clicks
        $('.camera-btn').off('click').on('click', async function() {
            const cameraType = $(this).data('camera');
            currentCamera = cameraType;
            
            try {
                // Show camera preview
                $(`#camera-${cameraType}`).show();
                $(`#preview-${cameraType}`).hide();
                
                // Show/hide buttons
                $(this).hide();
                $(`.capture-btn[data-camera="${cameraType}"]`).show();
                $(`.retake-btn[data-camera="${cameraType}"]`).hide();

                // Request camera access
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    const constraints = {
                        video: {
                            facingMode: 'environment',
                            width: { ideal: 640, min: 320 },
                            height: { ideal: 480, min: 240 }
                        }
                    };

                    try {
                        cameraStreams[cameraType] = await navigator.mediaDevices.getUserMedia(constraints);
                    } catch (err) {
                        console.log('Failed to get environment camera, trying user camera:', err);
                        constraints.video.facingMode = 'user';
                        cameraStreams[cameraType] = await navigator.mediaDevices.getUserMedia(constraints);
                    }
                    
                    const video = document.getElementById(`camera-${cameraType}`);
                    video.srcObject = cameraStreams[cameraType];
                    
                    video.onloadedmetadata = function() {
                        video.play().then(() => {
                            console.log(`Camera ${cameraType} started successfully`);
                        }).catch(err => {
                            console.error('Error playing video:', err);
                            throw new Error('Failed to start video stream');
                        });
                    };
                    
                } else {
                    throw new Error('Camera not supported in this browser');
                }
            } catch (err) {
                console.error("Camera access error:", err);
                
                let errorMessage = 'Tidak bisa mengakses kamera. ';
                if (err.name === 'NotAllowedError') {
                    errorMessage += 'Izin kamera ditolak. Silakan berikan izin kamera dan coba lagi.';
                } else if (err.name === 'NotFoundError') {
                    errorMessage += 'Kamera tidak ditemukan. Pastikan perangkat memiliki kamera.';
                } else if (err.name === 'NotSupportedError') {
                    errorMessage += 'Browser tidak mendukung akses kamera.';
                } else if (err.name === 'NotReadableError') {
                    errorMessage += 'Kamera sedang digunakan oleh aplikasi lain.';
                } else {
                    errorMessage += 'Pastikan Anda memberikan izin dan menggunakan koneksi aman (HTTPS).';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Camera Error!',
                    text: errorMessage,
                    confirmButtonText: 'OK'
                });
                
                // Reset button state
                $(`.camera-btn[data-camera="${cameraType}"]`).show();
                $(`.capture-btn[data-camera="${cameraType}"]`).hide();
            }
        });

        // Handle photo capture
        $('.capture-btn').off('click').on('click', function() {
            const cameraType = $(this).data('camera');
            const video = document.getElementById(`camera-${cameraType}`);
            const canvas = document.getElementById(`canvas-${cameraType}`);
            const preview = document.getElementById(`preview-${cameraType}`);
            const img = document.getElementById(`${cameraType}_preview_img`);
            const fileInput = document.getElementById(`foto_${cameraType}`);

            try {
                const context = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                // Convert to blob and create file
                canvas.toBlob(function(blob) {
                    if (!blob) {
                        console.error('Failed to create blob from canvas');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Gagal mengambil foto. Silakan coba lagi.'
                        });
                        return;
                    }

                    const fileName = `${cameraType}_${Date.now()}.jpg`;
                    const file = new File([blob], fileName, { 
                        type: "image/jpeg", 
                        lastModified: Date.now() 
                    });
                    
                    // Create DataTransfer and assign file to input
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    fileInput.files = dataTransfer.files;
                    
                    // Update preview
                    const dataUrl = canvas.toDataURL('image/jpeg');
                    img.src = dataUrl;
                    
                    // Show preview and hide camera
                    $(`#camera-${cameraType}`).hide();
                    $(`#preview-${cameraType}`).show();
                    
                    // Update button states
                    $(`.camera-btn[data-camera="${cameraType}"]`).hide();
                    $(`.capture-btn[data-camera="${cameraType}"]`).hide();
                    $(`.retake-btn[data-camera="${cameraType}"]`).show();
                    
                    console.log("Photo captured for:", cameraType);
                    
                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Foto berhasil diambil',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }, 'image/jpeg', 0.8);

            } catch (err) {
                console.error('Error capturing photo:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Gagal mengambil foto. Silakan coba lagi.'
                });
            }
        });

        // Handle retake photo
        $('.retake-btn').off('click').on('click', function() {
            const cameraType = $(this).data('camera');
            
            // Clear file input
            document.getElementById(`foto_${cameraType}`).value = '';
            
            // Hide preview and show camera
            $(`#preview-${cameraType}`).hide();
            $(`#camera-${cameraType}`).show();
            
            // Update button states
            $(`.camera-btn[data-camera="${cameraType}"]`).show();
            $(`.capture-btn[data-camera="${cameraType}"]`).show();
            $(`.retake-btn[data-camera="${cameraType}"]`).hide();
        });
    }

    // Stop camera stream
    function stopCamera(cameraType) {
        if (cameraStreams[cameraType]) {
            cameraStreams[cameraType].getTracks().forEach(track => track.stop());
            delete cameraStreams[cameraType];
            console.log(`Camera stream ${cameraType} stopped`);
        }
    }

    // Stop all cameras
    function stopAllCameras() {
        Object.keys(cameraStreams).forEach(cameraType => {
            stopCamera(cameraType);
        });
    }

    // Initialize camera when document is ready
    initializeCamera();

    // User selection change handler
    $('#user_id').change(function() {
        const selectedOption = $(this).find('option:selected');
        const userRole = selectedOption.data('role');
        const userBagian = selectedOption.data('bagian');
        const userGrupId = selectedOption.data('grup-id');
        
        // Hide both sections initially
        $('#mesin_section').hide();
        $('#meja_section').hide();
        
        // Reset form fields
        $('#mesin_id').val('').prop('required', false);
        $('#part_id').val('').prop('required', false);
        $('#meja_id').val('').prop('required', false);
        $('#part_id_assy').val('').prop('required', false);
        
        // Auto-fill grup based on user's grup
        if (userGrupId) {
            $('#grup_id').val(userGrupId);
        } else {
            $('#grup_id').val('');
        }
        
        // Show appropriate section based on role and bagian
        if (userRole === 'leader') {
            if (userBagian === 'inject') {
                $('#mesin_section').show();
                $('#mesin_id').prop('required', true);
                $('#part_id').prop('required', true);
            } else if (userBagian === 'assy') {
                $('#meja_section').show();
                $('#meja_id').prop('required', true);
                $('#part_id_assy').prop('required', true);
            }
        } else if (['kabag', 'manager_plant', 'super_admin'].includes(userRole)) {
            // For kabag, manager_plant, and super_admin, show both sections
            $('#mesin_section').show();
            $('#meja_section').show();
            $('#mesin_id').prop('required', false);
            $('#part_id').prop('required', false);
            $('#meja_id').prop('required', false);
            $('#part_id_assy').prop('required', false);
        } else {
            // For other roles, show mesin section by default
            $('#mesin_section').show();
            $('#mesin_id').prop('required', true);
            $('#part_id').prop('required', true);
        }
    });

    // Form submission
    $('#createChecksheetForm').on('submit', function(e) {
        e.preventDefault();
        
        // Stop all cameras before form submission
        stopAllCameras();
        
        // Validate form based on user selection
        const selectedUser = $('#user_id').find('option:selected');
        const userRole = selectedUser.data('role');
        const userBagian = selectedUser.data('bagian');
        
        let isValid = true;
        let errorMessage = '';
        
        // Validation for leader users
        if (userRole === 'leader') {
            if (userBagian === 'inject') {
                if (!$('#mesin_id').val()) {
                    isValid = false;
                    errorMessage = 'Mesin harus dipilih untuk leader bagian inject';
                }
                if (!$('#part_id').val()) {
                    isValid = false;
                    errorMessage = 'Part harus dipilih untuk leader bagian inject';
                }
            } else if (userBagian === 'assy') {
                if (!$('#meja_id').val()) {
                    isValid = false;
                    errorMessage = 'Meja harus dipilih untuk leader bagian assy';
                }
                if (!$('#part_id_assy').val()) {
                    isValid = false;
                    errorMessage = 'Part harus dipilih untuk leader bagian assy';
                }
            }
        }
        
        // Validate AMTPM standard
        if (!$('input[name="amptm_standar"]:checked').val()) {
            isValid = false;
            errorMessage = 'Pertanyaan Standar AMTPM harus dijawab';
        }
        
        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Validasi Error!',
                text: errorMessage
            });
            return;
        }
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...');
        
        const formData = new FormData(this);
        
        // Set the correct part_id value based on section
        if ($('#meja_section').is(':visible')) {
            formData.set('part_id', $('#part_id_assy').val());
            formData.set('meja_id', $('#meja_id').val());
        } else {
            formData.set('part_id', $('#part_id').val());
            formData.set('meja_id', '');
        }
        
        $.ajax({
            url: '/checksheet',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#createChecksheetModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: response.message,
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan saat menyimpan checksheet';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: errorMessage
                });
            },
            complete: function() {
                // Reset button state
                submitBtn.prop('disabled', false);
                submitBtn.html(originalText);
            }
        });
    });

    // Clean up cameras when page is unloaded
    $(window).on('beforeunload', function() {
        stopAllCameras();
    });

    // Clean up cameras when modal is closed
    $('#createChecksheetModal').on('hidden.bs.modal', function() {
        stopAllCameras();
    });
}); 