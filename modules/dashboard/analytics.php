<?php
// Class Analytics & Visual Progress

$page_title = 'Class Analytics';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

require_login();

include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Class Practical Performance Analytics</h2>
    </div>

    <div style="margin-bottom: 2rem;">
        <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: 1rem;">Average Marks Distribution per Experiment (0-25)</h3>
        <div id="analyticsChartContainer" style="background-color: var(--bg-primary); padding: 1.5rem; border-radius: var(--radius-md);"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Render sample distribution SVG chart
    renderBarChart('analyticsChartContainer', ['Exp 1', 'Exp 2', 'Exp 3', 'Exp 4', 'Exp 5'], [23.5, 21.0, 24.2, 19.8, 22.4], 25);
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
