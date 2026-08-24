/**
 * OEE Dashboard JavaScript
 * Bootstrap 4 Compatible
 */

// Global variables
let oeeChart, componentChart, machineChart, ngChart, oeeDataTable;

// Initialize dashboard when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    initializeCharts();
    initializeTableSearch();
    initializeFilters();
    initializeDataTables();
    initializeRealTimeUpdates();
});

/**
 * Initialize dashboard components
 */
function initializeDashboard() {
    // Add loading states to buttons
    const exportBtn = document.querySelector('button[onclick="exportReport()"]');
    if (exportBtn) {
        exportBtn.addEventListener('click', function() {
            this.classList.add('loading');
            setTimeout(() => {
                this.classList.remove('loading');
            }, 2000);
        });
    }
    
    // Initialize Bootstrap tooltips
    if (typeof $ !== 'undefined' && $.fn.tooltip) {
        $('[data-toggle="tooltip"]').tooltip();
    }
    
    // Initialize dropdowns
    if (typeof $ !== 'undefined' && $.fn.dropdown) {
        $('.dropdown-toggle').dropdown();
    }
}

/**
 * Initialize Chart.js charts
 */
function initializeCharts() {
    // Get chart data from backend
    const chartData = window.chartData || {};
    
    // Ensure we have fallback data
    const fallbackData = {
        dates: ['No Data'],
        oee: [0],
        availability: [0],
        performance: [0],
        quality: [0],
        machines: ['No Data'],
        machineOEE: [0],
        ngCategories: [0, 0, 0, 0, 0]
    };
    
    // Generate hourly fallback for daily view - only if no data exists
    // (Backend will send actual hours with data)
    if (window.currentPeriod === 'daily') {
        // Don't generate fallback - let backend data determine which hours to show
        // If no data, will show 'No Data' from initial fallbackData
    }
    
    // Normalize helper to ensure dataset lengths match labels
    function padToLength(arr, len) {
        const out = Array.isArray(arr) ? arr.slice(0, len) : [];
        while (out.length < len) out.push(0);
        return out;
    }

    const safeChartDataRaw = {
        dates: chartData.dates && chartData.dates.length > 0 ? chartData.dates : fallbackData.dates,
        oee: chartData.oee && chartData.oee.length > 0 ? chartData.oee : fallbackData.oee,
        availability: chartData.availability && chartData.availability.length > 0 ? chartData.availability : fallbackData.availability,
        performance: chartData.performance && chartData.performance.length > 0 ? chartData.performance : fallbackData.performance,
        quality: chartData.quality && chartData.quality.length > 0 ? chartData.quality : fallbackData.quality,
        machines: chartData.machines && chartData.machines.length > 0 ? chartData.machines : fallbackData.machines,
        machineOEE: chartData.machineOEE && chartData.machineOEE.length > 0 ? chartData.machineOEE : fallbackData.machineOEE,
        ngLabels: chartData.ngLabels && chartData.ngLabels.length > 0 ? chartData.ngLabels : ['Material', 'Process', 'Machine', 'Operator', 'Other'],
        ngCategories: chartData.ngCategories && chartData.ngCategories.length > 0 ? chartData.ngCategories : fallbackData.ngCategories
    };

    // Ensure all series have the same length as labels
    const safeChartData = {
        dates: safeChartDataRaw.dates,
        oee: padToLength(safeChartDataRaw.oee, safeChartDataRaw.dates.length),
        availability: padToLength(safeChartDataRaw.availability, safeChartDataRaw.dates.length),
        performance: padToLength(safeChartDataRaw.performance, safeChartDataRaw.dates.length),
        quality: padToLength(safeChartDataRaw.quality, safeChartDataRaw.dates.length),
        machines: safeChartDataRaw.machines,
        machineOEE: safeChartDataRaw.machineOEE,
        ngLabels: safeChartDataRaw.ngLabels,
        ngCategories: safeChartDataRaw.ngCategories
    };

    // Tick step helper for x-axis (reduce clutter on long ranges)
    function getTickStep(period, len) {
        if (period === 'daily') return 1; // show all hours for today
        const targetTickCount = 8; // aim to show ~8 labels max for long ranges
        return Math.max(1, Math.ceil(len / targetTickCount));
    }
    const initialTickStep = getTickStep(window.currentPeriod, safeChartData.dates.length);
    
    console.log('Using chart data:', safeChartData);
    console.log('Current period:', window.currentPeriod);
    console.log('Is daily view:', window.currentPeriod === 'daily');
    
    // OEE Trend Chart
    const oeeCtx = document.getElementById('oeeChart');
    if (oeeCtx) {
        oeeChart = new Chart(oeeCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: safeChartData.dates,
                datasets: [
                    {
                        label: 'OEE',
                        data: safeChartData.oee,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#667eea',
                        pointBorderColor: '#667eea',
                        pointRadius: safeChartData.dates.length === 1 ? 6 : 4,
                        pointHoverRadius: safeChartData.dates.length === 1 ? 8 : 6
                    },
                    {
                        label: 'Availability',
                        data: safeChartData.availability,
                        borderColor: '#84fab0',
                        backgroundColor: 'rgba(132, 250, 176, 0.1)',
                        tension: 0.4,
                        pointBackgroundColor: '#84fab0',
                        pointBorderColor: '#84fab0',
                        pointRadius: safeChartData.dates.length === 1 ? 6 : 4,
                        pointHoverRadius: safeChartData.dates.length === 1 ? 8 : 6
                    },
                    {
                        label: 'Performance',
                        data: safeChartData.performance,
                        borderColor: '#fa709a',
                        backgroundColor: 'rgba(250, 112, 154, 0.1)',
                        tension: 0.4,
                        pointBackgroundColor: '#fa709a',
                        pointBorderColor: '#fa709a',
                        pointRadius: safeChartData.dates.length === 1 ? 6 : 4,
                        pointHoverRadius: safeChartData.dates.length === 1 ? 8 : 6
                    },
                    {
                        label: 'Quality',
                        data: safeChartData.quality,
                        borderColor: '#30cfd0',
                        backgroundColor: 'rgba(48, 207, 208, 0.1)',
                        tension: 0.4,
                        pointBackgroundColor: '#30cfd0',
                        pointBorderColor: '#30cfd0',
                        pointRadius: safeChartData.dates.length === 1 ? 6 : 4,
                        pointHoverRadius: safeChartData.dates.length === 1 ? 8 : 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        },
                        // Prevent hiding the Quality series when legend is clicked
                        onClick: function(e, legendItem, legend) {
                            try {
                                if (legendItem && legendItem.text === 'Quality') {
                                    // Ignore clicks on Quality legend item
                                    return;
                                }
                                // Fallback to default toggle behavior for others
                                const defaultHandler = Chart.defaults.plugins.legend.onClick;
                                if (typeof defaultHandler === 'function') {
                                    defaultHandler(e, legendItem, legend);
                                }
                            } catch (err) {
                                // In case of any error, don't break user interaction
                                console.error('Legend onClick error:', err);
                            }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#667eea',
                        borderWidth: 1
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            },
                            color: '#6c757d'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#6c757d',
                            autoSkip: false,
                            maxTicksLimit: safeChartData.dates.length,
                            maxRotation: safeChartData.dates.length > 12 ? 45 : 0,
                            minRotation: 0,
                            padding: 10,
                            callback: function(value, index) {
                                // For daily view, show all labels (hourly)
                                if (window.currentPeriod === 'daily') {
                                    return this.getLabelForValue(value);
                                }
                                return (index % initialTickStep === 0) ? this.getLabelForValue(value) : '';
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        },
                        offset: safeChartData.dates.length > 1
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });
    }

    // OEE Component Pie Chart
    const componentCtx = document.getElementById('oeeComponentChart');
    if (componentCtx) {
        componentChart = new Chart(componentCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Availability', 'Performance', 'Quality'],
                datasets: [{
                    data: [
                        window.avgAvailability || 0,
                        window.avgPerformance || 0,
                        window.avgQuality || 0
                    ],
                    backgroundColor: ['#84fab0', '#fa709a', '#30cfd0'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + '%';
                            }
                        }
                    }
                },
                cutout: '60%'
            }
        });
    }

    // Machine Performance Chart
    const machineCtx = document.getElementById('machineChart');
    if (machineCtx) {
        machineChart = new Chart(machineCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: safeChartData.machines,
                datasets: [{
                    label: 'OEE %',
                    data: safeChartData.machineOEE,
                    backgroundColor: '#667eea',
                    borderColor: '#667eea',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'OEE: ' + context.parsed.x + '%';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            },
                            color: '#6c757d'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    y: {
                        ticks: {
                            color: '#6c757d'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // NG Category Chart
    const ngCtx = document.getElementById('ngChart');
    if (ngCtx) {
        ngChart = new Chart(ngCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: safeChartData.ngLabels,
                datasets: [{
                    label: 'NG Count',
                    data: safeChartData.ngCategories,
                    backgroundColor: [
                        '#f8d7da', '#fa709a', '#ffc107', '#17a2b8', '#6c757d',
                        '#667eea', '#84fab0', '#fa709a', '#30cfd0', '#f6d365',
                        '#a1c4fd', '#c2e9fb', '#d4fc79', '#96e6a1', '#84fab0',
                        '#ffeeee', '#ddffff', '#eeffdd', '#ffffdd', '#ffeeff'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'NG Count: ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#6c757d'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#6c757d'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
}

/**
 * Initialize table search functionality
 */
// Removed initializeTableSearch - now using DataTables built-in search functionality

/**
 * Initialize filter functionality
 */
function initializeFilters() {
    // Period change buttons
    const periodButtons = document.querySelectorAll('button[onclick^="changePeriod"]');
    periodButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            periodButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
        });
    });
    
    // Set initial active period based on current period
    if (window.currentPeriod) {
        updateActivePeriodButton(window.currentPeriod);
    }
}

/**
 * Change period for charts
 */
function changePeriod(period) {
    // Show loading state
    const buttons = document.querySelectorAll('button[onclick^="changePeriod"]');
    const clickedButton = event.target;
    
    // Disable all buttons and show loading on clicked button
    buttons.forEach(btn => {
        btn.disabled = true;
        if (btn === clickedButton) {
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Loading...';
        }
    });
    
    // Show loading overlay
    showLoadingOverlay();
    
    // Fetch new data for the selected period
    fetchOeeDataForPeriod(period)
        .then(data => {
            updateChartsWithNewData(data);
            updateKPIValues(data);
            updateActivePeriodButton(period);
        })
        .catch(error => {
            console.error('Error fetching period data:', error);
            showErrorMessage('Failed to load data for selected period. Falling back to page reload.');
            // Fallback to page reload if AJAX fails
            setTimeout(() => {
                const url = new URL(window.location);
                url.searchParams.set('period', period);
                window.location.href = url.toString();
            }, 2000);
        })
        .finally(() => {
            hideLoadingOverlay();
            // Reset button states
            buttons.forEach(btn => {
                btn.disabled = false;
                // Reset button text based on period
                const periodTexts = {
                    '7days': '7 Days',
                    '30days': '30 Days', 
                    'month': 'This Month',
                    'daily': 'Today'
                };
                btn.innerHTML = periodTexts[btn.getAttribute('onclick').match(/'(\w+)'/)[1]] || 'Period';
            });
        });
}

/**
 * View OEE detail in modal
 */
function viewDetail(id) {
    // Show modal with loading state
    $('#detailModal').modal('show');
    $('#detailModalContent').html(`
        <div class="text-center py-5">
            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
            <p class="mt-3 text-muted">Loading data...</p>
        </div>
    `);
    
    // Fetch data via AJAX
    $.ajax({
        url: `/oee/detail/${id}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const oee = response.data;
                const content = renderOeeDetail(oee);
                $('#detailModalContent').html(content);
            } else {
                $('#detailModalContent').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        ${response.message || 'Failed to load data'}
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            $('#detailModalContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    Terjadi kesalahan saat memuat data: ${error}
                </div>
            `);
        }
    });
}

/**
 * Render OEE detail HTML
 */
function renderOeeDetail(oee) {
    // Helper function to format numbers
    const formatNumber = (num) => {
        return num ? parseFloat(num).toLocaleString('id-ID') : '0';
    };

    // Helper function to get badge color
    const getBadgeColor = (value, thresholds = [85, 70]) => {
        if (value >= thresholds[0]) return 'success';
        if (value >= thresholds[1]) return 'warning';
        return 'danger';
    };

    // Determine machine/meja name based on bagian
    const machineName = oee.bagian === 'assy' 
        ? (oee.meja_nama || 'N/A') 
        : (oee.mesin_nama || 'N/A');

    return `
        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1"><i class="fas fa-calendar mr-2"></i>${oee.tanggal_formatted || 'N/A'}</h5>
                            <p class="text-muted mb-0">
                                <span class="badge badge-${oee.bagian === 'assy' ? 'info' : 'primary'}">${oee.bagian === 'assy' ? 'Assy' : 'Inject'}</span>
                                ${machineName}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- OEE Metrics -->
            <div class="row mb-4">
                <div class="col-xl-8 mb-3">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-chart-line mr-2"></i>OEE Metrics</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-3 mb-3">
                                    <h6 class="text-muted small">Availability</h6>
                                    <h3 class="text-${oee.availability_color || getBadgeColor(oee.availability_rate)}">${formatNumber(oee.availability_rate)}%</h3>
                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar bg-${oee.availability_color || getBadgeColor(oee.availability_rate)}" style="width: ${oee.availability_rate}%"></div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <h6 class="text-muted small">Performance</h6>
                                    <h3 class="text-${oee.performance_color || getBadgeColor(oee.performance_rate, [95, 85])}">${formatNumber(oee.performance_rate)}%</h3>
                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar bg-${oee.performance_color || getBadgeColor(oee.performance_rate, [95, 85])}" style="width: ${Math.min(oee.performance_rate, 100)}%"></div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <h6 class="text-muted small">Quality</h6>
                                    <h3 class="text-${oee.quality_color || getBadgeColor(oee.quality_rate, [99, 95])}">${formatNumber(oee.quality_rate)}%</h3>
                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar bg-${oee.quality_color || getBadgeColor(oee.quality_rate, [99, 95])}" style="width: ${oee.quality_rate}%"></div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <h6 class="text-muted small">Overall OEE</h6>
                                    <h3 class="text-${oee.oee_color || getBadgeColor(oee.oee_rate)}"><strong>${formatNumber(oee.oee_rate)}%</strong></h3>
                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar bg-${oee.oee_color || getBadgeColor(oee.oee_rate)}" style="width: ${oee.oee_rate}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Production Info -->
                <div class="col-xl-4 mb-3">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-info-circle mr-2"></i>Production Information</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <td class="text-muted"><strong>${oee.bagian === 'assy' ? 'Meja:' : 'Machine:'}</strong></td>
                                    <td>${machineName}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><strong>Part:</strong></td>
                                    <td>${oee.part_no || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><strong>Date:</strong></td>
                                    <td>${oee.tanggal_formatted || 'N/A'}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><strong>Planned Time:</strong></td>
                                    <td>${formatNumber(oee.planned_time)} min</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><strong>Run Time:</strong></td>
                                    <td>${formatNumber(oee.run_time)} min</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><strong>Down Time:</strong></td>
                                    <td>${formatNumber(oee.down_time)} min</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><strong>Cycle Time (Std):</strong></td>
                                    <td>${formatNumber(oee.cycle_time_std)} sec</td>
                                </tr>
                                <tr>
                                    <td class="text-muted"><strong>Cycle Time (Actual):</strong></td>
                                    <td>${formatNumber(oee.cycle_time_actual)} sec</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Output Summary & NG Analysis -->
            <div class="row">
                <div class="col-xl-6 mb-3">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-chart-bar mr-2"></i>Output Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <h4 class="text-primary">${formatNumber(oee.actual_output)}</h4>
                                    <small class="text-muted">Total Output</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-success">${formatNumber(oee.good_output)}</h4>
                                    <small class="text-muted">Good Output</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-danger">${formatNumber(oee.ng_output)}</h4>
                                    <small class="text-muted">NG Output</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 mb-3">
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-exclamation-triangle mr-2"></i>NG Analysis</h6>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm mb-0">
                                ${oee.ng_details && oee.ng_details.length > 0 ? 
                                    oee.ng_details.map(detail => `
                                        <tr>
                                            <td>${detail.name}:</td>
                                            <td class="text-right"><span class="badge badge-danger">${formatNumber(detail.quantity)}</span></td>
                                        </tr>
                                    `).join('') : 
                                    `
                                    <tr>
                                        <td>Material NG:</td>
                                        <td class="text-right"><span class="badge badge-danger">${formatNumber(oee.ng_material || 0)}</span></td>
                                    </tr>
                                    <tr>
                                        <td>Process NG:</td>
                                        <td class="text-right"><span class="badge badge-warning">${formatNumber(oee.ng_process || 0)}</span></td>
                                    </tr>
                                    <tr>
                                        <td>Machine NG:</td>
                                        <td class="text-right"><span class="badge badge-info">${formatNumber(oee.ng_machine || 0)}</span></td>
                                    </tr>
                                    <tr>
                                        <td>Operator NG:</td>
                                        <td class="text-right"><span class="badge badge-secondary">${formatNumber(oee.ng_operator || 0)}</span></td>
                                    </tr>
                                    <tr>
                                        <td>Other NG:</td>
                                        <td class="text-right"><span class="badge badge-dark">${formatNumber(oee.ng_other || 0)}</span></td>
                                    </tr>
                                    `
                                }
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Export OEE report
 */
function exportReport() {
    // Show loading state
    const button = event.target;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
    
    // Get current filter parameters
    const url = new URL(window.location);
    const params = new URLSearchParams(url.search);
    
    // Add export parameter
    params.set('export', 'excel');
    
    // Create download link
    const downloadUrl = `${window.location.pathname}?${params.toString()}`;
    
    // Trigger download
    const link = document.createElement('a');
    link.href = downloadUrl;
    link.download = `oee-report-${new Date().toISOString().split('T')[0]}.xlsx`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Reset button after delay
    setTimeout(() => {
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-download"></i> Export';
    }, 2000);
}

/**
 * Refresh dashboard data
 */
function refreshDashboard() {
    // Show loading overlay
    const container = document.querySelector('.container-fluid');
    container.classList.add('loading');
    
    // Reload page after short delay
    setTimeout(() => {
        window.location.reload();
    }, 1000);
}

/**
 * Animate value changes
 */
function animateValue(element, start, end, duration) {
    const startTime = performance.now();
    
    function updateValue(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const current = start + (end - start) * progress;
        element.textContent = current.toFixed(1) + '%';
        
        if (progress < 1) {
            requestAnimationFrame(updateValue);
        }
    }
    
    requestAnimationFrame(updateValue);
}

/**
 * Handle responsive chart resizing
 */
function handleChartResize() {
    if (oeeChart) oeeChart.resize();
    if (componentChart) componentChart.resize();
    if (machineChart) machineChart.resize();
    if (ngChart) ngChart.resize();
}

// Add resize listener
window.addEventListener('resize', handleChartResize);

/**
 * Initialize real-time updates
 */
function initializeRealTimeUpdates() {
    // Check if real-time updates are enabled (on body or specific element)
    const realTimeEnabled = document.body.getAttribute('data-real-time') === 'true' || 
                           (document.getElementById('oee-dashboard-data') && 
                            document.getElementById('oee-dashboard-data').getAttribute('data-real-time') === 'true');
    
    if (realTimeEnabled) {
        console.log('Real-time updates enabled for OEE dashboard');
        // Update data every 30 seconds
        setInterval(() => {
            updateDashboardRealtime();
        }, 30000);
    }
}

/**
 * Update the entire dashboard for real-time
 */
async function updateDashboardRealtime() {
    try {
        const data = await fetchOeeDataForPeriod(window.currentPeriod);
        if (data.success) {
            updateChartsWithNewData(data);
            updateKPIValues(data);
            if (data.oeeData) {
                updateOeeTable(data.oeeData);
            }
        }
    } catch (error) {
        console.error('Error fetching real-time data:', error);
    }
}

/**
 * Update charts with new data
 */
function updateCharts(data) {
    // Update OEE trend chart
    if (oeeChart && data.chartData) {
        oeeChart.data.labels = data.chartData.dates;
        oeeChart.data.datasets[0].data = data.chartData.oee;
        oeeChart.data.datasets[1].data = data.chartData.availability;
        oeeChart.data.datasets[2].data = data.chartData.performance;
        oeeChart.data.datasets[3].data = data.chartData.quality;
        oeeChart.update();
    }
    
    // Update component chart
    if (componentChart) {
        componentChart.data.datasets[0].data = [
            data.avgAvailability,
            data.avgPerformance,
            data.avgQuality
        ];
        componentChart.update();
    }
}

/**
 * Fetch OEE data for specific period
 */
async function fetchOeeDataForPeriod(period) {
    const url = new URL(window.location);
    url.searchParams.set('period', period);
    url.searchParams.set('ajax', '1');
    
    try {
        const response = await fetch(url.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.message || 'Failed to fetch data');
        }
        
        return data;
    } catch (error) {
        console.error('Error fetching OEE data:', error);
        throw error;
    }
}

/**
 * Update charts with new period data
 */
function updateChartsWithNewData(data) {
    console.log('Updating charts with data:', data);
    
    // Update OEE trend chart
    if (oeeChart && data.chartData) {
        console.log('Updating OEE chart with:', {
            labels: data.chartData.dates,
            oee: data.chartData.oee,
            availability: data.chartData.availability,
            performance: data.chartData.performance,
            quality: data.chartData.quality
        });
        
        const L = data.chartData.dates.length;
        function pad(arr){ const out = Array.isArray(arr)?arr.slice(0,L):[]; while(out.length<L) out.push(0); return out; }
        oeeChart.data.labels = data.chartData.dates;
        oeeChart.data.datasets[0].data = pad(data.chartData.oee);
        oeeChart.data.datasets[1].data = pad(data.chartData.availability);
        oeeChart.data.datasets[2].data = pad(data.chartData.performance);
        oeeChart.data.datasets[3].data = pad(data.chartData.quality);
        
        // Update point sizes for single data point
        const pointRadius = L === 1 ? 6 : 4;
        const hoverRadius = L === 1 ? 8 : 6;
        oeeChart.data.datasets.forEach(dataset => {
            dataset.pointRadius = pointRadius;
            dataset.pointHoverRadius = hoverRadius;
        });
        // Update x-axis tick density after period change
        if (oeeChart.options && oeeChart.options.scales && oeeChart.options.scales.x) {
            const step = (function(period, len){
                if (period === 'daily') return 1;
                const targetTickCount = 8;
                return Math.max(1, Math.ceil(len / targetTickCount));
            })(window.currentPeriod, L);
            oeeChart.options.scales.x.ticks.padding = 10;
            oeeChart.options.scales.x.offset = L > 1; // Only offset if more than 1 data point
            oeeChart.options.scales.x.ticks.autoSkip = false;
            oeeChart.options.scales.x.ticks.maxTicksLimit = L;
            oeeChart.options.scales.x.ticks.callback = function(value, index) {
                // For daily view (hourly data), show all labels
                if (window.currentPeriod === 'daily') {
                    return this.getLabelForValue(value);
                }
                return (index % step === 0) ? this.getLabelForValue(value) : '';
            };
            oeeChart.options.scales.x.ticks.maxRotation = L > 12 ? 45 : 0;
        }
        oeeChart.update('active');
    }
    
    // Update component chart
    if (componentChart) {
        componentChart.data.datasets[0].data = [
            data.avgAvailability,
            data.avgPerformance,
            data.avgQuality
        ];
        componentChart.update('active');
    }
    
    // Update machine chart
    if (machineChart && data.chartData) {
        machineChart.data.labels = data.chartData.machines;
        machineChart.data.datasets[0].data = data.chartData.machineOEE;
        machineChart.update('active');
    }
    
    // Update NG chart
    if (ngChart && data.chartData) {
        ngChart.data.datasets[0].data = data.chartData.ngCategories;
        ngChart.update('active');
    }
}

/**
 * Update KPI values with animation
 */
function updateKPIValues(data) {
    const kpis = [
        { id: 'avgOEE', value: data.avgOEE },
        { id: 'avgAvailability', value: data.avgAvailability },
        { id: 'avgPerformance', value: data.avgPerformance },
        { id: 'avgQuality', value: data.avgQuality }
    ];
    
    kpis.forEach(kpi => {
        const element = document.getElementById(kpi.id);
        if (element) {
            animateValue(element, parseFloat(element.textContent), kpi.value, 1000);
        }
    });

    // Update summary cards (total time cards)
    if (data.totalPlannedMinutes !== undefined) {
        updateSummaryCards(data);
    }
}

/**
 * Update summary cards with new time data
 */
function updateSummaryCards(data) {
    console.log('Updating summary cards with data:', data);
    
    // Update Planned Time
    const plannedValue = document.getElementById('summaryPlannedValue');
    const plannedMinutes = document.getElementById('summaryPlannedMinutes');
    if (plannedValue && data.totalPlannedMinutes !== undefined) {
        const hours = (data.totalPlannedMinutes / 60).toFixed(1);
        const minutes = data.totalPlannedMinutes.toFixed(0);
        plannedValue.textContent = hours + ' jam';
        if (plannedMinutes) {
            plannedMinutes.textContent = minutes + ' menit';
        }
    }

    // Update Run Time
    const runValue = document.getElementById('summaryRunValue');
    const runMinutes = document.getElementById('summaryRunMinutes');
    if (runValue && data.totalRunMinutes !== undefined) {
        const hours = (data.totalRunMinutes / 60).toFixed(1);
        const minutes = data.totalRunMinutes.toFixed(0);
        runValue.textContent = hours + ' jam';
        if (runMinutes) {
            runMinutes.textContent = minutes + ' menit';
        }
    }

    // Update Down Time
    const downValue = document.getElementById('summaryDownValue');
    const downMinutes = document.getElementById('summaryDownMinutes');
    if (downValue && data.totalDownMinutes !== undefined) {
        const hours = (data.totalDownMinutes / 60).toFixed(1);
        const minutes = data.totalDownMinutes.toFixed(0);
        downValue.textContent = hours + ' jam';
        if (downMinutes) {
            downMinutes.textContent = minutes + ' menit';
        }
    }
}

/**
 * Update active period button
 */
function updateActivePeriodButton(period) {
    const buttons = document.querySelectorAll('button[onclick^="changePeriod"]');
    buttons.forEach(btn => {
        btn.classList.remove('active');
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-outline-secondary');
        
        if (btn.getAttribute('onclick').includes(period)) {
            btn.classList.add('active');
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-primary');
        }
    });
}

/**
 * Show loading overlay
 */
function showLoadingOverlay() {
    let overlay = document.getElementById('loadingOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'loadingOverlay';
        overlay.className = 'loading-overlay';
        overlay.innerHTML = `
            <div class="loading-content">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Loading data...</p>
            </div>
        `;
        document.body.appendChild(overlay);
    }
    overlay.style.display = 'flex';
}

/**
 * Hide loading overlay
 */
function hideLoadingOverlay() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) {
        overlay.style.display = 'none';
    }
}

/**
 * Show error message
 */
function showErrorMessage(message) {
    // Create a more user-friendly error notification
    const errorDiv = document.createElement('div');
    errorDiv.className = 'alert alert-danger alert-dismissible fade show position-fixed';
    errorDiv.style.cssText = 'top: 20px; right: 20px; z-index: 10000; min-width: 300px;';
    errorDiv.innerHTML = `
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <strong>Error:</strong> ${message}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    `;
    
    document.body.appendChild(errorDiv);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (errorDiv.parentNode) {
            errorDiv.parentNode.removeChild(errorDiv);
        }
    }, 5000);
}

// Initialize real-time updates
initializeRealTimeUpdates();

/**
 * Initialize DataTables for OEE table
 */
function initializeDataTables() {
    if (typeof $ !== 'undefined' && $.fn.dataTable) {
        oeeDataTable = $('#oeeTable').DataTable({
            responsive: true,
            pageLength: 10,
            lengthChange: true,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
            ordering: true,
            order: [[0, 'desc']], // Sort by date descending
            info: true,
            searching: true,
            autoWidth: false,
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            language: {
                emptyTable: 'Tidak ada data OEE tersedia',
                info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ entri',
                infoEmpty: 'Menampilkan 0 sampai 0 dari 0 entri',
                infoFiltered: '(difilter dari _MAX_ total entri)',
                lengthMenu: 'Tampilkan _MENU_ entri',
                search: 'Cari:',
                zeroRecords: 'Tidak ada data yang ditemukan',
                paginate: {
                    first: 'Pertama',
                    last: 'Terakhir',
                    next: 'Selanjutnya',
                    previous: 'Sebelumnya'
                }
            },
            drawCallback: function(settings) {
                // Reinitialize tooltips after table redraw
                if (typeof $ !== 'undefined' && $.fn.tooltip) {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            }
        });
    }
}

/**
 * Update OEE Table with new data
 */
function updateOeeTable(oeeRecords) {
    if (!oeeDataTable) return;

    // Save current pagination state
    const currentInfo = oeeDataTable.page.info();
    const currentPage = currentInfo.page;

    // Clear and add new rows
    oeeDataTable.clear();

    oeeRecords.forEach(oee => {
        const dateStr = oee.tanggal ? new Date(oee.tanggal).toLocaleDateString('id-ID').replace(/\//g, '-') : 'N/A';
        const bagian = oee.planning && oee.planning.bagian ? oee.planning.bagian : 'inject';
        
        const machineCol = bagian === 'assy' 
            ? (oee.planning && oee.planning.meja ? oee.planning.meja.nama_meja : 'N/A')
            : `<div class="mb-1">${oee.mesin ? oee.mesin.nama_mesin : 'N/A'}</div>` + 
              (oee.mesin && oee.mesin.status ? `<span class="badge badge-${getStatusColor(oee.mesin.status)}" style="font-size: 0.65rem; padding: 2px 6px;">${oee.mesin.status.toUpperCase()}</span>` : '');

        oeeDataTable.row.add([
            dateStr,
            `<span class="badge badge-${bagian === 'assy' ? 'info' : 'primary'}">${bagian.toUpperCase()}</span>`,
            machineCol,
            oee.planning && oee.planning.part ? oee.planning.part.no_parts : 'N/A',
            `<span class="badge badge-secondary">${formatNumber(oee.actual_output)}</span>`,
            `<span class="badge badge-success">${formatNumber(oee.good_output)}</span>`,
            `<span class="badge badge-danger">${formatNumber(oee.ng_output)}</span>`,
            `<span class="badge badge-${oee.availability_color || 'secondary'}">${formatNumber(oee.availability_rate, 1)}%</span>`,
            `<span class="badge badge-${oee.performance_color || 'secondary'}">${formatNumber(oee.performance_rate, 1)}%</span>`,
            `<span class="badge badge-${oee.quality_color || 'secondary'}">${formatNumber(oee.quality_rate, 1)}%</span>`,
            `<span class="badge badge-${oee.oee_color || 'primary'} font-weight-bold">${formatNumber(oee.oee_rate, 1)}%</span>`,
            `<button class="btn btn-sm btn-info btn-view-detail" data-id="${oee.id}" title="Detail" data-toggle="tooltip"><i class="fas fa-eye"></i></button>`
        ]);
    });

    // Redraw and restore page
    oeeDataTable.draw(false);
    if (currentPage < oeeDataTable.page.info().pages) {
        oeeDataTable.page(currentPage).draw(false);
    }
}

function getStatusColor(status) {
    switch (status) {
        case 'running': return 'success';
        case 'maintenance': return 'danger';
        case 'stop': return 'warning';
        case 'offline': return 'dark';
        default: return 'secondary';
    }
}

function formatNumber(num, decimals = 0) {
    return parseFloat(num || 0).toLocaleString('id-ID', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
}

// Export functions to global scope for onclick handlers
window.changePeriod = changePeriod;
window.viewDetail = viewDetail;
window.exportReport = exportReport;
window.refreshDashboard = refreshDashboard;
