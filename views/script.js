// Form submission handlers
document.addEventListener("DOMContentLoaded", () => {
    const contactForm = document.getElementById("contactForm");
    const homeContactForm = document.getElementById("homeContactForm");
    const newsletterForm = document.querySelector('.newsletter-form');

    if (contactForm) {
        contactForm.addEventListener("submit", handleFormSubmit);
    }

    if (homeContactForm) {
        homeContactForm.addEventListener("submit", handleFormSubmit);
    }

    if (newsletterForm) {
        newsletterForm.addEventListener("submit", handleNewsletterSubmit);
    }

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

function handleFormSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    // Create a summary of form data
    const formSummary = {
        name: formData.get("name") || "Not provided",
        email: formData.get("email") || "Not provided",
        phone: formData.get("phone") || "Not provided",
        subject: formData.get("subject") || "Not provided",
        message: formData.get("message") || "Not provided",
        businessType: formData.get("businessType") || "Not specified",
        timestamp: new Date().toLocaleString(),
    };

    // Log to console (in real app, would send to server)
    console.log("Form submitted:", formSummary);

    // Show success message with Bootstrap alert
    showAlert("Thank you for your message! Mizrah Sharp will contact you shortly.", "success");

    // Reset form
    form.reset();
}

function handleNewsletterSubmit(e) {
    e.preventDefault();
    const email = e.target.querySelector('input[type="email"]').value;
    
    // Simulate newsletter subscription
    console.log("Newsletter subscription:", email);
    showAlert("Thank you for subscribing to our newsletter!", "success");
    e.target.reset();
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.querySelector('body').insertBefore(alertDiv, document.querySelector('body').firstChild);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentElement) {
            alertDiv.remove();
        }
    }, 5000);
}

// Smooth scrolling for navigation links
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
        const href = this.getAttribute("href");
        if (href !== "#" && document.querySelector(href)) {
            e.preventDefault();
            document.querySelector(href).scrollIntoView({
                behavior: "smooth",
            });
        }
    });
});

// Navbar background on scroll
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 100) {
        navbar.style.background = 'var(--primary)';
        navbar.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
    } else {
        navbar.style.background = 'var(--primary)';
        navbar.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
    }
});