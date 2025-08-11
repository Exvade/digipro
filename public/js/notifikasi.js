// Notifikasi System
class NotificationSystem {
    constructor() {
        this.notifUrl = window.notifUrl || '/notifications';
        this.pollingInterval = null;
        this.lastNotificationCount = 0;
        this.init();
    }

    init() {
        this.startPolling();
    }

    startPolling() {
        // Poll setiap 30 detik
        this.pollingInterval = setInterval(() => {
            this.checkNotifications();
        }, 30000);

        // Check sekali saat halaman dimuat
        this.checkNotifications();
    }

    stopPolling() {
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
            this.pollingInterval = null;
        }
    }

    async checkNotifications() {
        try {
            const response = await fetch(this.notifUrl, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            const notifications = data.notifications || [];

            // Jika ada notifikasi baru
            if (notifications.length > this.lastNotificationCount) {
                this.showNotification(notifications[0]); // Tampilkan notifikasi terbaru
                this.lastNotificationCount = notifications.length;
            }

        } catch (error) {
            console.error('Error checking notifications:', error);
        }
    }

    showNotification(notification) {
        // Gunakan SweetAlert2 jika tersedia
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Notifikasi Baru',
                text: notification.message || 'Ada notifikasi baru untuk Anda.',
                timer: 5000,
                showConfirmButton: false,
                position: 'top-end',
                toast: true
            });
        } else {
            // Fallback ke alert biasa
            alert('Notifikasi Baru: ' + (notification.message || 'Ada notifikasi baru untuk Anda.'));
        }
    }

    // Method untuk menampilkan modal notifikasi
    showNotificationModal() {
        $('#notifModal').modal('show');
    }
}

// Global function untuk memulai polling notifikasi
window.startNotifPolling = function() {
    if (!window.notificationSystem) {
        window.notificationSystem = new NotificationSystem();
    }
};

// Cleanup saat halaman unload
window.addEventListener('beforeunload', function() {
    if (window.notificationSystem) {
        window.notificationSystem.stopPolling();
    }
}); 