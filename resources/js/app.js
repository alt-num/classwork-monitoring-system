import './bootstrap';

// CSRF Token handling
const setupCsrfHandling = () => {
    // Add CSRF token to all AJAX requests
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    if (token) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    }

    // Handle page show event (triggered when navigating back/forward)
    window.addEventListener('pageshow', (event) => {
        // Check if the page is loaded from cache (back/forward navigation)
        if (event.persisted) {
            // Refresh CSRF token
            fetch('/sanctum/csrf-cookie').then(() => {
                const newToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (newToken) {
                    axios.defaults.headers.common['X-CSRF-TOKEN'] = newToken;
                }
            });
        }
    });

    // Create toast notification element
    const createToast = (message, type = 'error') => {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 p-4 rounded-md shadow-lg ${
            type === 'error' ? 'bg-red-500' : 'bg-green-500'
        } text-white transform transition-transform duration-300 translate-x-full`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        // Animate in
        setTimeout(() => toast.classList.remove('translate-x-full'), 100);
        
        // Remove after 5 seconds
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
        
        return toast;
    };

    // Handle form submissions
    document.addEventListener('submit', function(e) {
        const form = e.target;
        
        // Skip if form has data-no-csrf-handler attribute
        if (form.hasAttribute('data-no-csrf-handler')) {
            return;
        }

        if (form.method.toLowerCase() === 'post') {
            e.preventDefault();
            
            // Store form data before submission
            const formData = new FormData(form);
            
            axios.post(form.action, formData)
                .then(response => {
                    if (response.data.redirect) {
                        if (response.data.message) {
                            // Store message in sessionStorage to show after redirect
                            sessionStorage.setItem('flashMessage', JSON.stringify({
                                type: 'success',
                                message: response.data.message
                            }));
                        }
                        window.location.href = response.data.redirect;
                    } else if (response.data.message) {
                        createToast(response.data.message, 'success');
                        if (response.data.reload) {
                            window.location.reload();
                        }
                    } else {
                        window.location.reload();
                    }
                })
                .catch(error => {
                    if (error.response?.status === 419) {
                        // CSRF token mismatch
                        const toast = createToast('Your session has expired. Refreshing page...', 'warning');
                        
                        // Store form data in sessionStorage
                        const formDataObj = {};
                        formData.forEach((value, key) => formDataObj[key] = value);
                        sessionStorage.setItem('formData', JSON.stringify({
                            action: form.action,
                            data: formDataObj
                        }));
                        
                        // Refresh after showing toast
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        createToast(error.response?.data?.message || 'An error occurred. Please try again.');
                        console.error('Form submission error:', error);
                    }
                });
        }
    });

    // Check for flash messages on page load
    window.addEventListener('load', () => {
        const flashMessage = sessionStorage.getItem('flashMessage');
        if (flashMessage) {
            try {
                const { type, message } = JSON.parse(flashMessage);
                createToast(message, type);
            } catch (e) {
                console.error('Error displaying flash message:', e);
            }
            sessionStorage.removeItem('flashMessage');
        }
    });

    // Restore form data after page refresh if exists
    window.addEventListener('load', () => {
        const savedFormData = sessionStorage.getItem('formData');
        if (savedFormData) {
            try {
                const { action, data } = JSON.parse(savedFormData);
                const form = Array.from(document.forms).find(f => f.action === action);
                if (form) {
                    Object.entries(data).forEach(([key, value]) => {
                        const input = form.elements[key];
                        if (input) input.value = value;
                    });
                }
            } catch (e) {
                console.error('Error restoring form data:', e);
            }
            sessionStorage.removeItem('formData');
        }
    });
};

// Initialize CSRF handling
setupCsrfHandling();
