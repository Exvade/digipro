/**
 * QR Code Generator JavaScript
 * Handles QR code generation, preview, and download functionality
 */

class QRCodeGenerator {
    constructor() {
        this.maxAttempts = 10;
        this.attemptDelay = 500;
        this.init();
    }

    init() {
        this.setupCDNFallback();
        this.bindEvents();
    }

    /**
     * Setup QR code library check and fallback
     */
    setupCDNFallback() {
        // Check if both libraries are loaded from CDN
        if (typeof QRCode !== 'undefined' && typeof qrcode !== 'undefined') {
            console.log('✅ QR code libraries already loaded from CDN');
            this.updateCDNStatus('CDN Loaded');
            this.updateLibraryVersion();
            return;
        }
        
        console.log('⚠️ One or more QR code libraries not found, loading from CDN...');
        this.updateCDNStatus('Loading from CDN...');
        
        // Load libraries from CDN if not already loaded
        const loadLibraries = [];
        
        if (typeof qrcode === 'undefined') {
            loadLibraries.push(this.loadScript('https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js'));
        }
        
        if (typeof QRCode === 'undefined') {
            loadLibraries.push(this.loadScript('https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js'));
        }
        
        // Wait for all libraries to load
         Promise.all(loadLibraries)
             .then(() => {
                 console.log('✅ QR code libraries loaded from CDN');
                 this.updateCDNStatus('CDN Loaded');
                 this.updateLibraryVersion();
                 
                 // Test if libraries are working
                 setTimeout(() => {
                     if (this.testQRCodeGeneration()) {
                         console.log('✅ QR code libraries from CDN are working');
                         this.showSuccess('QR code libraries berhasil dimuat dari CDN.');
                     } else {
                         console.error('❌ QR code libraries from CDN loaded but not working');
                         this.updateCDNStatus('CDN Loaded but Not Working');
                         this.handleCDNFailure();
                     }
                 }, 500);
             })
             .catch((error) => {
                 console.error('❌ Failed to load QR code libraries from CDN:', error);
                 this.updateCDNStatus('CDN Failed');
                 
                 // Try one more time with different CDN
                 console.log('Trying alternative CDN sources...');
                 this.updateCDNStatus('Trying Alternative CDN...');
                 
                 const alternativeLoadLibraries = [];
                 
                 if (typeof qrcode === 'undefined') {
                     alternativeLoadLibraries.push(this.loadScript('https://unpkg.com/qrcode-generator@1.4.4/qrcode.js'));
                 }
                 
                 if (typeof QRCode === 'undefined') {
                     alternativeLoadLibraries.push(this.loadScript('https://unpkg.com/qrcode@1.5.3/build/qrcode.min.js'));
                 }
                 
                 Promise.all(alternativeLoadLibraries)
                     .then(() => {
                         console.log('✅ QR code libraries loaded from alternative CDN');
                         this.updateCDNStatus('Alternative CDN Loaded');
                         this.updateLibraryVersion();
                         
                         // Test if libraries are working
                         setTimeout(() => {
                             if (this.testQRCodeGeneration()) {
                                 console.log('✅ QR code libraries from alternative CDN are working');
                                 this.showSuccess('QR code libraries berhasil dimuat dari CDN alternatif.');
                             } else {
                                 console.error('❌ QR code libraries from alternative CDN loaded but not working');
                                 this.handleCDNFailure();
                             }
                         }, 500);
                     })
                     .catch((altError) => {
                         console.error('❌ Failed to load QR code libraries from alternative CDN:', altError);
                         this.updateCDNStatus('All CDNs Failed');
                         this.handleCDNFailure();
                     });
             });
    }
    
    /**
     * Load script asynchronously
     */
    loadScript(src) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            
            script.onload = () => resolve();
            script.onerror = () => reject(new Error(`Failed to load script: ${src}`));
            
            document.head.appendChild(script);
        });
    }

    /**
     * Wait for QRCode libraries to be ready
     */
    waitForQRCode(callback) {
        let attempts = 0;
        
        const checkQRCode = () => {
            attempts++;
            console.log(`Checking QRCode libraries... Attempt ${attempts}/${this.maxAttempts}`);
            
            // Check for any available QR code library
            if (typeof QRCode !== 'undefined' || typeof qrcode !== 'undefined' || 
                (window.QRCode && typeof window.QRCode === 'function')) {
                
                const qrcodeLoaded = typeof QRCode !== 'undefined';
                const qrcodeGeneratorLoaded = typeof qrcode !== 'undefined';
                console.log(`✅ QR code libraries detected: QRCode=${qrcodeLoaded}, qrcode-generator=${qrcodeGeneratorLoaded}`);
                
                // Test QR code generation with any available method
                if (this.testQRCodeGeneration()) {
                    console.log('✅ QR code generation test passed');
                    callback();
                } else {
                    console.error('❌ QR code generation test failed with all available methods');
                    this.showError('Libraries QR code terdeteksi tetapi tidak dapat generate QR code. Silakan refresh halaman atau periksa koneksi internet Anda.');
                }
            } else if (attempts < this.maxAttempts) {
                console.log(`⏳ Waiting for QR code libraries... Attempt ${attempts}/${this.maxAttempts}`);
                setTimeout(checkQRCode, this.attemptDelay);
            } else {
                console.error('❌ QR code libraries failed to load after multiple attempts');
                this.showError('Libraries QR code tidak dapat dimuat setelah beberapa percobaan. Silakan refresh halaman atau periksa koneksi internet Anda.');
            }
        };
        
        checkQRCode();
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Part selection change
        $(document).on('change', '#part_id', (e) => {
            this.handlePartSelection(e);
        });

        // Form submission
        $(document).on('submit', '#qrForm', (e) => {
            this.handleFormSubmission(e);
        });

        // Download buttons
        $(document).on('click', '.download-btn', (e) => {
            e.preventDefault();
            const index = $(e.target).data('index');
            const uniqueId = $(e.target).data('unique-id');
            this.downloadQRCode(index, uniqueId);
        });

        // Download all button
        $(document).on('click', '#downloadAll', (e) => {
            e.preventDefault();
            this.downloadAll();
        });
    }

    /**
     * Handle part selection for preview
     */
    handlePartSelection(event) {
        const selectedOption = $(event.target).find('option:selected');
        const noParts = selectedOption.data('no-parts');
        const namaPart = selectedOption.data('nama-part');
        
        if (noParts && namaPart) {
            this.showPartInfo(noParts, namaPart);
            this.generatePreview(noParts, namaPart);
        } else {
            this.hidePreview();
        }
    }

    /**
     * Show part information
     */
    showPartInfo(noParts, namaPart) {
        $('#partInfo').html(`
            <p><strong>No. Parts:</strong> ${noParts}</p>
            <p><strong>Nama Part:</strong> ${namaPart}</p>
        `);
    }

    /**
     * Generate preview QR code
     */
    generatePreview(noParts, namaPart) {
        const qrData = {
            no_parts: noParts,
            nama_part: namaPart,
            preview: true,
            unique_id: noParts + '_preview_' + Date.now()
        };
        
        $('#qrPreview').empty();
        $('#qrText').text(JSON.stringify(qrData, null, 2));
        
        console.log('Generating preview QR code:', qrData);
        
        this.waitForQRCode(() => {
            const container = $('#qrPreview')[0];
            const qrText = JSON.stringify(qrData);
            
            // Try using QRCode.toCanvas first
            if (typeof QRCode.toCanvas === 'function') {
                console.log('Using QRCode.toCanvas for preview');
                QRCode.toCanvas(container, qrText, {
                    width: 200,
                    height: 200,
                    margin: 2,
                    color: {
                        dark: '#000000',
                        light: '#FFFFFF'
                    }
                }, (error) => {
                    if (error) {
                        console.error('Error generating preview QR code with toCanvas:', error);
                        this.tryAlternativePreviewQRCode(container, qrText);
                    } else {
                        console.log('Preview QR code generated successfully with toCanvas');
                    }
                });
            } else {
                // Try alternative methods
                this.tryAlternativePreviewQRCode(container, qrText);
            }
        });
        
        $('#previewSection').show();
    }
    
    /**
     * Try alternative QR code generation for preview
     */
    tryAlternativePreviewQRCode(container, qrText) {
        console.log('Trying alternative QR code generation for preview');
        
        try {
            // Try using qrcode-generator library
            if (typeof qrcode !== 'undefined') {
                console.log('Using qrcode-generator for preview');
                try {
                    const qr = qrcode(0, 'L');
                    qr.addData(qrText);
                    qr.make();
                    
                    const img = document.createElement('img');
                    img.src = qr.createDataURL(10);
                    img.width = 200;
                    img.height = 200;
                    container.appendChild(img);
                    console.log('Preview QR code generated successfully with qrcode-generator');
                    return; // Success, exit function
                } catch (error) {
                    console.error('qrcode-generator execution failed for preview:', error);
                    // Continue to next method
                }
            }
            
            // Try using QRCode constructor
            if (window.QRCode && typeof window.QRCode === 'function') {
                console.log('Using QRCode constructor for preview');
                try {
                    // Check if CorrectLevel is defined
                    let correctLevel = 'H';
                    if (typeof QRCode !== 'undefined' && QRCode.CorrectLevel) {
                        correctLevel = QRCode.CorrectLevel.H;
                    } else if (window.QRCode.CorrectLevel) {
                        correctLevel = window.QRCode.CorrectLevel.H;
                    }
                    
                    new QRCode(container, {
                        text: qrText,
                        width: 200,
                        height: 200,
                        colorDark: '#000000',
                        colorLight: '#FFFFFF',
                        correctLevel: correctLevel
                    });
                    console.log('Preview QR code generated successfully with QRCode constructor');
                    return; // Success, exit function
                } catch (error) {
                    console.error('QRCode constructor execution failed for preview:', error);
                }
            }
            
            // If we get here, all methods failed
            console.error('No compatible QR code library found for preview or all methods failed');
            $(container).html(`
                <div class="alert alert-warning">
                    <small>Preview QR Code generation failed</small>
                </div>
            `);
        } catch (error) {
            console.error('Exception during alternative preview QR code generation:', error);
            $(container).html(`
                <div class="alert alert-warning">
                    <small>Preview QR Code generation failed: ${error.message}</small>
                </div>
            `);
        }
    }

    /**
     * Hide preview section
     */
    hidePreview() {
        $('#previewSection').hide();
    }

    /**
     * Handle form submission
     */
    handleFormSubmission(event) {
        const form = $(event.target);
        const partId = form.find('#part_id').val();
        const quantity = form.find('#quantity').val();
        
        if (!partId || !quantity) {
            event.preventDefault();
            this.showError('Silakan pilih part dan masukkan jumlah QR code');
            return false;
        }
        
        // Show loading state
        form.find('button[type="submit"]').prop('disabled', true).html(`
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Generating QR Codes...
        `);
    }

    /**
     * Generate QR codes for result page
     */
    generateQRCodes(qrCodes) {
        console.log('QR Codes data:', qrCodes);
        
        this.waitForQRCode(() => {
            this.updateStatus('Loaded Successfully', 'success');
            this.updateBrowserInfo();
            
            qrCodes.forEach((qrCode, index) => {
                this.generateSingleQRCode(qrCode, index);
            });
        });
    }

    /**
     * Generate single QR code
     */
    generateSingleQRCode(qrCode, index) {
        const container = document.getElementById(`qrCode${index}`);
        
        if (!container) {
            console.error(`❌ Container qrCode${index} not found`);
            return;
        }
        
        console.log(`🔄 Generating QR code ${index}:`, qrCode.qr_text);
        
        // Clear container first
        container.innerHTML = '';
        
        try {
            // First try using qrcode npm package (QRCode.toCanvas)
            if (typeof QRCode !== 'undefined' && typeof QRCode.toCanvas === 'function') {
                console.log('Using QRCode.toCanvas method');
                QRCode.toCanvas(container, qrCode.qr_text, {
                    width: 200,
                    height: 200,
                    margin: 2,
                    color: {
                        dark: '#000000',
                        light: '#FFFFFF'
                    }
                }, (error) => {
                    if (error) {
                        console.error('❌ Error generating QR code with toCanvas:', error);
                        this.tryAlternativeQRCode(container, qrCode, index);
                    } else {
                        console.log(`✅ QR Code ${index} generated successfully with toCanvas`);
                    }
                });
            } else {
                // Try alternative QRCode library
                this.tryAlternativeQRCode(container, qrCode, index);
            }
        } catch (error) {
            console.error('❌ Exception during QR code generation:', error);
            this.tryAlternativeQRCode(container, qrCode, index);
        }
    }
    
    /**
     * Try alternative QR code generation method
     */
    tryAlternativeQRCode(container, qrCode, index) {
        console.log('Trying alternative QR code generation method');
        
        try {
            // Try using qrcode-generator library
            if (typeof qrcode !== 'undefined') {
                console.log('Using qrcode-generator library');
                const qr = qrcode(0, 'L');
                qr.addData(qrCode.qr_text);
                qr.make();
                
                const img = document.createElement('img');
                img.src = qr.createDataURL(10);
                img.width = 200;
                img.height = 200;
                container.appendChild(img);
                console.log(`✅ QR Code ${index} generated successfully with qrcode-generator`);
            } else if (window.QRCode && typeof window.QRCode === 'function') {
                // Try using QRCode constructor (HTML5-QRCode or similar)
                console.log('Using QRCode constructor');
                new QRCode(container, {
                    text: qrCode.qr_text,
                    width: 200,
                    height: 200,
                    colorDark: '#000000',
                    colorLight: '#FFFFFF',
                    correctLevel: QRCode.CorrectLevel.H
                });
                console.log(`✅ QR Code ${index} generated successfully with QRCode constructor`);
            } else {
                console.error('❌ No compatible QR code library found');
                this.showQRCodeError(container, qrCode.qr_text);
            }
        } catch (error) {
            console.error('❌ Exception during alternative QR code generation:', error);
            this.showQRCodeError(container, qrCode.qr_text);
        }
    }

    /**
     * Show QR code error
     */
    showQRCodeError(container, qrText) {
        container.innerHTML = `
            <div class="text-center">
                <div class="alert alert-warning">
                    <small>QR Code generation failed. Data: ${qrText}</small>
                </div>
            </div>
        `;
    }

    /**
     * Update status indicator
     */
    updateStatus(status, type = 'info') {
        const statusElement = document.getElementById('qrcodeStatus');
        if (statusElement) {
            statusElement.textContent = status;
            statusElement.className = `status-${type}`;
        }
    }

    /**
     * Update browser information
     */
    updateBrowserInfo() {
        const browserElement = document.getElementById('browserInfo');
        if (browserElement) {
            browserElement.textContent = navigator.userAgent;
        }
    }

    /**
     * Update library status
     */
    updateCDNStatus(status, cdnNumber = null) {
        const cdnElement = document.getElementById('cdnStatus');
        if (cdnElement) {
            cdnElement.textContent = status;
            
            // Update status color based on CDN status
            if (status === 'CDN Loaded' || status === 'Already Loaded') {
                cdnElement.className = 'status-success';
            } else if (status === 'Loading from CDN...') {
                cdnElement.className = 'status-warning';
            } else if (status === 'CDN Failed') {
                cdnElement.className = 'status-danger';
            }
            
            // Add CDN indicator if using CDN
            if (status.includes('CDN')) {
                const cdnIndicator = document.createElement('span');
                cdnIndicator.className = 'badge badge-info ml-1';
                cdnIndicator.textContent = 'CDN';
                
                // Remove any existing CDN indicator before adding a new one
                const existingIndicator = cdnElement.querySelector('.badge.badge-info');
                if (existingIndicator) {
                    cdnElement.removeChild(existingIndicator);
                }
                
                cdnElement.appendChild(cdnIndicator);
            }
        }
    }

    /**
     * Update library version
     */
    updateLibraryVersion() {
        const versionElement = document.getElementById('libraryVersion');
        if (versionElement) {
            const qrcodeLoaded = typeof QRCode !== 'undefined';
            const qrcodeGeneratorLoaded = typeof qrcode !== 'undefined';
            
            if (qrcodeLoaded && qrcodeGeneratorLoaded) {
                versionElement.textContent = 'Both QR Code Libraries Loaded';
            } else if (qrcodeLoaded) {
                versionElement.textContent = QRCode.version || '1.5.3 (estimated)';
            } else if (qrcodeGeneratorLoaded) {
                versionElement.textContent = 'qrcode-generator Loaded';
            } else {
                versionElement.textContent = 'No QR Code Libraries Loaded';
            }
        }
    }

    /**
     * Download single QR code
     */
    downloadQRCode(index, uniqueId) {
        const canvas = document.querySelector(`#qrCode${index} canvas`);
        if (canvas) {
            const link = document.createElement('a');
            link.download = `qrcode_${uniqueId}.png`;
            link.href = canvas.toDataURL();
            link.click();
        } else {
            this.showError('QR Code tidak tersedia untuk download');
        }
    }

    /**
     * Download all QR codes
     */
    downloadAll() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = window.qrCodeDownloadUrl || '/qrcode/download-all';
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        form.appendChild(csrfToken);
        
        // Add QR codes data
        if (window.qrCodes) {
            window.qrCodes.forEach((qrCode, index) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'qr_codes[]';
                input.value = qrCode.qr_text;
                form.appendChild(input);
            });
        }
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    /**
     * Show error message
     */
    showError(message) {
        // Show error in alert with refresh button
        const alert = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <strong>Error:</strong> ${message}
                <div class="mt-2">
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Refresh Halaman
                    </button>
                </div>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;
        
        // Remove existing alerts
        $('.alert-danger').remove();
        
        // Add new alert
        $('.card-body').prepend(alert);
        
        // Log error to console
        console.error(`QR Code Error: ${message}`);
        
        // Don't auto-hide error messages that require user action
        if (!message.includes('tidak dapat dimuat')) {
            // Auto-hide after 8 seconds for non-critical errors
            setTimeout(() => {
                $('.alert-danger').fadeOut();
            }, 8000);
        }
    }
    
    /**
     * Show success message
     */
    showSuccess(message) {
        // Show success in alert
        const alert = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                <strong>Sukses:</strong> ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;
        
        // Remove existing success alerts
        $('.alert-success').remove();
        
        // Add new alert
        $('.card-body').prepend(alert);
        
        // Log success to console
        console.log(`QR Code Success: ${message}`);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            $('.alert-success').fadeOut();
        }, 5000);
    }
    
    /**
     * Handle CDN failure with fallback options
     */
    handleCDNFailure() {
        console.log('Handling CDN failure with fallback options');
        
        const alert = `
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Peringatan:</strong> QR code libraries tidak dapat dimuat dari CDN.
                <div class="mt-2">
                    <p>Silakan pilih salah satu opsi berikut:</p>
                    <button type="button" class="btn btn-sm btn-primary mr-2" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Refresh Halaman
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="window.qrCodeGenerator.setupCDNFallback()">
                        <i class="fas fa-redo"></i> Coba Lagi
                    </button>
                </div>
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;
        
        // Remove existing alerts
        $('.alert-warning').remove();
        
        // Add new alert
        $('.card-body').prepend(alert);
    }

    /**
     * Show success message
     */
    showSuccess(message) {
        const alert = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;
        
        // Remove existing alerts
        $('.alert-success').remove();
        
        // Add new alert
        $('.card-body').prepend(alert);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            $('.alert-success').fadeOut();
        }, 5000);
    }

    /**
     * Initialize QR code generation for result page
     */
    initResultPage(qrCodes) {
        if (qrCodes && qrCodes.length > 0) {
            this.generateQRCodes(qrCodes);
        }
    }

    /**
     * Test QR code generation
     */
    testQRCodeGeneration() {
        if (typeof QRCode === 'undefined' && typeof qrcode === 'undefined') {
            console.log('❌ No QRCode library available for testing');
            return false;
        }
        
        try {
            // Create a test container
            const testContainer = document.createElement('div');
            testContainer.id = 'qrcode-test-container';
            testContainer.style.position = 'absolute';
            testContainer.style.left = '-9999px';
            testContainer.style.top = '-9999px';
            document.body.appendChild(testContainer);
            
            // Test QR code generation with different methods
            if (typeof QRCode !== 'undefined' && typeof QRCode.toCanvas === 'function') {
                // Test with QRCode.toCanvas
                console.log('Testing QRCode.toCanvas method...');
                try {
                    QRCode.toCanvas(testContainer, 'test', {
                        width: 100,
                        height: 100,
                        margin: 1
                    }, (error) => {
                        if (error) {
                            console.log('❌ QRCode.toCanvas test failed:', error);
                            this.testAlternativeQRCodeGeneration(testContainer);
                        } else {
                            console.log('✅ QRCode.toCanvas test successful');
                            document.body.removeChild(testContainer);
                        }
                    });
                    return true;
                } catch (error) {
                    console.log('❌ QRCode.toCanvas execution failed:', error);
                    return this.testAlternativeQRCodeGeneration(testContainer);
                }
            } else {
                console.log('QRCode.toCanvas method not available, trying alternatives...');
                // Try alternative methods
                return this.testAlternativeQRCodeGeneration(testContainer);
            }
        } catch (error) {
            console.log('❌ QR code generation test exception:', error);
            return false;
        }
    }
    
    /**
     * Test alternative QR code generation methods
     */
    testAlternativeQRCodeGeneration(testContainer) {
        try {
            // Try qrcode-generator library if available
            if (typeof qrcode !== 'undefined') {
                console.log('Testing qrcode-generator library...');
                try {
                    const qr = qrcode(0, 'L');
                    qr.addData('test');
                    qr.make();
                    
                    const img = document.createElement('img');
                    img.src = qr.createDataURL(5);
                    testContainer.appendChild(img);
                    
                    console.log('✅ qrcode-generator test successful');
                    document.body.removeChild(testContainer);
                    return true;
                } catch (error) {
                    console.error('❌ qrcode-generator execution failed:', error);
                    // Continue to next method
                }
            }
            
            // Try QRCode constructor if available
            if (window.QRCode && typeof window.QRCode === 'function') {
                console.log('Testing QRCode constructor...');
                try {
                    // Check if CorrectLevel is defined
                    const errorCorrectionLevel = window.QRCode.CorrectLevel ? 
                        window.QRCode.CorrectLevel.L : 
                        (window.QRCode.CorrectLevel ? window.QRCode.CorrectLevel.L : 'L');
                    
                    new QRCode(testContainer, {
                        text: 'test',
                        width: 100,
                        height: 100,
                        correctLevel: errorCorrectionLevel
                    });
                    
                    console.log('✅ QRCode constructor test successful');
                    document.body.removeChild(testContainer);
                    return true;
                } catch (error) {
                    console.error('❌ QRCode constructor execution failed:', error);
                }
            }
            
            console.log('❌ No compatible QR code library found for testing or all methods failed');
            document.body.removeChild(testContainer);
            return false;
        } catch (error) {
            console.error('❌ Alternative QR code generation test exception:', error);
            if (document.body.contains(testContainer)) {
                document.body.removeChild(testContainer);
            }
            return false;
        }
    }
}

// Initialize QR Code Generator
$(document).ready(() => {
    window.qrCodeGenerator = new QRCodeGenerator();
    
    // Initialize result page if qrCodes data is available
    if (typeof qrCodes !== 'undefined') {
        window.qrCodeGenerator.initResultPage(qrCodes);
    }
    
    // Add debugging commands to window object
    window.qrDebug = {
        // Test QRCode library
        testLibrary: () => {
            console.log('=== QR Code Library Test ===');
            console.log('QRCode available:', typeof QRCode !== 'undefined');
            console.log('QRCode.toCanvas available:', typeof QRCode?.toCanvas === 'function');
            console.log('QRCode version:', QRCode?.version || 'Unknown');
            
            // Check for alternative libraries
            console.log('\n=== Alternative QR Code Libraries ===');
            console.log('qrcode-generator available:', typeof qrcode !== 'undefined');
            console.log('QRCode constructor available:', window.QRCode && typeof window.QRCode === 'function');
            
            // Check which library is being used
            console.log('\n=== Active QR Code Library ===');
            if (typeof QRCode !== 'undefined' && typeof QRCode.toCanvas === 'function') {
                console.log('Active library: QRCode (npm package) with toCanvas method');
            } else if (typeof qrcode !== 'undefined') {
                console.log('Active library: qrcode-generator');
            } else if (window.QRCode && typeof window.QRCode === 'function') {
                console.log('Active library: QRCode constructor');
            } else {
                console.log('No active QR code library detected');
            }
        },
        
        // Test QR code generation
        testGeneration: () => {
            if (window.qrCodeGenerator) {
                window.qrCodeGenerator.testQRCodeGeneration();
            } else {
                console.log('QR Code Generator not available');
            }
        },
        
        // Check library status
        checkCDN: () => {
            console.log('=== Library Status ===');
            const cdnElement = document.getElementById('cdnStatus');
            const statusElement = document.getElementById('qrcodeStatus');
            const versionElement = document.getElementById('libraryVersion');
            
            console.log('Library Load Status:', cdnElement?.textContent);
            console.log('Library Status:', statusElement?.textContent);
            console.log('Library Version:', versionElement?.textContent);
        },
        
        // Force reload QRCode library
        reloadLibrary: () => {
            console.log('=== Reloading QR Code Library ===');
            if (window.qrCodeGenerator) {
                window.qrCodeGenerator.setupCDNFallback();
            } else {
                console.log('QR Code Generator not initialized');
                const script = document.createElement('script');
                script.src = '/js/qrcode-lib.js';
                document.head.appendChild(script);
            }
        },
        
        // Generate test QR code
        generateTest: () => {
            console.log('=== Generating Test QR Code ===');
            const testContainer = document.createElement('div');
            testContainer.style.border = '1px solid red';
            testContainer.style.padding = '10px';
            testContainer.style.margin = '10px';
            document.body.appendChild(testContainer);
            
            // Try using QRCode.toCanvas first
            if (typeof QRCode !== 'undefined' && typeof QRCode.toCanvas === 'function') {
                console.log('Testing with QRCode.toCanvas');
                QRCode.toCanvas(testContainer, 'Test QR Code', {
                    width: 200,
                    height: 200,
                    margin: 2
                }, (error) => {
                    if (error) {
                        console.error('Test QR code generation failed with toCanvas:', error);
                        // Try alternative methods
                        tryAlternativeMethods();
                    } else {
                        console.log('Test QR code generated successfully with toCanvas');
                    }
                });
            } else {
                // Try alternative methods
                tryAlternativeMethods();
            }
            
            // Function to try alternative QR code generation methods
            function tryAlternativeMethods() {
                // Try qrcode-generator library
                if (typeof qrcode !== 'undefined') {
                    console.log('Testing with qrcode-generator');
                    try {
                        const qr = qrcode(0, 'L');
                        qr.addData('Test QR Code');
                        qr.make();
                        
                        const img = document.createElement('img');
                        img.src = qr.createDataURL(5);
                        testContainer.innerHTML = '';
                        testContainer.appendChild(img);
                        console.log('Test QR code generated successfully with qrcode-generator');
                    } catch (error) {
                        console.error('Test QR code generation failed with qrcode-generator:', error);
                        tryQRCodeConstructor();
                    }
                } else {
                    tryQRCodeConstructor();
                }
            }
            
            // Try QRCode constructor as last resort
            function tryQRCodeConstructor() {
                if (window.QRCode && typeof window.QRCode === 'function') {
                    console.log('Testing with QRCode constructor');
                    try {
                        testContainer.innerHTML = '';
                        new QRCode(testContainer, {
                            text: 'Test QR Code',
                            width: 200,
                            height: 200
                        });
                        console.log('Test QR code generated successfully with QRCode constructor');
                    } catch (error) {
                        console.error('Test QR code generation failed with QRCode constructor:', error);
                        testContainer.innerHTML = `<div style="color: red;">All QR code generation methods failed</div>`;
                    }
                } else {
                    testContainer.innerHTML = '<div style="color: red;">No compatible QR code library available</div>';
                }
            }
        }
    };
    
    console.log('QR Code Generator initialized. Use qrDebug.testLibrary() to test the library.');
});