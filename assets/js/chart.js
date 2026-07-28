// Lightweight SVG Chart Renderer for Student & Class Analytics

function renderBarChart(containerId, labels, data, maxVal = 25) {
  const container = document.getElementById(containerId);
  if (!container) return;

  let svgHtml = `<svg width="100%" height="220" viewBox="0 0 500 220" style="background:transparent;">`;
  
  const barWidth = Math.floor(400 / data.length) - 10;
  const startX = 50;

  data.forEach((val, index) => {
    const barHeight = Math.floor((val / maxVal) * 160);
    const x = startX + index * (barWidth + 10);
    const y = 180 - barHeight;

    svgHtml += `
      <rect x="${x}" y="${y}" width="${barWidth}" height="${barHeight}" fill="#0284c7" rx="4" />
      <text x="${x + barWidth / 2}" y="${y - 8}" text-anchor="middle" fill="currentColor" font-size="11" font-weight="bold">${val}</text>
      <text x="${x + barWidth / 2}" y="200" text-anchor="middle" fill="currentColor" font-size="10">${labels[index] || ''}</text>
    `;
  });

  svgHtml += `</svg>`;
  container.innerHTML = svgHtml;
}
