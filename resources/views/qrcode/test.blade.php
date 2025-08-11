<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Test</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- QR Code CSS -->
    <link href="{{ asset('css/qrcode.css') }}" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcode/1.5.3/qrcode.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h1 class="text-center">QR Code Test</h1>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Library Test</h5>
                                <div id="testQR" class="text-center mb-3"></div>
                                <div class="text-center">
                                    <p class="text-muted">Test QR Code Library</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h5>Debug Information</h5>
                                <div id="debugInfo" class="alert alert-info">
                                    <strong>Library Status:</strong> <span id="libStatus">Checking...</span><br>
                                    <strong>CDN Status:</strong> <span id="cdnStatus">Loading...</span><br>
                                    <strong>Browser:</strong> <span id="browserInfo"></span><br>
                                    <strong>Timestamp:</strong> <span id="timestamp"></span>
                                </div>
                                
                                <h6>Debug Commands</h6>
                                <div class="btn-group-vertical w-100">
                                    <button class="btn btn-sm btn-info" onclick="testLibrary()">Test Library</button>
                                    <button class="btn btn-sm btn-warning" onclick="testGeneration()">Test Generation</button>
                                    <button class="btn btn-sm btn-success" onclick="generateTest()">Generate Test QR</button>
                                    <button class="btn btn-sm btn-secondary" onclick="checkStatus()">Check Status</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Update debug info
        function updateDebugInfo() {
            document.getElementById('libStatus').textContent = typeof QRCode !== 'undefined' ? 'Available' : 'Not Available';
            document.getElementById('cdnStatus').textContent = 'Loaded from CDN';
            document.getElementById('browserInfo').textContent = navigator.userAgent;
            document.getElementById('timestamp').textContent = new Date().toLocaleString();
        }
        
        // Test library function
        function testLibrary() {
            console.log('=== QR Code Library Test ===');
            console.log('QRCode available:', typeof QRCode !== 'undefined');
            console.log('QRCode.toCanvas available:', typeof QRCode?.toCanvas === 'function');
            console.log('QRCode version:', QRCode?.version || 'Unknown');
            
            const status = typeof QRCode !== 'undefined' ? 'Available' : 'Not Available';
            document.getElementById('libStatus').textContent = status;
        }
        
        // Test generation function
        function testGeneration() {
            console.log('=== Testing QR Code Generation ===');
            if (typeof QRCode !== 'undefined' && typeof QRCode.toCanvas === 'function') {
                const testContainer = document.getElementById('testQR');
                testContainer.innerHTML = '';
                
                QRCode.toCanvas(testContainer, 'Test QR Code', {
                    width: 200,
                    height: 200,
                    margin: 2
                }, (error) => {
                    if (error) {
                        console.error('Test QR code generation failed:', error);
                        testContainer.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
                    } else {
                        console.log('Test QR code generated successfully');
                    }
                });
            } else {
                console.log('QRCode library not available for testing');
                document.getElementById('testQR').innerHTML = '<div class="alert alert-danger">QRCode library not available</div>';
            }
        }
        
        // Generate test QR code
        function generateTest() {
            console.log('=== Generating Test QR Code ===');
            const testContainer = document.createElement('div');
            testContainer.style.border = '1px solid red';
            testContainer.style.padding = '10px';
            testContainer.style.margin = '10px';
            document.body.appendChild(testContainer);
            
            if (typeof QRCode !== 'undefined' && typeof QRCode.toCanvas === 'function') {
                QRCode.toCanvas(testContainer, 'Test QR Code', {
                    width: 200,
                    height: 200,
                    margin: 2
                }, (error) => {
                    if (error) {
                        console.error('Test QR code generation failed:', error);
                        testContainer.innerHTML = `<div style="color: red;">Error: ${error.message}</div>`;
                    } else {
                        console.log('Test QR code generated successfully');
                    }
                });
            } else {
                testContainer.innerHTML = '<div style="color: red;">QRCode library not available</div>';
            }
        }
        
        // Check status function
        function checkStatus() {
            console.log('=== Checking Status ===');
            updateDebugInfo();
            console.log('Status updated');
        }
        
        // Initialize on page load
        $(document).ready(function() {
            updateDebugInfo();
            
            // Test QRCode library
            if (typeof QRCode !== 'undefined') {
                console.log('QRCode library loaded successfully');
                
                // Generate initial test QR code
                setTimeout(() => {
                    testGeneration();
                }, 1000);
            } else {
                console.error('QRCode library not loaded');
                document.getElementById('testQR').innerHTML = '<div class="alert alert-danger">QRCode library not loaded</div>';
            }
        });
    </script>
</body>
</html> 