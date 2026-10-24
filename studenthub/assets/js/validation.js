/**
 * StudentHub Portal - CHARUSAT FTE
 * Practical 5: Registration Form with Frontend Validation and User-Friendly Error Handling
 * Intermediate: Real-time validation on keyup/change
 * Advanced: Custom CAPTCHA using HTML5 Canvas
 */

document.addEventListener('DOMContentLoaded', () => {
  initRegistrationValidation();
  initCanvasCaptcha();
});

let currentCaptchaCode = '';

/* -------------------------------------------------------------------------
   1. Canvas CAPTCHA Generator (Practical 5 Advanced Extension)
------------------------------------------------------------------------- */
function initCanvasCaptcha() {
  const canvas = document.getElementById('captchaCanvas');
  const refreshBtn = document.getElementById('refreshCaptchaBtn');
  if (!canvas) return;

  generateCaptcha(canvas);

  if (refreshBtn) {
    refreshBtn.addEventListener('click', (e) => {
      e.preventDefault();
      generateCaptcha(canvas);
    });
  }
}

function generateCaptcha(canvas) {
  const ctx = canvas.getContext('2d');
  const chars = '23456789ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz';
  let code = '';
  for (let i = 0; i < 6; i++) {
    code += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  currentCaptchaCode = code;

  // Clear canvas
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  // Background tint
  ctx.fillStyle = '#f1f5f9';
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  // Background noise lines
  for (let i = 0; i < 6; i++) {
    ctx.strokeStyle = `hsl(${Math.random() * 360}, 50%, 75%)`;
    ctx.beginPath();
    ctx.moveTo(Math.random() * canvas.width, Math.random() * canvas.height);
    ctx.lineTo(Math.random() * canvas.width, Math.random() * canvas.height);
    ctx.lineWidth = 1.5;
    ctx.stroke();
  }

  // Draw characters with random rotation and colors
  for (let i = 0; i < code.length; i++) {
    ctx.save();
    const char = code[i];
    ctx.font = 'bold 24px monospace';
    ctx.fillStyle = `hsl(${Math.random() * 360}, 70%, 30%)`;
    const x = 20 + i * 22;
    const y = 30 + (Math.random() * 8 - 4);
    const angle = (Math.random() * 30 - 15) * (Math.PI / 180);
    ctx.translate(x, y);
    ctx.rotate(angle);
    ctx.fillText(char, 0, 0);
    ctx.restore();
  }

  // Dots noise
  for (let i = 0; i < 30; i++) {
    ctx.fillStyle = `rgba(0,0,0,0.15)`;
    ctx.beginPath();
    ctx.arc(Math.random() * canvas.width, Math.random() * canvas.height, 1.5, 0, Math.PI * 2);
    ctx.fill();
  }
}

/* -------------------------------------------------------------------------
   2. Form Validation & Password Strength (Practical 5)
------------------------------------------------------------------------- */
function initRegistrationValidation() {
  const form = document.getElementById('registrationForm');
  if (!form) return;

  const fullNameInput = document.getElementById('full_name');
  const emailInput = document.getElementById('email');
  const mobileInput = document.getElementById('mobile');
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('confirm_password');
  const courseInput = document.getElementById('course');
  const yearInput = document.getElementById('year_of_study');
  const termsInput = document.getElementById('terms');
  const captchaInput = document.getElementById('captcha');

  // Real-time validation listeners
  if (fullNameInput) fullNameInput.addEventListener('input', () => validateFullName(fullNameInput));
  if (emailInput) emailInput.addEventListener('input', () => validateEmail(emailInput));
  if (mobileInput) mobileInput.addEventListener('input', () => validateMobile(mobileInput));
  if (passwordInput) {
    passwordInput.addEventListener('input', () => {
      validatePassword(passwordInput);
      updatePasswordStrength(passwordInput.value);
      if (confirmPasswordInput && confirmPasswordInput.value) {
        validateConfirmPassword(passwordInput, confirmPasswordInput);
      }
    });
  }
  if (confirmPasswordInput) {
    confirmPasswordInput.addEventListener('input', () => validateConfirmPassword(passwordInput, confirmPasswordInput));
  }
  if (courseInput) courseInput.addEventListener('change', () => validateSelect(courseInput, 'Please select your department/course.'));
  if (yearInput) yearInput.addEventListener('change', () => validateSelect(yearInput, 'Please select your academic year.'));

  // Form submit intercept
  form.addEventListener('submit', (e) => {
    let isValid = true;

    if (!validateFullName(fullNameInput)) isValid = false;
    if (!validateEmail(emailInput)) isValid = false;
    if (!validateMobile(mobileInput)) isValid = false;
    if (!validatePassword(passwordInput)) isValid = false;
    if (!validateConfirmPassword(passwordInput, confirmPasswordInput)) isValid = false;
    if (!validateSelect(courseInput, 'Please select your department/course.')) isValid = false;
    if (!validateSelect(yearInput, 'Please select your academic year.')) isValid = false;
    if (!validateGender()) isValid = false;
    if (!validateTerms(termsInput)) isValid = false;
    if (!validateCaptcha(captchaInput)) isValid = false;

    if (!isValid) {
      e.preventDefault();
      // Scroll to first error
      const firstError = form.querySelector('.is-invalid, .has-error');
      if (firstError) {
        firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
      }
    }
  });
}

function setFieldError(field, errorId, message) {
  field.classList.add('is-invalid');
  field.classList.remove('is-valid');
  const errEl = document.getElementById(errorId);
  if (errEl) {
    errEl.textContent = message;
    errEl.style.display = 'block';
  }
}

function setFieldSuccess(field, errorId) {
  field.classList.remove('is-invalid');
  field.classList.add('is-valid');
  const errEl = document.getElementById(errorId);
  if (errEl) {
    errEl.textContent = '';
    errEl.style.display = 'none';
  }
}

function validateFullName(field) {
  if (!field) return true;
  const val = field.value.trim();
  const nameRegex = /^[a-zA-Z\s.]{3,50}$/;
  if (!val) {
    setFieldError(field, 'fullNameError', 'Full name is required.');
    return false;
  }
  if (!nameRegex.test(val)) {
    setFieldError(field, 'fullNameError', 'Full name must contain only letters and be at least 3 characters.');
    return false;
  }
  setFieldSuccess(field, 'fullNameError');
  return true;
}

function validateEmail(field) {
  if (!field) return true;
  const val = field.value.trim();
  const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (!val) {
    setFieldError(field, 'emailError', 'Email address is required.');
    return false;
  }
  if (!emailRegex.test(val)) {
    setFieldError(field, 'emailError', 'Please enter a valid email address (e.g., student@charusat.edu.in).');
    return false;
  }
  setFieldSuccess(field, 'emailError');
  return true;
}

function validateMobile(field) {
  if (!field) return true;
  const val = field.value.trim();
  const mobileRegex = /^[6-9]\d{9}$/;
  if (!val) {
    setFieldError(field, 'mobileError', 'Mobile number is required.');
    return false;
  }
  if (!mobileRegex.test(val)) {
    setFieldError(field, 'mobileError', 'Enter a valid 10-digit mobile number starting with 6, 7, 8, or 9.');
    return false;
  }
  setFieldSuccess(field, 'mobileError');
  return true;
}

function validatePassword(field) {
  if (!field) return true;
  const val = field.value;
  // Min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char
  const passRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{8,}$/;
  if (!val) {
    setFieldError(field, 'passwordError', 'Password is required.');
    return false;
  }
  if (!passRegex.test(val)) {
    setFieldError(field, 'passwordError', 'Min 8 chars, uppercase, lowercase, number & special symbol (@$!%*?&#).');
    return false;
  }
  setFieldSuccess(field, 'passwordError');
  return true;
}

function validateConfirmPassword(passField, confirmField) {
  if (!confirmField) return true;
  const val = confirmField.value;
  if (!val) {
    setFieldError(confirmField, 'confirmPasswordError', 'Please confirm your password.');
    return false;
  }
  if (val !== passField.value) {
    setFieldError(confirmField, 'confirmPasswordError', 'Passwords do not match.');
    return false;
  }
  setFieldSuccess(confirmField, 'confirmPasswordError');
  return true;
}

function validateSelect(field, errorMsg) {
  if (!field) return true;
  if (!field.value) {
    setFieldError(field, field.id + 'Error', errorMsg);
    return false;
  }
  setFieldSuccess(field, field.id + 'Error');
  return true;
}

function validateGender() {
  const genderRadios = document.querySelectorAll('input[name="gender"]');
  const errorEl = document.getElementById('genderError');
  let checked = false;
  genderRadios.forEach((r) => { if (r.checked) checked = true; });
  if (!checked) {
    if (errorEl) {
      errorEl.textContent = 'Please select your gender.';
      errorEl.style.display = 'block';
    }
    return false;
  }
  if (errorEl) errorEl.style.display = 'none';
  return true;
}

function validateTerms(field) {
  if (!field) return true;
  const errorEl = document.getElementById('termsError');
  if (!field.checked) {
    if (errorEl) {
      errorEl.textContent = 'You must accept the terms and conditions.';
      errorEl.style.display = 'block';
    }
    return false;
  }
  if (errorEl) errorEl.style.display = 'none';
  return true;
}

function validateCaptcha(field) {
  if (!field) return true;
  const val = field.value.trim();
  if (!val) {
    setFieldError(field, 'captchaError', 'Please enter the CAPTCHA code.');
    return false;
  }
  if (val.toLowerCase() !== currentCaptchaCode.toLowerCase()) {
    setFieldError(field, 'captchaError', 'Incorrect CAPTCHA code. Please try again.');
    const canvas = document.getElementById('captchaCanvas');
    if (canvas) generateCaptcha(canvas);
    field.value = '';
    return false;
  }
  setFieldSuccess(field, 'captchaError');
  return true;
}

/* -------------------------------------------------------------------------
   3. Password Strength Meter Calculation
------------------------------------------------------------------------- */
function updatePasswordStrength(password) {
  const fill = document.getElementById('passwordMeterFill');
  const label = document.getElementById('passwordMeterLabel');
  if (!fill || !label) return;

  let score = 0;
  if (password.length >= 8) score += 25;
  if (/[A-Z]/.test(password)) score += 25;
  if (/[0-9]/.test(password)) score += 25;
  if (/[^A-Za-z0-9]/.test(password)) score += 25;

  fill.style.width = score + '%';

  if (score <= 25) {
    fill.style.backgroundColor = '#ef4444';
    label.textContent = 'Strength: Weak';
    label.style.color = '#ef4444';
  } else if (score <= 50) {
    fill.style.backgroundColor = '#f97316';
    label.textContent = 'Strength: Fair';
    label.style.color = '#f97316';
  } else if (score <= 75) {
    fill.style.backgroundColor = '#eab308';
    label.textContent = 'Strength: Good';
    label.style.color = '#ca8a04';
  } else {
    fill.style.backgroundColor = '#10b981';
    label.textContent = 'Strength: Strong';
    label.style.color = '#10b981';
  }
}
