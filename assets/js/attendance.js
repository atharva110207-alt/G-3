// Attendance Marker Interactions

document.addEventListener('DOMContentLoaded', () => {
  const markAllPresentBtn = document.getElementById('markAllPresent');
  const markAllAbsentBtn = document.getElementById('markAllAbsent');
  const attendanceRows = document.querySelectorAll('.attendance-row');

  if (markAllPresentBtn) {
    markAllPresentBtn.addEventListener('click', () => {
      document.querySelectorAll('input[type="radio"][value="Present"]').forEach(radio => {
        radio.checked = true;
        updateRowHighlight(radio);
      });
      updateCounters();
    });
  }

  if (markAllAbsentBtn) {
    markAllAbsentBtn.addEventListener('click', () => {
      document.querySelectorAll('input[type="radio"][value="Absent"]').forEach(radio => {
        radio.checked = true;
        updateRowHighlight(radio);
      });
      updateCounters();
    });
  }

  document.querySelectorAll('input[type="radio"][name^="status_"]').forEach(radio => {
    radio.addEventListener('change', (e) => {
      updateRowHighlight(e.target);
      updateCounters();
    });
  });

  function updateRowHighlight(radio) {
    const tr = radio.closest('tr');
    if (!tr) return;
    if (radio.value === 'Present') {
      tr.style.backgroundColor = 'rgba(22, 163, 74, 0.05)';
    } else {
      tr.style.backgroundColor = 'rgba(220, 38, 38, 0.05)';
    }
  }

  function updateCounters() {
    const presentCount = document.querySelectorAll('input[type="radio"][value="Present"]:checked').length;
    const absentCount = document.querySelectorAll('input[type="radio"][value="Absent"]:checked').length;
    
    const presentEl = document.getElementById('presentCountDisplay');
    const absentEl = document.getElementById('absentCountDisplay');
    if (presentEl) presentEl.textContent = presentCount;
    if (absentEl) absentEl.textContent = absentCount;
  }
  
  updateCounters();
});
