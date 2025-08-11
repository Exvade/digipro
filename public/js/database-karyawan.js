// Database Karyawan Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling for anchor links
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add loading animation for the back button
    const backBtn = document.querySelector('.back-btn');
    if (backBtn) {
        backBtn.addEventListener('click', function() {
            // Add loading state
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
            this.style.pointerEvents = 'none';
        });
    }

    // Add fade-in animation for the coming soon card
    const comingSoonCard = document.querySelector('.coming-soon-card');
    if (comingSoonCard) {
        comingSoonCard.style.opacity = '0';
        comingSoonCard.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            comingSoonCard.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
            comingSoonCard.style.opacity = '1';
            comingSoonCard.style.transform = 'translateY(0)';
        }, 100);
    }

    // Add interactive hover effects for the progress bar
    const progressFill = document.querySelector('.progress-fill');
    if (progressFill) {
        progressFill.addEventListener('mouseenter', function() {
            this.style.animationPlayState = 'paused';
        });
        
        progressFill.addEventListener('mouseleave', function() {
            this.style.animationPlayState = 'running';
        });
    }

    // Add keyboard navigation support
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            // Navigate back to menu on Escape key
            window.location.href = document.querySelector('.back-btn').href;
        }
    });

    // Add touch gesture support for mobile devices
    let touchStartX = 0;
    let touchEndX = 0;

    document.addEventListener('touchstart', function(e) {
        touchStartX = e.changedTouches[0].screenX;
    });

    document.addEventListener('touchend', function(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleSwipe();
    });

    function handleSwipe() {
        const swipeThreshold = 50;
        const diff = touchStartX - touchEndX;
        
        if (Math.abs(diff) > swipeThreshold) {
            if (diff > 0) {
                // Swipe left - go back to menu
                window.location.href = document.querySelector('.back-btn').href;
            }
        }
    }

    // Add console log for debugging
    console.log('Database Karyawan page loaded successfully');
}); 