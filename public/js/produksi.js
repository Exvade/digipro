// produksi.js - QR Code Scanner Implementation
let html5QrcodeScanner = null;
let isScanning = false;
let availableCameras = [];
let currentCameraIndex = 0;
let rearCameraId = null;

$(document).ready(function() {
    // Initialize scanner when page loads
    initializeScanner();
    
    // Get available cameras first
    getAvailableCameras();
    
    // Start scan button click event
    $('#startScanBtn').on('click', function() {
        startScanning();
    });
    
    // Modal close event - stop scanner
    $('#scannerModal').on('hidden.bs.modal', function() {
        stopScanning();
    });
    
    // Switch camera button
    $('#switchCameraBtn').on('click', function() {
        switchCamera();
    });
});

function initializeScanner() {
    console.log('Initializing QR Scanner...');
    
    // Check if html5QrcodeScanner is available
    if (typeof Html5QrcodeScanner === 'undefined') {
        console.error('Html5QrcodeScanner library not loaded');
        showError('QR Scanner library tidak tersedia');
        return;
    }
}

async function startScanning() {
    if (isScanning) {
        return;
    }
    
    console.log('Starting QR scan...');
    $('#scannerModal').modal('show');
    
    // Wait for modal to be fully shown
    $('#scannerModal').on('shown.bs.modal', async function() {
        await initializeQrScanner();
    });
}

async function initializeQrScanner() {
    try {
        // Show loading indicator
        $('#cameraLoading').show();
        $('#interactive').hide();
        
        // Get the preferred camera (rear camera if available)
        const cameraId = getPreferredCameraId();
        
    const config = {
        fps: 10,
        qrbox: {
                width: 280,
                height: 280
        },
        aspectRatio: 1.0,
        disableFlip: false,
        supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
    };
    
    html5QrcodeScanner = new Html5QrcodeScanner(
        "interactive",
        config,
        false // verbose logging
    );
    
        // Start with preferred camera
        if (cameraId) {
            const camera = availableCameras.find(cam => cam.deviceId === cameraId);
            console.log('🚀 Starting scanner with camera:', camera ? camera.label : 'Unknown');
            console.log('📱 Camera ID:', cameraId);
            console.log('🔍 Is Rear Camera:', cameraId === rearCameraId);
            
            await html5QrcodeScanner.render(onScanSuccess, onScanFailure, {
                preferredCamera: cameraId
            });
        } else {
            console.log('🚀 Starting scanner with default camera (no specific camera ID)');
            await html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }
        
        // Hide loading and show scanner
        $('#cameraLoading').hide();
        $('#interactive').show();
        isScanning = true;
        
        // Show camera status
        showCameraStatus(cameraId);
        
        // Show switch camera button if multiple cameras available
        if (availableCameras.length > 1) {
            $('#switchCameraBtn').show();
        }
        
    } catch (error) {
        console.error('Error starting scanner:', error);
        
        let errorMessage = 'Tidak dapat mengakses kamera. ';
        
        if (error.name === 'NotAllowedError') {
            errorMessage += 'Izin akses kamera ditolak. Silakan izinkan akses kamera dan coba lagi.';
        } else if (error.name === 'NotFoundError') {
            errorMessage += 'Kamera tidak ditemukan. Pastikan kamera tersedia dan tidak digunakan aplikasi lain.';
        } else if (error.name === 'NotReadableError') {
            errorMessage += 'Kamera sedang digunakan oleh aplikasi lain. Tutup aplikasi lain yang menggunakan kamera.';
        } else if (error.name === 'OverconstrainedError') {
            errorMessage += 'Konfigurasi kamera tidak didukung. Coba gunakan kamera lain.';
        } else {
            errorMessage += 'Error: ' + error.message;
        }
        
        // Hide loading indicator
        $('#cameraLoading').hide();
        showError(errorMessage);
        
        // Try fallback without preferred camera
        try {
            console.log('🔄 Trying fallback scanner without preferred camera...');
            $('#cameraLoading').show();
            
            html5QrcodeScanner = new Html5QrcodeScanner(
                "interactive",
                config,
                false
            );
            await html5QrcodeScanner.render(onScanSuccess, onScanFailure);
            
            $('#cameraLoading').hide();
            $('#interactive').show();
            isScanning = true;
            
            // Show status for fallback camera
            showCameraStatus(null);
            
            console.log('✅ Fallback scanner started successfully');
            console.log('📷 Using default system camera');
        } catch (fallbackError) {
            console.error('❌ Fallback scanner also failed:', fallbackError);
            $('#cameraLoading').hide();
            showError('Tidak dapat menginisialisasi scanner. Pastikan kamera tersedia dan izin diberikan.');
        }
    }
}

function stopScanning() {
    if (html5QrcodeScanner && isScanning) {
        console.log('Stopping QR scan...');
        html5QrcodeScanner.clear().then(() => {
            console.log('Scanner cleared successfully');
        }).catch(error => {
            console.error('Error clearing scanner:', error);
        });
        
        html5QrcodeScanner = null;
        isScanning = false;
    }
}

function onScanSuccess(decodedText, decodedResult) {
    console.log('QR Code scanned successfully:', decodedText);
    
    // Stop scanning immediately after successful scan
    stopScanning();
    $('#scannerModal').modal('hide');
    
    // Show loading state
    showLoading(true);
    
    // Process the scanned data
    processScannedData(decodedText);
}

function onScanFailure(error) {
    // This will be called for every failed scan attempt
    // We don't want to log every failed attempt as it's normal
    // console.warn('QR scan failed:', error);
}

function processScannedData(scannedText) {
    console.log('Processing scanned data:', scannedText);
    
    try {
        // Try to parse as JSON first
        let qrData;
        try {
            qrData = JSON.parse(scannedText);
            console.log('Parsed QR data as JSON:', qrData);
        } catch (e) {
            // If not JSON, treat as plain text
            console.log('QR data is plain text:', scannedText);
            qrData = {
                raw_data: scannedText,
                scanned_at: new Date().toISOString()
            };
        }
        
        // Validate if it's our QR code format
        if (qrData.part_id || qrData.no_parts || qrData.raw_data) {
            displayProductInfo(qrData);
            showQualityCheck();
            addToScanHistory(qrData);
        } else {
            throw new Error('QR code format tidak valid');
        }
        
    } catch (error) {
        console.error('Error processing scanned data:', error);
        showError('QR code tidak dapat diproses: ' + error.message);
    } finally {
        showLoading(false);
    }
}

function displayProductInfo(qrData) {
    console.log('Displaying product info:', qrData);
    
    let productHtml = '';
    
    if (qrData.part_id && qrData.no_parts) {
        // Our QR code format
        productHtml = `
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-check-circle mr-2"></i>
                        Produk Berhasil Di-scan
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID Part:</strong></td>
                                    <td>${qrData.part_id}</td>
                                </tr>
                                <tr>
                                    <td><strong>No. Parts:</strong></td>
                                    <td><span class="badge badge-primary">${qrData.no_parts}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Nama Part:</strong></td>
                                    <td>${qrData.nama_part || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <td><strong>Unique ID:</strong></td>
                                    <td><code>${qrData.unique_id || 'N/A'}</code></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Waktu Generate:</strong></td>
                                    <td>${formatDateTime(qrData.generated_at)}</td>
                                </tr>
                                <tr>
                                    <td><strong>Waktu Scan:</strong></td>
                                    <td>${formatDateTime(new Date().toISOString())}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td><span class="badge badge-success">Valid</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        `;
    } else {
        // Unknown QR code format
        productHtml = `
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        QR Code Tidak Dikenal
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Data yang di-scan:</strong>
                        <br><code>${qrData.raw_data || JSON.stringify(qrData)}</code>
                    </div>
                    <p class="text-muted">
                        QR code ini bukan dari sistem DIGIPRO. Pastikan Anda scan QR code yang di-generate dari sistem.
                    </p>
                </div>
            </div>
        `;
    }
    
    $('#productInfo').html(productHtml).show();
}

function showQualityCheck() {
    $('#qualityCheck').show();
    
    // Handle quality check buttons
    $('.quality-btn').off('click').on('click', function() {
        const quality = $(this).data('quality');
        handleQualityCheck(quality);
    });
}

function handleQualityCheck(quality) {
    console.log('Quality check:', quality);
    
    // Remove previous selections
    $('.quality-btn').removeClass('selected');
    
    // Add selection to clicked button
    $(`.quality-btn[data-quality="${quality}"]`).addClass('selected');
    
    // Show result based on quality
    let message = '';
    let alertClass = '';
    
    switch(quality) {
        case 'good':
            message = '<i class="fas fa-thumbs-up mr-2"></i>Produk berkualitas BAIK';
            alertClass = 'alert-success';
            break;
        case 'ng':
            message = '<i class="fas fa-times-circle mr-2"></i>Produk tidak memenuhi standar (NG)';
            alertClass = 'alert-danger';
            break;
        case 'retry':
            message = '<i class="fas fa-redo mr-2"></i>Produk perlu di-cek ulang (RETRY)';
            alertClass = 'alert-warning';
            break;
    }
    
    // Show quality result
    if ($('#qualityResult').length === 0) {
        $('#qualityCheck').append('<div id="qualityResult" class="mt-3"></div>');
    }
    
    $('#qualityResult').html(`
        <div class="alert ${alertClass}">
            ${message}
        </div>
    `);
    
    // You can send this data to server if needed
    // saveQualityCheck(quality);
}

function addToScanHistory(qrData) {
    const historyItem = `
        <div class="scan-history-item border-bottom pb-2 mb-2">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>${qrData.no_parts || 'Unknown'}</strong>
                    <br>
                    <small class="text-muted">${qrData.nama_part || qrData.raw_data || 'No description'}</small>
                </div>
                <div class="text-right">
                    <small class="text-muted">
                        <i class="fas fa-clock mr-1"></i>
                        ${formatDateTime(new Date().toISOString())}
                    </small>
                </div>
            </div>
        </div>
    `;
    
    // If no history yet, clear the empty message
    if ($('#scanHistory .text-center').length > 0) {
        $('#scanHistory').html('');
    }
    
    // Prepend new item to history
    $('#scanHistory').prepend(historyItem);
    
    // Keep only last 10 items
    $('#scanHistory .scan-history-item:gt(9)').remove();
}

function showLoading(show) {
    if (show) {
        $('#loading').show();
        $('#productInfo').hide();
        $('#qualityCheck').hide();
    } else {
        $('#loading').hide();
    }
}

function showError(message) {
    const errorHtml = `
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <strong>Error:</strong> ${message}
        </div>
    `;
    
    $('#productInfo').html(errorHtml).show();
    $('#qualityCheck').hide();
}

function formatDateTime(dateString) {
    if (!dateString) return 'N/A';
    
    try {
        const date = new Date(dateString);
        return date.toLocaleString('id-ID', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    } catch (e) {
        return dateString;
    }
}


async function getAvailableCameras() {
    try {
        if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
            console.warn('❌ Media devices API not supported');
            showCameraWarning('Browser tidak mendukung akses kamera. Gunakan browser yang lebih baru.');
            return;
        }
        
        console.log('✅ Media devices API supported');
        
        // Request permission first to get device labels
        console.log('🔐 Requesting camera permission...');
        try {
            await navigator.mediaDevices.getUserMedia({ video: true });
            console.log('✅ Camera permission granted');
        } catch (permissionError) {
            console.warn('❌ Camera permission denied:', permissionError);
            showCameraWarning('Izin akses kamera diperlukan untuk scan QR code. Silakan izinkan akses kamera.');
            return;
        }
        
        const devices = await navigator.mediaDevices.enumerateDevices();
        availableCameras = devices.filter(device => device.kind === 'videoinput');
        
        console.log('📷 Available cameras found:', availableCameras.length);
        availableCameras.forEach((camera, index) => {
            console.log(`  Camera ${index + 1}: "${camera.label}" (${camera.deviceId})`);
        });
        
        if (availableCameras.length === 0) {
            console.warn('❌ No cameras detected');
            showCameraWarning('Tidak ada kamera yang terdeteksi. Pastikan kamera tersedia dan tidak digunakan aplikasi lain.');
            return;
        }
        
        console.log('✅ Camera detection completed successfully');
        
        // Try to identify rear camera
        console.log('🔍 Starting rear camera identification...');
        identifyRearCamera();
        console.log('✅ Rear camera identification completed');
        
        // Show camera info to user
        console.log('📊 Displaying camera information...');
        showCameraInfo();
        console.log('✅ Camera setup completed successfully');
        console.log('🎯 Ready to start scanning with rear camera priority');
        console.log('📱 Rear camera ID:', rearCameraId);
        
    } catch (error) {
        console.error('❌ Error getting available cameras:', error);
        showCameraWarning('Gagal mengakses informasi kamera: ' + error.message);
    }
}

function showCameraWarning(message) {
    console.warn('Camera warning:', message);
    // You can show a toast notification or alert here if needed
}

function showCameraInfo() {
    if (availableCameras.length > 0) {
        console.log('=== CAMERA INFORMATION ===');
        console.log(`Total cameras found: ${availableCameras.length}`);
        
        availableCameras.forEach((camera, index) => {
            console.log(`Camera ${index + 1}:`, {
                label: camera.label,
                deviceId: camera.deviceId,
                isRear: camera.deviceId === rearCameraId
            });
        });
        
        const rearCamera = availableCameras.find(cam => cam.deviceId === rearCameraId);
        if (rearCamera) {
            console.log('✅ Using REAR camera:', rearCamera.label);
            console.log('📱 Rear camera ID:', rearCamera.deviceId);
        } else {
            console.log('⚠️ No rear camera identified, using default');
        }
        
        if (availableCameras.length > 1) {
            console.log(`🔄 Found ${availableCameras.length} cameras available for switching`);
        }
        console.log('========================');
    }
}

function identifyRearCamera() {
    console.log('🔍 Identifying rear camera...');
    
    // Try to identify rear camera based on device labels
    for (let i = 0; i < availableCameras.length; i++) {
        const camera = availableCameras[i];
        const label = camera.label.toLowerCase();
        
        console.log(`Checking camera ${i + 1}: "${camera.label}"`);
        
        // Common rear camera identifiers
        if (label.includes('back') || 
            label.includes('rear') || 
            label.includes('environment') ||
            label.includes('facing back') ||
            label.includes('world') ||
            (!label.includes('front') && !label.includes('facing front') && !label.includes('user'))) {
            rearCameraId = camera.deviceId;
            console.log('✅ IDENTIFIED REAR CAMERA:', camera.label);
            console.log('📱 Rear camera device ID:', camera.deviceId);
            break;
        } else {
            console.log('❌ Not rear camera (contains front/user indicators)');
        }
    }
    
    // If no rear camera found, use the first available camera
    if (!rearCameraId && availableCameras.length > 0) {
        rearCameraId = availableCameras[0].deviceId;
        console.log('⚠️ No rear camera identified, using first available camera as rear:', availableCameras[0].label);
        console.log('📱 Using device ID:', availableCameras[0].deviceId);
    }
    
    if (!rearCameraId) {
        console.log('❌ No cameras available');
    }
}

function getPreferredCameraId() {
    // Return rear camera ID if available, otherwise null (will use default)
    return rearCameraId;
}

function showCameraStatus(cameraId) {
    const camera = availableCameras.find(cam => cam.deviceId === cameraId);
    
    if (camera) {
        const isRear = cameraId === rearCameraId;
        const cameraType = isRear ? 'Belakang' : 'Depan';
        const icon = isRear ? 'fas fa-camera' : 'fas fa-user';
        const alertClass = isRear ? 'alert-success' : 'alert-warning';
        
        // Update status text
        $('#cameraStatusText').html(`
            <strong>${cameraType}</strong> - ${camera.label}
            ${isRear ? '<span class="badge badge-success ml-2">✓ Rear Camera</span>' : '<span class="badge badge-warning ml-2">Front Camera</span>'}
        `);
        
        // Update alert class
        $('#cameraStatus').removeClass('alert-info alert-success alert-warning')
                          .addClass(alertClass);
        
        // Update icon
        $('#cameraStatus i').removeClass('fas fa-camera fas fa-user')
                           .addClass(icon);
        
        // Show status
        $('#cameraStatus').show();
        
        // Console log for debugging
        console.log(`📷 Camera Status: ${cameraType} camera active`);
        console.log(`📱 Camera Label: ${camera.label}`);
        console.log(`🔍 Camera ID: ${camera.deviceId}`);
        console.log(`✅ Is Rear Camera: ${isRear}`);
        
    } else {
        // Default camera (no specific ID)
        $('#cameraStatusText').html(`
            <strong>Default</strong> - Camera default sistem
            <span class="badge badge-secondary ml-2">Default</span>
        `);
        $('#cameraStatus').removeClass('alert-success alert-warning')
                          .addClass('alert-info');
        $('#cameraStatus i').removeClass('fas fa-user').addClass('fas fa-camera');
        $('#cameraStatus').show();
        
        console.log('📷 Camera Status: Default camera active');
    }
}

async function switchCamera() {
    if (!html5QrcodeScanner || availableCameras.length <= 1) {
        console.log('Cannot switch camera - insufficient cameras available');
        return;
    }
    
    try {
        // Show loading indicator
        $('#cameraLoading').show();
        $('#interactive').hide();
        
        // Stop current scanner
        await stopScanning();
        
        // Switch to next camera
        currentCameraIndex = (currentCameraIndex + 1) % availableCameras.length;
        const nextCamera = availableCameras[currentCameraIndex];
        
        console.log('🔄 Switching to camera:', nextCamera.label);
        console.log('📱 New camera ID:', nextCamera.deviceId);
        console.log('🔍 Is Rear Camera:', nextCamera.deviceId === rearCameraId);
        
        // Restart scanner with new camera
        setTimeout(async () => {
            await initializeQrScannerWithCamera(nextCamera.deviceId);
        }, 300);
        
    } catch (error) {
        console.error('Error switching camera:', error);
        $('#cameraLoading').hide();
        showError('Gagal mengganti kamera: ' + error.message);
    }
}

async function initializeQrScannerWithCamera(cameraId) {
    try {
        const camera = availableCameras.find(cam => cam.deviceId === cameraId);
        console.log('🚀 Initializing scanner with camera:', camera ? camera.label : 'Unknown');
        console.log('📱 Camera ID:', cameraId);
        console.log('🔍 Is Rear Camera:', cameraId === rearCameraId);
        
        const config = {
            fps: 10,
            qrbox: {
                width: 280,
                height: 280
            },
            aspectRatio: 1.0,
            disableFlip: false,
            supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
        };
        
        html5QrcodeScanner = new Html5QrcodeScanner(
            "interactive",
            config,
            false
        );
        
        await html5QrcodeScanner.render(onScanSuccess, onScanFailure, {
            preferredCamera: cameraId
        });
        
        // Hide loading and show scanner
        $('#cameraLoading').hide();
        $('#interactive').show();
        isScanning = true;
        
        // Show camera status
        showCameraStatus(cameraId);
        
        // Update button text to show current camera
        const currentCamera = availableCameras[currentCameraIndex];
        const cameraType = currentCamera.label.toLowerCase().includes('back') || 
                          currentCamera.label.toLowerCase().includes('rear') ? 'Belakang' : 'Depan';
        $('#cameraButtonText').text(`Ganti Kamera (${cameraType})`);
        
    } catch (error) {
        console.error('Error initializing scanner with camera:', error);
        $('#cameraLoading').hide();
        showError('Tidak dapat mengakses kamera yang dipilih');
    }
}