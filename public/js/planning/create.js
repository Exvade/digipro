(function ($) {
    "use strict";

    console.log("Planning Create Script Loaded");

    // Fungsi untuk toggle visibility berdasarkan bagian
    function toggleBagianFields(bagian) {
        console.log("Toggle bagian:", bagian);

        if (bagian === "inject") {
            // Tampilkan Mold & Mesin, sembunyikan Meja
            $("#mold-mesin-card").show();
            $("#meja-card").hide();
            $("#moldshop_id").prop("required", true).prop("disabled", false);
            $("#mesin_id").prop("required", true).prop("disabled", false);
            $("#meja_id")
                .prop("required", false)
                .val("")
                .prop("disabled", true);
            console.log("Inject selected - Mold & Mesin shown");
        } else if (bagian === "assy") {
            // Sembunyikan Mold & Mesin, tampilkan Meja
            $("#mold-mesin-card").hide();
            $("#meja-card").show();
            $("#moldshop_id")
                .prop("required", false)
                .val("")
                .prop("disabled", true);
            $("#mesin_id")
                .prop("required", false)
                .val("")
                .prop("disabled", true);
            $("#meja_id").prop("required", true).prop("disabled", false);
            console.log("Assembly selected - Meja shown");
        } else {
            // Reset semua - sembunyikan semua card
            $("#mold-mesin-card").hide();
            $("#meja-card").hide();
            $("#moldshop_id")
                .prop("required", false)
                .val("")
                .prop("disabled", true);
            $("#mesin_id")
                .prop("required", false)
                .val("")
                .prop("disabled", true);
            $("#meja_id")
                .prop("required", false)
                .val("")
                .prop("disabled", true);
            console.log("No bagian selected - All cards hidden");
        }
    }

    // Event handler untuk perubahan bagian - gunakan event delegation
    $(document).on("change", "#bagian", function () {
        const bagian = $(this).val();
        console.log("Bagian changed to:", bagian);
        toggleBagianFields(bagian);
    });

    // Handle form submit - pastikan field yang tidak digunakan dikirim sebagai null
    $("#formCreatePlanning").on("submit", function (e) {
        const bagian = $("#bagian").val();

        // Validasi total shift vs qty planning
        const qtyPlanning = parseInt($("#qty_planning_produksi").val()) || 0;
        const totalShift = parseInt($("#total_shift").val()) || 0;

        if (qtyPlanning > 0 && totalShift < qtyPlanning) {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Tidak Dapat Menyimpan!",
                html: `<p>Total Target Shift (<strong>${totalShift}</strong>) kurang dari Qty Planning (<strong>${qtyPlanning}</strong>).</p>
                       <p>Kekurangan: <strong>${
                           qtyPlanning - totalShift
                       }</strong></p>
                       <p class="text-muted mb-0">Silakan sesuaikan target shift agar minimal sama dengan qty planning.</p>`,
                confirmButtonText: "OK, Saya Mengerti",
            });
            return false;
        }

        // Validasi Total Hourly vs Target Shift
        const targetPagi = parseInt($("#target_shift_pagi").val()) || 0;
        const totalPagi = parseInt($("#total_pagi").text()) || 0;
        if (totalPagi > targetPagi) {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Validasi Gagal!",
                text: `Total jam Shift Pagi (${totalPagi}) melebihi Target Shift Pagi (${targetPagi}).`,
            });
            return false;
        }

        const targetSiang = parseInt($("#target_shift_siang").val()) || 0;
        const totalSiang = parseInt($("#total_siang").text()) || 0;
        if (totalSiang > targetSiang) {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Validasi Gagal!",
                text: `Total jam Shift Siang (${totalSiang}) melebihi Target Shift Siang (${targetSiang}).`,
            });
            return false;
        }

        const targetMalam = parseInt($("#target_shift_malam").val()) || 0;
        const totalMalam = parseInt($("#total_malam").text()) || 0;
        if (totalMalam > targetMalam) {
            e.preventDefault();
            Swal.fire({
                icon: "error",
                title: "Validasi Gagal!",
                text: `Total jam Shift Malam (${totalMalam}) melebihi Target Shift Malam (${targetMalam}).`,
            });
            return false;
        }

        // Enable semua field dulu agar bisa dikirim
        $("#moldshop_id").prop("disabled", false);
        $("#mesin_id").prop("disabled", false);
        $("#meja_id").prop("disabled", false);

        if (bagian === "inject") {
            // Inject: mold & mesin terisi, meja kosong
            // Jika meja kosong, pastikan dikirim sebagai empty string
            if (!$("#meja_id").val()) {
                $("#meja_id").val("");
            }
        } else if (bagian === "assy") {
            // Assembly: meja terisi, mold & mesin kosong
            // Jika mold atau mesin kosong, pastikan dikirim sebagai empty string
            if (!$("#moldshop_id").val()) {
                $("#moldshop_id").val("");
            }
            if (!$("#mesin_id").val()) {
                $("#mesin_id").val("");
            }
        }

        console.log("Form submitted with bagian:", bagian);
        console.log("moldshop_id:", $("#moldshop_id").val() || "empty");
        console.log("mesin_id:", $("#mesin_id").val() || "empty");
        console.log("meja_id:", $("#meja_id").val() || "empty");

        // Disable submit button to prevent double submission
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn
            .prop("disabled", true)
            .html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
    });

    // Reset saat modal dibuka
    $("#modalCreate").on("show.bs.modal", function () {
        console.log("Modal Create opened");
        // Reset form
        $("#bagian").val("");
        // Sembunyikan semua card
        $("#mold-mesin-card").hide();
        $("#meja-card").hide();
        // Reset semua field
        $("#moldshop_id")
            .prop("required", false)
            .val("")
            .prop("disabled", true);
        $("#mesin_id").prop("required", false).val("").prop("disabled", true);
        $("#meja_id").prop("required", false).val("").prop("disabled", true);
    });

    // Juga trigger saat modal sudah fully shown
    $("#modalCreate").on("shown.bs.modal", function () {
        console.log("Modal Create fully shown");
        // Pastikan card tersembunyi saat modal pertama kali dibuka
        $("#mold-mesin-card").hide();
        $("#meja-card").hide();
    });

    // Auto-calculate total_shift
    function calculateTotalShift() {
        const pagi = parseInt($("#target_shift_pagi").val()) || 0;
        const siang = parseInt($("#target_shift_siang").val()) || 0;
        const malam = parseInt($("#target_shift_malam").val()) || 0;
        const total = pagi + siang + malam;
        $("#total_shift").val(total);
        console.log("Total shift calculated:", total);

        // Validasi dengan qty_planning_produksi
        validateTotalShift();
    }

    // Validasi total shift vs qty planning
    function validateTotalShift() {
        const qtyPlanning = parseInt($("#qty_planning_produksi").val()) || 0;
        const totalShift = parseInt($("#total_shift").val()) || 0;

        // Hapus alert sebelumnya jika ada
        $(".alert-target-shift").remove();

        if (qtyPlanning > 0 && totalShift > 0 && totalShift < qtyPlanning) {
            const selisih = qtyPlanning - totalShift;
            const alertHtml = `
                <div class="alert alert-warning alert-dismissible fade show alert-target-shift mt-2" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Peringatan!</strong> Total Target Shift (${totalShift}) kurang dari Qty Planning (${qtyPlanning}). 
                    Kekurangan: <strong>${selisih}</strong>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
            $("#total_shift").closest(".form-group").append(alertHtml);
        } else if (qtyPlanning > 0 && totalShift > qtyPlanning) {
            const kelebihan = totalShift - qtyPlanning;
            const alertHtml = `
                <div class="alert alert-info alert-dismissible fade show alert-target-shift mt-2" role="alert">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Info:</strong> Total Target Shift (${totalShift}) melebihi Qty Planning (${qtyPlanning}). 
                    Kelebihan: <strong>${kelebihan}</strong>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            `;
            $("#total_shift").closest(".form-group").append(alertHtml);
        }
    }

    // Trigger calculation saat input berubah
    $(
        "#target_shift_pagi, #target_shift_siang, #target_shift_malam, #qty_planning_produksi"
    ).on("input change", function () {
        calculateTotalShift();
        calculateHourlyTotals(); // Recalculate validation when targets change
    });

    // Calculate hourly totals
    function calculateHourlyTotals() {
        // Shift Pagi
        let totalPagi = 0;
        $('input[name^="pagi_jam_"]').each(function () {
            totalPagi += parseInt($(this).val()) || 0;
        });
        $("#total_pagi").text(totalPagi);

        // Shift Siang
        let totalSiang = 0;
        $('input[name^="siang_jam_"]').each(function () {
            totalSiang += parseInt($(this).val()) || 0;
        });
        $("#total_siang").text(totalSiang);

        // Shift Malam
        let totalMalam = 0;
        $('input[name^="malam_jam_"]').each(function () {
            totalMalam += parseInt($(this).val()) || 0;
        });
        $("#total_malam").text(totalMalam);

        // Visual feedback
        validateShiftTotal("pagi", totalPagi);
        validateShiftTotal("siang", totalSiang);
        validateShiftTotal("malam", totalMalam);
    }

    function validateShiftTotal(shift, total) {
        const target = parseInt($(`#target_shift_${shift}`).val()) || 0;
        const totalElem = $(`#total_${shift}`);

        if (total > target) {
            totalElem.addClass("text-danger").removeClass("text-success");
            totalElem.closest("tr").addClass("table-danger");
        } else if (total === target && target > 0) {
            totalElem.addClass("text-success").removeClass("text-danger");
            totalElem.closest("tr").removeClass("table-danger");
        } else {
            totalElem.removeClass("text-danger text-success");
            totalElem.closest("tr").removeClass("table-danger");
        }
    }

    // Listen for hourly input changes
    $(
        'input[name^="pagi_jam_"], input[name^="siang_jam_"], input[name^="malam_jam_"]'
    ).on("input", function () {
        calculateHourlyTotals();
    });
})(jQuery);
