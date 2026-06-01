var page = document.getElementById("plant_report_block");
    if (page) page.style.display = "none";

    var pdfbtn = document.getElementById("cmd");
    if (pdfbtn) pdfbtn.style.visibility = "hidden";

    //Check if it is a number
    function isNumeric(str) {
      if (typeof str != "string") return false // we only process strings!  
      return !isNaN(str) && // use type coercion to parse the _entirety_ of the string (`parseFloat` alone does not do this)...
             !isNaN(parseFloat(str)) // ...and ensure strings of whitespace fail
    }

    /** After #plant_report_block is shown, chart parents may have been display:none — sync canvas to layout */
    function resizePlantCharts() {
        if (typeof Chart === "undefined") return;
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                ["barChart", "barChart2"].forEach(function (id) {
                    var c = Chart.getChart(id);
                    if (c) c.resize();
                });
            });
        });
    }

    function updatePlantReportTitle() {
        var select = document.getElementById('site_opt');
        var output = document.getElementById('output');
        if (!select || !output) return;
        var opt = select.options[select.selectedIndex];
        output.textContent = (opt && opt.value) ? opt.textContent.trim() : '';
    }

    var plantChartInteraction = {
        mode: 'index'
    };
    
    //Change Date Format
    function convertDate(dateString){
        var p = dateString.split(/\D/g)
        return [p[2],p[1],p[0] ].join("-")
    }
    
    
    function loadPlantSitesIntoSelect() {
        var select = document.getElementById("site_opt");
        if (!select) return;

        fetch("scripts/get_all_sites", {
            method: "POST",
            credentials: "same-origin",
            headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" }
        })
            .then(function (res) {
                if (!res.ok) throw new Error(res.status + " " + res.statusText);
                return res.json();
            })
            .then(function (data) {
                var raw = data && data.data;
                var sites = Array.isArray(raw) ? raw : [];

                while (select.options.length > 1) {
                    select.remove(1);
                }

                for (var i = 0; i < sites.length; i++) {
                    var s = sites[i];
                    var option = document.createElement("option");
                    var id = s.id != null ? s.id : s[0];
                    var name = s.site_name != null ? s.site_name : s[1];
                    option.value = id === undefined || id === null ? "" : String(id);
                    option.textContent = name != null ? String(name) : "";
                    select.appendChild(option);
                }
            })
            .catch(function (err) {
                console.error("get_all_sites failed:", err);
            });
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", loadPlantSitesIntoSelect);
    } else {
        loadPlantSitesIntoSelect();
    }

    function numberWithSpaces(x) {
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, " ");
        return parts.join(".");
    }
    
    function query(){
        var queryBtn = document.getElementById('preview_btn');
        
        let str_date = document.getElementById("endDate").value;
        var currentDate = new Date(str_date);
        
        var month = currentDate.toLocaleString('default', { month: 'long' });
        var year = currentDate.getFullYear();
        var lastyear = currentDate.getFullYear() - 1;
        
        
        document.getElementById("monthyear").innerHTML = month + " " + year;
        document.getElementById("prod-monthyear").innerHTML = month + " " + lastyear;
        // document.getElementById("proj-monthyear").innerHTML = month + " " + year;
        document.getElementById("rec-monthyear").innerHTML = month + " " + year;

        
        var siteOpt = document.getElementById("site_opt");
        var site_id = siteOpt ? siteOpt.value : "";
        if (!site_id || String(site_id).trim() === "") {
            EES.alert("Please choose a plant from the list.", "warning");
            return;
        }

        updatePlantReportTitle();
        
        if(!document.getElementById("startDate").value || !document.getElementById("endDate").value) {
            EES.alert('Starting and Ending Date is Missing!', 'warning');
        }
        else if(document.getElementById("budget_prod_input").value == "" || !isNumeric(document.getElementById("budget_prod_input").value)){
            EES.alert('Budgeted production value is missing or is not a number!', 'warning');
        }
        else{
            EES.btnLoad(queryBtn, 'Loading…');
            page.style.display = 'block';
            pdfbtn.style.visibility = 'visible';
            
            var budget_prod = document.getElementById("budget_prod_input").value;
            document.getElementById("plant_budget_prod").innerHTML = numberWithSpaces(budget_prod);
            
            var start_date = document.getElementById("startDate").value;
            var end_date = document.getElementById("endDate").value;
            
            $.ajax({
                type: "POST",
                url: "scripts/get_plant_total_prod",
                dataType: 'json',
                headers: { "X-Requested-With": "XMLHttpRequest" },
                data: {
                    "site_id": site_id,
                    "start_date": start_date,
                    "end_date": end_date
                    
                },
                success: function(data) {
                    EES.btnReset(queryBtn);
                    if (!data || data.status === 'Err') {
                        if (data && data.message) EES.alert(data.message, "warning");
                        return;
                    }
                    document.getElementById("plant_total_prod").innerHTML = numberWithSpaces(data.prod);
                    document.getElementById("plant_total_prod2").innerHTML = numberWithSpaces(data.prod);
                    
                    var deviation = data.prod > 0
                        ? ((data.prod - parseFloat(budget_prod)) / data.prod) * 100
                        : 0;
                    
                    document.getElementById("deviation").innerHTML = deviation.toFixed(2) + " %";
                    
                    document.getElementById("plant_total_insolation").innerHTML = numberWithSpaces(data.insolation) ;
                    document.getElementById("plant_pr").innerHTML = numberWithSpaces(data.pr) + "%";
                    document.getElementById("plant_co2_avoided").innerHTML = numberWithSpaces(data.co2);
                },
                error: function() { EES.btnReset(queryBtn); }
            });
            
            // var textarea = document.getElementById("comments_input");
            // var p = document.getElementById("comments_output");
            // var text =textarea.value;
            // p.textContent=text;
            
            document.getElementById("comments_output").textContent = document.getElementById("comments_input").value;
            
            get_day_values(site_id, start_date, end_date);
 
            
        }
    }
    
    var chart_date = []
    var daily_prod = [];
    var daily_ins = [];
    var daily_pr = [];
    
    function get_day_values(site_id, start_date, end_date){
        
        chart_date = []
        daily_prod = [];
        daily_ins = [];
        daily_pr = [];
        
        $("#plantTable tbody tr").remove(); 
        
        $.ajax({
                type: "POST",
                url: "scripts/get_plant_daily",
                dataType: 'json',
                headers: { "X-Requested-With": "XMLHttpRequest" },
                data: {
                    "site_id": site_id,
                    "start_date": start_date,
                    "end_date": end_date
                },
                success: function(data) {
                    if (!data || data.status === 'Err' || !Array.isArray(data)) {
                        if (data && data.message) EES.alert(data.message, "warning");
                        return;
                    }
                    for (var i = 0; i < data.length; i++){
                        
                            var new_date = convertDate(data[i]['date']);
                            var row = "<tr><td>" + new_date +"</td><td>"+ numberWithSpaces(data[i]['prod']) +"</td><td>"+ data[i]['insolation'] +"</td><td>"+ data[i]['pr'] +"</td></tr>";
                            $('#plantTable tbody').append(row);
                            
                            chart_date.push(new_date);
                            daily_prod.push(parseFloat(data[i]['prod']) || 0);
                            daily_ins.push(parseFloat(data[i]['insolation']) || 0);
                            daily_pr.push(parseFloat(data[i]['pr']) || 0);

                    }
                    
                    const dataBar = {
                      labels: chart_date,
                      datasets: [{
                        type: 'line',
                        label: 'Insolation (kWh/m2)',
                        yAxisID: 'A',
                        data: daily_ins,
                        fill: false,
                        backgroundColor: '#F2BB46',
                        borderColor: '#F2BB46',
                        title: 'Insolation (kWh/2)',
                        tension: 0.4,
                        pointRadius: 0       
                      },
                      {
                        type: 'line',
                        label: 'PR (%)',
                        yAxisID: 'B',
                        data: daily_pr,
                        fill: false,
                        backgroundColor: '#7CAF57',
                        borderColor: '#7CAF57',
                        tension: 0.4,
                        pointRadius: 0
                      },
                        {
                        type: 'bar',
                        label: 'Production (kWh)',
                        yAxisID: 'C',
                        data: daily_prod,
                        backgroundColor: '#CA5952',
                        maxBarThickness: 35
                      }  ]
                    };
                    
                    const plugin = {
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
                    // config 
                    
                    const configBar = {
                      data: dataBar,
                      options: {
                        enabled: true,
                        scales: {       
                            A: {
                                type: 'linear',
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Insolation (kWh/m2)',
                                    font: {
                                        size: 17,
                                        weight: 'bold'
                                    }
                                },
                                min: 0,
                                grid: {
                            	   display: false
                            	}  
                            },
                            B: {
                                type: 'linear',
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'PR (%)',
                                    font: {
                                        size: 17,
                                        weight: 'bold'
                                    }                
                                },
                                min: 0,
                                grid: {
                            	   display: false
                            	}
                            },
                            C: {
                                type: 'linear',
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Production (kWh)',
                                    font: {
                                        size: 17,
                                        weight: 'bold'
                                    }
                                },
                                grid: {
                            	   display: false
                            	}
                            },
                            x: {
                              title: {
                                display: true,
                                text: 'Day',
                                font: {
                                    size: 17,
                                }            
                              }        
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                display: true,
                            }
                        },
                        maintainAspectRatio:false,
                        responsive: true,
                        interaction: plantChartInteraction,
                      },
                      plugins: [plugin],
                    };
                    
                    let chartStatus = Chart.getChart("barChart"); // <canvas> id
                    if (chartStatus != undefined) {
                      chartStatus.destroy();
                    }
                    
                    // render init block
                    const barChart = new Chart(
                      document.getElementById('barChart'),
                      configBar
                    );
                    resizePlantCharts();
                    
                }
        });
        
        
        get_historical(site_id, end_date);
        
    }
    
    
    function get_historical(site_id, end_date){
        
        var h_year = [];
        var h_production = [];
        var h_insolation = [];
        
        $.ajax({
            type: "POST",
            url: "scripts/get_plant_historical",
            dataType: 'json',
            headers: { "X-Requested-With": "XMLHttpRequest" },
            data: {
                "site_id": site_id,
                "end_date": end_date
            },
            success: function(data) {
                if (!data || data.status === 'Err' || !Array.isArray(data)) {
                    if (data && data.message) EES.alert(data.message, "warning");
                    return;
                }
                for (var i = 0; i < data.length; i++){

                    h_year.push(String(data[i]['year']));
                    h_production.push(parseFloat(data[i]['production']) || 0);
                    h_insolation.push(parseFloat(data[i]['insolation']) || 0);
                    
                }
                    
                const dataBar2 = {
                  labels: h_year,
                  datasets: [{
                    type: 'line',
                    label: 'Insolation (kWh/m2)',
                    yAxisID: 'A',
                    data: h_insolation,
                    fill: false,
                    backgroundColor: '#F2BB46',
                    borderColor: '#F2BB46',
                    title: 'Insolation (kWh/2)',
                    tension: 0,
                    pointRadius: 0       
                  },
                    {
                    type: 'bar',
                    label: 'Production (kWh)',
                    yAxisID: 'B',
                    data: h_production,
                    backgroundColor: '#CA5952'
                  }  ]
                };
                
                const plugin2 = {
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
                // config 
                
                const configBar2 = {
                  data: dataBar2,
                  options: {
                    scales: {       
                        A: {
                            type: 'linear',
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Insolation (kWh/m2)',
                                font: {
                                    size: 17,
                                    weight: 'bold'
                                }
                            },
                            min: 0
                        },
                        B: {
                            type: 'linear',
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Production (kWh)',
                                font: {
                                    size: 17,
                                    weight: 'bold'
                                }
                            }            
                        },
                        x: {
                          title: {
                            display: true,
                            text: 'Year',
                            font: {
                                size: 17,
                
                            }             
                          }        
                        }
                    },
                    barThickness: 20,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            display: true,
                        }
                    },
                    responsive: true, 
                    maintainAspectRatio: false,
                    interaction: plantChartInteraction,
                  },
                  plugins: [plugin2],
                };
                
                let chartStatus = Chart.getChart("barChart2"); // <canvas> id
                if (chartStatus != undefined) {
                  chartStatus.destroy();
                }
                
                // render init block
                const barChart2 = new Chart(
                  document.getElementById('barChart2'),
                  configBar2
                );
                resizePlantCharts();
                
            }
        });
        
    }
    
    
    


// --- next block ---


$("#startDate").on("change", function(){
  $("#endDate").attr("min", $(this).val());
}); 

$("#endDate").on("change", function(){
  $("#startDate").attr("max", $(this).val());
});


// --- next block ---







// --- next block ---


// Production v/s Irradiance
// setup 





// --- next block ---


// Historical Data    
// setup 





// --- next block ---


// document.getElementById('cmd')
// .addEventListener('click', () => {
//     const element = document.getElementById('plant_report_block');
//     const options = {
//         filename: 'Report.pdf',
//         margin: 0,
//         html2canvas: { scale: 15 },
//         jsPDF: { 
//             unit: 'in', 
//             format: 'letter', 
//             orientation: 'portrait' 
//         }
//     };
//     html2pdf().set(options).from(element).save();
// });

window.jsPDF = window.jspdf.jsPDF;

function generatePdf() {
    var el = document.getElementById('plant_report_block');
    if (!el) return;
    if (el.style.display === 'none' || window.getComputedStyle(el).display === 'none') {
        if (typeof EES !== 'undefined' && EES.alert) {
            EES.alert('Run “View Changes” first so the report is visible, then generate the PDF.', 'warning');
        }
        return;
    }

    resizePlantCharts();

    var pdfBtn = document.getElementById('cmd');
    if (typeof EES !== 'undefined' && EES.btnLoad && pdfBtn) EES.btnLoad(pdfBtn, 'Building PDF…');

    requestAnimationFrame(function () {
        requestAnimationFrame(function () {
            if (typeof html2canvas === 'undefined') {
                if (typeof EES !== 'undefined' && EES.btnReset && pdfBtn) EES.btnReset(pdfBtn);
                if (typeof EES !== 'undefined' && EES.alert) EES.alert('PDF capture library failed to load.', 'error');
                return;
            }

            var er = el.getBoundingClientRect();
            /* Force a desktop-width layout in the PDF clone (mobile/tablet viewports are too narrow) */
            var captureW = Math.max(el.scrollWidth, el.offsetWidth, 1280);
            el.querySelectorAll('canvas').forEach(function (cv) {
                var cr = cv.getBoundingClientRect();
                captureW = Math.max(captureW, Math.ceil(cr.right - er.left) + 16);
            });
            el.querySelectorAll('table').forEach(function (tb) {
                var tr = tb.getBoundingClientRect();
                captureW = Math.max(captureW, Math.ceil(tr.right - er.left) + 16);
            });
            var titleEl = el.querySelector('.reports-title');
            if (titleEl) {
                var t = titleEl.getBoundingClientRect();
                captureW = Math.max(captureW, Math.ceil(t.right - er.left) + 16);
            }

            html2canvas(el, {
                scale: 2,
                useCORS: true,
                allowTaint: true,
                logging: false,
                scrollX: 0,
                scrollY: 0,
                backgroundColor: '#ffffff',
                onclone: function (clonedDoc) {
                    var root = clonedDoc.getElementById('plant_report_block');
                    if (!root) return;
                    root.style.width = captureW + 'px';
                    root.style.minWidth = captureW + 'px';
                    root.style.maxWidth = 'none';
                    root.style.overflow = 'visible';
                    root.style.boxSizing = 'border-box';
                    clonedDoc.querySelectorAll('.card, .plant-chart-card, .chartBox, .chartBox2, .table-responsive').forEach(function (node) {
                        node.style.overflow = 'visible';
                    });
                    clonedDoc.querySelectorAll('.row').forEach(function (row) {
                        row.style.maxWidth = 'none';
                    });
                    clonedDoc.querySelectorAll('canvas').forEach(function (cv) {
                        var p = cv.parentElement;
                        if (!p) return;
                        var rw = cv.width || cv.getAttribute('width') || cv.clientWidth;
                        rw = parseFloat(rw, 10) || 0;
                        if (rw > 0) p.style.minWidth = Math.ceil(rw) + 'px';
                    });
                }
            })
                .then(function (canvas) {
                    var jsPdf = new jsPDF({ orientation: 'p', unit: 'pt', format: 'letter' });
                    var pageW = jsPdf.internal.pageSize.getWidth();
                    var pageH = jsPdf.internal.pageSize.getHeight();
                    var margin = 24;
                    var contentW = pageW - 2 * margin;
                    var contentH = pageH - 2 * margin;

                    var cw = canvas.width;
                    var ch = canvas.height;
                    if (cw < 1 || ch < 1) {
                        if (typeof EES !== 'undefined' && EES.btnReset && pdfBtn) EES.btnReset(pdfBtn);
                        if (typeof EES !== 'undefined' && EES.alert) EES.alert('Could not capture report (empty canvas).', 'warning');
                        return;
                    }

                    /* Single page: scale entire capture to fit inside printable area (preserve aspect) */
                    var imgPdfW = contentW;
                    var imgPdfH = (ch / cw) * contentW;
                    if (imgPdfH > contentH) {
                        var s = contentH / imgPdfH;
                        imgPdfW = imgPdfW * s;
                        imgPdfH = contentH;
                    }
                    var x = margin + (contentW - imgPdfW) / 2;
                    var y = margin + (contentH - imgPdfH) / 2;

                    var imgData = canvas.toDataURL('image/jpeg', 0.92);
                    jsPdf.addImage(imgData, 'JPEG', x, y, imgPdfW, imgPdfH);

                    jsPdf.save('Report.pdf');
                    window.open(jsPdf.output('bloburl'));
                })
                .catch(function (err) {
                    console.error('generatePdf', err);
                    if (typeof EES !== 'undefined' && EES.alert) EES.alert('PDF generation failed. See console for details.', 'error');
                })
                .then(function () {
                    if (typeof EES !== 'undefined' && EES.btnReset && pdfBtn) EES.btnReset(pdfBtn);
                });
        });
    });
}



// --- next block ---



document.addEventListener('DOMContentLoaded', () => {
    const selectMenu = document.getElementById('site_opt');
    if (!selectMenu) return;

    updatePlantReportTitle();

    selectMenu.addEventListener('change', updatePlantReportTitle);
});


// --- next block ---


document.getElementById("eco-logo").src = "data:image/jpeg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/4QMGRXhpZgAATU0AKgAAAAgABAEOAAIAAAAUAAABSodpAAQAAAABAAABXpybAAEAAAAoAAAC1uocAAcAAAEMAAAAPgAAAAAc6gAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAARUNPQVNJUyBMT0dPIFNPVVJDRQAABZADAAIAAAAUAAACrJAEAAIAAAAUAAACwJKRAAIAAAADMDAAAJKSAAIAAAADMDAAAOocAAcAAAEMAAABoAAAAAAc6gAAAAEAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMjAyMzowNDowNCAxNTowMzoyMAAyMDIzOjA0OjA0IDE1OjAzOjIwAAAARQBDAE8AQQBTAEkAUwAgAEwATwBHAE8AIABTAE8AVQBSAEMARQAAAP/hBN1odHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvADw/eHBhY2tldCBiZWdpbj0n77u/JyBpZD0nVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkJz8+DQo8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIj48cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPjxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSJ1dWlkOmZhZjViZGQ1LWJhM2QtMTFkYS1hZDMxLWQzM2Q3NTE4MmYxYiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIj48eG1wOkNyZWF0ZURhdGU+MjAyMy0wNC0wNFQxNTowMzoyMDwveG1wOkNyZWF0ZURhdGU+PC9yZGY6RGVzY3JpcHRpb24+PHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9InV1aWQ6ZmFmNWJkZDUtYmEzZC0xMWRhLWFkMzEtZDMzZDc1MTgyZjFiIiB4bWxuczpkYz0iaHR0cDovL3B1cmwub3JnL2RjL2VsZW1lbnRzLzEuMS8iLz48cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0idXVpZDpmYWY1YmRkNS1iYTNkLTExZGEtYWQzMS1kMzNkNzUxODJmMWIiIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyI+PGRjOnRpdGxlPjxyZGY6QWx0IHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+PHJkZjpsaSB4bWw6bGFuZz0ieC1kZWZhdWx0Ij5FQ09BU0lTIExPR08gU09VUkNFPC9yZGY6bGk+PC9yZGY6QWx0Pg0KCQkJPC9kYzp0aXRsZT48ZGM6ZGVzY3JpcHRpb24+PHJkZjpBbHQgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj48cmRmOmxpIHhtbDpsYW5nPSJ4LWRlZmF1bHQiPkVDT0FTSVMgTE9HTyBTT1VSQ0U8L3JkZjpsaT48L3JkZjpBbHQ+DQoJCQk8L2RjOmRlc2NyaXB0aW9uPjwvcmRmOkRlc2NyaXB0aW9uPjwvcmRmOlJERj48L3g6eG1wbWV0YT4NCiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAg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oAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAqhr3/ID1H/r3k/9BNX6oa9/yA9R/wCvaT/0E0nsXD4kfiHRRRXxB/WQUUUUDCiiigAooooAKKKKACiiigQV1fg74reMfh/IreHfEup6QqnPk29ywiP+9Hna34iuUopqTi7ozqU4VY8lSKa7PU+v/hn/AMFFvE+jSRW3jXSLfxDaZw17ZAW10B3JUfu2+gCfWvsb4T/tA+B/jNahvDesxvfBN8ml3X7q7i9cxk/MB3ZSy+9fj1VjT9QutKvYbyyuZrO7hYPFcW8hSSNh0KsOQfcV6NLHVaektUfE5lwhgMYnKgvZT8tvu/ysfuFmlr4Q/Z1/b3nt5LXw/wDEyTzoDiOHxCifOnoLhR94f7ajPqDy1fdFneW+oWkN1azx3VtOgkimhcMkikZDKRwQRzkV71GvCurwZ+OZnlOKymr7PEx32a2fo/03J6KSlroPHCiiigAoopKAFpKhvL2302zmu7ueO2tYEMks0zBURQMlmJ4AA718SftAftvXWoy3Wg/DuRrSzGY5deIxLL2PkAj5B/tn5j224ydqVGVZ2ieXmGZYfLafPXfourPpr4qftBeCvg/EV13VBJqJGU0uyAluW+q5wg93Kg9q+SPiJ+3t4u15pbfwrYW3hmzPC3EgFzdEeuWGxc+m049a+Yrq6nv7qW5uZpLi4lYvJLKxZ3Y9SSeSTUdezTwlOGstWfl2O4lxmKbjSfs4+W/3/wCVje8UePvEnja4M2v69qGrvnIF3cM6r/uqThfwFYGKWiu1JLRHykpyqPmm7sSilooJCiiimAUUUUAFFFFABRRRQAUlLSUhH7DeCP8AkS9A/wCwfb/+i1rbrE8Ef8iXoH/YPt//AEWtbdfKS3Z/R1L+HH0QUUUUjUKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKACiiigAooooAKKKKAC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"

document.getElementById("enl-logo").src ="data:image/png;base64,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"

