/**
 * Dashboard Assy - Data Initialization
 * Reads data from script tag data attributes and initializes window variables
 */

(function() {
    // Read data from script tag
    const scriptTag = document.querySelector('script[src*="dashboard-assy.js"]');
    
    if (scriptTag) {
        window.countAktif = parseInt(scriptTag.dataset.countAktif) || 0;
        window.countMaintenance = parseInt(scriptTag.dataset.countMaintenance) || 0;
        window.countTidakAktif = parseInt(scriptTag.dataset.countTidakAktif) || 0;
        window.checksheetStoreUrl = scriptTag.dataset.checksheetStoreUrl || '';
        window.mejaDocumentsUrl = scriptTag.dataset.mejaDocumentsUrl || '';
        window.kabagApproved = scriptTag.dataset.kabagApproved === 'true';
        
        console.log('Dashboard Assy data initialized:', {
            countAktif: window.countAktif,
            countMaintenance: window.countMaintenance,
            countTidakAktif: window.countTidakAktif
        });
    } else {
        console.warn('Dashboard Assy script tag not found, using default values');
        window.countAktif = 0;
        window.countMaintenance = 0;
        window.countTidakAktif = 0;
        window.checksheetStoreUrl = '';
        window.mejaDocumentsUrl = '';
        window.kabagApproved = false;
    }
    // Clock functionality
    function updateClock() {
        const now = new Date();
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

        const dayName = days[now.getDay()];
        const day = now.getDate();
        const month = months[now.getMonth()];
        const year = now.getFullYear();

        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');

        const dateString = `${dayName}, ${day} ${month} ${year}`;
        const timeString = `${hours}:${minutes}:${seconds}`;

        const dateEl = document.getElementById('realtimeDate');
        const timeEl = document.getElementById('realtimeTime');

        if (dateEl) dateEl.textContent = dateString;
        if (timeEl) timeEl.textContent = timeString;
    }

    // Start clock
    updateClock();
    setInterval(updateClock, 1000);
})();
