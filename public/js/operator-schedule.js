document.addEventListener('DOMContentLoaded', function() {
    // Greet user
    const hour = new Date().getHours();
    const gt = document.getElementById('greeting-text');
    const userName = window.userData ? window.userData.nama : 'Operator';

    if (gt) {
        if (hour < 11) gt.innerText = `Selamat pagi, ${userName}! Mari kita mulai hari ini.`;
        else if (hour < 15) gt.innerText = `Selamat siang, ${userName}! Tetap semangat bekerja.`;
        else if (hour < 19) gt.innerText = `Selamat sore, ${userName}! Persiapan selesai tugas.`;
        else gt.innerText = `Selamat malam, ${userName}! Jaga kesehatan Anda.`;
    }

    // DateTime update
    function updateTime() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('id-ID', {
            hour12: false,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
        const dateStr = now.toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        const el = document.getElementById('current-datetime');
        if (el) el.innerHTML = `<i class="far fa-calendar-alt me-2"></i>${dateStr} <span class="mx-3 opacity-25">|</span> <i class="far fa-clock me-2"></i>${timeStr}`;
    }
    setInterval(updateTime, 1000);
    updateTime();

    // Progress Bar
    if (window.scheduleData && window.scheduleData.hasSchedule) {
        function updateProgress() {
            const now = new Date();
            const [hIn, mIn, sIn] = window.scheduleData.jamMasuk.split(':');
            const [hOut, mOut, sOut] = window.scheduleData.jamKeluar.split(':');

            const start = new Date(now);
            start.setHours(parseInt(hIn), parseInt(mIn), parseInt(sIn || 0));

            const end = new Date(now);
            end.setHours(parseInt(hOut), parseInt(mOut), parseInt(sOut || 0));

            if (end < start) end.setDate(end.getDate() + 1);

            const total = end - start;
            const elapsed = now - start;

            let percent = (elapsed / total) * 100;
            percent = Math.min(100, Math.max(0, percent));

            const pb = document.getElementById('work-progress');
            const pt = document.getElementById('progress-percentage');

            if (pb) pb.style.width = percent + '%';
            if (pt) pt.innerText = Math.round(percent) + '%';
        }
        setInterval(updateProgress, 30000);
        updateProgress();
    }

    // Dark Mode Functionality
    const darkModeToggle = document.getElementById('darkModeToggle');
    const body = document.body;
    
    // Check for saved dark mode preference
    if (localStorage.getItem('darkMode') === 'enabled') {
        body.classList.add('dark-mode');
        if (darkModeToggle) darkModeToggle.checked = true;
    }

    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', () => {
            if (darkModeToggle.checked) {
                body.classList.add('dark-mode');
                localStorage.setItem('darkMode', 'enabled');
            } else {
                body.classList.remove('dark-mode');
                localStorage.setItem('darkMode', 'disabled');
            }
        });
    }
});
