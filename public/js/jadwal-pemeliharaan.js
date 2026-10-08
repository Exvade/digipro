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

    // 2. Initialize KPI Counters Animation & Interactive Table Filtering
    initKpiCounters();
    initKpiFilter();

    function initKpiCounters() {
        $('.kpi-counter').each(function () {
            const $this = $(this);
            const target = parseInt($this.data('target'), 10) || 0;
            if (target === 0) {
                $this.text('0');
                return;
            }

            const duration = 800;
            const start = 0;
            const startTime = performance.now();

            function updateCounter(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                // easeOutQuad easing function
                const ease = 1 - (1 - progress) * (1 - progress);
                const currentVal = Math.floor(start + (target - start) * ease);

                $this.text(currentVal);

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    $this.text(target);
                }
            }

            requestAnimationFrame(updateCounter);
        });
    }

    function initKpiFilter() {
        $('.kpi-card').on('click', function () {
            const $card = $(this);
            const filterType = $card.data('filter');

            // Toggle active card styling
            $('.kpi-card').removeClass('active border-indigo-500 ring-2 ring-indigo-500/20');
            $('.kpi-card .kpi-active-indicator').addClass('hidden');

            $card.addClass('active border-indigo-500 ring-2 ring-indigo-500/20');
            $card.find('.kpi-active-indicator').removeClass('hidden');

            applyKpiFilter(filterType);
        });

        $('#kpiFilterResetBtn').on('click', function () {
            $('.kpi-card[data-filter="all"]').trigger('click');
        });
    }

    function applyKpiFilter(type) {
        const $rows = $('tr.machine-row');
        const uniqueMachines = new Set();

        $rows.each(function () {
            const $row = $(this);
            const machineId = $row.data('machine-id');
            const hasPlan = $row.data('has-plan') === 1 || $row.data('has-plan') === '1';
            const hasApproved = $row.data('has-approved') === 1 || $row.data('has-approved') === '1';
            const hasActual = $row.data('has-actual') === 1 || $row.data('has-actual') === '1';

            let isVisible = false;
            if (type === 'all') {
                isVisible = true;
            } else if (type === 'plan') {
                isVisible = hasPlan;
            } else if (type === 'approved') {
                isVisible = hasApproved;
            } else if (type === 'actual') {
                isVisible = hasActual;
            }

            if (isVisible) {
                $row.show();
                uniqueMachines.add(machineId);
            } else {
                $row.hide();
            }
        });

        const matchCount = uniqueMachines.size;

        // Update notification banner
        if (type === 'all') {
            $('#kpiFilterBadge').addClass('hidden');
        } else {
            const labels = {
                plan: 'Target Plan (Memiliki Jadwal)',
                approved: 'Realisasi Approved (Disetujui)',
                actual: 'Input Aktual (Memiliki Realisasi)'
            };
            $('#kpiFilterLabel').text(labels[type] || type);
            $('#kpiFilterCount').text(matchCount);
            $('#kpiFilterBadge').removeClass('hidden');
        }
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
                    location.reload();
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
