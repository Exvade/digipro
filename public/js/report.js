$(document).ready(function() {
    console.log('Report.js loaded successfully');
    console.log('Report store URL:', window.reportStoreUrl);
    console.log('Export Excel URL:', window.exportExcelUrl);
    console.log('Export PDF URL:', window.exportPdfUrl);
    console.log('Report type:', window.reportType);
    console.log('User role:', window.userRole);
    console.log('User department:', window.userDepartment);
    
    // Initialize report functionality
    initializeReport();
});

/**
 * Initialize all report functionality
 */
function initializeReport() {
    // Set default dates
    setDefaultDates();
    
    // Initialize form handlers
    initializeFormHandlers();
    
    // Initialize group functionality
    initializeGroupFunctionality();
    
    // Initialize filter handlers
    initializeFilterHandlers();
}

/**
 * Set default date range (last month to today)
 */
function setDefaultDates() {
    const today = new Date();
    const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, today.getDate());
    
    $('#start_date').val(lastMonth.toISOString().split('T')[0]);
    $('#end_date').val(today.toISOString().split('T')[0]);
}

/**
 * Initialize form submission and field change handlers
 */
function initializeFormHandlers() {
    // Create report form submission
    $('#createReportForm').on('submit', handleReportSubmission);
    
    // Handle report type change
    $('#report_type').on('change', function() {
        updateFormFields($(this).val());
    });
    
    // Handle user selection change
    $('#user_id').on('change', function() {
        const selectedUserId = $(this).val();
        if (selectedUserId) {
            updateGroupField(selectedUserId);
        }
    });
    
    // Handle group selection change
    $('#grup_id').on('change', function() {
        const selectedGroupId = $(this).val();
        if (selectedGroupId) {
            updateUsersByGroup(selectedGroupId);
        } else {
            $('#user_id option').show();
        }
    });
}

/**
 * Handle report form submission
 */
function handleReportSubmission(e) {
    e.preventDefault();
    console.log('Report form submission started');
    
    // Validate required fields
    if (!validateReportForm()) {
        console.log('Form validation failed');
        return;
    }
    
    const formData = new FormData(this);
    console.log('Form data collected');
    
    // Ensure grup_id is properly set
    const grupId = $('#grup_id').val();
    if (grupId === null || grupId === undefined) {
        formData.set('grup_id', '');
    }
    
    // Log form data for debugging
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }
    
    // Show loading state
    showLoadingState();
    
    console.log('Sending AJAX request to:', window.reportStoreUrl);
    
    $.ajax({
        url: window.reportStoreUrl,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            console.log('Report generation success:', response);
            hideLoadingState();
            $('#createReportModal').modal('hide');
            
            if (response.data) {
                showReportResult(response.data);
            }
        },
        error: function(xhr, status, error) {
            console.error('Report generation error:', xhr.responseText);
            console.error('Status:', status);
            console.error('Error:', error);
            hideLoadingState();
            handleReportError(xhr);
        }
    });
}

/**
 * Validate report form
 */
function validateReportForm() {
    console.log('Validating report form...');
    
    const startDate = $('#start_date').val();
    const endDate = $('#end_date').val();
    const reportType = $('#report_type').val();
    const grupId = $('#grup_id').val();
    
    console.log('Start date:', startDate);
    console.log('End date:', endDate);
    console.log('Report type:', reportType);
    console.log('Grup ID:', grupId);
    
    if (!startDate || !endDate) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Tanggal mulai dan tanggal akhir harus diisi'
        });
        return false;
    }
    
    // Check if start date is before end date
    if (new Date(startDate) > new Date(endDate)) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir'
        });
        return false;
    }
    
    // For non-leader users, report type is required
    if (window.userRole !== 'leader' && (!reportType || reportType === '')) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Jenis laporan harus dipilih'
        });
        return false;
    }
    
    console.log('Form validation passed');
    return true;
}

/**
 * Handle report generation error
 */
function handleReportError(xhr) {
    let errorMessage = 'Terjadi kesalahan saat membuat laporan';
    
    if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMessage = xhr.responseJSON.message;
    } else if (xhr.responseText) {
        try {
            const response = JSON.parse(xhr.responseText);
            errorMessage = response.message || errorMessage;
        } catch (e) {
            errorMessage = xhr.responseText;
        }
    }
    
    Swal.fire({
        icon: 'error',
        title: 'Error!',
        text: errorMessage
    });
}

/**
 * Initialize group functionality
 */
function initializeGroupFunctionality() {
    // Add group filter to existing forms
    addGroupFilterToForms();
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Initialize select2 for better UX
    if ($.fn.select2) {
        $('#grup_id, #user_id').select2({
            placeholder: 'Pilih...',
            allowClear: true,
            width: '100%'
        });
    }
}

/**
 * Add group filter to report forms
 */
function addGroupFilterToForms() {
    const generalForm = $('#createReportForm');
    if (generalForm.length && !generalForm.find('#grup_id').length) {
        const groupFilterHtml = `
            <div class="col-md-6">
                <div class="form-group">
                    <label for="grup_id">Grup</label>
                    <select class="form-control" id="grup_id" name="grup_id">
                        <option value="">Semua Grup</option>
                        ${window.grups ? window.grups.map(grup => 
                            `<option value="${grup.id}">${grup.nama_grup}</option>`
                        ).join('') : ''}
                    </select>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Filter berdasarkan grup kerja
                    </small>
                </div>
            </div>
        `;
        
        // Insert after user_id field
        const userField = generalForm.find('#user_id').closest('.col-md-6');
        userField.after(groupFilterHtml);
    }
}

/**
 * Initialize filter handlers
 */
function initializeFilterHandlers() {
    // Handle page filter form submission
    $('#groupFilterForm').on('submit', handleFilterSubmission);
    
    // Handle page filter group change
    $('#filter_grup_id').on('change', function() {
        const selectedGroupId = $(this).val();
        updatePageFilterUsers(selectedGroupId);
    });
    
    // Handle page filter user change
    $('#filter_user_id').on('change', function() {
        const selectedUserId = $(this).val();
        if (selectedUserId) {
            updatePageFilterGroup(selectedUserId);
        }
    });
}

/**
 * Handle filter form submission
 */
function handleFilterSubmission(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const params = new URLSearchParams();
    
    // Add form data to URL params
    for (let [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }
    
    // Add current report type if exists
    const currentReportType = window.reportType;
    if (currentReportType && currentReportType !== 'general') {
        params.append('report_type', currentReportType);
    }
    
    // Redirect with filter parameters
    const currentUrl = new URL(window.location);
    currentUrl.search = params.toString();
    window.location.href = currentUrl.toString();
}

/**
 * Update form fields based on report type
 */
function updateFormFields(reportType) {
    // Update department_type hidden field
    $('input[name="department_type"]').val(reportType);
    
    // Show/hide relevant fields based on report type
    if (reportType === 'assy') {
        $('#mesin_id').closest('.col-md-6').hide();
        $('#meja_id').closest('.col-md-6').show();
    } else if (reportType === 'inject') {
        $('#mesin_id').closest('.col-md-6').show();
        $('#meja_id').closest('.col-md-6').hide();
    } else {
        $('#mesin_id').closest('.col-md-6').show();
        $('#meja_id').closest('.col-md-6').show();
    }
}

/**
 * Update group field based on selected user
 */
function updateGroupField(userId) {
    if (!userId) return;
    
    const userOption = $(`#user_id option[value="${userId}"]`);
    const groupId = userOption.data('grup-id');
    
    if (groupId) {
        $('#grup_id').val(groupId).trigger('change');
    }
}

/**
 * Update users based on selected group
 */
function updateUsersByGroup(groupId) {
    if (!groupId) {
        $('#user_id option').show();
        return;
    }
    
    // Show loading in user dropdown
    $('#user_id').prop('disabled', true);
    
    // Filter users by group
    $('#user_id option').each(function() {
        const optionGroupId = $(this).data('grup-id');
        
        if (optionGroupId == groupId || $(this).val() === '') {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
    
    $('#user_id').prop('disabled', false);
    
    // Reset user selection if current user is not in selected group
    const currentUserId = $('#user_id').val();
    if (currentUserId) {
        const currentUserOption = $(`#user_id option[value="${currentUserId}"]`);
        const currentUserGroupId = currentUserOption.data('grup-id');
        
        if (currentUserGroupId != groupId) {
            $('#user_id').val('').trigger('change');
        }
    }
}

/**
 * Show loading state
 */
function showLoadingState() {
    Swal.fire({
        title: 'Membuat Laporan...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
}

/**
 * Hide loading state
 */
function hideLoadingState() {
    Swal.close();
}

/**
 * Show report result in modal
 */
function showReportResult(data) {
    let reportType = window.reportType || 'general';
    
    // Check if department_type is available in the current form
    const currentForm = document.getElementById('createReportForm');
    if (currentForm) {
        const formData = new FormData(currentForm);
        const departmentType = formData.get('department_type');
        if (departmentType && ['assy', 'inject'].includes(departmentType)) {
            reportType = departmentType;
        }
    }
    
    // Create modal content
    const modalContent = createReportModalContent(data, reportType);
    
    // Remove existing modal if any
    $('#reportResultModal').remove();
    
    // Append and show modal
    $('body').append(modalContent);
    $('#reportResultModal').modal('show');
}

/**
 * Create report modal content
 */
function createReportModalContent(data, reportType) {
    const statusBadge = item => {
        const status = item.approval_status;
        const badgeClass = status === 'pending' ? 'badge-warning' : 
                          status === 'approved' ? 'badge-success' : 'badge-danger';
        const statusText = status === 'pending' ? 'Pending' : 
                          status === 'approved' ? 'Approved' : 'Rejected';
        return `<span class="badge ${badgeClass}">${statusText}</span>`;
    };
    
    const equipmentName = item => {
        return reportType === 'assy' ? 
            (item.meja?.nama_meja || '-') : 
            (item.mesin?.nama_mesin || '-');
    };
    
    const tableRows = data.data.map((item, index) => `
        <tr>
            <td>${index + 1}</td>
            <td>${new Date(item.created_at).toLocaleDateString('id-ID')}</td>
            <td>${equipmentName(item)}</td>
            <td>${item.part?.nama_part || '-'}</td>
            <td>${item.user?.nama || '-'}</td>
            <td>${item.user?.grup?.nama_grup || '-'}</td>
            <td><span class="badge badge-info">${item.shift ? item.shift.replace('_', ' ').toUpperCase() : '-'}</span></td>
            <td>${statusBadge(item)}</td>
        </tr>
    `).join('');
    
    return `
        <div class="modal fade" id="reportResultModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-file-alt mr-2"></i>Hasil Laporan
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <div class="info-box bg-info">
                                    <span class="info-box-icon"><i class="fas fa-clipboard-check"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Data</span>
                                        <span class="info-box-number">${data.total_records}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-warning">
                                    <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Pending</span>
                                        <span class="info-box-number">${data.summary.by_status.pending}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-success">
                                    <span class="info-box-icon"><i class="fas fa-check"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Approved</span>
                                        <span class="info-box-number">${data.summary.by_status.approved}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box bg-danger">
                                    <span class="info-box-icon"><i class="fas fa-times"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Rejected</span>
                                        <span class="info-box-number">${data.summary.by_status.rejected}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>${reportType === 'assy' ? 'Meja' : 'Mesin'}</th>
                                        <th>Part</th>
                                        <th>User</th>
                                        <th>Grup</th>
                                        <th>Shift</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${tableRows}
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        <button type="button" class="btn btn-success" onclick="exportReport('excel')">
                            <i class="fas fa-file-excel mr-1"></i>Export Excel
                        </button>
                        <button type="button" class="btn btn-danger" onclick="exportReport('pdf')">
                            <i class="fas fa-file-pdf mr-1"></i>Export PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

/**
 * Export report to Excel or PDF
 */
function exportReport(format) {
    const formData = new FormData($('#createReportForm')[0]);
    
    // Get report type
    let reportType = window.reportType || 'general';
    const departmentType = formData.get('department_type');
    if (departmentType && ['assy', 'inject'].includes(departmentType)) {
        reportType = departmentType;
    }
    
    // Show loading
    Swal.fire({
        title: 'Mengekspor Laporan...',
        text: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
    
    // Add report type to form data
    formData.append('report_type', reportType);
    
    // Determine export URL
    const exportUrl = format === 'excel' ? window.exportExcelUrl : window.exportPdfUrl;
    
    // Create and submit temporary form
    submitExportForm(exportUrl, formData);
    
    // Close loading and show success message
    Swal.close();
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: `Laporan berhasil diekspor ke format ${format.toUpperCase()}`,
        timer: 2000,
        showConfirmButton: false
    });
}

/**
 * Submit export form
 */
function submitExportForm(exportUrl, formData) {
    const tempForm = document.createElement('form');
    tempForm.method = 'POST';
    tempForm.action = exportUrl;
    tempForm.target = '_blank';
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = $('meta[name="csrf-token"]').attr('content');
    tempForm.appendChild(csrfToken);
    
    // Add form data
    for (let [key, value] of formData.entries()) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        tempForm.appendChild(input);
    }
    
    // Submit form
    document.body.appendChild(tempForm);
    tempForm.submit();
    document.body.removeChild(tempForm);
}

/**
 * Update page filter users based on selected group
 */
function updatePageFilterUsers(groupId) {
    if (!groupId) {
        $('#filter_user_id option').show();
        return;
    }
    
    $('#filter_user_id').prop('disabled', true);
    
    $('#filter_user_id option').each(function() {
        const optionGroupId = $(this).data('grup-id');
        
        if (optionGroupId == groupId || $(this).val() === '') {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
    
    $('#filter_user_id').prop('disabled', false);
    
    // Reset user selection if current user is not in selected group
    const currentUserId = $('#filter_user_id').val();
    if (currentUserId) {
        const currentUserOption = $(`#filter_user_id option[value="${currentUserId}"]`);
        const currentUserGroupId = currentUserOption.data('grup-id');
        
        if (currentUserGroupId != groupId) {
            $('#filter_user_id').val('').trigger('change');
        }
    }
}

/**
 * Update page filter group based on selected user
 */
function updatePageFilterGroup(userId) {
    if (!userId) return;
    
    const userOption = $(`#filter_user_id option[value="${userId}"]`);
    const groupId = userOption.data('grup-id');
    
    if (groupId) {
        $('#filter_grup_id').val(groupId).trigger('change');
    }
}

/**
 * Reset all filters
 */
function resetFilters() {
    // Reset form fields
    $('#groupFilterForm')[0].reset();
    
    // Show all options
    $('#filter_grup_id option, #filter_user_id option').show();
    
    // Redirect to clean URL
    const currentUrl = new URL(window.location);
    const params = new URLSearchParams();
    
    // Keep only report_type if exists
    const currentReportType = window.reportType;
    if (currentReportType && currentReportType !== 'general') {
        params.append('report_type', currentReportType);
    }
    
    currentUrl.search = params.toString();
    window.location.href = currentUrl.toString();
}

// Global functions for backward compatibility
window.changeReportType = function() {
    const selectedType = document.getElementById('report_type_selector').value;
    console.log('Selected report type:', selectedType);
};

window.applyReportType = function() {
    const selectedType = document.getElementById('report_type_selector').value;
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('report_type', selectedType);
    window.location.href = currentUrl.toString();
};

window.exportReport = exportReport;
window.showReportResult = showReportResult;
window.resetFilters = resetFilters; 