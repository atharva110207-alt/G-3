// Smart Real-time Assessment Evaluation Calculation Engine (25 Marks per Experiment)

document.addEventListener('DOMContentLoaded', () => {
  const assessmentTable = document.querySelector('.assessment-table');

  if (!assessmentTable) return;

  function calculateRowTotal(row) {
    const regularitySelect = row.querySelector('.regularity-select');
    const conductionSelect = row.querySelector('.conduction-select');
    const outputSelect = row.querySelector('.output-select');
    const vivaSelect = row.querySelector('.viva-select');
    const totalDisplay = row.querySelector('.total-score-badge');
    const totalInput = row.querySelector('.total-score-input');

    if (!regularitySelect || !conductionSelect || !outputSelect || !vivaSelect || !totalDisplay) return;

    const rScore = parseInt(regularitySelect.value, 10) || 0;
    const cScore = parseInt(conductionSelect.value, 10) || 0;
    const oScore = parseInt(outputSelect.value, 10) || 0;
    const vScore = parseInt(vivaSelect.value, 10) || 0;

    const total = rScore + cScore + oScore + vScore;

    totalDisplay.textContent = total + ' / 25';
    if (totalInput) totalInput.value = total;

    // Update color styling badge dynamically
    totalDisplay.className = 'total-score-badge';
    if (total >= 22) {
      totalDisplay.classList.add('score-perfect');
    } else if (total >= 15) {
      totalDisplay.classList.add('score-warning');
    } else {
      totalDisplay.classList.add('score-danger');
    }
  }

  // Bind change events to all select dropdowns
  assessmentTable.querySelectorAll('tbody tr').forEach(row => {
    // Initial calculation on load
    calculateRowTotal(row);

    const selects = row.querySelectorAll('select');
    selects.forEach(select => {
      select.addEventListener('change', () => calculateRowTotal(row));
    });
  });
});
