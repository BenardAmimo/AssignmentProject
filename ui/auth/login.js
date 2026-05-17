/* ============================================================
   login.js — Form Logic for Login Page
   Greenfield Institute Course Registration System
   ============================================================ */

// ── DOM References ──────────────────────────────────────────
const form      = document.getElementById('loginForm');
const alertBox  = document.getElementById('alert');
const submitBtn = document.getElementById('submitBtn');
const spinner   = document.getElementById('spinner');
const btnText   = document.getElementById('btnText');

// ── API Base ─────────────────────────────────────────────────
// The shared API helper is loaded from ../../js/api.js and defines postData/getData.

// ── Show / Hide Password Toggle ─────────────────────────────
document.querySelectorAll('.toggle-pw').forEach(btn => {

  btn.addEventListener('click', () => {

    const input = document.getElementById(btn.dataset.target);

    const isHidden = input.type === 'password';

    input.type = isHidden ? 'text' : 'password';

    btn.textContent = isHidden ? 'Hide' : 'Show';
  });
});

// ── Alert Helpers ────────────────────────────────────────────
function showAlert(type, message) {

  alertBox.className = `alert ${type}`;
  alertBox.textContent = message;

  alertBox.scrollIntoView({
    behavior: 'smooth',
    block: 'nearest'
  });
}

function clearAlert() {

  alertBox.className = 'alert';
  alertBox.textContent = '';
}

// ── Client-Side Validation ──────────────────────────────────
function validateForm(data) {

  if (!data.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
    return 'Please enter a valid email address.';
  }

  if (!data.password || data.password.length < 1) {
    return 'Please enter your password.';
  }

  return null;
}

// ── Loading State ────────────────────────────────────────────
function setLoading(isLoading) {

  submitBtn.disabled = isLoading;

  spinner.style.display = isLoading ? 'block' : 'none';

  btnText.textContent = isLoading
    ? 'Signing in…'
    : 'Sign In →';
}

// ── Redirect Based On Role ──────────────────────────────────
function redirectByRole(role) {

  if (role === 'admin') {

    window.location.href = '../admin/dashboard.html';

  } else {

    window.location.href = '../students/dashboard.html';
  }
}

// ── Form Submit Handler ─────────────────────────────────────
form.addEventListener('submit', async (e) => {

  e.preventDefault();

  clearAlert();

  // Collect form data
  const data = {

    email: document.getElementById('email')
      .value
      .trim()
      .toLowerCase(),

    password: document.getElementById('password').value
  };

  // Validate first
  const clientError = validateForm(data);

  if (clientError) {

    showAlert('error', clientError);
    return;
  }

  // Loading state
  setLoading(true);

  try {

    // Send request to controller
    const result = await postData('login.php', data);

    // Success
    if (result.success) {

      showAlert(
        'success',
        `✓ Welcome back, ${result.user.full_name}! Redirecting…`
      );

      setTimeout(() => {

        redirectByRole(result.user.role);

      }, 1200);

    } else {

      showAlert(
        'error',
        result.message || 'Invalid email or password.'
      );
    }

  } catch (err) {

    console.error('Login error:', err);

    showAlert(
      'error',
      'Network error. Please check your connection and try again.'
    );

  } finally {

    setLoading(false);
  }
});