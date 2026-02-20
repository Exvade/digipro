// Notifikasi System
class NotificationSystem {
    constructor() {
        this.notifUrl = window.notifUrl || '/notifications';
        this.pollingInterval = null;
        this.lastNotificationIds = new Set();
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
            
            this.renderNotifications(notifications);

            // Check for NEW notifications to show toast
            notifications.forEach(notif => {
                if (!this.lastNotificationIds.has(notif.id)) {
                    // this.showNotificationToast(notif); // Disabled by user request
                    this.lastNotificationIds.add(notif.id);
                }
            });

        } catch (error) {
            console.error('Error checking notifications:', error);
        }
    }

    renderNotifications(notifications) {
        // Target specific elements relative to the notification system to avoid global side-effects
        const dropdownMenu = $('.nav-notif-link').next('.dropdown-menu');
        const navLink = $('.nav-notif-link');
        const badge = navLink.find('.badge');
        
        // Handle Badge Logic
        if (notifications.length === 0) {
            // Hide badge instead of removing to prevent layout shifts/flickering
            badge.hide(); 
            badge.text(''); 
            
            // Empty state for dropdown
            dropdownMenu.html('<span class="dropdown-item dropdown-header">Tidak ada notifikasi</span>');
            return;
        }

        // Show and Update Badge
        if (badge.length === 0) {
            // Create if doesn't exist (safety fallback)
            navLink.append(`<span class="badge badge-danger navbar-badge">${notifications.length}</span>`);
        } else {
            // Update existing
            badge.removeClass('badge-warning').addClass('badge-danger');
            badge.text(notifications.length);
            badge.show();
        }

        // Render Dropdown Items
        let html = `<span class="dropdown-item dropdown-header">${notifications.length} Notifikasi</span>`;
        
        notifications.slice(0, 5).forEach(notif => {
            let messageContent = notif.message;
            let iconClass = notif.icon;
            let iconColor = notif.color;
            let textColor = '';

            // Format message (remove SOS prefix or specific formatting)
            if (notif.type === 'call') {
                // Remove "SOS:" or similar prefixes if present
                const cleanMessage = notif.message.replace(/^(SOS:|PANGGILAN:|CALL:)/i, '').trim();
                messageContent = `<strong class="text-danger">Panggilan Bantuan!</strong><br>${cleanMessage}`;
                iconClass = 'fas fa-exclamation-triangle';
                iconColor = 'text-danger';
            }

            html += `
                <div class="dropdown-divider"></div>
                <a href="/dashboard/inject/history" class="dropdown-item d-flex align-items-start py-2">
                    <div class="mr-3" style="min-width: 20px;">
                        <i class="${iconClass} ${iconColor}"></i>
                    </div>
                    <div class="flex-grow-1" style="min-width: 0;">
                        <span class="d-block text-wrap text-sm ${textColor}" style="line-height: 1.4;">${messageContent}</span>
                        <span class="d-block text-muted text-xs mt-1"><i class="far fa-clock mr-1"></i>${notif.time_display}</span>
                    </div>
                    <div class="ml-2">
                        ${!notif.read_at ? '<span class="badge badge-success badge-dot"></span>' : ''}
                    </div>
                </a>
            `;
        });
        
        html += `
            <div class="dropdown-divider"></div>
            <a href="/dashboard/inject/history" class="dropdown-item dropdown-footer text-center"><strong>Lihat Semua Notifikasi</strong></a>
        `;
        
        dropdownMenu.html(html);
    }

    showNotificationToast(notification) {
        const isDarkMode = $('body').hasClass('dark-mode');
        const bgColor = isDarkMode ? '#343a40' : '#fff';
        const textColor = isDarkMode ? '#fff' : '#1f2d3d';

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: notification.type === 'call' ? 'warning' : 'info',
                title: notification.type === 'call' ? '<span class="text-danger">PANGGILAN BANTUAN!</span>' : 'Notifikasi Baru',
                html: `<span style="color: ${textColor}">${notification.message || 'Ada notifikasi baru untuk Anda.'}</span>`,
                timer: 10000,
                showConfirmButton: false,
                position: 'top-end',
                toast: true,
                background: notification.type === 'call' ? (isDarkMode ? '#3d0e0e' : '#fff5f5') : bgColor,
                customClass: {
                    popup: 'colored-toast',
                    title: 'toast-title-custom'
                },
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            // Play sound for CALL if available
            if (notification.type === 'call') {
                this.playAlertSound();
            }
        }
    }

    playAlertSound() {
        // Optional: Add alert sound logic here if needed
    }
}

// Global function untuk memulai polling notifikasi
window.startNotifPolling = function() {
    if (!window.notificationSystem) {
        window.notificationSystem = new NotificationSystem();
    }
};

// Auto-start if it's leader or foreman (handled by global include)
$(document).ready(function() {
    if (window.isLeaderOrForeman) {
        window.startNotifPolling();
    }
});

// Cleanup saat halaman unload
window.addEventListener('beforeunload', function() {
    if (window.notificationSystem) {
        window.notificationSystem.stopPolling();
    }
}); 