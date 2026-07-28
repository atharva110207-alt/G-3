// Practical Assessment System - Rubric Evaluation JS Helper
function updateRubricTotal(row) {
  const reg = parseInt(row.querySelector('.score-reg').value) || 0;
  const cond = parseInt(row.querySelector('.score-cond').value) || 0;
  const out = parseInt(row.querySelector('.score-out').value) || 0;
  const viva = parseInt(row.querySelector('.score-viva').value) || 0;

  const total = reg + cond + out + viva;
  const badge = row.querySelector('.row-total-badge');
  if (badge) {
    badge.innerText = total + " / 25";
  }
}
