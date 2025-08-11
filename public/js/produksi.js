// Produksi Page JavaScript - AdminLTE Compatible
class ProduksiApp {
    constructor() {
        this.currentProduct = null;
        this.scanHistory = [];
        this.sampleLimit = 4; // Limit sampel untuk produk tertentu
        this.scannedCount = 0; // Counter untuk scan
        this.isScanning = false;
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadScanHistory();
        this.loadScannedCount();
    }

    bindEvents() {
        // Scan area click
        const scanArea = document.querySelector('#scanArea');
        if (scanArea) {
            scanArea.addEventListener('click', () => this.openScanner());
        }

        // Quality check buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('quality-btn')) {
                this.handleQualityCheck(e.target);
            }
        });

        // Drag and drop
        if (scanArea) {
            scanArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                scanArea.classList.add('dragover');
            });
            
            scanArea.addEventListener('dragleave', () => {
                scanArea.classList.remove('dragover');
            });
            
            scanArea.addEventListener('drop', (e) => {
                e.preventDefault();
                scanArea.classList.remove('dragover');
                this.handleFileDrop(e.dataTransfer.files);
            });
        }

        // Modal events
        $('#scannerModal').on('hidden.bs.modal', () => {
            this.stopScanner();
        });
    }

    openScanner() {
        $('#scannerModal').modal('show');
        this.startScanner();
    }

    startScanner() {
        if (this.isScanning) return;
        
        this.isScanning = true;
        
        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: "#interactive",
                constraints: {
                    width: 640,
                    height: 480,
                    facingMode: "environment"
                },
            },
            locator: {
                patchSize: "medium",
                halfSample: true
            },
            numOfWorkers: 2,
            frequency: 10,
            decoder: {
                readers: [
                    "code_128_reader",
                    "ean_reader",
                    "ean_8_reader",
                    "code_39_reader",
                    "code_39_vin_reader",
                    "codabar_reader",
                    "upc_reader",
                    "upc_e_reader",
                    "i2of5_reader"
                ]
            },
            locate: true
        }, (err) => {
            if (err) {
                console.error('Error starting scanner:', err);
                this.showError('Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.');
                return;
            }
            
            Quagga.start();
        });

        // Handle successful scans
        Quagga.onDetected((result) => {
            if (result && result.codeResult && result.codeResult.code) {
                const barcode = result.codeResult.code;
                console.log('Barcode detected:', barcode);
                
                // Stop scanner and close modal
                this.stopScanner();
                $('#scannerModal').modal('hide');
                
                // Process the scanned barcode
                this.processScan(barcode);
            }
        });

        // Handle scan errors
        Quagga.onProcessed((result) => {
            if (result) {
                if (result.codeResult && result.codeResult.code) {
                    console.log('Processing barcode:', result.codeResult.code);
                }
            }
        });
    }

    stopScanner() {
        if (this.isScanning) {
            Quagga.stop();
            this.isScanning = false;
        }
    }

    async processScan(barcode) {
        this.showLoading();
        
        try {
            console.log('Processing scanned code:', barcode);
            
            // Check if it might be a QR code (JSON format)
            let isQrCode = false;
            try {
                const testParse = JSON.parse(barcode);
                if (testParse && typeof testParse === 'object') {
                    isQrCode = true;
                    console.log('Detected QR code format (JSON)');
                }
            } catch (e) {
                // Not JSON, continue as barcode
                console.log('Not a QR code format, processing as barcode');
            }
            
            // Add to scan history
            this.addToScanHistory(barcode, isQrCode);
            
            // Simulate API call delay
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            // Fetch product info from database based on barcode
            const productInfo = await this.fetchProductInfo(barcode);
            
            if (productInfo) {
                // Check if this is a limit sample product
                if (productInfo.no_parts === '4LimitSampel') {
                    this.scannedCount++;
                    this.saveScannedCount();
                    
                    if (this.scannedCount > this.sampleLimit) {
                        this.hideLoading();
                        this.showError(`Limit sampel sudah tercapai (${this.sampleLimit} sampel). Tidak dapat scan lagi.`);
                        return;
                    }
                }
                
                this.currentProduct = productInfo;
                this.displayProductInfo(productInfo);
                this.hideLoading();
                
                // Show success message for QR codes
                if (isQrCode) {
                    this.showSuccess('QR Code berhasil di-scan dan diverifikasi');
                }
            } else {
                this.hideLoading();
                this.showError('Produk tidak ditemukan dalam database');
            }
        } catch (error) {
            this.hideLoading();
            this.showError('Terjadi kesalahan saat memproses scan');
            console.error('Scan error:', error);
        }
    }

    async fetchProductInfo(barcode) {
        try {
            // Check if barcode is JSON (from generated QR code)
            let qrData = null;
            try {
                qrData = JSON.parse(barcode);
                console.log('Successfully parsed QR data:', qrData);
                // Log more details about the parsed QR data
                if (qrData.no_parts) {
                    console.log('QR code contains no_parts:', qrData.no_parts);
                    console.log('QR code unique_id:', qrData.unique_id);
                    console.log('QR code generated_at:', qrData.generated_at);
                } else {
                    console.warn('QR code data missing no_parts field');
                }
            } catch (e) {
                console.log('Not JSON format, treating as regular barcode:', e.message);
                // Not JSON, treat as regular barcode
            }

            // If it's a generated QR code, use the data directly
            if (qrData && qrData.no_parts) {
                console.log('Valid QR code data detected with no_parts:', qrData.no_parts);
                // Simulate API call to fetch part information based on QR data
                const mockData = {
                    '4LimitSampel': {
                        id: 1,
                        no_parts: '4LimitSampel',
                        nama_part: 'Limit Sample',
                        type: 'Sample Type A',
                        quantity: '100',
                        bruto: '2.5 kg',
                        pdf_file: 'barang/4LimitSampel.pdf',
                        description: 'Limit sample untuk quality control (Maksimal 4 sampel)',
                        specifications: {
                            'Material': 'Plastic ABS',
                            'Color': 'Black',
                            'Size': '100x50x25 mm',
                            'Weight': '25g per piece',
                            'Sample Limit': '4 sampel'
                        },
                        isLimitSample: true
                    },
                    'ABC123': {
                        id: 2,
                        no_parts: 'ABC123',
                        nama_part: 'Part ABC',
                        type: 'Type B',
                        quantity: '50',
                        bruto: '1.8 kg',
                        pdf_file: null,
                        description: 'Standard part ABC',
                        specifications: {
                            'Material': 'Steel',
                            'Color': 'Silver',
                            'Size': '75x40x15 mm',
                            'Weight': '36g per piece'
                        },
                        isLimitSample: false
                    },
                    'XYZ789': {
                        id: 3,
                        no_parts: 'XYZ789',
                        nama_part: 'Part XYZ',
                        type: 'Type C',
                        quantity: '75',
                        bruto: '3.2 kg',
                        pdf_file: null,
                        description: 'Premium part XYZ',
                        specifications: {
                            'Material': 'Aluminum',
                            'Color': 'Gray',
                            'Size': '120x60x30 mm',
                            'Weight': '42g per piece'
                        },
                        isLimitSample: false
                    },
                    'DEF456': {
                        id: 4,
                        no_parts: 'DEF456',
                        nama_part: 'Part DEF',
                        type: 'Type D',
                        quantity: '25',
                        bruto: '1.5 kg',
                        pdf_file: null,
                        description: 'Special part DEF',
                        specifications: {
                            'Material': 'Titanium',
                            'Color': 'Silver',
                            'Size': '90x45x20 mm',
                            'Weight': '60g per piece'
                        },
                        isLimitSample: false
                    }
                };

                // Find the part based on no_parts from QR data
                const part = mockData[qrData.no_parts];
                if (part) {
                    // Add QR code specific data
                    part.unique_id = qrData.unique_id;
                    part.generated_at = qrData.generated_at;
                    part.qr_scanned = true; // Flag to indicate this came from a QR code
                    console.log('Found matching part for QR code:', part);
                    return part;
                } else {
                    console.warn('No matching part found for QR code with no_parts:', qrData.no_parts);
                    // Create a basic part object from QR data if no match in mockData
                    return {
                        id: 0,
                        no_parts: qrData.no_parts,
                        nama_part: qrData.nama_part || 'Unknown Part',
                        type: 'Unknown Type',
                        quantity: '1',
                        description: 'Part scanned from QR code',
                        unique_id: qrData.unique_id,
                        generated_at: qrData.generated_at,
                        qr_scanned: true,
                        specifications: {}
                    };
                }
            }

            // Fallback to original barcode logic
            const mockData = {
                '4LimitSampel': {
                    id: 1,
                    no_parts: '4LimitSampel',
                    nama_part: 'Limit Sample',
                    type: 'Sample Type A',
                    quantity: '100',
                    bruto: '2.5 kg',
                    pdf_file: 'barang/4LimitSampel.pdf',
                    description: 'Limit sample untuk quality control (Maksimal 4 sampel)',
                    specifications: {
                        'Material': 'Plastic ABS',
                        'Color': 'Black',
                        'Size': '100x50x25 mm',
                        'Weight': '25g per piece',
                        'Sample Limit': '4 sampel'
                    },
                    isLimitSample: true
                },
                'ABC123': {
                    id: 2,
                    no_parts: 'ABC123',
                    nama_part: 'Part ABC',
                    type: 'Type B',
                    quantity: '50',
                    bruto: '1.8 kg',
                    pdf_file: null,
                    description: 'Standard part ABC',
                    specifications: {
                        'Material': 'Steel',
                        'Color': 'Silver',
                        'Size': '75x40x15 mm',
                        'Weight': '36g per piece'
                    },
                    isLimitSample: false
                },
                'XYZ789': {
                    id: 3,
                    no_parts: 'XYZ789',
                    nama_part: 'Part XYZ',
                    type: 'Type C',
                    quantity: '75',
                    bruto: '3.2 kg',
                    pdf_file: null,
                    description: 'Premium part XYZ',
                    specifications: {
                        'Material': 'Aluminum',
                        'Color': 'Gray',
                        'Size': '120x60x30 mm',
                        'Weight': '42g per piece'
                    },
                    isLimitSample: false
                },
                'DEF456': {
                    id: 4,
                    no_parts: 'DEF456',
                    nama_part: 'Part DEF',
                    type: 'Type D',
                    quantity: '25',
                    bruto: '1.5 kg',
                    pdf_file: null,
                    description: 'Special part DEF',
                    specifications: {
                        'Material': 'Titanium',
                        'Color': 'Silver',
                        'Size': '90x45x20 mm',
                        'Weight': '60g per piece'
                    },
                    isLimitSample: false
                }
            };

            // Simulate network delay
            await new Promise(resolve => setTimeout(resolve, 500));
            
            return mockData[barcode] || null;
        } catch (error) {
            console.error('Error fetching product info:', error);
            return null;
        }
    }

    displayProductInfo(product) {
        const productInfoDiv = document.querySelector('#productInfo');
        
        // Add limit sample warning if applicable
        const limitSampleWarning = product.isLimitSample ? `
            <div class="alert alert-warning mb-3">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Limit Sample:</strong> Produk ini memiliki batasan maksimal ${this.sampleLimit} sampel. 
                Sampel yang sudah di-scan: ${this.scannedCount}/${this.sampleLimit}
                <button class="btn btn-sm btn-outline-warning ml-2" onclick="app.resetSampleLimit()">
                    <i class="fas fa-redo mr-1"></i>Reset Limit
                </button>
            </div>
        ` : '';

        // Add QR code info if available
        const qrCodeInfo = product.unique_id ? `
            <div class="alert alert-info mb-3">
                <i class="fas fa-qrcode mr-2"></i>
                <strong>QR Code Info:</strong> 
                <br><strong>Part Number:</strong> ${product.no_parts}
                <br><strong>Unique ID:</strong> ${product.unique_id}
                <br><strong>Generated:</strong> ${product.generated_at ? new Date(product.generated_at).toLocaleString('id-ID') : 'N/A'}
                ${product.qr_scanned ? '<br><span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i>Verified QR Code</span>' : ''}
            </div>
        ` : '';

        const pdfSection = product.pdf_file ? `
            <div class="pdf-section mb-4">
                <h4 class="pdf-title">
                    <i class="fas fa-file-pdf mr-2"></i>
                    Dokumentasi Produk
                </h4>
                <div class="pdf-container">
                    <iframe src="/images/${product.pdf_file}" 
                            width="100%" 
                            height="400" 
                            style="border: 1px solid #dee2e6; border-radius: 8px;">
                        <p>Browser Anda tidak mendukung tampilan PDF. 
                        <a href="/images/${product.pdf_file}" target="_blank">Klik di sini untuk download</a></p>
                    </iframe>
                </div>
            </div>
        ` : `
            <div class="pdf-section mb-4">
                <h4 class="pdf-title">
                    <i class="fas fa-info-circle mr-2"></i>
                    Informasi Dokumentasi
                </h4>
                <div class="pdf-container">
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Dokumentasi Tidak Tersedia</h5>
                        <p class="text-muted">Produk ini tidak memiliki dokumentasi PDF yang dapat ditampilkan.</p>
                    </div>
                </div>
            </div>
        `;

        productInfoDiv.innerHTML = `
            ${limitSampleWarning}
            ${qrCodeInfo}
            
            <div class="product-header">
                <div class="product-image">
                    <i class="fas fa-box"></i>
                </div>
                <div class="product-details">
                    <h3>${product.nama_part}</h3>
                    <p>${product.description}</p>
                </div>
            </div>

            <div class="product-grid">
                <div class="info-card">
                    <h4>No. Parts</h4>
                    <p>${product.no_parts}</p>
                </div>
                <div class="info-card">
                    <h4>Type</h4>
                    <p>${product.type}</p>
                </div>
                <div class="info-card">
                    <h4>Quantity</h4>
                    <p>${product.quantity} pcs</p>
                </div>
                <div class="info-card">
                    <h4>Bruto</h4>
                    <p>${product.bruto}</p>
                </div>
            </div>

            <div class="specifications">
                <h4>Spesifikasi Produk</h4>
                <div class="row">
                    ${Object.entries(product.specifications).map(([key, value]) => `
                        <div class="col-md-6 mb-2">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">${key}:</span>
                                <span class="font-weight-bold">${value}</span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>

            ${pdfSection}
        `;

        productInfoDiv.classList.add('show');
        
        // Show quality check section
        const qualityCheck = document.querySelector('#qualityCheck');
        qualityCheck.classList.add('show');
    }

    resetSampleLimit() {
        this.scannedCount = 0;
        this.saveScannedCount();
        this.showSuccess('Limit sampel berhasil direset');
        
        // Refresh product info display
        if (this.currentProduct && this.currentProduct.isLimitSample) {
            this.displayProductInfo(this.currentProduct);
        }
    }

    saveScannedCount() {
        localStorage.setItem('scannedCount', this.scannedCount.toString());
    }

    loadScannedCount() {
        const saved = localStorage.getItem('scannedCount');
        if (saved) {
            this.scannedCount = parseInt(saved);
        }
    }

    handleQualityCheck(button) {
        const quality = button.dataset.quality;
        const product = this.currentProduct;
        
        if (!product) {
            this.showError('Tidak ada produk yang dipilih');
            return;
        }

        this.submitQualityCheck(product, quality);
    }

    async submitQualityCheck(product, quality) {
        try {
            // Simulate API call
            await new Promise(resolve => setTimeout(resolve, 1000));
            
            const qualityText = {
                'good': 'GOOD',
                'ng': 'NG',
                'retry': 'RETRY'
            };

            this.showSuccess(`Quality check ${qualityText[quality]} berhasil disimpan untuk ${product.nama_part}`);
            
            // Add to scan history
            this.addToScanHistory(product, quality);
            
            // Reset scan
            this.resetScan();
            
        } catch (error) {
            this.showError('Gagal menyimpan quality check');
            console.error('Quality check error:', error);
        }
    }

    resetScan() {
        this.currentProduct = null;
        
        // Hide product info and quality check
        const productInfo = document.querySelector('#productInfo');
        const qualityCheck = document.querySelector('#qualityCheck');
        
        productInfo.classList.remove('show');
        qualityCheck.classList.remove('show');
        
        // Clear scan area
        const scanArea = document.querySelector('#scanArea');
        scanArea.style.display = 'block';
    }

    addToScanHistory(data, qualityOrIsQrCode) {
        let historyItem;
        
        // Check if this is a product with quality check or a barcode/QR code scan
        if (typeof data === 'object' && data.nama_part) {
            // This is a product with quality check
            historyItem = {
                id: Date.now(),
                product: data,
                quality: qualityOrIsQrCode, // In this case, second param is quality
                timestamp: new Date().toLocaleString('id-ID'),
                type: 'quality_check'
            };
        } else {
            // This is a barcode/QR code scan
            const isQrCode = qualityOrIsQrCode === true;
            historyItem = {
                id: Date.now(),
                barcode: data,
                isQrCode: isQrCode,
                timestamp: new Date().toLocaleString('id-ID'),
                type: 'scan'
            };
        }
        
        this.scanHistory.unshift(historyItem);
        
        // Keep only last 10 items
        if (this.scanHistory.length > 10) {
            this.scanHistory = this.scanHistory.slice(0, 10);
        }
        
        this.updateScanHistory();
        this.saveScanHistory();
    }

    updateScanHistory() {
        const historyContainer = document.querySelector('#scanHistory');
        
        if (this.scanHistory.length === 0) {
            historyContainer.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-history fa-2x mb-2"></i>
                    <p>Belum ada riwayat scan</p>
                </div>
            `;
            return;
        }
        
        const historyHTML = this.scanHistory.map(item => {
            if (item.type === 'quality_check') {
                // Quality check history item
                return `
                    <div class="history-item">
                        <div class="history-info">
                            <strong>${item.product.nama_part}</strong>
                            <small>${item.product.no_parts}</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge ${this.getQualityBadgeClass(item.quality)} mr-2">
                                ${item.quality.toUpperCase()}
                            </span>
                            <small class="history-time">${item.timestamp}</small>
                        </div>
                    </div>
                `;
            } else {
                // Scan history item
                let barcodeDisplay = item.barcode;
                // If it's a QR code (JSON), try to make it more readable
                if (item.isQrCode) {
                    try {
                        const qrData = JSON.parse(item.barcode);
                        if (qrData.no_parts) {
                            barcodeDisplay = qrData.no_parts;
                        }
                    } catch (e) {
                        // Use the original barcode if parsing fails
                    }
                }
                
                return `
                    <div class="history-item">
                        <div class="history-info">
                            <strong>${item.isQrCode ? 'QR Code Scan' : 'Barcode Scan'}</strong>
                            <small>${barcodeDisplay}</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge ${item.isQrCode ? 'badge-info' : 'badge-secondary'} mr-2">
                                <i class="fas fa-${item.isQrCode ? 'qrcode' : 'barcode'} mr-1"></i>
                                SCAN
                            </span>
                            <small class="history-time">${item.timestamp}</small>
                        </div>
                    </div>
                `;
            }
        }).join('');
        
        historyContainer.innerHTML = historyHTML;
    }

    getQualityBadgeClass(quality) {
        switch (quality) {
            case 'good': return 'badge-success';
            case 'ng': return 'badge-danger';
            case 'retry': return 'badge-warning';
            default: return 'badge-secondary';
        }
    }

    saveScanHistory() {
        localStorage.setItem('scanHistory', JSON.stringify(this.scanHistory));
    }

    loadScanHistory() {
        const saved = localStorage.getItem('scanHistory');
        if (saved) {
            this.scanHistory = JSON.parse(saved);
            this.updateScanHistory();
        }
    }

    handleFileDrop(files) {
        if (files.length > 0) {
            const file = files[0];
            if (file.type.startsWith('image/')) {
                // Simulate processing image file
                this.showLoading();
                setTimeout(() => {
                    this.hideLoading();
                    this.simulateScan();
                }, 2000);
            } else {
                this.showError('File harus berupa gambar');
            }
        }
    }

    simulateScan() {
        // Simulate barcode scanning for testing
        const barcodes = ['4LimitSampel', 'ABC123', 'XYZ789', 'DEF456'];
        const randomBarcode = barcodes[Math.floor(Math.random() * barcodes.length)];
        this.processScan(randomBarcode);
    }

    showLoading() {
        const loading = document.querySelector('#loading');
        loading.classList.add('show');
    }

    hideLoading() {
        const loading = document.querySelector('#loading');
        loading.classList.remove('show');
    }

    showSuccess(message) {
        this.showNotification(message, 'success');
    }

    showError(message) {
        this.showNotification(message, 'error');
    }

    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Show notification
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        // Remove notification after 5 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 5000);
    }
}

// Global app instance
let app;

document.addEventListener('DOMContentLoaded', () => {
    app = new ProduksiApp();
});