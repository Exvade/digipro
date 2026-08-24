$(document).ready(function () {
    console.log("Jadwal Pemeliharaan JS (Advanced) Loaded");

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    // 1. Cell Click Handler
    $(document).on("click", "td.edit-trigger", function (e) {
        if (window.userRole === "operator" && window.userDept === "Produksi") return;

        e.stopPropagation();
        const $el = $(this);
        
        const data = {
            id: $el.data("id"),
            mesinId: $el.data("mesin-id"),
            mesinNama: $el.data("mesin-nama"),
            bulan: $el.data("bulan"),
            minggu: $el.data("minggu"),
            typeClick: $el.data("type"),
            currentPlan: $el.data("plan"),
            currentActual: $el.data("actual"),
            keterangan: $el.data("keterangan"),
            bukti: $el.data("bukti"),
            status: $el.data("status"),
            approvedBy: $el.data("approved-by"),
            approvedAt: $el.data("approved-at")
        };

        openEditModal(data);
    });

    function openEditModal(data) {
        const $modal = $("#modalEditJadwal");
        
        $("#labelMesin").text(data.mesinNama);
        $("#labelBulan").text(data.bulan);
        $("#labelMinggu").text(data.minggu);
        $("#inputMesinId").val(data.mesinId);
        $("#inputBulan").val(data.bulan);
        $("#inputMinggu").val(data.minggu);
        $("#inputJadwalId").val(data.id || "");
        $("#inputKeterangan").val(data.keterangan || "");
        
        $("#inputPlan").val(data.currentPlan || "");
        $("#inputActual").val(data.currentActual || "");

        // Bukti Preview
        if (data.bukti) {
            $("#previewBukti, #previewBuktiReadOnly").show();
            $("#linkBukti, #linkBuktiReadOnly").attr("href", "/storage/maintenance_bukti/" + data.bukti);
        } else {
            $("#previewBukti, #previewBuktiReadOnly").hide();
        }

        // Approval Section Logic
        if (data.id && data.currentActual) {
            $("#approvalSection").show();
            const $statusLabel = $("#statusLabel");
            $statusLabel.text(data.status ? data.status.toUpperCase() : "PENDING");
            
            // Color mapping
            $statusLabel.removeClass("bg-warning bg-success bg-danger");
            if (data.status === "approved") $statusLabel.addClass("bg-success");
            else if (data.status === "rejected") $statusLabel.addClass("bg-danger");
            else $statusLabel.addClass("bg-warning");

            if (data.status === 'pending') {
                $("#approvalButtons").show();
                $("#approvedBySection").hide();
            } else if (data.status === 'approved') {
                $("#approvalButtons").hide();
                $("#approvedBySection").show();
                $("#labelApprovedBy").text(data.approvedBy);
                $("#labelApprovedAt").text(data.approvedAt);
            } else {
                $("#approvalButtons").show();
                $("#approvedBySection").hide();
            }
        } else {
            $("#approvalSection").hide();
        }

        // Visibility Logic
        if (data.typeClick === "plan") {
            $("#sectionPlan").show();
            $("#sectionActual").hide();
        } else {
            $("#sectionPlan").hide();
            $("#sectionActual").show();
        }

        $modal.modal("show");
    }

    window.saveJadwal = function () {
        const $form = $("#formUpdateJadwal");
        const formData = new FormData($form[0]);
        
        $("#btnSaveJadwal").prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i>...');

        $.ajax({
            url: window.urlUpdateJadwal,
            method: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.status === "success") {
                    location.reload(); // Reload for simplicity with many changes
                } else {
                    alert(res.message);
                }
            },
            complete: function() {
                $("#btnSaveJadwal").prop("disabled", false).html('<i class="fas fa-save"></i> Simpan');
            }
        });
    };

    window.approveJadwal = function() {
        const id = $("#inputJadwalId").val();
        if(!id) return;
        $.post(`${window.urlApprove}/${id}`, function(res) {
            if(res.status === 'success') location.reload();
            else alert(res.message);
        });
    };

    window.rejectJadwal = function() {
        const id = $("#inputJadwalId").val();
        if(!id) return;
        $.post(`${window.urlReject}/${id}`, function(res) {
            if(res.status === 'success') location.reload();
            else alert(res.message);
        });
    };
});
