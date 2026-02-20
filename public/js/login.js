/**
 * Login Page - Simplified Interactions
 * Reduced animations for better performance and cleaner feel.
 */

document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // Form Interactions
    // ============================================
    const animatedInputs = document.querySelectorAll('.animated-input');
    
    animatedInputs.forEach((input) => {
        const inputGroup = input.closest('.input-group-animated');
        const icon = inputGroup?.querySelector('.icon-animated');
        
        // Focus effect
        input.addEventListener('focus', function() {
            if (inputGroup) {
                inputGroup.classList.add('focused');
            }
            if (icon) {
                icon.style.color = '#3b82f6';
            }
        });
        
        // Blur effect
        input.addEventListener('blur', function() {
            if (inputGroup) {
                inputGroup.classList.remove('focused');
            }
            if (icon) {
                icon.style.color = '';
            }
        });

        // Initial check for value
        if (input.value.length > 0) {
            inputGroup?.classList.add('has-value');
        }

        // Input typing state
        input.addEventListener('input', function() {
            if (this.value.length > 0) {
                inputGroup?.classList.add('has-value');
            } else {
                inputGroup?.classList.remove('has-value');
            }
        });
    });

    // ============================================
    // Password Visibility Toggle
    // ============================================
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (togglePassword && passwordInput && eyeIcon) {
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });
    }

    // ============================================
    // Button Loading State
    // ============================================
    const loginForm = document.querySelector('.login-form');
    const loginButton = document.querySelector('.btn-login');
    const buttonText = document.querySelector('.button-text');
    const buttonLoading = document.querySelector('.button-loading');

    if (loginForm && loginButton) {
        loginForm.addEventListener('submit', function() {
            if (buttonText && buttonLoading) {
                buttonText.style.display = 'none';
                buttonLoading.style.display = 'inline-block';
            }
            loginButton.disabled = true;
            loginButton.style.opacity = '0.8';
            loginButton.style.cursor = 'not-allowed';
        });
    }

    // ============================================
    // Alert Auto-dismiss
    // ============================================
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach((alert) => {
        setTimeout(() => {
            if (alert && alert.parentNode) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => {
                    if (alert.parentNode) {
                        alert.parentNode.removeChild(alert);
                    }
                }, 500);
            }
        }, 5000);
    });
});
