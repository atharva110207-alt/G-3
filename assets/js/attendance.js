// Practical Assessment System - Attendance Toggle Helper JS
function toggleAllStudents(status) {
  const checkboxes = document.querySelectorAll('.att-toggle-checkbox');
  checkboxes.forEach(cb => {
    cb.checked = (status === 'Present');
    if (typeof updateToggleLabel === 'function') {
      updateToggleLabel(cb);
    }
  });
}
