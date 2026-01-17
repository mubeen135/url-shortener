// Global JavaScript functions
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        showToast('Copied to clipboard!');
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
    });
}

function showToast(message, type = 'success') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 px-4 py-2 rounded-md text-white ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } z-50`;
    toast.textContent = message;
    
    // Add to DOM
    document.body.appendChild(toast);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// Global AJAX error handler
$(document).ajaxError(function(event, jqxhr, settings, thrownError) {
    if (jqxhr.status === 401) {
        // Unauthorized - redirect to login
        window.location.href = '/login';
    } else if (jqxhr.status === 403) {
        // Forbidden
        showToast('You do not have permission to perform this action.', 'error');
    } else if (jqxhr.status === 422) {
        // Validation errors
        const errors = jqxhr.responseJSON.errors;
        let errorMessage = 'Please fix the following errors:';
        for (const field in errors) {
            errorMessage += `\n• ${errors[field].join(', ')}`;
        }
        showToast(errorMessage, 'error');
    } else {
        // Other errors
        showToast('An error occurred. Please try again.', 'error');
    }
});