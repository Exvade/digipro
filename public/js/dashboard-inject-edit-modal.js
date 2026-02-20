/**
 * Dashboard Inject - Edit Mesin Modal Handler
 * Handles the edit machine modal functionality for dashboard inject
 */

/**
 * Function to open edit mesin modal
 * @param {number} mesinId - ID of the machine to edit
 */
function openEditMesinModal(mesinId) {
    // Show the modal
    $("#editMesinModal").modal("show");

    // Load the edit form via AJAX
    $.ajax({
        url: "/documents/mesin/" + mesinId + "/edit",
        method: "GET",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        beforeSend: function () {
            $("#editMesinModalBody").html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Memuat data mesin...</p>
                </div>
            `);
        },
        success: function (response) {
            $("#editMesinModalBody").html(response);
        },
        error: function (xhr) {
            let errorMessage = "Terjadi kesalahan saat memuat data mesin.";

            if (xhr.status === 404) {
                errorMessage = "Data mesin tidak ditemukan.";
            } else if (xhr.status === 401) {
                errorMessage = "Anda harus login terlebih dahulu.";
            } else if (xhr.status === 403) {
                errorMessage =
                    "Anda tidak memiliki akses untuk mengedit mesin ini.";
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }

            $("#editMesinModalBody").html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    ${errorMessage}
                </div>
                <div class="text-right">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-2"></i>Tutup
                    </button>
                </div>
            `);
        },
    });
}

// Document ready handler
$(document).ready(function () {
    // Event delegation for edit machine button
    $(document).on('click', '.btn-edit-machine', function(e) {
        e.stopPropagation(); // Prevent triggering card click event
        
        const mesinId = $(this).data('mesin-id');
        if (mesinId) {
            openEditMesinModal(mesinId);
        }
    });

    // Handle modal close event to reset content
    $("#editMesinModal").on("hidden.bs.modal", function () {
        $("#editMesinModalBody").html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Memuat data mesin...</p>
            </div>
        `);
    });
});
