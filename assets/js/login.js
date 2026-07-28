// Login Form Validation & Quick Fill Helper

document.addEventListener('DOMContentLoaded', () => {
  const loginForm = document.getElementById('loginForm');
  const emailInput = document.getElementById('email');
  const passwordInput = document.getElementById('password');

  // Quick fill demo account credentials
  const demoButtons = document.querySelectorAll('.fill-demo-account');
  demoButtons.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const email = btn.getAttribute('data-email');
      const pass = btn.getAttribute('data-pass');
      if (emailInput && passwordInput) {
        emailInput.value = email;
        passwordInput.value = pass;
      }
    });
  });

  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      if (!emailInput.value.trim() || !passwordInput.value.trim()) {
        e.preventDefault();
        alert('Please fill in both email and password.');
      }
    });
  }
});
