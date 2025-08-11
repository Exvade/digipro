$(document).ready(function() {
    console.log('Approval.js loaded successfully');
    
    // Initialize DataTable
    $('#approvalTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        },
        "pageLength": 25,
        "order": [[1, "desc"]]
    });

    // Approve form submission
    $('#approveForm').on('submit', function(e) {
        e.preventDefault();
        console.log('Approve form submitted');
        
        const checksheetId = $('#approve_checksheet_id').val();
        const formData = new FormData(this);
        
        console.log('Checksheet ID:', checksheetId);
        console.log('CSRF Token:', $('meta[name="csrf-token"]').attr('content'));
        
        $.ajax({
            url: `/approval/${checksheetId}/approve`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Approve success:', response);
                $('#approveModal').modal('hide');
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
            error: function(xhr, status, error) {
                console.error('Approve error:', xhr.responseText);
                console.error('Status:', status);
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menyetujui checksheet'
                });
            }
        });
    });

    // Reject form submission
    $('#rejectForm').on('submit', function(e) {
        e.preventDefault();
        console.log('Reject form submitted');
        
        const checksheetId = $('#reject_checksheet_id').val();
        const formData = new FormData(this);
        
        console.log('Checksheet ID:', checksheetId);
        
        $.ajax({
            url: `/approval/${checksheetId}/reject`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Reject success:', response);
                $('#rejectModal').modal('hide');
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
            error: function(xhr, status, error) {
                console.error('Reject error:', xhr.responseText);
                console.error('Status:', status);
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menolak checksheet'
                });
            }
        });
    });
});

// --- Approval Semua (Approve All) ---
$(document).on('click', '#approveAllBtn', function() {
    console.log('Approve all button clicked');
    Swal.fire({
        title: 'Approve Semua?',
        text: "Pastikan anda sudah mengecek semua checksheet yang ada.Semua checksheet pending akan di-approve. Lanjutkan?\n\n",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Approve Semua'
    }).then((result) => {
        if (result.isConfirmed) {
            console.log('Approve all confirmed');
            $.ajax({
                url: '/approval/approve-all',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Approve all success:', response);
                    Swal.fire('Berhasil!', response.message, 'success').then(() => {
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Approve all error:', xhr.responseText);
                    console.error('Status:', status);
                    console.error('Error:', error);
                    Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan.', 'error');
                }
            });
        }
    });
});

function viewChecksheet(id) {
    console.log('View checksheet:', id);
    $.get(`/approval/${id}`, function(response) {
        $('#checksheetDetailContent').html(response);
        $('#viewChecksheetModal').modal('show');
    }).fail(function(xhr, status, error) {
        console.error('View checksheet error:', xhr.responseText);
        Swal.fire('Error!', 'Gagal memuat detail checksheet', 'error');
    });
}

function approveChecksheet(id) {
    console.log('Approve checksheet function called with ID:', id);
    $('#approve_checksheet_id').val(id);
    $('#approval_note').val('');
    $('#approveModal').modal('show');
}

function rejectChecksheet(id) {
    console.log('Reject checksheet function called with ID:', id);
    $('#reject_checksheet_id').val(id);
    $('#rejection_reason').val('');
    $('#rejectModal').modal('show');
}

function applyFilter() {
    const grupFilter = $('#grup_filter').val();
    const currentUrl = new URL(window.location);
    
    // Update or add grup filter parameter
    if (grupFilter) {
        currentUrl.searchParams.set('grup_filter', grupFilter);
    } else {
        currentUrl.searchParams.delete('grup_filter');
    }
    
    // Preserve existing filter parameter
    const filterParam = currentUrl.searchParams.get('filter');
    if (filterParam) {
        currentUrl.searchParams.set('filter', filterParam);
    }
    
    window.location.href = currentUrl.toString();
}

function clearFilter() {
    const currentUrl = new URL(window.location);
    
    // Remove grup filter parameter
    currentUrl.searchParams.delete('grup_filter');
    
    // Preserve existing filter parameter
    const filterParam = currentUrl.searchParams.get('filter');
    if (filterParam) {
        currentUrl.searchParams.set('filter', filterParam);
    }
    
    window.location.href = currentUrl.toString();
} 