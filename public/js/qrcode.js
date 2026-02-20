/**
 * QR Code Generator JavaScript - Fixed Version
 * Handles QR code generation, preview, and download functionality
 */

class QRCodeGenerator {
    constructor() {
        this.maxAttempts = 10;
        this.attemptDelay = 500;
        this.qrLibraryLoaded = false;
        this.init();
    }

    init() {
        console.log('Initializing QRCodeGenerator...');
        this.checkExistingLibraries();
        this.bindEvents();
    }

    /**
     * Check if QR libraries already exist
     */
    checkExistingLibraries() {
        // Check multiple library formats
        if (typeof QRCode !== 'undefined' && QRCode.toCanvas) {
            console.log('✅ QRCode (node-qrcode) library detected');
            this.qrLibraryLoaded = true;
            this.updateCDNStatus('QRCode Library Ready');
            return true;
        }
        
        if (typeof qrcode !== 'undefined') {
            console.log('✅ qrcode-generator library detected');
            this.qrLibraryLoaded = true;
            this.updateCDNStatus('QRCode Generator Ready');
            return true;
        }

        if (typeof QRious !== 'undefined') {
            console.log('✅ QRious library detected');
            this.qrLibraryLoaded = true;
            this.updateCDNStatus('QRious Ready');
            return true;
        }

        console.log('⚠️ No QR libraries found, attempting to load...');
        this.loadQRLibraries();
        return false;
    }

    /**
     * Load QR libraries dynamically
     */
    loadQRLibraries() {
        this.updateCDNStatus('Loading Libraries...');
        
        // Try loading multiple libraries for fallback
        const libraries = [
            'https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.3/qrcode.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/qrious/4.0.2/qrious.min.js'
        ];

        let loadedCount = 0;
        libraries.forEach((src, index) => {
            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            
            script.onload = () => {
                loadedCount++;
                console.log(`✅ Loaded library ${index + 1}: ${src}`);
                
                if (this.checkExistingLibraries()) {
                    this.updateCDNStatus('Libraries Loaded');
                    // Re-initialize if we're on result page
                    if (typeof qrCodes !== 'undefined') {
                        this.initResultPage(qrCodes);
                    }
                }
            };
            
            script.onerror = () => {
                console.error(`❌ Failed to load: ${src}`);
                if (index === libraries.length - 1 && loadedCount === 0) {
                    this.handleLoadFailure();
                }
            };
            
            document.head.appendChild(script);
        });
    }

    bindEvents() {
        // Use event delegation for better compatibility
        $(document).on('change', '#part_id', (e) => this.handlePartSelection(e));
        $(document).on('submit', '#qrForm', (e) => this.handleFormSubmission(e));
        $(document).on('click', '.download-btn', (e) => {
            e.preventDefault();
            const button = $(e.currentTarget);
            const index = button.data('index');
            const uniqueId = button.data('unique-id');
            this.downloadQRCode(index, uniqueId);
        });
        $(document).on('click', '#downloadAll', (e) => {
            e.preventDefault();
            this.downloadAll();
        });
        $(document).on('click', '.print-btn', (e) => {
            e.preventDefault();
            const index = $(e.currentTarget).data('index');
            this.printQRCode(index);
        });
        $(document).on('click', '.print-all-btn', (e) => {
            e.preventDefault();
            window.print();
        });
    }

    handlePartSelection(event) {
        const selectedOption = $(event.target).find('option:selected');
        const partId = selectedOption.val();
        const noParts = selectedOption.data('no-parts');
        const namaPart = selectedOption.data('nama-part');
        
        if (partId && noParts && namaPart) {
            this.showPartInfo(partId, noParts, namaPart);
            this.generatePreview(noParts, namaPart);
        } else {
            this.hidePreview();
        }
    }

    showPartInfo(partId, noParts, namaPart) {
        const infoHtml = `
            <table class="table table-sm">
                <tr>
                    <td><strong>ID Part:</strong></td>
                    <td>${partId}</td>
                </tr>
                <tr>
                    <td><strong>No. Parts:</strong></td>
                    <td><span class="badge badge-primary">${noParts}</span></td>
                </tr>
                <tr>
                    <td><strong>Nama Part:</strong></td>
                    <td>${namaPart}</td>
                </tr>
            </table>
        `;
        $('#partInfo').html(infoHtml);
    }

    generatePreview(noParts, namaPart) {
        const previewData = {
            type: 'DIGIPRO_PART',
            version: '1.0',
            no_parts: noParts,
            nama_part: namaPart,
            preview: true,
            unique_id: `${noParts}_preview_${Date.now()}`
        };

        const qrText = JSON.stringify(previewData);
        console.log('Generating preview QR:', previewData);

        $('#qrPreview').empty().html('<div class="text-center">Generating preview...</div>');
        $('#qrText').text(`Data: ${noParts} - ${namaPart}`);

        // Wait a bit for libraries to load
        setTimeout(() => {
            this.generateSingleQR('#qrPreview', qrText, 200, () => {
                $('#previewSection').show();
                console.log('Preview QR generated successfully');
            });
        }, 500);
    }

    hidePreview() {
        $('#previewSection').hide();
        $('#partInfo').html('<p class="text-muted">Pilih part untuk melihat informasi</p>');
    }

    handleFormSubmission(event) {
        const form = $(event.target);
        const partId = form.find('#part_id').val();
        const quantity = form.find('#quantity').val();
        
        if (!partId || !quantity) {
            event.preventDefault();
            this.showError('Silakan pilih part dan masukkan jumlah QR code');
            return false;
        }
        
        if (quantity < 1 || quantity > 100) {
            event.preventDefault();
            this.showError('Jumlah QR code harus antara 1-100');
            return false;
        }

        // Show loading state
        const submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true).html(`
            <i class="fas fa-spinner fa-spin mr-2"></i>
            Generating QR Codes...
        `);
        
        return true;
    }

    /**
     * Main method to generate QR codes on result page
     */
    initResultPage(qrCodes) {
        console.log('Initializing result page with QR codes:', qrCodes);
        
        if (!qrCodes || !Array.isArray(qrCodes) || qrCodes.length === 0) {
            console.error('Invalid or empty qrCodes data:', qrCodes);
            this.showError('Data QR code tidak valid atau kosong.');
            return;
        }

        // Store globally for download functionality
        window.qrCodes = qrCodes;
        
        // Wait for libraries to load then generate
        this.waitForLibraries(() => {
            this.generateAllQRCodes(qrCodes);
        });
    }

    waitForLibraries(callback, attempt = 1) {
        if (this.qrLibraryLoaded || this.checkExistingLibraries()) {
            callback();
            return;
        }

        if (attempt > this.maxAttempts) {
            console.error('❌ QR libraries failed to load after maximum attempts');
            this.handleLoadFailure();
            return;
        }

        console.log(`Waiting for QR libraries... Attempt ${attempt}/${this.maxAttempts}`);
        setTimeout(() => {
            this.waitForLibraries(callback, attempt + 1);
        }, this.attemptDelay);
    }

    generateAllQRCodes(qrCodes) {
        console.log(`Generating ${qrCodes.length} QR codes...`);
        
        qrCodes.forEach((qrCode, index) => {
            const container = `#qrCode${index}`;
            const qrText = qrCode.qr_text || qrCode.unique_id || JSON.stringify(qrCode.data);
            
            console.log(`Generating QR ${index + 1}:`, qrText.substring(0, 50) + '...');
            
            // Add small delay between generations to prevent browser freeze
            setTimeout(() => {
                this.generateSingleQR(container, qrText, 120, () => {
                    console.log(`QR ${index + 1} generated successfully`);
                });
            }, index * 100); // 100ms delay between each QR
        });

        this.updateStatus('All QR codes generated', 'success');
    }

    generateSingleQR(container, qrText, size = 120, onSuccess = null) {
        const $container = $(container);
        
        if ($container.length === 0) {
            console.error(`Container not found: ${container}`);
            return;
        }

        // Clear container
        $container.empty().html('<div class="text-center text-muted">Generating...</div>');

        // Validate QR text
        if (!qrText || qrText.trim() === '') {
            console.error(`Invalid QR text for container: ${container}`);
            this.showQRError($container, 'Invalid data');
            return;
        }

        // Try different methods in order of preference
        this.tryQRCodeGeneration($container, qrText, size, onSuccess);
    }

    tryQRCodeGeneration($container, qrText, size, onSuccess) {
        const containerElement = $container[0];

        // Method 1: QRCode (node-qrcode) - Canvas
        if (typeof QRCode !== 'undefined' && QRCode.toCanvas) {
            console.log('Using QRCode.toCanvas method');
            
            // Create canvas element
            const canvas = document.createElement('canvas');
            containerElement.appendChild(canvas);
            
            QRCode.toCanvas(canvas, qrText, {
                width: size,
                height: size,
                margin: 2,
                color: {
                    dark: '#000000',
                    light: '#ffffff'
                }
            }, (error) => {
                if (error) {
                    console.error('QRCode.toCanvas failed:', error);
                    this.tryMethod2($container, qrText, size, onSuccess);
                } else {
                    console.log('QRCode.toCanvas success');
                    if (onSuccess) onSuccess();
                }
            });
            return;
        }

        // Try method 2 if method 1 not available
        this.tryMethod2($container, qrText, size, onSuccess);
    }

    tryMethod2($container, qrText, size, onSuccess) {
        // Method 2: QRious
        if (typeof QRious !== 'undefined') {
            console.log('Using QRious method');
            
            try {
                const canvas = document.createElement('canvas');
                $container[0].appendChild(canvas);
                
                const qr = new QRious({
                    element: canvas,
                    value: qrText,
                    size: size,
                    background: 'white',
                    foreground: 'black'
                });
                
                console.log('QRious success');
                if (onSuccess) onSuccess();
                return;
            } catch (error) {
                console.error('QRious failed:', error);
            }
        }

        // Method 3: qrcode-generator fallback
        this.tryMethod3($container, qrText, size, onSuccess);
    }

    tryMethod3($container, qrText, size, onSuccess) {
        // Method 3: qrcode-generator (fallback)
        if (typeof qrcode !== 'undefined') {
            console.log('Using qrcode-generator fallback');
            
            try {
                const qr = qrcode(0, 'M');
                qr.addData(qrText);
                qr.make();
                
                const img = document.createElement('img');
                img.src = qr.createDataURL(Math.floor(size/25));
                img.width = size;
                img.height = size;
                img.style.border = '1px solid #ddd';
                
                $container[0].appendChild(img);
                console.log('qrcode-generator success');
                if (onSuccess) onSuccess();
                return;
            } catch (error) {
                console.error('qrcode-generator failed:', error);
            }
        }

        // All methods failed
        console.error('All QR generation methods failed');
        this.showQRError($container, qrText.substring(0, 20) + '...');
    }

    showQRError($container, errorText) {
        $container.html(`
            <div class="text-center p-3 border border-warning rounded">
                <i class="fas fa-exclamation-triangle text-warning fa-2x mb-2"></i><br>
                <small class="text-muted">QR Generation Failed</small><br>
                <small class="text-muted">${errorText}</small>
            </div>
        `);
    }

    downloadQRCode(index, uniqueId) {
        const canvas = document.querySelector(`#qrCode${index} canvas`);
        const img = document.querySelector(`#qrCode${index} img`);
        
        if (canvas) {
            this.downloadFromCanvas(canvas, uniqueId);
        } else if (img) {
            this.downloadFromImage(img, uniqueId);
        } else {
            this.showError('QR Code tidak tersedia untuk download');
        }
    }

    downloadFromCanvas(canvas, uniqueId) {
        try {
            const link = document.createElement('a');
            link.download = `qrcode_${uniqueId}.png`;
            link.href = canvas.toDataURL('image/png');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            console.log('Downloaded QR code:', uniqueId);
        } catch (error) {
            console.error('Download failed:', error);
            this.showError('Gagal download QR code');
        }
    }

    downloadFromImage(img, uniqueId) {
        try {
            const link = document.createElement('a');
            link.download = `qrcode_${uniqueId}.png`;
            link.href = img.src;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            console.log('Downloaded QR image:', uniqueId);
        } catch (error) {
            console.error('Image download failed:', error);
            this.showError('Gagal download QR code');
        }
    }

    printQRCode(index) {
        const qrContainer = $(`.qr-container`).eq(index);
        if (qrContainer.length) {
            const printContent = qrContainer.clone();
            printContent.find('.no-print').remove();
            
            const printWindow = window.open('', '_blank', 'width=800,height=600');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Print QR Code</title>
                        <style>
                            body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
                            .qr-container { border: 1px solid #ddd; padding: 20px; margin: 20px; }
                        </style>
                    </head>
                    <body>
                        ${printContent.html()}
                    </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.focus();
            printWindow.print();
            setTimeout(() => printWindow.close(), 1000);
        }
    }

    downloadAll() {
        if (!window.qrCodes || window.qrCodes.length === 0) {
            this.showError('Tidak ada QR code untuk didownload');
            return;
        }

        // Download each QR code with delay
        window.qrCodes.forEach((qrCode, index) => {
            setTimeout(() => {
                this.downloadQRCode(index, qrCode.unique_id);
            }, index * 500); // 500ms delay between downloads
        });

        this.showSuccess(`Memulai download ${window.qrCodes.length} QR codes...`);
    }

    updateStatus(message, type = 'info') {
        console.log(`Status: ${message}`);
        const statusEl = $('#qrcodeStatus');
        if (statusEl.length) {
            statusEl.text(message).removeClass().addClass(`alert alert-${type}`);
        }
    }

    updateCDNStatus(status) {
        const cdnEl = $('#cdnStatus');
        if (cdnEl.length) {
            cdnEl.text(status);
        }
        console.log('CDN Status:', status);
    }

    showError(message) {
        console.error('QR Error:', message);
        
        const alert = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <strong>Error:</strong> ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        
        $('.alert-danger').remove();
        const container = $('.content-wrapper .container-fluid').first();
        if (container.length) {
            container.prepend(alert);
        }
    }

    showSuccess(message) {
        console.log('QR Success:', message);
        
        const alert = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle mr-2"></i>
                <strong>Success:</strong> ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;
        
        $('.alert-success').remove();
        const container = $('.content-wrapper .container-fluid').first();
        if (container.length) {
            container.prepend(alert);
            setTimeout(() => $('.alert-success').fadeOut(), 5000);
        }
    }

    handleLoadFailure() {
        this.updateCDNStatus('Failed to Load');
        this.showError(`
            QR code libraries gagal dimuat. 
            <button type="button" class="btn btn-sm btn-primary ml-2" onclick="location.reload()">
                <i class="fas fa-refresh"></i> Refresh Page
            </button>
        `);
    }
}

// Initialize when DOM is ready
$(document).ready(function() {
    console.log('DOM ready, initializing QRCodeGenerator...');
    
    // Create global instance
    window.qrCodeGenerator = new QRCodeGenerator();
    
    // Initialize result page if qrCodes data exists
    if (typeof qrCodes !== 'undefined' && qrCodes) {
        console.log('QR codes data found, initializing result page...');
        window.qrCodeGenerator.initResultPage(qrCodes);
    }
    
    // Debug helpers
    window.qrDebug = {
        testLibrary: () => {
            console.log('QRCode available:', typeof QRCode !== 'undefined');
            console.log('QRCode.toCanvas available:', typeof QRCode?.toCanvas === 'function');
            console.log('QRious available:', typeof QRious !== 'undefined');
            console.log('qrcode available:', typeof qrcode !== 'undefined');
        },
        reloadLibrary: () => window.qrCodeGenerator.loadQRLibraries(),
        testGeneration: (text = 'test') => {
            const container = $('<div>').appendTo('body');
            window.qrCodeGenerator.generateSingleQR(container, text, 200, () => {
                console.log('Test generation successful');
            });
        }
    };
    
    console.log('QRCodeGenerator ready. Use qrDebug.testLibrary() to debug.');
});