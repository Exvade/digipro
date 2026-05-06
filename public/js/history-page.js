// Data Initialization from Blade
(function() {
    var dataEl = document.getElementById('history-log-data');
    if (dataEl) {
        var encodedData = dataEl.getAttribute('data-logs');
        try {
            window.historyLogData = JSON.parse(atob(encodedData));
        } catch (e) {
            window.historyLogData = [];
            console.error('Failed to parse history log data:', e);
        }
    } else {
        window.historyLogData = [];
    }
})();

$(document).ready(function () {
    var historyTable = $("#historyTable").DataTable({
        ordering: true,
        order: [[4, 'desc']], // Default sort by Waktu Mulai descending
        // Sorting enabled for all columns as requested
        columnDefs: [],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya",
            },
            zeroRecords: "Tidak ada data yang ditemukan",
            emptyTable: "Tidak ada data tersedia",
        },
    });

    // Function to get category from raw downtime type
    function getCategory(type) {
        if (!type) return "standby";
        type = type.toString().toLowerCase();
        if (type === "aktif" || type === "running") return "aktif";
        if (
            type === "maintenance" ||
            type === "breakdown" ||
            type === "downtime"
        )
            return "maintenance";
        if (type === "offline" || type === "tidak aktif") return "offline";
        return "standby";
    }

    // Calculate clock skew between server and client
    var serverTimeStr = $("#server-time-ref").data("value");
    var serverTime = serverTimeStr
        ? new Date(serverTimeStr).getTime()
        : new Date().getTime();
    var clientTime = new Date().getTime();
    var clockSkew = serverTime - clientTime;

    // Function to update durations
    function updateDurations() {
        var machineOngoingCategorySeconds = {}; // machineId -> category -> seconds
        var globalOngoingCategorySeconds = {
            aktif: 0,
            maintenance: 0,
            offline: 0,
            standby: 0,
        };

        $(".duration-counter").each(function () {
            var startTime = $(this).data("start");
            var machineId = $(this).data("machine-id");
            var type = $(this).data("type");

            var start = new Date(startTime).getTime();
            var now = new Date().getTime() + clockSkew; // Sync with server time

            var distance = now - start;
            if (distance < 0) distance = 0;

            // Calculate diff in seconds for summary updates
            var diffSeconds = Math.floor(distance / 1000);
            var category = getCategory(type);

            if (machineId) {
                if (!machineOngoingCategorySeconds[machineId]) {
                    machineOngoingCategorySeconds[machineId] = {
                        aktif: 0,
                        maintenance: 0,
                        offline: 0,
                        standby: 0,
                    };
                }
                machineOngoingCategorySeconds[machineId][category] +=
                    diffSeconds;
                globalOngoingCategorySeconds[category] += diffSeconds;
            }
        });

        // Update Log Table Counters independently
        $(".log-duration-counter").each(function () {
            var startTime = $(this).data("start");
            var start = new Date(startTime).getTime();
            var now = new Date().getTime() + clockSkew;
            var distance = Math.max(0, now - start);
            var ds = Math.floor(distance / 1000);
            $(this).text(formatDuration(ds) + " (Berjalan)");
        });

        // Update Global Summary Cards
        $(".summary-total").each(function () {
            var type = $(this).data("type"); // This is the target category (aktif, maintenance, etc)
            var baseSeconds = parseInt($(this).data("base")) || 0;
            var ongoingSeconds = globalOngoingCategorySeconds[type] || 0;

            var totalSeconds = baseSeconds + ongoingSeconds;
            $(this).text(formatDuration(totalSeconds));
        });

        // Update Per Machine Grid Cards
        $(".machine-total").each(function () {
            var machineId = $(this).data("machine-id");
            var type = $(this).data("type"); // This is the target category
            var baseSeconds = parseInt($(this).data("base")) || 0;
            var ongoingSeconds = 0;

            if (machineOngoingCategorySeconds[machineId]) {
                ongoingSeconds =
                    machineOngoingCategorySeconds[machineId][type] || 0;
            }

            var totalSeconds = baseSeconds + ongoingSeconds;
            $(this).text(formatDuration(totalSeconds));

            // Also update modal if open and matching
            if (
                $("#machineDetailModal").is(":visible") &&
                $("#machineDetailModal").data("machine-id") == machineId
            ) {
                var modalType =
                    type === "aktif"
                        ? "Runtime"
                        : type === "maintenance"
                          ? "Maintenance"
                          : type === "standby"
                            ? "Standby"
                            : "Offline";
                $("#modal" + modalType).text(formatDurationShort(totalSeconds));
            }
        });
    }

    function formatDuration(totalSeconds) {
        var h = Math.floor(totalSeconds / 3600);
        var m = Math.floor((totalSeconds % 3600) / 60);
        var s = totalSeconds % 60;

        var result = "";
        if (h > 0) result += h + "j ";
        if (m > 0 || h > 0) result += m + "m ";
        result += s + "d";

        return result.trim();
    }

    function formatDurationShort(totalSeconds) {
        var h = Math.floor(totalSeconds / 3600);
        var m = Math.floor((totalSeconds % 3600) / 60);
        return h + "j " + m + "m";
    }

    // --- Date Formatting Helper ---
    function formatDate(date) {
        let d = new Date(date),
            m = "" + (d.getMonth() + 1),
            day = "" + d.getDate(),
            y = d.getFullYear();
        if (m.length < 2) m = "0" + m;
        if (day.length < 2) day = "0" + day;
        return [y, m, day].join("-");
    }

    // --- Quick Date Filter Helper ---
    window.setQuickDate = function (type, fromModal = false) {
        const form =
            document.getElementById("historyFilterForm") ||
            document.querySelector('form[action*="history"]');
        if (!form) return;

        const startInput = form.querySelector('input[name="start_date"]');
        const endInput = form.querySelector('input[name="end_date"]');
        const mesinInput = form.querySelector('input[name="mesin_id"]');

        if (fromModal) {
            const activeMesinId = document.getElementById(
                "modal-active-mesin-id",
            ).value;
            if (activeMesinId && mesinInput) {
                mesinInput.value = activeMesinId;
            }
        }

        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth();

        switch (type) {
            case "today":
                startInput.value = formatDate(now);
                endInput.value = formatDate(now);
                break;
            case "this_week":
                const curr = new Date();
                const dayOfWeek = curr.getDay() || 7;
                const first = curr.getDate() - dayOfWeek + 1;
                const monday = new Date(curr);
                monday.setDate(first);
                startInput.value = formatDate(monday);
                endInput.value = formatDate(now);
                break;
            case "this_month":
                startInput.value = formatDate(new Date(year, month, 1));
                endInput.value = formatDate(now);
                break;
        }

        if (fromModal) {
            const mesinId = document.getElementById(
                "modal-active-mesin-id",
            ).value;
            const baseUrl = form.action;
            const sDate = startInput.value;
            const eDate = endInput.value;
            window.location.href = `${baseUrl}?start_date=${sDate}&end_date=${eDate}&mesin_id=${mesinId}`;
        } else {
            if (startInput.form) startInput.form.submit();
        }
    };

    // --- Machine Card Click Handler ---
    document.body.addEventListener("click", function (e) {
        const card = e.target.closest(
            ".machine-card-trigger, .machine-card-compact",
        );
        if (card) {
            const machineId = card.getAttribute("data-machine-id");
            const machineName = card.getAttribute("data-machine-name");

            const inputMesin = document.getElementById("modal-active-mesin-id");
            if (inputMesin) inputMesin.value = machineId;

            if (typeof Swal === "undefined") {
                alert("Error: SweetAlert2 library not loaded.");
                return;
            }

            Swal.fire({
                title: `Detail Mesin: ${machineName}`,
                html: `
                    <p class="mb-3">Lihat log aktivitas untuk periode kapan?</p>
                    <div class="d-flex justify-content-center flex-wrap gap-2 mb-3">
                        <button class="btn btn-success btn-sm m-1" onclick="setQuickDate('today', true)">
                            <i class="fas fa-calendar-day mr-1"></i> Hari Ini
                        </button>
                        <button class="btn btn-secondary btn-sm m-1" onclick="setQuickDate('this_week', true)">
                            <i class="fas fa-calendar-week mr-1"></i> Minggu Ini
                        </button>
                        <button class="btn btn-primary btn-sm m-1" onclick="setQuickDate('this_month', true)">
                            <i class="fas fa-calendar-alt mr-1"></i> Bulan Ini
                        </button>
                    </div>
                    <div class="text-center">
                        <button class="btn btn-outline-secondary btn-sm px-4" onclick="Swal.close()">
                            Batal
                        </button>
                    </div>
                `,
                icon: "info",
                showConfirmButton: false,
                showCancelButton: false,
                footer: '', // Clear footer
                customClass: {
                    popup: "rounded-xl shadow-lg border-0",
                    title: "text-primary font-weight-bold",
                    htmlContainer: "m-0 p-0", // Remove default padding to control layout
                    actions: "d-none" // Ensure actions area is hidden
                },
            });
        }
    });

    // Function to actually open the modal (called by the page when it loads with filtered data)
    window.showMachineModal = function (machineId, machineName, color) {
        $("#machineDetailModal").data("machine-id", machineId);
        $("#modal-active-mesin-id").val(machineId);
        $("#modalMachineName").text(machineName);
        $("#machineDetailModal .modal-header")
            .removeClass("bg-info bg-success bg-danger bg-warning bg-dark")
            .addClass("bg-" + color);

        // Filter logs specific to this machine
        var logs = window.historyLogData
            ? window.historyLogData.filter(function (log) {
                  return log.mesin_id == machineId;
              })
            : [];

        var tbody = $("#modalLogTable tbody");
        tbody.empty();

        // Reset summary counters for the modal
        let modalSummary = {
            running: 0, // aktif/running
            breakdown: 0, // maintenance
            standby: 0, // standby
            offline: 0, // offline
        };

        if (logs.length > 0) {
            logs.forEach(function (log) {
                var durationText = "";
                var statusColor = "secondary";
                var statusLabel = log.type.toUpperCase();

                // Helper vars for summation
                let durationSeconds = 0;

                // Determine category for summary
                let category = "standby";
                // Get specific reason if available
                let specificReason = (log.reason && log.reason !== '-' && log.reason.trim() !== '') ? log.reason.toUpperCase() : null;
                // Sometimes 'keterangan' might be used instead of reason in backend
                if (!specificReason && log.keterangan && log.keterangan !== '-' && log.keterangan.trim() !== '') {
                    specificReason = log.keterangan.toUpperCase();
                }

                if (log.type === "aktif" || log.type === "running") {
                    statusColor = "success";
                    statusLabel = "RUNNING";
                    category = "running";
                } else if (
                    log.type === "maintenance" ||
                    log.type === "breakdown"
                ) {
                    statusColor = "danger";
                    statusLabel = specificReason || "BREAKDOWN";
                    category = "breakdown";
                } else if (
                    log.type === "offline" ||
                    log.type === "tidak aktif"
                ) {
                    statusColor = "dark";
                    statusLabel = specificReason || "OFF";
                    category = "offline";
                } else {
                    statusColor = "warning";
                    // Use reason if available, otherwise fallback to type (e.g. SET DOWN MOLD)
                    statusLabel = specificReason || log.type.toUpperCase();
                    category = "standby";
                }

                if (log.is_ongoing) {
                    var start = new Date(log.start_raw).getTime();

                    // Determine calculation end time based on filter
                    // If filter end_date is in the past relative to today, clamp calculation to end of that day
                    // enhancing "Yesterday" / "Last Month" accuracy
                    var now =
                        new Date().getTime() +
                        (typeof clockSkew !== "undefined" ? clockSkew : 0);
                    var filterEndDateVal = $('input[name="end_date"]').val();
                    var isPastFilter = false;

                    if (filterEndDateVal) {
                        var filterEnd = new Date(filterEndDateVal);
                        var todayDate = new Date();
                        todayDate.setHours(0, 0, 0, 0);

                        // Compare just dates
                        if (filterEnd < todayDate) {
                            // Filter is strictly in the past (e.g. yesterday)
                            // Set 'now' to 23:59:59 of filterEnd
                            var endOfDay = new Date(filterEnd);
                            endOfDay.setHours(23, 59, 59, 999);
                            now = endOfDay.getTime();
                            isPastFilter = true;
                        }
                    }

                    // Also effectively clamp start if start < filterStartDate?
                    // Usually backend handles providing logs within range.
                    // But if ongoing starts before range, we might want to clamp start?
                    // User complained about "Running 45h" when filtering today.
                    // Let's stick to clamping END time for now as that fixes "ongoing into future relative to filter".

                    var diff = Math.max(0, now - start);
                    durationSeconds = Math.floor(diff / 1000);

                    var suffix = isPastFilter ? "" : " (Berjalan)";
                    var initialDuration =
                        formatDuration(durationSeconds) + suffix;

                    // We add a data attribute to keep it updating via the main interval loop
                    // But if isPastFilter is true, it shouldn't really update, but we keep structure
                    durationText = `<span class="duration-counter-modal" data-start="${log.start_raw}" data-past="${isPastFilter}">${initialDuration}</span>`;
                } else {
                    durationSeconds = log.duration_seconds;
                    durationText = formatDuration(durationSeconds);
                }

                // Add to summary
                modalSummary[category] += durationSeconds;

                // Remove "(Lant.)" or similar text from start time display
                // Assuming log.start might contain HTML or text like "00:00:00 (Lant.)"
                // detailed in controller usually builds strings.
                // We'll strip specific patterns if they exist in valid HTML/Text
                let cleanStart = log.start
                    .replace(/\s*\(.*?\)/g, "")
                    .replace(/<[^>]*>/g, "");
                // If it was HTML with <br>, we might want to keep time only.
                // But simplified: just take the time part usually "HH:mm:ss"
                // Adjust regex to be safer if needed, or simply trust the simpler display.
                // For now, removing anything in parentheses is what requested.

                // Re-clean purely for the specific request "(Lant.)" removal
                let displayStart = log.start
                    .replace("(Lant.)", "")
                    .replace("(Lanjutan Kemarin)", "");

                // Determine user display
                // Request: "usernya itu ditampilkan oleh siapa yang sedang membuka modal tersebut"
                // Try reading from hidden inputs first (more robust against linter issues), then window vars, then log data
                let domUser = document.getElementById("current-user-name")
                    ? document.getElementById("current-user-name").value
                    : "";
                let domRole = document.getElementById("current-user-role")
                    ? document.getElementById("current-user-role").value
                    : "";

                let displayUser =
                    domUser ||
                    (typeof window.currentUser !== "undefined" &&
                    window.currentUser
                        ? window.currentUser
                        : log.user || "System");
                let displayRole =
                    domRole ||
                    (typeof window.currentRole !== "undefined" &&
                    window.currentRole
                        ? window.currentRole
                        : log.role || "-");

                tbody.append(`<tr>
                    <td>${displayStart}</td>
                    <td>${log.end || (isPastFilter ? "23:59:59" : "-")}</td>
                    <td><span class="badge badge-${statusColor}">${statusLabel}</span></td>
                    <td>
                        <div class="d-flex flex-column" style="line-height:1.2">
                            <span class="font-weight-bold" style="font-size:0.85rem">${displayUser}</span>
                            <small class="text-muted" style="font-size:0.7rem">${displayRole}</small>
                        </div>
                    </td>
                    <td class="font-weight-bold text-${statusColor}">${durationText}</td>
                </tr>`);
            });
        } else {
            tbody.append(
                '<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada log untuk periode ini</td></tr>',
            );
        }

        // Update the 4 summary cards in modal directly from recalculated data
        $("#modalRuntime").text(formatDurationShort(modalSummary["running"]));
        $("#modalMaintenance").text(
            formatDurationShort(modalSummary["breakdown"]),
        );
        $("#modalStandby").text(formatDurationShort(modalSummary["standby"]));
        $("#modalOffline").text(formatDurationShort(modalSummary["offline"]));

        $("#machineDetailModal").modal("show");
    };

    // Helper to update modal counters in real-time
    function updateModalDurations() {
        if (!$("#machineDetailModal").is(":visible")) return;

        // Determine calculation parameters based on filter
        var now =
            new Date().getTime() +
            (typeof clockSkew !== "undefined" ? clockSkew : 0);
        var filterEndDateVal = $('input[name="end_date"]').val();
        var isPastFilter = false;

        if (filterEndDateVal) {
            var filterEnd = new Date(filterEndDateVal);
            var todayDate = new Date();
            todayDate.setHours(0, 0, 0, 0);

            if (filterEnd < todayDate) {
                var endOfDay = new Date(filterEnd);
                endOfDay.setHours(23, 59, 59, 999);
                now = endOfDay.getTime();
                isPastFilter = true;
            }
        }

        // Recalculate summary live
        let liveSummary = {
            running: 0,
            breakdown: 0,
            standby: 0,
            offline: 0,
        };

        var machineId = $("#modal-active-mesin-id").val();
        var logs = window.historyLogData
            ? window.historyLogData.filter((l) => l.mesin_id == machineId)
            : [];

        logs.forEach(function (log) {
            let durationSeconds = 0;
            let category = "standby";
            if (log.type === "aktif" || log.type === "running")
                category = "running";
            else if (log.type === "maintenance" || log.type === "breakdown")
                category = "breakdown";
            else if (log.type === "offline" || log.type === "tidak aktif")
                category = "offline";

            if (log.is_ongoing) {
                var start = new Date(log.start_raw).getTime();
                // Use the clamped 'now'
                var diff = Math.max(0, now - start);
                durationSeconds = Math.floor(diff / 1000);
            } else {
                durationSeconds = log.duration_seconds;
            }
            liveSummary[category] += durationSeconds;
        });

        $("#modalRuntime").text(formatDurationShort(liveSummary["running"]));
        $("#modalMaintenance").text(
            formatDurationShort(liveSummary["breakdown"]),
        );
        $("#modalStandby").text(formatDurationShort(liveSummary["standby"]));
        $("#modalOffline").text(formatDurationShort(liveSummary["offline"]));

        // Update individual row text
        $(".duration-counter-modal").each(function () {
            var start = new Date($(this).data("start")).getTime();
            // Check if this row is marked as past frozen
            // Although we recalculate 'now' above, we can just use it.

            var diff = Math.max(0, now - start);
            var suffix = isPastFilter ? "" : " (Berjalan)";
            $(this).text(formatDuration(Math.floor(diff / 1000)) + suffix);
        });
    }

    // Add to global update loop
    setInterval(updateModalDurations, 1000);

    // Modal Filter Button
    $("#btnFilterTable").on("click", function () {
        var machineName = $("#modalMachineName").text();
        historyTable.search(machineName).draw();
        $("#machineDetailModal").modal("hide");
        $("html, body").animate(
            {
                scrollTop: $("#historyTable").offset().top - 100,
            },
            500,
        );
    });

    // Update every 1000ms (1 second)
    setInterval(updateDurations, 1000);
    updateDurations();

    // Auto refresh page every 5 minutes
    setInterval(function () {
        if (!$(".modal").is(":visible")) {
            window.location.reload();
        }
    }, 300000);

    // If page was loaded with a specific machine search, show that machine's modal automatically
    const urlParams = new URLSearchParams(window.location.search);
    const filteredMesinId = urlParams.get("mesin_id");

    if (filteredMesinId) {
        // Wait slightly to ensure DOM is ready and JS event listeners are bound
        setTimeout(function () {
            // Find machine data from grid
            // Try vanilla JS selector first
            const card = document.querySelector(
                `.machine-card-compact[data-machine-id="${filteredMesinId}"], .machine-card-trigger[data-machine-id="${filteredMesinId}"]`,
            );

            if (card) {
                const machineName =
                    card.getAttribute("data-machine-name") ||
                    card.getAttribute("data-mesin-nama");
                // Color might not be available directly if dynamic class used, fallback to info
                // Try to parse class list for border color
                let color = "info";
                if (card.classList.contains("border-success"))
                    color = "success";
                else if (card.classList.contains("border-danger"))
                    color = "danger";
                else if (card.classList.contains("border-warning"))
                    color = "warning";
                else if (card.classList.contains("border-dark")) color = "dark";

                showMachineModal(filteredMesinId, machineName, color);
            }
        }, 500);
    }
    // Scroll To Top Functionality
    var scrollBtn = $('#scrollToTopBtn');
    $(window).scroll(function() {
        if ($(window).scrollTop() > 300) {
            scrollBtn.fadeIn();
        } else {
            scrollBtn.fadeOut();
        }
    });

    scrollBtn.click(function() {
        $('html, body').animate({scrollTop:0}, 300);
        return false;
    });

    // --- Handle Andon Call ---
    $(document).on('click', '.btn-handle-call', function() {
        const id = $(this).data('id');
        const machine = $(this).data('machine');
        const title = $(this).data('title');

        Swal.fire({
            title: 'Penanganan Panggilan Bantuan',
            html: `
                <div class="text-left mb-3">
                    <p><b>Unit:</b> ${machine}</p>
                    <p><b>Kategori:</b> ${title.replace('CALL: ', '')}</p>
                    <p>Silakan pilih tindakan penanganan untuk panggilan ini:</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            showDenyButton: true,
            confirmButtonText: '<i class="fas fa-check-circle mr-1"></i> Selesai (Resolved)',
            denyButtonText: '<i class="fab fa-whatsapp mr-1"></i> Eskalasi ke WA Group',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#28a745',
            denyButtonColor: '#ffc107',
            reverseButtons: true,
            customClass: {
                denyButton: 'text-dark font-weight-bold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Resolve
                sendHandleCallAction(id, 'resolve');
            } else if (result.isDenied) {
                // Escalate
                sendHandleCallAction(id, 'escalate');
            }
        });
    });

    function sendHandleCallAction(id, action) {
        Swal.fire({
            title: 'Sedang memproses...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: `/andon/handle/${id}`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                action: action
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Gagal', response.message, 'error');
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem';
                Swal.fire('Error', msg, 'error');
            }
        });
    }
});
