/**
 * OEE SYSTEM - MAIN JAVASCRIPT FILE
 * ===================================
 * This file contains all the JavaScript functionality for the OEE system
 */

// ===================================
// GLOBAL VARIABLES
// ===================================
const OEE_CONFIG = {
    API_BASE_URL: '/oee',
    REFRESH_INTERVAL: 30000, // 30 seconds
    ANIMATION_DURATION: 300,
    CHART_COLORS: {
        primary: '#007bff',
        success: '#28a745',
        warning: '#ffc107',
        danger: '#dc3545',
        info: '#17a2b8',
        secondary: '#6c757d'
    }
};

// ===================================
// UTILITY FUNCTIONS
// ===================================

/**
 * Format number with commas
 * @param {number} num - Number to format
 * @returns {string} Formatted number
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * Format percentage with 2 decimal places
 * @param {number} num - Number to format
 * @returns {string} Formatted percentage
 */
function formatPercentage(num) {
    return parseFloat(num).toFixed(2) + '%';
}

/**
 * Format duration from minutes to hours and minutes
 * @param {number} minutes - Minutes to format
 * @returns {string} Formatted duration
 */
function formatDuration(minutes) {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return `${hours}h ${mins}m`;
}

/**
 * Show notification/toast message
 * @param {string} message - Message to display
 * @param {string} type - Type of notification (success, error, warning, info)
 * @param {number} duration - Duration in milliseconds
 */
function showNotification(message, type = 'info', duration = 5000) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    `;
    
    notification.innerHTML = `
        <i class="fas fa-${getNotificationIcon(type)} mr-2"></i>
        ${message}
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after duration
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, duration);
    
    // Add animation
    setTimeout(() => {
        notification.classList.add('fade-in');
    }, 100);
}

/**
 * Get icon for notification type
 * @param {string} type - Notification type
 * @returns {string} Icon name
 */
function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

/**
 * Show loading state
 * @param {string} elementId - Element ID to show loading
 */
function showLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.classList.add('loading');
    }
}

/**
 * Hide loading state
 * @param {string} elementId - Element ID to hide loading
 */
function hideLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.classList.remove('loading');
    }
}

/**
 * Validate form data
 * @param {Object} data - Form data to validate
 * @returns {Object} Validation result
 */
function validateFormData(data) {
    const errors = {};
    
    Object.keys(data).forEach(key => {
        if (!data[key] || data[key].toString().trim() === '') {
            errors[key] = `${key.replace('_', ' ')} is required`;
        }
    });
    
    return {
        isValid: Object.keys(errors).length === 0,
        errors
    };
}

// ===================================
// MACHINE MANAGEMENT FUNCTIONS
// ===================================

/**
 * View machine details
 * @param {number} machineId - Machine ID
 */
function viewMachineDetails(machineId) {
    try {
        window.location.href = `${OEE_CONFIG.API_BASE_URL}/machines/${machineId}/details`;
    } catch (error) {
        console.error('Error navigating to machine details:', error);
        showNotification('Error navigating to machine details', 'error');
    }
}

/**
 * Edit machine
 * @param {number} machineId - Machine ID
 */
function editMachine(machineId) {
    try {
        window.location.href = `${OEE_CONFIG.API_BASE_URL}/machines/${machineId}/edit`;
    } catch (error) {
        console.error('Error navigating to edit machine:', error);
        showNotification('Error navigating to edit machine', 'error');
    }
}

/**
 * View OEE history
 * @param {number} machineId - Machine ID
 */
function viewOeeHistory(machineId) {
    try {
        window.location.href = `${OEE_CONFIG.API_BASE_URL}/machines/${machineId}/oee-history`;
    } catch (error) {
        console.error('Error navigating to OEE history:', error);
        showNotification('Error navigating to OEE history', 'error');
    }
}

/**
 * Export machines data
 */
function exportMachines() {
    try {
        showLoading('exportBtn');
        window.location.href = `${OEE_CONFIG.API_BASE_URL}/machines/export`;
        
        setTimeout(() => {
            hideLoading('exportBtn');
            showNotification('Export started successfully', 'success');
        }, 1000);
    } catch (error) {
        console.error('Error exporting machines:', error);
        showNotification('Error exporting machines', 'error');
        hideLoading('exportBtn');
    }
}

/**
 * Add new machine
 * @param {Object} formData - Form data
 */
async function addMachine(formData) {
    try {
        showLoading('addMachineBtn');
        
        const response = await fetch(`${OEE_CONFIG.API_BASE_URL}/machines`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Machine added successfully', 'success');
            $('#addMachineModal').modal('hide');
            location.reload();
        } else {
            showNotification(result.message || 'Failed to add machine', 'error');
        }
    } catch (error) {
        console.error('Error adding machine:', error);
        showNotification('Error adding machine', 'error');
    } finally {
        hideLoading('addMachineBtn');
    }
}

/**
 * Delete machine
 * @param {number} machineId - Machine ID
 */
async function deleteMachine(machineId) {
    if (!confirm('Are you sure you want to delete this machine?')) {
        return;
    }
    
    try {
        showLoading(`deleteBtn${machineId}`);
        
        const response = await fetch(`${OEE_CONFIG.API_BASE_URL}/machines/${machineId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Machine deleted successfully', 'success');
            location.reload();
        } else {
            showNotification(result.message || 'Failed to delete machine', 'error');
        }
    } catch (error) {
        console.error('Error deleting machine:', error);
        showNotification('Error deleting machine', 'error');
    } finally {
        hideLoading(`deleteBtn${machineId}`);
    }
}

// ===================================
// NOTIFICATION SYSTEM
// ===================================

/**
 * Load notifications
 */
async function loadNotifications() {
    try {
        const response = await fetch(`${OEE_CONFIG.API_BASE_URL}/notifications`);
        const data = await response.json();
        
        // Update notification count
        const countElement = document.getElementById('notification-count');
        const headerElement = document.getElementById('notification-header');
        
        if (countElement) countElement.textContent = data.count;
        if (headerElement) headerElement.textContent = `${data.count} Notifications`;
        
        // Update notification list
        const listElement = document.getElementById('notification-list');
        if (listElement && data.notifications) {
            let html = '';
            data.notifications.forEach(notification => {
                html += `
                    <a href="${notification.link}" class="dropdown-item">
                        <i class="fas fa-${notification.icon} mr-2"></i> ${notification.message}
                        <span class="float-right text-muted text-sm">${notification.time}</span>
                    </a>
                `;
            });
            listElement.innerHTML = html;
        }
    } catch (error) {
        console.error('Error loading notifications:', error);
    }
}

// ===================================
// CHART FUNCTIONS
// ===================================

/**
 * Initialize OEE charts
 * @param {Object} config - Chart configuration
 */
function initializeOeeCharts(config = {}) {
    const defaultConfig = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            }
        }
    };
    
    return { ...defaultConfig, ...config };
}

/**
 * Create OEE bar chart
 * @param {string} canvasId - Canvas element ID
 * @param {Array} labels - Chart labels
 * @param {Array} data - Chart data
 * @param {string} color - Chart color
 */
function createOeeBarChart(canvasId, labels, data, color = OEE_CONFIG.CHART_COLORS.primary) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'OEE (%)',
                data: data,
                backgroundColor: color,
                borderColor: color,
                borderWidth: 1
            }]
        },
        options: initializeOeeCharts({
            plugins: {
                legend: { display: false }
            }
        })
    });
}

/**
 * Create OEE pie chart
 * @param {string} canvasId - Canvas element ID
 * @param {Array} labels - Chart labels
 * @param {Array} data - Chart data
 * @param {Array} colors - Chart colors
 */
function createOeePieChart(canvasId, labels, data, colors = []) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    
    const defaultColors = [
        OEE_CONFIG.CHART_COLORS.primary,
        OEE_CONFIG.CHART_COLORS.success,
        OEE_CONFIG.CHART_COLORS.warning
    ];
    
    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors.length > 0 ? colors : defaultColors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: initializeOeeCharts()
    });
}

/**
 * Create OEE line chart
 * @param {string} canvasId - Canvas element ID
 * @param {Array} labels - Chart labels
 * @param {Array} data - Chart data
 * @param {string} color - Chart color
 */
function createOeeLineChart(canvasId, labels, data, color = OEE_CONFIG.CHART_COLORS.success) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return null;
    
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'OEE (%)',
                data: data,
                borderColor: color,
                backgroundColor: color + '20',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: initializeOeeCharts()
    });
}

// ===================================
// FORM HANDLERS
// ===================================

/**
 * Initialize form handlers
 */
function initializeFormHandlers() {
    // Add machine form handler
    const addMachineForm = document.getElementById('addMachineForm');
    if (addMachineForm) {
        addMachineForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            // Validate form data
            const validation = validateFormData(data);
            if (!validation.isValid) {
                const errorMessage = Object.values(validation.errors).join(', ');
                showNotification(errorMessage, 'error');
                return;
            }
            
            await addMachine(data);
        });
    }
    
    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

// ===================================
// EVENT LISTENERS
// ===================================

/**
 * Initialize event listeners
 */
function initializeEventListeners() {
    // Modal events
    $('#addMachineModal').on('shown.bs.modal', function() {
        $(this).find('input:first').focus();
    });
    
    // Tooltip initialization
    $('[data-toggle="tooltip"]').tooltip();
    
    // Popover initialization
    $('[data-toggle="popover"]').popover();
    
    // Auto-hide alerts
    $('.alert').on('close.bs.alert', function() {
        $(this).fadeOut();
    });
}

// ===================================
// INITIALIZATION
// ===================================

/**
 * Initialize OEE system
 */
function initializeOeeSystem() {
    // Initialize form handlers
    initializeFormHandlers();
    
    // Initialize event listeners
    initializeEventListeners();
    
    // Load initial notifications
    loadNotifications();
    
    // Set up periodic refresh
    setInterval(loadNotifications, OEE_CONFIG.REFRESH_INTERVAL);
    
    // Add fade-in animation to cards
    document.querySelectorAll('.card').forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in');
    });
    
    console.log('OEE System initialized successfully');
}

// ===================================
// EXPORT FUNCTIONS
// ===================================

// Export functions for global use
window.OEE = {
    viewMachineDetails,
    editMachine,
    viewOeeHistory,
    exportMachines,
    addMachine,
    deleteMachine,
    showNotification,
    formatNumber,
    formatPercentage,
    formatDuration,
    createOeeBarChart,
    createOeePieChart,
    createOeeLineChart,
    initializeOeeSystem
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeOeeSystem();
}); 