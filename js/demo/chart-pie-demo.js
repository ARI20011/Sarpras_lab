// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#858796';

var dashboardData = window.dashboardChartData || {};
var pieLabels = Array.isArray(dashboardData.pieLabels) ? dashboardData.pieLabels : ["Belum Ada Data"];
var pieData = Array.isArray(dashboardData.pieData) ? dashboardData.pieData : [1];
var pieBgColors = ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'];
var pieHoverColors = ['#2e59d9', '#17a673', '#2c9faf', '#dda20a', '#be2617'];

// Pie Chart Example
var ctx = document.getElementById("myPieChart");
if (ctx) {
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: pieLabels,
      datasets: [{
        data: pieData,
        backgroundColor: pieBgColors.slice(0, pieLabels.length),
        hoverBackgroundColor: pieHoverColors.slice(0, pieLabels.length),
        hoverBorderColor: "rgba(234, 236, 244, 1)"
      }]
    },
    options: {
      maintainAspectRatio: false,
      tooltips: {
        backgroundColor: "rgb(255,255,255)",
        bodyFontColor: "#858796",
        borderColor: '#dddfeb',
        borderWidth: 1,
        xPadding: 15,
        yPadding: 15,
        displayColors: false,
        caretPadding: 10
      },
      legend: {
        display: false
      },
      cutoutPercentage: 70
    }
  });
}
