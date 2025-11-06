//function showForm(formId){
  //  document.querySelectorAll(".form-box").forEach(form => form.classList.remove("active"));
    //document.getElementById(formId).classList.add("active");
//}

/**
 * Toggle between login and register forms
 * @param {string} formId - ID of the form to display
 */
function showForm(formId) {
    // Hide all forms
    const forms = document.querySelectorAll('.form-box');
    forms.forEach(form => {
        form.classList.remove('active');
    });
    
    // Show the selected form
    const selectedForm = document.getElementById(formId);
    if (selectedForm) {
        selectedForm.classList.add('active');
    }
    
    // Prevent default link behavior
    return false;
}

// Ensure only one form is active on page load
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('.form-box');
    let hasActive = false;
    
    // Check if any form has active class
    forms.forEach(form => {
        if (form.classList.contains('active')) {
            hasActive = true;
        }
    });
    
    // If no form is active, activate login form by default
    if (!hasActive) {
        const loginForm = document.getElementById('login-box');
        if (loginForm) {
            loginForm.classList.add('active');
        }
    }
    
    // Auto-focus on first input of active form
    const activeForm = document.querySelector('.form-box.active');
    if (activeForm) {
        const firstInput = activeForm.querySelector('input');
        if (firstInput) {
            firstInput.focus();
        }
    }
});