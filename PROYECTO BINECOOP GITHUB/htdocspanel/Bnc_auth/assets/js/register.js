document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.login-form');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('confirm_password');
    const strengthMeter = document.createElement('div');
    strengthMeter.className = 'password-strength';
    strengthMeter.innerHTML = '<div class="strength-meter"></div>';
    
    if (passwordInput) {
        passwordInput.parentNode.insertBefore(strengthMeter, passwordInput.nextElementSibling);
        
        // Validación en tiempo real
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const meter = strengthMeter.querySelector('.strength-meter');
            let strength = 0;
            
            // Longitud mínima
            if (password.length >= 8) strength += 20;
            
            // Contiene mayúscula
            if (/[A-Z]/.test(password)) strength += 20;
            
            // Contiene número
            if (/[0-9]/.test(password)) strength += 20;
            
            // Contiene caracter especial
            if (/[^A-Za-z0-9]/.test(password)) strength += 20;
            
            // Longitud adicional
            if (password.length >= 12) strength += 20;
            
            // Actualizar medidor
            meter.style.width = strength + '%';
            
            // Cambiar color según fortaleza
            if (strength < 40) {
                meter.style.backgroundColor = var(--danger-color);
            } else if (strength < 70) {
                meter.style.backgroundColor = var(--warning-color);
            } else {
                meter.style.backgroundColor = var(--success-color);
            }
        });
        
        // Confirmación de contraseña
        confirmInput.addEventListener('input', function() {
            if (this.value !== passwordInput.value) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }
    
    // Validación del formulario
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const terms = document.getElementById('terms');
            
            if (!terms.checked) {
                terms.classList.add('is-invalid');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                const errorElement = document.createElement('span');
                errorElement.className = 'error-message';
                errorElement.innerHTML = '<i class="fas fa-exclamation-circle"></i> Debes aceptar los términos y condiciones';
                terms.parentNode.appendChild(errorElement);
            }
        });
    }
});
