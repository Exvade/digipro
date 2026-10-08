// Wait for document ready AND SweetAlert to be loaded
$(document).ready(function () {
    
    // Check if SweetAlert is loaded
    if (typeof Swal === 'undefined') {
        console.error('SweetAlert2 is not loaded!');
        alert('Error: SweetAlert2 library is missing. Please check your script includes.');
        return;
    }

    console.log('Jadwal Kerja JS initialized successfully');

    // =========================================
    // CSRF Token Setup
    // =========================================
    $.ajaxSetup({
        headers: { 
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") 
        }
    });

    // =========================================
    // DataTable Initialization
    // =========================================
    // Initialize DataTable untuk jadwal kerja table (index page)
    if ($('#jadwalKerjaTable').length > 0 && $('#jadwalKerjaTable tbody tr td[colspan]').length === 0) {
        // Destroy existing DataTable instance if it exists
        if ($.fn.dataTable.isDataTable('#jadwalKerjaTable')) {
            $('#jadwalKerjaTable').DataTable().destroy();
        }
        
        // Initialize DataTable with clean dom (no empty controls row)
        $('#jadwalKerjaTable').DataTable({
            "dom": 'rt',
            "paging": false,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "destroy": true,
            "order": [],
            "columnDefs": [
                {
                    "targets": [0, -1], // No & Aksi
                    "orderable": false,
                    "searchable": false
                }
            ],
            "language": {
                "emptyTable": "Tidak ada jadwal kerja",
                "zeroRecords": "Tidak ada data yang sesuai dengan filter"
            }
        });
    }

    // Initialize DataTable untuk jadwal table (main page)
    if ($.fn.DataTable && $("#jadwalTable").length > 0) {
        $("#jadwalTable").DataTable({
            responsive: true,
            pageLength: 10,
            language: { 
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" 
            }
        });
    }

    // =========================================
    // Counter Textarea
    // =========================================
    function updateCounter(textareaId, counterId) {
        const max = 500;
        const $textarea = $(textareaId);
        const $counter = $(counterId);
        
        if ($textarea.length && $counter.length) {
            const length = $textarea.val().length;
            $counter.text(`${length}/${max}`);
        }
    }

    $("#keterangan").on("input", function () {
        updateCounter("#keterangan", "#charCount");
    });

    $("#update_keterangan").on("input", function () {
        updateCounter("#update_keterangan", "#updateCharCount");
    });

    // =========================================
    // Detail Modal Handler (Index Page)
    // =========================================
    $(document).on('click', '.btn-detail', function() {
        const id = $(this).data('id');
        // Get URL from data attribute or fallback
        const container = $(this).closest('[data-jadwal-kerja-show-url]');
        const urlTemplate = container.length ? container.data('jadwal-kerja-show-url') : (window.jadwalKerjaShowUrl || '/jadwal-kerja/:id');
        const url = urlTemplate.replace(':id', id);

        $('#modalDetailContent').html('<div class="text-center text-muted py-3">Memuat data...</div>');

        $.ajax({
            url: url,
            method: 'GET',
            success: function(response) {
                $('#modalDetailContent').html(response);
                $('#detailModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error('Error loading detail:', error);
                $('#modalDetailContent').html(
                    '<div class="alert alert-danger">' +
                    '<i class="fas fa-exclamation-triangle mr-2"></i>' +
                    'Gagal memuat data. Error: ' + error +
                    '</div>'
                );
            }
        });
    });

    // =========================================
    // Edit Button Handler (Using Data Attribute)
    // =========================================
    $(document).on('click', '.btn-edit', function() {
        const id = $(this).data('id');
        editJadwal(id);
    });

    // =========================================
    // Delete Button Handler (Using Data Attribute)
    // =========================================
    $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        deleteJadwal(id, nama);
    });

   // =========================================
// CREATE Jadwal Kerja (AJAX)
// =========================================

$("#createForm").on("submit", function (e) {
    e.preventDefault();
    e.stopPropagation();

    const $form = $(this);
    const $submitBtn = $form.find('button[type="submit"]');
    const originalBtnText = $submitBtn.html();

    console.debug('🟡 Form submit triggered...');

    // Disable tombol dan tampilkan spinner
    $submitBtn.prop('disabled', true)
        .html('<span class="spinner-border spinner-border-sm mr-2"></span>Menyimpan...');

    $.ajax({
        url: $form.attr("action"),
        type: "POST",
        data: $form.serialize(),
        dataType: 'json',
        success: function (response) {
            console.debug('✅ Response received:', response);

            // Re-enable tombol
            $submitBtn.prop('disabled', false).html(originalBtnText);

            if (response.success) {
                Swal.fire({
                    title: "Berhasil!",
                    text: response.message || "Jadwal berhasil ditambahkan.",
                    icon: "success",
                    timer: 2000,
                    showConfirmButton: false,
                    allowOutsideClick: false
                }).then(() => {
                    console.debug('🔄 Closing modal and reloading...');
                    $("#createModal").modal("hide");
                    $form[0].reset();
                    $("#charCount").text("0/500");

                    // Reload halaman dengan sedikit jeda agar animasi modal halus
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                });
            } else {
                Swal.fire({
                    title: "Gagal",
                    text: response.message || "Gagal menambahkan jadwal kerja.",
                    icon: "error",
                    confirmButtonText: "OK"
                });
            }
        },
        error: function (xhr) {
            console.error('❌ AJAX Error:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText
            });

            // Re-enable tombol
            $submitBtn.prop('disabled', false).html(originalBtnText);

            if (xhr.status === 422 && xhr.responseJSON?.errors) {
                // Tampilkan semua error validasi
                let errorMessages = Object.values(xhr.responseJSON.errors)
                    .flat()
                    .join("<br>");
                Swal.fire({
                    title: "Validasi Gagal",
                    html: errorMessages,
                    icon: "warning",
                    confirmButtonText: "OK"
                });
            } else if (xhr.status === 403) {
                Swal.fire({
                    title: "Akses Ditolak",
                    text: "Anda tidak memiliki izin untuk menambah jadwal kerja.",
                    icon: "error"
                });
            } else {
                Swal.fire({
                    title: "Terjadi Kesalahan",
                    text: xhr.responseJSON?.message || "Silakan coba lagi nanti.",
                    icon: "error"
                });
            }
        }
    });

    return false;
});


    // =========================================
    // UPDATE Jadwal
    // =========================================
    $("#updateForm").on("submit", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $form = $(this);
        const id = $("#update_id").val();
        const $submitBtn = $form.find('button[type="submit"]');
        const originalBtnText = $submitBtn.html();

        if (!id) {
            Swal.fire({
                title: "Error",
                text: "ID jadwal tidak ditemukan",
                icon: "error"
            });
            return false;
        }

        $submitBtn.prop('disabled', true)
                  .html('<span class="spinner-border spinner-border-sm mr-2"></span>Memperbarui...');

        $.ajax({
            url: `/jadwal-kerja/${id}`,
            type: "POST",
            data: $form.serialize() + "&_method=PUT",
            dataType: 'json',
            success: function (response) {
                console.log('Update response:', response);
                
                $submitBtn.prop('disabled', false).html(originalBtnText);

                if (response.success) {
                    Swal.fire({
                        title: "Berhasil!",
                        html: `
                            <b>${response.message}</b><br>
                            <hr>
                            <small>
                                Shift: ${response.data?.shift || '-'}<br>
                                Jam: ${response.data?.jam_masuk || '-'} - ${response.data?.jam_keluar || '-'}<br>
                                Bagian: ${response.data?.bagian || '-'}
                            </small>
                        `,
                        icon: "success",
                        timer: 2500,
                        showConfirmButton: false
                    }).then(() => {
                        $("#updateModal").modal("hide");
                        $form[0].reset();
                        $("#updateCharCount").text("0/500");
                        
                        setTimeout(() => {
                            window.location.reload();
                        }, 300);
                    });
                } else {
                    Swal.fire({
                        title: "Gagal",
                        text: response.message || "Gagal memperbarui jadwal",
                        icon: "error"
                    });
                }
            },
            error: function (xhr) {
                console.error('Update error:', xhr.responseText);
                $submitBtn.prop('disabled', false).html(originalBtnText);
                handleAjaxError(xhr);
            }
        });

        return false;
    });

    // =========================================
    // Real-time Availability Check
    // =========================================
    function checkAvailability(userId, tanggal, currentId = null, $userSelect) {
        if (!userId || !tanggal) return;

        $.ajax({
            url: "/jadwal-kerja/get-user-shift",
            method: 'GET',
            data: {
                user_id: userId,
                tanggal: tanggal,
                ignore_id: currentId
            },
            success: function(response) {
                if (response.success && response.source === 'jadwal_kerja') {
                    Swal.fire({
                        title: "Karyawan Sudah Terjadwal",
                        html: `Karyawan tersebut sudah memiliki jadwal <b>${response.shift_display}</b> pada tanggal ini.<br>Satu karyawan hanya bisa memiliki satu jadwal per hari.`,
                        icon: "warning",
                        confirmButtonText: "OK",
                        confirmButtonColor: "#3085d6"
                    }).then(() => {
                        $userSelect.val('');
                    });
                }
            }
        });
    }

    // Create Form Events
    $('#user_id, #tanggal').on('change', function() {
        const userId = $('#user_id').val();
        const tanggal = $('#tanggal').val();
        checkAvailability(userId, tanggal, null, $('#user_id'));
    });

    // Update Form Events
    $('#update_user_id, #update_tanggal').on('change', function() {
        const userId = $('#update_user_id').val();
        const tanggal = $('#update_tanggal').val();
        const currentId = $('#update_id').val();
        checkAvailability(userId, tanggal, currentId, $('#update_user_id'));
    });
});

// =========================================
// Edit Jadwal Function (Global)
// =========================================
function editJadwal(id) {
    if (typeof Swal === 'undefined') {
        alert('SweetAlert2 not loaded');
        return;
    }

    $.ajax({
        url: `/jadwal-kerja/${id}/edit`,
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                const data = response.data;

                $("#update_id").val(data.id);
                $("#update_user_id").val(data.user_id);
                $("#update_tanggal").val(data.tanggal);
                $("#update_shift").val(data.shift);
                $("#update_bagian").val(data.bagian);
                $("#update_status").val(data.status);
                
                // Konversi format waktu dari H:i:s atau H:i ke HH:mm untuk input type="time"
                function formatTimeForInput(timeString) {
                    if (!timeString) return '';
                    // Jika format H:i:s, ambil hanya H:i
                    if (timeString.includes(':')) {
                        const parts = timeString.split(':');
                        // Pastikan format HH:mm (2 digit untuk jam dan menit)
                        const hours = parts[0].padStart(2, '0');
                        const minutes = parts[1] ? parts[1].padStart(2, '0') : '00';
                        return `${hours}:${minutes}`;
                    }
                    return timeString;
                }
                
                $("#update_jam_masuk").val(formatTimeForInput(data.jam_masuk));
                $("#update_jam_keluar").val(formatTimeForInput(data.jam_keluar));
                $("#update_keterangan").val(data.keterangan || '');
                
                const keteranganLength = (data.keterangan || '').length;
                $("#updateCharCount").text(`${keteranganLength}/500`);

                $("#updateForm").attr("action", `/jadwal-kerja/${data.id}`);
                $("#updateModal").modal("show");
            } else {
                Swal.fire({
                    title: "Gagal",
                    text: response.message || "Data tidak ditemukan",
                    icon: "error"
                });
            }
        },
        error: function (xhr) {
            console.error('Edit error:', xhr.responseText);
            Swal.fire({
                title: "Error",
                text: "Terjadi kesalahan saat mengambil data",
                icon: "error"
            });
        }
    });
}

// =========================================
// Delete Jadwal Function (Global)
// =========================================
function deleteJadwal(id, nama) {
    if (typeof Swal === 'undefined') {
        if (confirm(`Yakin hapus jadwal milik ${nama}?`)) {
            performDelete(id);
        }
        return;
    }

    Swal.fire({
        title: "Yakin hapus?",
        text: `Jadwal milik ${nama} akan dihapus permanen.`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Ya, Hapus",
        cancelButtonText: "Batal"
    }).then((result) => {
        if (result.isConfirmed) {
            performDelete(id);
        }
    });
}

function performDelete(id) {
    $.ajax({
        url: `/jadwal-kerja/${id}`,
        type: "POST",
        data: { 
            _method: "DELETE",
            _token: $('meta[name="csrf-token"]').attr("content")
        },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: "Terhapus!",
                        text: response.message || "Jadwal berhasil dihapus",
                        icon: "success",
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    alert(response.message || "Jadwal berhasil dihapus");
                    window.location.reload();
                }
            } else {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: "Gagal",
                        text: response.message || "Gagal menghapus jadwal",
                        icon: "error"
                    });
                } else {
                    alert(response.message || "Gagal menghapus jadwal");
                }
            }
        },
        error: function (xhr) {
            console.error('Delete error:', xhr.responseText);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: "Error",
                    text: "Terjadi kesalahan saat menghapus data",
                    icon: "error"
                });
            } else {
                alert("Terjadi kesalahan saat menghapus data");
            }
        }
    });
}

// =========================================
// Error Handler
// =========================================
function handleAjaxError(xhr) {
    console.error('Ajax Error:', {
        status: xhr.status,
        statusText: xhr.statusText,
        responseText: xhr.responseText,
        responseJSON: xhr.responseJSON
    });

    if (typeof Swal === 'undefined') {
        alert('Error: ' + (xhr.responseJSON?.message || 'Terjadi kesalahan'));
        return;
    }

    if (xhr.status === 422) {
        const errors = xhr.responseJSON?.errors || {};
        
        if (Object.keys(errors).length > 0) {
            let errorMessages = Object.entries(errors)
                .map(([field, messages]) => {
                    const fieldName = field.replace(/_/g, ' ')
                        .replace(/\b\w/g, l => l.toUpperCase());
                    return `<b>${fieldName}:</b> ${messages.join(', ')}`;
                })
                .join('<br>');

            Swal.fire({
                title: "Validasi Gagal",
                html: errorMessages,
                icon: "warning",
                confirmButtonText: "OK"
            });
        } else {
            Swal.fire({
                title: "Validasi Gagal",
                text: xhr.responseJSON?.message || "Data tidak valid",
                icon: "warning"
            });
        }
    } else if (xhr.status === 500) {
        const errorMessage = xhr.responseJSON?.message || xhr.responseText || '';
        
        if (errorMessage.includes('Duplicate entry')) {
            Swal.fire({
                title: "Duplikat Data",
                text: "Jadwal untuk karyawan dan tanggal tersebut sudah ada!",
                icon: "error"
            });
        } else {
            Swal.fire({
                title: "Error Server",
                text: "Terjadi kesalahan pada server.",
                icon: "error"
            });
        }
    } else if (xhr.status === 419) {
        Swal.fire({
            title: "Session Expired",
            text: "Sesi Anda telah berakhir. Halaman akan di-refresh.",
            icon: "warning",
            confirmButtonText: "OK"
        });
    }
}

// =========================================
// Dark Mode Toggle Functionality
// =========================================
document.addEventListener('DOMContentLoaded', function() {
    const darkModeSwitch = document.getElementById('darkModeSwitch');
    const body = document.body;

    // Check if element exists
    if (!darkModeSwitch) {
        console.warn('Dark mode switch not found');
        return;
    }

    // Check for saved dark mode preference
    const isDarkMode = localStorage.getItem('darkMode') === 'enabled';

    // Apply saved preference on page load
    if (isDarkMode) {
        enableDarkMode();
    }

    // Toggle dark mode on switch change
    darkModeSwitch.addEventListener('change', function() {
        if (this.checked) {
            enableDarkMode();
        } else {
            disableDarkMode();
        }
    });

    /**
     * Enable dark mode
     */
    function enableDarkMode() {
        body.classList.add('dark-mode');
        darkModeSwitch.checked = true;
        localStorage.setItem('darkMode', 'enabled');
    }

    /**
     * Disable dark mode
     */
    function disableDarkMode() {
        body.classList.remove('dark-mode');
        darkModeSwitch.checked = false;
        localStorage.setItem('darkMode', 'disabled');
    }
});

