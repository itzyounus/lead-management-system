const form = document.getElementById('leadForm');
const inputs = {
    name: document.getElementById('name'),
    email: document.getElementById('email'),
    company: document.getElementById('company'),
    phone: document.getElementById('phone')
};

// Email validation regex
const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

// Phone validation regex (allows various formats)
const phoneRegex = /^[\d\s\-\+\(\)]{10,}$/;

function showError(field, message) {
    const input = inputs[field];
    const errorDiv = document.getElementById(field + 'Error');
    input.classList.add('error');
    errorDiv.textContent = message;
    errorDiv.classList.add('show');
}

function clearError(field) {
    const input = inputs[field];
    const errorDiv = document.getElementById(field + 'Error');
    input.classList.remove('error');
    errorDiv.classList.remove('show');
}

function validateField(field) {
    clearError(field);
    const value = inputs[field].value.trim();

    if (!value) {
        showError(field, `Please enter your ${field}`);
        return false;
    }

    if (field === 'email' && !emailRegex.test(value)) {
        showError(field, 'Please enter a valid email address');
        return false;
    }

    if (field === 'phone' && !phoneRegex.test(value)) {
        showError(field, 'Please enter a valid phone number (min 10 digits)');
        return false;
    }

    return true;
}

// Real-time validation
Object.keys(inputs).forEach(field => {
    inputs[field].addEventListener('blur', () => validateField(field));
    inputs[field].addEventListener('input', () => {
        if (inputs[field].classList.contains('error')) {
            validateField(field);
        }
    });
});

form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate all fields
    let isValid = true;
    Object.keys(inputs).forEach(field => {
        if (!validateField(field)) {
            isValid = false;
        }
    });

    if (isValid) {
        // Submit form data via AJAX
        const formData = new FormData(form);
        
        fetch('submit_lead.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                document.getElementById('successMessage').classList.add('show');
                
                // Reset form
                form.reset();
                
                // Hide success message after 5 seconds
                setTimeout(() => {
                    document.getElementById('successMessage').classList.remove('show');
                }, 5000);
            } else {
                alert('Error: ' + (data.message || 'Something went wrong'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while submitting the form');
        });
    }
});