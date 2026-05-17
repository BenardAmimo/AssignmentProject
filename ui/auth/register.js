/* ============================================================
   register.js — Form Logic for Registration Page
   Greenfield Institute Course Registration System
   ============================================================ */
console.log("REGISTER JS IS LOADED");
// ── DOM References ─────────────────────────────────────────────────────────────
const form      = document.getElementById('registerForm');
const alertBox  = document.getElementById('alert');
const submitBtn = document.getElementById('submitBtn');
const spinner   = document.getElementById('spinner');
const btnText   = document.getElementById('btnText');

// ── Show / Hide Password Toggle ────────────────────────────────────────────────
document.querySelectorAll('.toggle-pw').forEach(btn => {
  btn.addEventListener('click', () => {
    const input = document.getElementById(btn.dataset.target);
    const isHidden = input.type === 'password';
    input.type       = isHidden ? 'text' : 'password';
    btn.textContent  = isHidden ? 'Hide' : 'Show';
  });
});

// ── Alert Helpers ──────────────────────────────────────────────────────────────
function showAlert(type, messages) {
  alertBox.className = `alert ${type}`;

  if (Array.isArray(messages) && messages.length > 1) {
    alertBox.innerHTML = '<ul>' + messages.map(m => `<li>${m}</li>`).join('') + '</ul>';
  } else {
    alertBox.textContent = Array.isArray(messages) ? messages[0] : messages;
  }

  alertBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function clearAlert() {
  alertBox.className   = 'alert';
  alertBox.textContent = '';
}

// ── Client-Side Validation ─────────────────────────────────────────────────────
function validateForm(data) {
  const errors = [];

  if (data.full_name.trim().length < 3) {
    errors.push('Full name must be at least 3 characters.');
  }

  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
    errors.push('Please enter a valid email address.');
  }

  if (!['student', 'admin'].includes(data.role)) {
    errors.push('Please choose a valid role.');
  }

  if (data.role === 'admin' && !data.email.includes('admin@')) {
    errors.push('Admin registration requires your email to contain admin@ (e.g., benardadmin@gmail.com).');
  }

  if (data.password.length < 8) {
    errors.push('Password must be at least 8 characters.');
  } else {
    if (!/[A-Z]/.test(data.password)) {
      errors.push('Password must contain at least one uppercase letter.');
    }
    if (!/[0-9]/.test(data.password)) {
      errors.push('Password must contain at least one number.');
    }
  }

  if (data.password !== data.confirm) {
    errors.push('Passwords do not match.');
  }

  return errors;
}

// ── Loading State Helpers ──────────────────────────────────────────────────────
function setLoading(isLoading) {
  submitBtn.disabled      = isLoading;
  spinner.style.display   = isLoading ? 'block' : 'none';
  btnText.textContent     = isLoading ? 'Creating account…' : 'Create My Account →';
}

// ── Form Submit Handler ────────────────────────────────────────────────────────
form.addEventListener('submit', async (e) => {
  e.preventDefault();
  clearAlert();

  // Collect form data
  const data = {
    full_name: document.getElementById('full_name').value,
    email:     document.getElementById('email').value.trim().toLowerCase(),
    role:      document.getElementById('role').value,
    password:  document.getElementById('password').value,
    confirm:   document.getElementById('confirm').value,
  };

  // Run client-side validation first (fast feedback)
  const clientErrors = validateForm(data);
  if (clientErrors.length > 0) {
    showAlert('error', clientErrors);
    return;
  }

  // Send to business logic layer (PHP)
  setLoading(true);

  try {
    const result = await postData('register.php', data);

    if (result.success) {
      showAlert('success', '✓ Account created! Redirecting to your dashboard…');
      form.reset();
      setTimeout(() => {
        if (result.user.role === 'admin') {
          window.location.href = '../admin/dashboard.html';
        } else {
          window.location.href = '../students/courses.html';
        }
      }, 1500);
    } else {
      // Show errors returned from server
      showAlert('error', result.errors ?? [result.message ?? 'Registration failed.']);
    }

  } catch (err) {
    console.error('Registration error:', err);
    showAlert('error', 'Network error. Please check your connection and try again.');
  } finally {
    setLoading(false);
  }
});