// Application Form Handler
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('application_form');
    const submitButton = form.querySelector('button[type="submit"]');
    
    // Form validation and enhancement
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        const originalButtonText = submitButton.innerHTML;
        submitButton.innerHTML = '<span>Submitting...</span>';
        submitButton.disabled = true;
        
        // Basic validation
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        let firstInvalidField = null;
        
        requiredFields.forEach(field => {
            const value = field.type === 'radio' ? form.querySelector(`input[name="${field.name}"]:checked`) : field.value;
            
            if (!value || (typeof value === 'string' && !value.trim())) {
                field.style.borderColor = '#dc3545';
                if (!firstInvalidField) {
                    firstInvalidField = field;
                }
                isValid = false;
            } else {
                field.style.borderColor = '#20d34a';
            }
        });
        
        // Email validation
        const emailFields = form.querySelectorAll('input[type="email"]');
        emailFields.forEach(field => {
            if (field.value && !isValidEmail(field.value)) {
                field.style.borderColor = '#dc3545';
                isValid = false;
                if (!firstInvalidField) {
                    firstInvalidField = field;
                }
            }
        });
        
        // File validation
        const cvUpload = form.querySelector('#cv_upload');
        if (cvUpload.files.length === 0) {
            cvUpload.style.borderColor = '#dc3545';
            isValid = false;
            if (!firstInvalidField) {
                firstInvalidField = cvUpload;
            }
        }
        
        if (isValid) {
            // Prepare form data
            const formData = new FormData(form);
            
            // Submit form via AJAX
            fetch('submit_application.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessMessage();
                    form.reset();
                } else {
                    showErrorMessage(data.message || 'An error occurred while submitting your application.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showErrorMessage('Network error. Please check your connection and try again.');
            })
            .finally(() => {
                // Restore button state
                submitButton.innerHTML = originalButtonText;
                submitButton.disabled = false;
            });
        } else {
            // Scroll to first invalid field
            if (firstInvalidField) {
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstInvalidField.focus();
            }
            
            showErrorMessage('Please fill in all required fields correctly.');
            
            // Restore button state
            submitButton.innerHTML = originalButtonText;
            submitButton.disabled = false;
        }
    });
    
    // File upload preview
    document.querySelectorAll('input[type="file"]').forEach(input => {
        input.addEventListener('change', function() {
            const fileName = this.files[0]?.name;
            const fileSize = this.files[0]?.size;
            const maxSize = 10 * 1024 * 1024; // 10MB
            
            if (fileName) {
                const label = this.previousElementSibling;
                
                if (fileSize > maxSize) {
                    this.style.borderColor = '#dc3545';
                    label.style.color = '#dc3545';
                    label.innerHTML = label.innerHTML.split(' - ')[0] + ' - File too large (max 10MB)';
                } else {
                    this.style.borderColor = '#20d34a';
                    label.style.color = '#20d34a';
                    label.innerHTML = label.innerHTML.split(' - ')[0] + ` - ${fileName}`;
                }
            }
        });
    });
    
    // Real-time validation
    const inputs = form.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            // Remove error styling when user starts typing
            if (this.style.borderColor === 'rgb(220, 53, 69)') {
                this.style.borderColor = '';
            }
        });
    });
    
    // Criminal conviction conditional display
    const criminalRadios = form.querySelectorAll('input[name="criminal_conviction"]');
    const criminalDetails = form.querySelector('textarea[name="criminal_details"]');
    
    criminalRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'yes') {
                criminalDetails.style.display = 'block';
                criminalDetails.required = true;
            } else {
                criminalDetails.style.display = 'none';
                criminalDetails.required = false;
                criminalDetails.value = '';
            }
        });
    });
    
    // Auto-save draft (optional)
    let saveTimeout;
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(saveTimeout);
            saveTimeout = setTimeout(() => {
                saveDraft();
            }, 2000);
        });
    });
});

function validateField(field) {
    let isValid = true;
    
    if (field.required && (!field.value || !field.value.trim())) {
        isValid = false;
    }
    
    if (field.type === 'email' && field.value && !isValidEmail(field.value)) {
        isValid = false;
    }
    
    if (field.type === 'tel' && field.value && !isValidPhone(field.value)) {
        isValid = false;
    }
    
    field.style.borderColor = isValid ? '#20d34a' : '#dc3545';
    return isValid;
}

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPhone(phone) {
    const phoneRegex = /^[\d\s\-\+\(\)]{10,}$/;
    return phoneRegex.test(phone.replace(/\s/g, ''));
}

function showSuccessMessage() {
    const message = document.createElement('div');
    message.className = 'alert alert-success';
    message.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        background: #20d34a;
        color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        max-width: 400px;
        animation: slideIn 0.3s ease-out;
    `;
    message.innerHTML = `
        <h5 style="margin: 0 0 10px 0; color: white;">✅ Application Submitted Successfully!</h5>
        <p style="margin: 0; color: white;">Thank you for your application. Our recruitment team will review your details and contact you within 2-3 business days.</p>
    `;
    
    document.body.appendChild(message);
    
    setTimeout(() => {
        message.remove();
    }, 8000);
}

function showErrorMessage(errorMessage) {
    const message = document.createElement('div');
    message.className = 'alert alert-danger';
    message.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        background: #dc3545;
        color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        max-width: 400px;
        animation: slideIn 0.3s ease-out;
    `;
    message.innerHTML = `
        <h5 style="margin: 0 0 10px 0; color: white;">❌ Error</h5>
        <p style="margin: 0; color: white;">${errorMessage}</p>
    `;
    
    document.body.appendChild(message);
    
    setTimeout(() => {
        message.remove();
    }, 6000);
}

function saveDraft() {
    const formData = new FormData(document.getElementById('application_form'));
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    localStorage.setItem('applicationDraft', JSON.stringify(data));
}

function loadDraft() {
    const draft = localStorage.getItem('applicationDraft');
    if (draft) {
        const data = JSON.parse(draft);
        const form = document.getElementById('application_form');
        
        Object.keys(data).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field && field.type !== 'file') {
                field.value = data[key];
            }
        });
    }
}

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);
