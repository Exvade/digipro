/**
 * Dashboard Inject Main Logic
 * Separated from index.blade.php to improve maintainability and fix linter errors.
 */

$(function() {
    // ====== DATA INITIALIZATION ======
    const dataEl = document.getElementById('dashboard-data');
    if (!dataEl) return;

    window.isAuthenticated = dataEl.dataset.isAuthenticated === 'true';
    window.loginRoute = dataEl.dataset.loginRoute;
    window.dashboardInjectDocsUrl = dataEl.dataset.docsUrl;
    window.machinesData = JSON.parse(dataEl.dataset.machines || '[]');
    window.productionData = JSON.parse(dataEl.dataset.productionData || '{}');
    window.kabagApproved = dataEl.dataset.kabagApproved ? JSON.parse(dataEl.dataset.kabagApproved) : null;

    // ====== AUTH & MACHINE DETAIL OVERRIDE ======
    // Capture the original function after it has been defined by dashboard-inject.js
    const originalShowMachineDetail = window.showMachineDetail;

    // Override with auth check
    window.showMachineDetail = function(id, nama, brand, tonase, status) {
        if (!window.isAuthenticated) {
            Swal.fire({
                icon: 'info',
                title: 'Login Diperlukan',
                text: 'Silakan login untuk melihat detail mesin dan mengisi checksheet.',
                showCancelButton: true,
                confirmButtonText: 'Login',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                background: '#1a1d21',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = window.loginRoute;
                }
            });
            return;
        }

        // Call original function if authenticated
        if (typeof originalShowMachineDetail === 'function') {
            originalShowMachineDetail(id, nama, brand, tonase, status);
        } else {
            console.error('Original showMachineDetail function not found');
        }
    };

    // ====== REALTIME CLOCK ======
    function updateRealtimeClock() {
        const now = new Date();

        // Format date: Senin, 27 Januari 2026
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        const dayName = days[now.getDay()];
        const day = now.getDate();
        const month = months[now.getMonth()];
        const year = now.getFullYear();

        const dateString = `${dayName}, ${day} ${month} ${year}`;

        // Format time: 15:57:19
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        const timeString = `${hours}:${minutes}:${seconds}`;

        // Update DOM
        const dateElement = document.getElementById('realtimeDate');
        const timeElement = document.getElementById('realtimeTime');

        if (dateElement) dateElement.textContent = dateString;
        if (timeElement) timeElement.textContent = timeString;
    }

    // Update immediately on load
    updateRealtimeClock();

    // Update every second
    setInterval(() => {
        updateRealtimeClock();
    }, 1000);

    // ====== SHARE TO WHATSAPP FEATURE ======
    const shareBtn = document.getElementById('shareWhatsappBtn');
    if (shareBtn) {
        shareBtn.addEventListener('click', function() {
            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            const dayName = days[now.getDay()];
            const day = now.getDate();
            const month = months[now.getMonth()];
            const year = now.getFullYear();

            const timeString = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });
            const dateString = `${dayName}, ${day} ${month} ${year} | ${timeString}`;

            // Determine Shift
            const hours = now.getHours();
            let shift = "Shift 3";
            if (hours >= 7 && hours < 15) shift = "Shift 1";
            else if (hours >= 15 && hours < 23) shift = "Shift 2";

            let reportText = `*Laporan Awal Produksi ${shift}*\n_${dateString}_\n\n`;

            // Get machine data from global variables
            const machines = window.machinesData || [];
            const productionData = window.productionData || {};

            // 1. Mesin Yang Belum Running
            reportText += `*• Mesin Yang Belum Running :*\n`;
            let notRunningCount = 1;
            machines.forEach(m => {
                const status = m.status;
                if (status !== 'aktif') {
                    const data = productionData[m.id] || {};
                    const machineId = m.nama_mesin.replace('Mesin ', 'MC ');
                    let statusLabel = '';

                    if (status === 'maintenance') statusLabel = 'on maintenance';
                    else if (status === 'setup') statusLabel = 'proses set up';
                    else if (status === 'setdown') statusLabel = 'setdown';
                    else if (status === 'material_habis') statusLabel = 'material habis';
                    else if (status === 'packaging_habis') statusLabel = 'packaging habis';
                    else if (status === 'tidak aktif' || status === 'offline') statusLabel = 'Off';
                    else if (status === 'stop') statusLabel = 'Stop';

                    const keterangan = data.keterangan || '';
                    const separator = (keterangan && statusLabel) ? ', ' : ' ';

                    reportText += `${notRunningCount}. ${machineId} ${keterangan}${separator}${statusLabel}\n`;
                    notRunningCount++;
                }
            });

            reportText += `\n`;

            // 2. Full Machine List
            machines.forEach((m, index) => {
                const data = productionData[m.id] || {};
                const machineId = m.nama_mesin.replace('Mesin ', 'Mc ');
                const partName = data.part_name || 'No Part';
                const status = m.status;

                let statusLabel = 'running';
                if (status === 'maintenance') statusLabel = 'On maintenance';
                else if (status === 'setup') statusLabel = 'proses setup';
                else if (status === 'setdown') statusLabel = 'setdown';
                else if (status === 'material_habis') statusLabel = 'material habis';
                else if (status === 'packaging_habis') statusLabel = 'packaging habis';
                else if (status === 'tidak aktif' || status === 'offline') statusLabel = 'off';
                else if (status === 'stop') statusLabel = 'Stop';

                const num = String(index + 1).padStart(1, '0');
                reportText += `${num}. ${machineId} = ${statusLabel}\n`;
            });

            // Open WhatsApp
            const encodedText = encodeURIComponent(reportText);
            const whatsappUrl = `https://api.whatsapp.com/send?text=${encodedText}`;
            window.open(whatsappUrl, '_blank');
        });
    }
});
