function switchTab(evt, tabId) {
    const tabContents = document.getElementsByClassName("tab-content");
    for (let i = 0; i < tabContents.length; i++) { tabContents[i].classList.remove("active"); }
    const tabBtns = document.getElementsByClassName("tab-btn");
    for (let i = 0; i < tabBtns.length; i++) { tabBtns[i].classList.remove("active"); }
    document.getElementById(tabId).classList.add("active");
    evt.currentTarget.classList.add("active");
}

document.addEventListener("DOMContentLoaded", function() {
    const chartCanvas = document.getElementById('progressChart');
    if (chartCanvas) {
        const labels = JSON.parse(chartCanvas.dataset.labels || '[]');
        const weight = JSON.parse(chartCanvas.dataset.weight || '[]');
        const fat = JSON.parse(chartCanvas.dataset.fat || '[]');

        const ctx = chartCanvas.getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Weight (kg)',
                        data: weight,
                        borderColor: '#ccff00',
                        backgroundColor: 'rgba(204, 255, 0, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Body Fat (%)',
                        data: fat,
                        borderColor: '#a855f7',
                        backgroundColor: 'rgba(168, 85, 247, 0.1)',
                        borderWidth: 2,
                        tension: 0.3,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { color: '#313131' }, ticks: { color: '#ffffff', font: { family: 'Inter' } } },
                    y: { type: 'linear', display: true, position: 'left', title: { display: true, text: 'Weight (kg)', color: '#ccff00', font: { family: 'Inter', weight: 'bold' } }, grid: { color: '#313131' }, ticks: { color: '#ffffff' } },
                    y1: { type: 'linear', display: true, position: 'right', title: { display: true, text: 'Body Fat (%)', color: '#a855f7', font: { family: 'Inter', weight: 'bold' } }, grid: { drawOnChartArea: false }, ticks: { color: '#ffffff' } }
                },
                plugins: { legend: { labels: { color: '#ffffff', font: { family: 'Inter', size: 12 } } } }
            }
        });
    }
});
