/* SITE_DB is injected by the PHP page as a global variable */

var barchart_labels = [];
var barchart_data = [];
var line_labels = [];
var line_irradiance = [];
var line_ambtemp = [];
var line_pantemp = [];
var active_power = [];

var kpi_irradiance = [];
var kpi_prod = [];
var kpi_active_power = [];

//Get Card Data
$(function get_card_data(){

    $.ajax({
        type: "POST",
        url: "scripts/get_site_card_data.php",
        async: false,
        data: {
            "site_db": SITE_DB
        },
        success: function(data) {
            document.getElementById('active_power').innerHTML = data.active_power.toFixed(2) + " kW";

            if(data.daily_prod < 1000){
                document.getElementById('daily_prod').innerHTML = data.daily_prod.toFixed(2) + " kWh";
            }
            else{
                document.getElementById('daily_prod').innerHTML = (data.daily_prod/1000).toFixed(2) + " MWh";
            }

            if(data.monthly_prod < 1000){
                document.getElementById('monthly_prod').innerHTML = data.monthly_prod.toFixed(2) + " kWh";
            }
            else{
                document.getElementById('monthly_prod').innerHTML = (data.monthly_prod/1000).toFixed(2) + " MWh";
            }

            if(data.yearly_prod < 1000){
                document.getElementById('yearly_prod').innerHTML = data.yearly_prod.toFixed(2) + " kWh";
            }
            else{
                document.getElementById('yearly_prod').innerHTML = (data.yearly_prod/1000).toFixed(2) + " MWh";
            }

            document.getElementById('avg_irradiance').innerHTML = data.avg_irr + " W/m<sup>2<sup>";

            let sun_hours   = Math.floor(parseInt(data.sun_hours) / 60);
            let sun_minutes = parseInt(data.sun_hours) % 60;

            document.getElementById('sun_hours').innerHTML = sun_hours + " hours " + sun_minutes + " minutes ";
        }
    });
});


//Get Barchart Data
function get_barchart_data(date){

    $.ajax({
        type: "POST",
        url: "scripts/get_site_barchart.php",
        async: false,
        data: {
            "site_db": SITE_DB,
            "date": date
        },
        success: function(data) {
            for(var i = 0; i < data.length; i++){
                kpi_prod.push({"x": data[i].time, "y": data[i].production});
                barchart_labels.push(data[i].time);
                barchart_data.push(data[i].production);
            }
        }
    });
}

//Get Line Chart Data
function get_linechart_data(date){

    $.ajax({
        type: "POST",
        url: "scripts/get_site_irradiance.php",
        async: false,
        data: {
            "site_db": SITE_DB,
            "date": date
        },
        success: function(data) {
            for(var i = 0; i < data.length; i++){
                kpi_irradiance.push({"x": data[i].time, "y": data[i].irradiance});
                line_labels.push(data[i].time);
                line_irradiance.push(data[i].irradiance);
                line_ambtemp.push(data[i].ambient_temp);
                line_pantemp.push(data[i].panel_temp);
            }
        }
    });
}

function getActivePower(date){

    $.ajax({
        type: "POST",
        url: "scripts/get_site_active_power.php",
        async: false,
        data: {
            "site_db": SITE_DB,
            "date": date
        },
        success: function(data) {
            for(var i = 0; i < data.length; i++){
                kpi_active_power.push({"x": data[i].time, "y": data[i].active_power});
            }
        }
    });
}

// White background plugin
const white_back = {
  id: 'customCanvasBackgroundColor',
  beforeDraw: (chart, args, options) => {
    const {ctx} = chart;
    ctx.save();
    ctx.globalCompositeOperation = 'destination-over';
    ctx.fillStyle = options.color || 'white';
    ctx.fillRect(0, 0, chart.width, chart.height);
    ctx.restore();
  }
};

var kpi;

function renderKpiChart(){

    const zoomOptions = {
      pan: {
        enabled: true,
        modifierKey: 'ctrl',
      },
      zoom: {
        drag: {
          enabled: true
        },
        mode: 'x',
      },
    };

    const dataKPI = {
      labels: line_labels,
      datasets: [
      {
        type: 'line',
        label: 'Active Power (kW)',
        yAxisID: 'A',
        data: kpi_active_power,
        fill: false,
        backgroundColor: '#5cb7f2',
        borderColor: '#5cb7f2',
        tension: 0.4,
        pointRadius: 1,
      },
      {
        type: 'line',
        label: 'Irradiance (W/m2)',
        yAxisID: 'B',
        data: kpi_irradiance,
        fill: false,
        backgroundColor: '#F2BB46',
        borderColor: '#F2BB46',
        title: 'Irradiance (W/2)',
        tension: 0.4,
        pointRadius: 1,
      },
      {
        type: 'bar',
        label: 'Production (kWh)',
        yAxisID: 'C',
        data: kpi_prod,
        backgroundColor: '#CA5952',
        borderColor: '#CA5952',
        maxBarThickness: 35
      }
      ]
    };

    const configKPI = {
      data: dataKPI,
      options: {
        enabled: true,
        scales: {
            A: {
                type: 'linear',
                position: 'left',
                title: {
                    display: true,
                    text: 'Active Power (kW)',
                    font: { size: 17, weight: 'bold' }
                },
                grid:{ display:false }
            },
            B: {
                type: 'linear',
                position: 'right',
                title: {
                    display: true,
                    text: 'Irradiance (W/m2)',
                    font: { size: 17, weight: 'bold' }
                },
                grid:{ display:false }
            },
            C: {
                type: 'linear',
                position: 'left',
                title: {
                    display: true,
                    text: 'Production (kWh)',
                    font: { size: 17, weight: 'bold' }
                }
            },
            x: {
              title: {
                display: true,
                text: 'Time',
                font: { size: 13, weight:'bold' }
              },
              type: 'time',
              time: {
                parser: 'HH:mm:ss',
                unit: 'hour',
                tooltipFormat: 'HH:mm',
                displayFormats: { hour: 'HH:mm' }
              }
            }
        },
        plugins: {
            zoom: zoomOptions,
            legend: { position: 'bottom', display: true },
            tooltip: { enabled: true }
        },
        maintainAspectRatio: false,
        responsive: true,
      },
      plugins: [white_back],
    };

    let chartStatus = Chart.getChart("kpi");
    if (chartStatus != undefined) {
      chartStatus.destroy();
    }

    kpi = new Chart(
      document.getElementById('kpi'),
      configKPI
    );
}

function resetZoomBtn(){
    kpi.resetZoom();
}

function renderBarchart(){

    const dataBar = {
      labels: barchart_labels,
      datasets: [{
        label: 'Production (KWh)',
        data: barchart_data,
        backgroundColor: ['#CA5952'],
        borderColor: ['#CA5952']
      }]
    };

    const configBar = {
      type: 'bar',
      maxBarThickness: 30,
      data: dataBar,
      options: {
        scales: {
          x: {
            grid: { display: false },
            title: {
                display: true,
                text: 'Time',
                font: { size: 13, weight: 'bold' }
            },
            ticks:{ autoSkip: false }
          },
          y: {
            grid: { display: false },
            title: {
                display: true,
                text: 'Production(kWh)',
                font: { size: 13, weight: 'bold' }
            }
          }
        },
        maintainAspectRatio: false,
        responsive: true
      },
      plugins: [white_back],
    };

    let chartStatus = Chart.getChart("barChart");
    if (chartStatus != undefined) {
      chartStatus.destroy();
    }

    const barChart = new Chart(
      document.getElementById('barChart'),
      configBar
    );
}

function renderLineChart(){

    const dataLine = {
      labels: line_labels,
      datasets: [
      {
        type: 'line',
        label: 'Irradiance (W/m2)',
        yAxisID: 'A',
        fill: false,
        data: line_irradiance,
        backgroundColor: "#F2BB46",
        borderColor: "#F2BB46",
        tension: 0.4,
        pointRadius: 1,
      },
      {
        type: 'line',
        label: 'Ambient Temperature (°C)',
        yAxisID: 'B',
        fill: false,
        data: line_ambtemp,
        backgroundColor: "RGB(154,181,255)",
        borderColor: "RGB(154,181,255)",
        tension: 0.4,
        pointRadius: 1,
      },
      {
        type: 'line',
        label: 'Panel Temperature (°C)',
        yAxisID: 'B',
        fill: false,
        data: line_pantemp,
        backgroundColor: "#ff7a00",
        borderColor: "#ff7a00",
        tension: 0.4,
        pointRadius: 1,
      }]
    };

    const configLine = {
      type: 'line',
      data: dataLine,
      options: {
        enabled: true,
        scales: {
            A: {
                type: 'linear',
                position: 'left',
                title: {
                    display: true,
                    text: 'Irradiance (kWh/m2)',
                    font: { size: 13, weight: 'bold' }
                },
                grid: { display: false }
            },
            B: {
                type: 'linear',
                position: 'right',
                title: {
                    display: true,
                    text: 'Temperature (°C)',
                    font: { size: 13, weight: 'bold' }
                },
                grid: { display: false }
            },
            x: {
              title: {
                display: true,
                text: 'Time',
                font: { size: 13, weight: 'bold' }
              },
              type: 'time',
              time: {
                parser: 'HH:mm:ss',
                unit: 'hour',
                tooltipFormat: 'HH:mm',
                displayFormats: { hour: 'HH:mm' }
              }
            }
        },
        maintainAspectRatio: false,
        responsive: true,
        interaction: { mode: 'index' },
      },
      plugins: [white_back],
    };

    let chartStatus = Chart.getChart("lineChart");
    if (chartStatus != undefined) {
      chartStatus.destroy();
    }
    const lineChart = new Chart(
      document.getElementById('lineChart'),
      configLine
    );
}

var date = new Date();
var currentDate = date.toISOString().substring(0, 10);
document.getElementById('calendar').value = currentDate;

const prevDate = document.getElementById("prevDate");
const nextDate = document.getElementById("nextDate");

prevDate.addEventListener('click', () => {
    let val = new Date(document.getElementById('calendar').value);
    val.setDate(val.getDate() - 1);
    let calendar_date = val.toISOString().substring(0, 10);
    document.getElementById('calendar').value = calendar_date;
    render();
});

nextDate.addEventListener('click', () => {
    let val = new Date(document.getElementById('calendar').value);
    val.setDate(val.getDate() + 1);
    let calendar_date = val.toISOString().substring(0, 10);
    if(calendar_date <= currentDate){
        document.getElementById('calendar').value = calendar_date;
        render();
    }
});

function render(){
    let val = new Date(document.getElementById('calendar').value);
    let db_date = val.toISOString().split('T')[0];

    barchart_labels = [];
    barchart_data   = [];
    line_labels     = [];
    line_irradiance = [];
    line_ambtemp    = [];
    line_pantemp    = [];
    active_power    = [];

    kpi_irradiance   = [];
    kpi_prod         = [];
    kpi_active_power = [];

    get_barchart_data(db_date);
    get_linechart_data(db_date);
    getActivePower(db_date);

    renderKpiChart();
    renderBarchart();
    renderLineChart();
}

$(document).ready(function() {
    var val  = new Date(document.getElementById('calendar').value);
    let date = val.toISOString().split('T')[0];

    get_barchart_data(date);
    get_linechart_data(date);
    getActivePower(date);
    renderKpiChart();
    renderBarchart();
    renderLineChart();
});

// Chart download helpers
function downloadKPI() {
    const imageLink = document.createElement('a');
    const canvas = document.getElementById('kpi');
    imageLink.download = 'kpi.png';
    imageLink.href = canvas.toDataURL('image/png', 1);
    imageLink.click();
}

function downloadBar() {
    const imageLink = document.createElement('a');
    const canvas = document.getElementById('barChart');
    imageLink.download = 'total-production.png';
    imageLink.href = canvas.toDataURL('image/png', 1);
    imageLink.click();
}

function downloadWeather() {
    const imageLink = document.createElement('a');
    const canvas = document.getElementById('lineChart');
    imageLink.download = 'weather.png';
    imageLink.href = canvas.toDataURL('image/png', 1);
    imageLink.click();
}
