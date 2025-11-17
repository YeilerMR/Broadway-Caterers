// contact.js - Validación y manejo del formulario de contacto

// ===== FUNCIÓN DE MÁSCARA PARA TELÉFONO (EE.UU.) =====
function applyPhoneMask(input) {
  input.addEventListener("input", function (e) {
    let value = e.target.value.replace(/\D/g, "");
    if (value.length > 10) value = value.substring(0, 10);

    let formatted = "";
    if (value.length >= 1) {
      formatted = "(" + value.substring(0, 3);
      if (value.length >= 4) {
        formatted += ") " + value.substring(3, 6);
        if (value.length >= 7) {
          formatted += "-" + value.substring(6, 10);
        }
      }
    }
    e.target.value = formatted;
    if (value.length === 10) clearFieldError(input);
  });

  input.addEventListener("paste", () => {
    setTimeout(() => input.dispatchEvent(new Event("input")), 10);
  });

  input.addEventListener("focus", () => clearFieldError(input));
}

// ===== VALIDACIÓN VISUAL DE CAMPOS =====
function setFieldError(field, message) {
  field.classList.add("field-input");
  field.classList.add("!border-red-500", "!focus:border-red-500", "!focus:ring-red-500");

  const existingError = field.parentNode.querySelector(".field-error");
  if (existingError) existingError.remove();

  const errorDiv = document.createElement("p");
  errorDiv.className = "field-error !text-red-500 text-sm mt-1";
  errorDiv.textContent = message;
  field.parentNode.appendChild(errorDiv);
}

function clearFieldError(field) {
  if (!field) return;
  field.classList.remove("!border-red-500", "!focus:border-red-500", "!focus:ring-red-500");
  const errorDiv = field.parentNode.querySelector(".field-error");
  if (errorDiv) errorDiv.remove();
}

function addLoadingState(button, loadingText = "Sending...") {
  const originalText = button.innerHTML;
  button.innerHTML = loadingText;
  button.disabled = true;
  return () => {
    button.innerHTML = originalText;
    button.disabled = false;
  };
}

function showAlert(message, type) {
  const existing = document.querySelector(".form-alert");
  if (existing) existing.remove();

  const alertDiv = document.createElement("div");
  alertDiv.className = `form-alert fixed top-20 right-6 bg-${type === "success" ? "green":"red"}-500 text-white px-6 py-4 rounded-lg shadow-lg z-50`;
  alertDiv.innerHTML = `
    <div class="flex items-center">
      <i class="fas fa-${type === "success" ? "check" : "exclamation"}-circle mr-3"></i>
      <span>${message}</span>
    </div>
  `;
  document.body.appendChild(alertDiv);

  setTimeout(() => {
    alertDiv.classList.add("opacity-0", "translate-y-2");
    setTimeout(() => alertDiv.remove(), 300);
  }, 5000);
}

// ===== VALIDACIÓN DEL FORMULARIO =====
function validateForm(form) {
  let isValid = true;

  const name = form.querySelector("#name");
  const email = form.querySelector("#email");
  const phone = form.querySelector("#phone");
  const subject = form.querySelector("#subject");
  const message = form.querySelector("#message");

  // Limpiar errores previos
  [name, email, phone, subject, message].forEach(clearFieldError);

  if (!name.value.trim()) {
    setFieldError(name, "Full name is required.");
    isValid = false;
  }

  if (!email.value.trim()) {
    setFieldError(email, "Email is required.");
    isValid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
    setFieldError(email, "Please enter a valid email address.");
    isValid = false;
  }

  if (phone.value.trim()) {
    const digitsOnly = phone.value.replace(/\D/g, "");
    if (digitsOnly.length !== 10) {
      setFieldError(phone, "Phone must be 10 digits (e.g., (123) 456-7890).");
      isValid = false;
    }
  }

  if (!subject.value.trim()) {
    setFieldError(subject, "Subject is required.");
    isValid = false;
  }

  if (!message.value.trim()) {
    setFieldError(message, "Message is required.");
    isValid = false;
  }

  // Validar servicios
  const services = form.querySelectorAll('input[name="services"]:checked');
  if (services.length === 0) {
    const servicesContainer = document.querySelector("div.grid.md\\:grid-cols-2.gap-4");

    //
    if (servicesContainer) {
    const servicesTitle = servicesContainer.previousElementSibling;
    if (servicesTitle && !servicesTitle.querySelector(".field-error")) {
        
        const errorDiv = document.createElement("p");
        errorDiv.className = "field-error !text-red-500 text-sm mt-1";
        errorDiv.textContent = "Please select at least one service.";
        servicesTitle.appendChild(errorDiv);
    }
    }
    isValid = false;
  } else {
    const existingError = document.querySelector(".field-error");
    if (existingError && existingError.textContent.includes('service')) {
        existingError.remove();
    }
  }

  return isValid;
}

// ===== INICIALIZACIÓN PRINCIPAL =====
document.addEventListener("DOMContentLoaded", function () {
  const contactForm = document.getElementById("contactForm");
  if (!contactForm) return;

  const phoneInput = document.getElementById("phone");
  if (phoneInput) applyPhoneMask(phoneInput);

  // Limpiar errores al interactuar
  contactForm.querySelectorAll("input, select, textarea").forEach(input => {
    input.addEventListener("input", () => clearFieldError(input));
    input.addEventListener("change", () => clearFieldError(input));
  });

  // ===== ENVÍO CON reCAPTCHA =====
  contactForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (!validateForm(contactForm)) {
      showAlert("Please fix the errors below.", "error");
      return;
    }

    const submitBtn = contactForm.querySelector('button[type="submit"]');
    const removeLoading = addLoadingState(submitBtn);

    try {
      // Ejecutar reCAPTCHA v3
      const token = await new Promise((resolve, reject) => {
        grecaptcha.ready(() => {
          grecaptcha.execute("6LcoRNwrAAAAAMtKWFfyC5SRwo9Jh8zWAIxvgAmc", { action: "contact" })
            .then(resolve)
            .catch(reject);
        });
      });

      // Preparar datos
      const formData = new FormData(contactForm);
      formData.append("g-recaptcha-response", token);

      // Enviar al backend
      const response = await fetch("/Broadway-Caterers/services/contact-submit.php", {
        method: "POST",
        body: formData,
      });

      const data = await response.json();

      if (data.success) {
        showAlert("Thank you! Mizrah Sharp will contact you shortly.", "success");
        contactForm.reset();
        // Limpiar todos los errores
        contactForm.querySelectorAll(".field-error, .service-error").forEach(el => el.remove());
      } else {
        showAlert(data.message || "Failed to send message. Please try again.", "error");
      }
    } catch (error) {
      console.error("Form submission error:", error);
      showAlert("An error occurred. Please try again.", "error");
    } finally {
      removeLoading();
    }
  });
});