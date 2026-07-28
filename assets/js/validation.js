// Form Input Validation & Sanity Checks

function validateScoreLimits(input, min, max) {
  const val = parseInt(input.value, 10);
  if (isNaN(val) || val < min || val > max) {
    alert(`Value must be between ${min} and ${max}`);
    input.value = Math.max(min, Math.min(max, val || min));
    return false;
  }
  return true;
}
