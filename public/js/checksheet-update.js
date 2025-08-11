/* Checksheet Update Page JavaScript */

$(document).ready(function() {
    // File preview functionality
    function previewImage(input, previewId, previewDivId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $(previewId).attr('src', e.target.result);
                $(previewDivId).show();
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // APD photo preview
    $('#foto_apd').change(function() {
        previewImage(this, '#apd_preview', '#preview_apd');
    });

    // Instruksi Kerja photo preview
    $('#foto_instruksi_kerja').change(function() {
        previewImage(this, '#instruksi_kerja_preview', '#preview_instruksi_kerja');
    });

    // Q-Point photo preview
    $('#foto_qpoint').change(function() {
        previewImage(this, '#qpoint_preview', '#preview_qpoint');
    });

    // Limit Sampel photo preview
    $('#foto_limit_sampel').change(function() {
        previewImage(this, '#limit_sampel_preview', '#preview_limit_sampel');
    });

    // AMTPM photo preview
    $('#foto_amptm').change(function() {
        previewImage(this, '#amptm_preview', '#preview_amptm');
    });

    // Form submission
    $('#updateChecksheetForm').on('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...');
        
        const checksheetId = $('#checksheet_id').val();
        const formData = new FormData(this);
        
        // Validate AMTPM standard
        if (!$('input[name="amptm_standar"]:checked').val()) {
            Swal.fire({
                icon: 'error',
                title: 'Validasi Error!',
                text: 'Pertanyaan Standar AMTPM harus dijawab'
            });
            return;
        }
        
        $.ajax({
            url: `/checksheet/${checksheetId}`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#editChecksheetModal').modal('hide');
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
                let errorMessage = 'Terjadi kesalahan saat memperbarui checksheet';
                
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
}); 