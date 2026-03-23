$(function hideCards(){
    $(".custom_hide_card").css('display','none');
});


// Setup - add a text input to each footer cell
$('#tbl_archive thead tr')
    .clone(true)
    .addClass('filters')
    .appendTo('#tbl_archive thead');

var archive_table = $('#tbl_archive').DataTable({
    ordering: false,
    autoWidth: false,
    columnDefs: [{ width: '10%', targets: 0 },{ width: '13%', targets: 1 },{ width: '11%', targets: 2 },{ width: '12%', targets: 3 }],   
    orderCellsTop: true,
    fixedHeader: true,
    dom: 'Bfrtip',
    buttons: [
        'copy', 'csv', 'excel', 'pdf', 'print'
    ],  
    initComplete: function () {
        this.api()
            .columns()
            .every(function () {
                let column = this;
                let title = column.header().textContent;
 
                if(title != 'Site' && title != 'Production (kWh)' && title != 'Insolation (kWh/m2)'){
                    $('<input type="text" placeholder="Search ' + title + '" />')
                        .appendTo($(column.header()).empty())
                        .on('keyup change clear', function () {
                            if (column.search() !== this.value) {
                                column.search(this.value).draw();
                            }
                        });
                    }
            });
    }           
});


$(function sites_name(){

    $.ajax({
        type:     "POST",
        url:      "scripts/get_all_sites.php",
        dataType: "json",
        success: function(data) {
            
            let sites = data.data;
            
            for(var i = 0; i < sites.length; i++){
                
                let option = "<option value = '"+ sites[i].id +"'>"+ sites[i].site_name +"</option>";
                
                $('#site').append(option);
            }
        }
    });
});


function validate_date(){
    
    let site_id = document.getElementById("site").value;
    
    
    $.ajax({
        type: "POST",
        url: "scripts/get_archive_date_bounds.php",
        dataType: "json",
        data: {
            "site" : site_id
        },
        success: function(data) {
            let min_date = data.min;
            let max_date = data.max;
            
            document.getElementById("startDate").value = "";
            document.getElementById("endDate").value = "";
            
            document.getElementById("startDate").setAttribute("min", min_date);
            document.getElementById("startDate").setAttribute("max", max_date);
            
            document.getElementById("endDate").setAttribute("min", min_date); 
            document.getElementById("endDate").setAttribute("max", max_date);
            
        }
    });
    
}

var custom_archive;

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

function render_chart(dates, prod_dataset, ins_dataset){

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
    
    const dataArchive = {
        labels: dates,
        datasets: [
                {
                    type: 'line',
                    label: 'Insolation (kWh/m2)',
                    yAxisID: 'B',
                    data: ins_dataset,
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
                    data: prod_dataset,
                    backgroundColor: '#CA5952',
                    borderColor: '#CA5952',
                    barThickness: 30,
                }
        ]
        
    };
    
    const configArchive = {
        data: dataArchive,
      options: {
        enabled: true,
        scales: {       
            A: {
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
                grid:{
                    display:false
                }            
            },
            B: {
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
                grid:{
                    display:false
                }
            },
            x: {
              title: {
                display: true,
                text: 'Date/Time',
                font: {
                    size: 13,
                    weight:'bold'
                }            
              }
            },              
            },
            plugins: {
                zoom: zoomOptions,
                legend: {
                    position: 'bottom',
                    display: true,
                },
                tooltip: {
                    enabled: true,
                },
            },
            animation: false,            
        },
        plugins: [white_back],
        maintainAspectRatio:true,
        responsive: true,
    };
    
    let chartStatus = Chart.getChart("archive_chart");
    if (chartStatus != undefined) {
      chartStatus.destroy();
    }
    
    custom_archive = new Chart(
      document.getElementById('archive_chart'),
      configArchive
    );

}

function query(){
    
    let site = document.getElementById("site").value;
    
    let start_date = new Date(document.getElementById("startDate").value).toISOString().split('T')[0];
    let end_date = new Date(document.getElementById("endDate").value).toISOString().split('T')[0];
    
    $.ajax({
        type: "POST",
        url: "scripts/get_archive_data.php",
        dataType: "json",
        data: {
            "site" : site,
            "start_date": start_date,
            "end_date": end_date
        },
        success: function(data) {
            if (!data || data.status === 'Err' || !Array.isArray(data.archive)) {
                alert("Failed to load archive data. Please try again.");
                return;
            }

            let site_name = data.site_name;
            let archive = data.archive;
            
            let tempDate, date, prod, insolation;
            
            let date_dataset = [];
            let prod_dataset = [];
            let ins_dataset = [];
            let total_prod = 0, total_ins = 0;
            
            archive_table.rows().remove().draw();
            if(archive.length > 0){
                for(let i = 0; i < archive.length; i++){
                    
                    tempDate = new Date(archive[i].date);
                    
                    date = [tempDate.getDate(), tempDate.getMonth() + 1, tempDate.getFullYear()].join('/');
                    
                    prod = archive[i].production;
                    insolation = archive[i].insolation;
                    
                    archive_table.row.add([date, site_name, prod, insolation]).draw();
                    
                    date_dataset.push(archive[i].date);
                    prod_dataset.push(prod);
                    ins_dataset.push(insolation);
                    
                    total_prod += parseFloat(prod);
                    total_ins += parseFloat(insolation);
                    
                }
            }
            else{
                alert("No data for this date!");
            }
            
            if(total_prod < 1000){
                document.getElementById("total_prod").innerHTML = total_prod.toFixed(2) + " kWh";
            }
            else{
                document.getElementById("total_prod").innerHTML = (total_prod/1000).toFixed(2) + " MWh";
            }
            
            document.getElementById("total_insolation").innerHTML = total_ins.toFixed(2) + " kWh/m<sup>2</sup>";
            
            render_chart(date_dataset, prod_dataset, ins_dataset);
            $(".custom_hide_card").css('display','block');
        }
    });
    
}

function resetZoomBtn(){
    custom_archive.resetZoom();
}

function downloadCustom() {
    const imageLink = document.createElement('a');
    const canvas = document.getElementById('archive_chart');
    imageLink.download = 'archive_chart.png';
    imageLink.href = canvas.toDataURL('image/png', 1);
    imageLink.click();
}
