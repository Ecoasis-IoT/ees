$("#chart_type").hide();

$(".meter_param").change(function () {
    $(".meter_param").not(this).prop('checked', false);
});

function check_all(){
    let custom_meters = document.getElementsByName("custom_meters");
    let all_meters = document.getElementById("all_meters");
    
    if(all_meters.checked == true){
        for (let i = 0; i < custom_meters.length; i++) {
            custom_meters[i].checked = true;
        }
        show_param();
    }
    else{
        
        for (let i = 0; i < custom_meters.length; i++) {
            custom_meters[i].checked = false;
        }
        // show_param(); 
        $("#con_meter_parameters").hide();
        
    }
    
}


function show_param(){
    $("#con_meter_parameters").show();
}

function showtype(){
    $("#chart_type").show();
}

$("#zoom_reset").hide();

function show_zoom(){
    $("#zoom_reset").show();
}

$(function hideCards(){
    $(".custom_hide_card").css('display','none');
});

//Initialize DataTables
 
var tbl_hourly = $('#tbl_prod_hourly').DataTable({
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]
    
});

var tbl_ir_day = $('#tbl_irradiance_day').DataTable({ordering: false,
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]

});


var tbl_power_day = $('#tbl_apower_day').DataTable({
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]
});

var tbl_daily = $('#tbl_prod_daily').DataTable({
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]
});

var tbl_ir_month = $('#tbl_irradiance_month').DataTable({ordering: false,
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]

});


var tbl_power_month = $('#tbl_apower_month').DataTable({
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]
});

var tbl_monthly = $('#tbl_prod_monthly').DataTable({
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]
});

var tbl_ir_year = $('#tbl_irradiance_year').DataTable({ordering: false,
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]

});


var tbl_custom_prod = $('#tbl_custom_prod').DataTable({
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]
});

var tbl_custom_active = $('#tbl_custom_active').DataTable({
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]
});

var tbl_custom_irradiance = $('#tbl_custom_irradiance').DataTable({ordering: false,
                        dom: 'Bflrtip',
                        "destroy": true,
                        "lengthMenu": [ 10, 25, 50, 75, 100 ],
                        buttons: 
                        ['copy', 
                        'csv', 
                        'excel', 
                        'pdf', 
                        'print'
                        ]

});

$(function sites_name(){

        $.ajax({
            type: "POST",
            url: "scripts/get_all_sites",
            dataType: 'json',
            data: {},
            success: function(data) {
                var sites = data.data || [];
                for (var i = 0; i < sites.length; i++) {
                    var opt = "<option value='" + sites[i].id + "'>" + sites[i].site_name + "</option>";
                    $('#sites_opt_day').append(opt);
                    $('#sites_opt_month').append(opt);
                    $('#sites_opt_year').append(opt);
                    $('#sites_opt_custom').append(opt);
                }
            }
        });
});

function intializeOptions(){
    
    let default_opt = "<option hidden>Choose an Energy Meter</option>";
    
    $("#meters_opt_day").empty();
    $("#meters_opt_day").append(default_opt);
    
    $("#meters_opt_month").empty();
    $("#meters_opt_month").append(default_opt);
    
    $("#meters_opt_year").empty();
    $("#meters_opt_year").append(default_opt);
    
    $("#meters_opt_custom").empty();
}

function filter_meters(tab){
    intializeOptions();
    
    let all_months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    
    let site;
    let meter_opt;
    
    if(tab == 1){
        site = document.getElementById("sites_opt_day").value; 
        meter_opt = "#meters_opt_day";
        // console.log(site);
    }
    else if(tab == 2){
        site = document.getElementById("sites_opt_month").value; 
        meter_opt = "#meters_opt_month";
        
    }
    else if(tab == 3){
        site = document.getElementById("sites_opt_year").value; 
        meter_opt = "#meters_opt_year";
        
    }
    else if(tab == 4){
        site = document.getElementById("sites_opt_custom").value; 
        meter_opt_custom = "#meters_opt_custom";
        
    }

    // let meters = document.getElementById("meters_opt");
    $.ajax({
            type: "POST",
            url: "scripts/get_query_meters",
            data: {
                "site": site
            },
            success: function(data) {
                if(data.status != undefined){
                    EES.alert('Failed to retrieve meters for this site!', 'error');
                }
                else{
                    // $("#meters_opt").empty();
                    // let default_opt = "<option hidden>Choose Meter</option>";
                    // $("#meters_opt").append(default_opt);
                    
                    // meters available
                    
                    let arr_meters = data.meters;
                    let dates = data.dates;
                    
                    if(tab < 4){
                    
                        for (const key in arr_meters) {
                            let rec = arr_meters[key];
                            
                            $(meter_opt).append($('<option>', {value: rec.meter_id, text: rec.name}));
                            
                        }
                    }
                    else{
                        
                        let ul = document.getElementById("meters_opt_custom");
                        
                        //Select All CheckBox
                        
                        let chkbox = document.createElement('INPUT');
                        chkbox.setAttribute('type', 'checkbox');
                        chkbox.setAttribute('onchange', 'check_all()');
                        chkbox.setAttribute('id', 'all_meters');
                        
                        let li = document.createElement("li");
                        let description = document.createTextNode("Select All");
                        li.appendChild(chkbox);
                        li.appendChild(description);
                        ul.appendChild(li);
                        
                        
                        for (const key in arr_meters) {
                            let rec = arr_meters[key];
                            
                            // $(meter_opt_custom).append($('<option>', {value: rec.meter_id, text: rec.name}));
                            
                            let chkbox = document.createElement('INPUT');
                            chkbox.setAttribute('type', 'checkbox');
                            chkbox.setAttribute('onchange', 'show_param()');
                            chkbox.setAttribute('name', 'custom_meters');
                            chkbox.setAttribute('value', rec.meter_id);
                            
                            let li = document.createElement("li");
                            let description = document.createTextNode(rec.name);
                            li.appendChild(chkbox);
                            li.appendChild(description);
                            ul.appendChild(li);
                            
                            
                        }
                        
                        
                        
                    }
                    
                    
                    if(tab == 2){
                        
                        let default_opt = "<option hidden>Choose a Month</option>";
                        $("#month_opt").empty();
                        $("#month_opt").append(default_opt);
                        
                        console.log(dates.startmth);
                        console.log(dates.endmth);
                        
                        for(let i = parseInt(dates.startmth); i <= parseInt(dates.endmth); i++){
                            
                            
                            $('#month_opt').append($('<option>', {value: i, text: all_months[i-1]}));
                            
                        }
                        
                        let default_year = "<option hidden>Choose a Year</option>";
                        $("#m_year_opt").empty();
                        $("#m_year_opt").append(default_year);
                        
                        for(let i = dates.startyr; i <= dates.endyr; i++){
                         
                            $('#m_year_opt').append($('<option>', {value: i, text: i}));
                            
                        }
                        
                        
                        
                        
                    }
                    else if(tab == 3){
                        let default_year = "<option hidden>Choose a Year</option>";
                        $("#year_opt").empty();
                        $("#year_opt").append(default_year);
                        
                        for(let i = dates.startyr; i <= dates.endyr; i++){
                         
                            $('#year_opt').append($('<option>', {value: i, text: i}));
                            
                        }
                        
                        
                    }
                    
                }
            
                
                
            },
            error: function(req, err){ console.log('error' + err)}
        });
}


//============================================================Day Script=============================================================

function get_day(){
    var _btn = document.querySelector('#Day .submit');
    EES.btnLoad(_btn, 'Loading…');

    let chart_labels = [];
    let kpi_irradiance = [];
    let kpi_prod = [];
    let kpi_active_power = [];
    
    let url = "";
    
    let arr_multiple = ["7780","7779"]; // Sites with more than one PVDB (Main meters)
    
    if(arr_multiple.includes(document.getElementById("sites_opt_day").value)){
        url = "scripts/query_dayv2"
    }
    else{
        url = "scripts/query_day"
    }
    
    $.ajax({
        type: "POST",
        url: url,
        async : false,
        data: {
            "site": document.getElementById("sites_opt_day").value,
            "meter": document.getElementById("meters_opt_day").value,
            "date": document.getElementById("date_day").value
        },
        success: function(data) {
            // Backend returns {status:'Err', message:...} on failure — surface it
            // instead of crashing on undefined arrays below.
            if (!data || data.status === 'Err') {
                EES.alert((data && data.message) ? data.message : 'Could not load data for this selection.', 'error');
                return;
            }

            //production
            let prod = Array.isArray(data.prod) ? data.prod : [];
            
            let total_prod_day = 0;
            
            tbl_hourly.rows().remove().draw();
            
            for(var i = 0; i < prod.length; i++){
                
                total_prod_day += parseFloat(prod[i].production);
                
                chart_labels.push(prod[i].datetime);
                
                kpi_prod.push({"x": prod[i].datetime, "y": prod[i].production});
                
                tbl_hourly.row.add([prod[i].datetime, data.site_name, prod[i].meter_name, prod[i].production]).draw();
            }
            
            tbl_hourly.columns.adjust().draw();
            
            if(total_prod_day < 1000){
                document.getElementById('day_total_prod').innerHTML = total_prod_day.toFixed(2) + " kWh";
            }
            else{
                document.getElementById('day_total_prod').innerHTML = (total_prod_day/1000).toFixed(2) + " MWh";
            }
            
            //Irradiance
            let irradiance = Array.isArray(data.irradiance) ? data.irradiance : [];
            let total_day_insolation = 0;
            
            tbl_ir_day.rows().remove().draw();
            
            for(var i = 0; i < irradiance.length; i++){
                
                total_day_insolation += parseFloat(irradiance[i].insolation);
                
                kpi_irradiance.push({"x": irradiance[i].date, "y": irradiance[i].irradiance});
                
                tbl_ir_day.row.add([irradiance[i].date, data.site_name, irradiance[i].irradiance, irradiance[i].insolation, irradiance[i].ambient_temp, irradiance[i].panel_temp]).draw();
                                
                // var row = "<tr><td>"+ irradiance[i].date +"</td><td>" + data.site_name + "</td><td>" + irradiance[i].irradiance + "</td><td>" + irradiance[i].insolation + "</td><td>" + irradiance[i].ambient_temp + "</td><td>" + irradiance[i].panel_temp + "</td></tr>";
                    
                // $('#tbl_irradiance_day tbody').append(row);
                
            }
            
            tbl_ir_day.columns.adjust().draw();
            
            document.getElementById('day_total_insolation').innerHTML = total_day_insolation.toFixed(2) + " kWh/m<sup>2</sup>";
            
            //PR Ratio
            let day_pr = (total_prod_day/(total_day_insolation * data.site_capacity)) * 100;
            document.getElementById('day_pr').innerHTML = day_pr.toFixed(0) + "%";
            
            //CO2 Avoided
            
            let day_co2_avoided = (total_prod_day * 0.966);
            document.getElementById('day_co2').innerHTML = day_co2_avoided.toFixed(0) + "Kg";
            
            //Active Power
            
            let active_power = Array.isArray(data.active_power) ? data.active_power : [];
            
            tbl_power_day.rows().remove().draw();
            
            for(var i = 0; i < active_power.length; i++){
                
                if(active_power[i].active_power < 0){
                    kpi_active_power.push({"x": active_power[i].date, "y": 0});
                }
                else{
                    kpi_active_power.push({"x": active_power[i].date, "y": active_power[i].active_power});
                }
                
                tbl_power_day.row.add([active_power[i].date, data.site_name, active_power[i].meter_name, active_power[i].active_power]).draw();
                
                // var row = "<tr><td>"+ active_power[i].date +"</td><td>" + data.site_name + "</td><td>" + active_power[i].meter_name + "</td><td>"  + active_power[i].active_power + "</td></tr>";
                    
                // $('#tbl_apower_day tbody').append(row);
                
            }
            
            tbl_power_day.columns.adjust().draw();
            
            // console.log(chart_labels);
            // console.log(kpi_active_power);
            // console.log(kpi_irradiance);
            // console.log(kpi_prod);


            // render KPI Chart
            const dataKPIday = {
              labels: chart_labels,
              datasets: [
              {
                type: 'line',
                label: 'Active Power (kW)',
                yAxisID: 'A',
                // data: [{x:"08:45",y: 25},{x: "09:00",y: 45},{ x: "09:15",y: 65},{x: "09:30",y: 70},{x: "09:45",y: 91}],
                data: kpi_active_power,
                fill: false,
                backgroundColor: '#36648B',
                borderColor: '#36648B',
                tension: 0.4,
                pointRadius: 1,
              },
                {
                type: 'line',
                label: 'Irradiance (W/m2)',
                yAxisID: 'B',
                data: kpi_irradiance,
                // data: [{x:"08:45",y: 12},{x: "09:00",y: 19},{ x: "09:15",y: 7},{x: "09:30",y: 9},{x: "09:45",y: 10}],
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
                // data: [{x: "09:00",y: 31},{x: "10:00",y: 55},{x: "11:00",y: 34}],
                backgroundColor: '#CA5952',
                borderColor: '#CA5952',
                barThickness:30,
              }
              ]
            };
            
                        
            const configKPIday = {
              data: dataKPIday,
              options: {
                enabled: true,
                scales: {       
                    A: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Active Power (kW)',
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
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Irradiance (W/m2)',
                            font: {
                                size: 17,
                                weight: 'bold'
                            }                
                        },
                        grid:{
                            display:false
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
                        }            
                    },        
                    x: {
                      title: {
                        display: true,
                        text: 'Time',
                        font: {
                            size: 13,
                            weight:'bold'
                        }            
                      },
                        // type: 'time',
                        // time: {
                        //   unit: 'second'
                        // }              
                      type: 'time',
                      time: {
                        parser: 'HH:mm:ss',
                        unit: 'hour',
                        tooltipFormat: 'HH:mm',
                        displayFormats: {
                          hour: 'HH:mm'
                        }                
                    },              
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        display: true,
                    },
                },
                maintainAspectRatio:false,
                responsive: true,
              },
            //   plugins: [white_back],
            };
            
            let chartStatus = Chart.getChart("day_kpi"); // <canvas> id
            if (chartStatus != undefined) {
              chartStatus.destroy();
            }
                                
            
            const day_kpi = new Chart(
              document.getElementById('day_kpi'),
              configKPIday
            );       
            
            $("#Day .custom_hide_card").css('display','block');
            
        },
        complete: function() { EES.btnReset(_btn); }
    });
    
}

//===========================================MONTH SCRIPT=========================================================

function get_month(){
    var _btn = document.querySelector('#Month .submit');
    EES.btnLoad(_btn, 'Loading…');

    let chart_labels = [];
    let kpi_insolation = [];
    let kpi_prod = [];
    let kpi_pr = [];
    
    $.ajax({
        type: "POST",
        url: "scripts/query_month",
        async : true,
        data: {
            "site": document.getElementById("sites_opt_month").value,
            "meter": document.getElementById("meters_opt_month").value,
            "month": document.getElementById("month_opt").value,
            "year": document.getElementById("m_year_opt").value
        },
        success: function(data) {
            //production
            let prod = data.prod;
            
            let total_prod_month = 0;
            
            tbl_daily.rows().remove().draw();
            
            for(var i = 0; i < prod.length; i++){
                
                total_prod_month += parseFloat(prod[i].production);
                
                chart_labels.push(prod[i].datetime);
                
                kpi_prod.push({"x": prod[i].datetime, "y": prod[i].production});
                
                tbl_daily.row.add([prod[i].datetime, data.site_name, prod[i].meter_name, prod[i].production]).draw();
            }
            
            tbl_daily.columns.adjust().draw();
            
            if(total_prod_month < 1000){
                document.getElementById('month_total_prod').innerHTML = total_prod_month.toFixed(2) + " kWh";
            }
            else{
                document.getElementById('month_total_prod').innerHTML = (total_prod_month/1000).toFixed(2) + " MWh";
            }
            
            // //Irradiance
            let irradiance = data.irradiance;
            let total_month_insolation = 0;
            let pr;

            // Build lookup: date string → production value (prod and irradiance may differ in length)
            var prodByDate = {};
            for (var j = 0; j < prod.length; j++) {
                prodByDate[prod[j].datetime] = parseFloat(prod[j].production) || 0;
            }
            
            tbl_ir_month.rows().remove().draw();
            
            for(var i = 0; i < irradiance.length; i++){
                
                total_month_insolation += parseFloat(irradiance[i].insolation);
                
                kpi_insolation.push({"x": irradiance[i].date, "y": irradiance[i].insolation});

                var dayProd = prodByDate[irradiance[i].date] || 0;
                pr = irradiance[i].insolation > 0
                    ? (dayProd / (parseFloat(irradiance[i].insolation) * data.site_capacity)) * 100
                    : 0;
                
                kpi_pr.push({"x": irradiance[i].date, "y": pr});
                
                tbl_ir_month.row.add([irradiance[i].date, data.site_name, irradiance[i].insolation]).draw();
                
            }
            
            tbl_ir_month.columns.adjust().draw();
            
            document.getElementById('month_total_insolation').innerHTML = total_month_insolation.toFixed(2) + " kWh/m<sup>2</sup>";
            
            // //PR Ratio
            let month_pr = (total_prod_month/(total_month_insolation * data.site_capacity)) * 100;
            document.getElementById('month_pr').innerHTML = month_pr.toFixed(0) + "%";
            
            //CO2 Avoided
            
            let month_co2_avoided = (total_prod_month * 0.966);
            document.getElementById('month_co2').innerHTML = month_co2_avoided.toFixed(0) + "Kg";
            
            //Active Power
            
            let active_power = data.active_power;
            
            tbl_power_day.rows().remove().draw();
            
            for(var i = 0; i < active_power.length; i++){
                
                
                // kpi_active_power.push({"x": active_power[i].date, "y": active_power[i].active_power});
                
                tbl_power_month.row.add([active_power[i].date, data.site_name, active_power[i].meter_name, active_power[i].active_power]).draw();
                
            }
            
            tbl_power_day.columns.adjust().draw();
            
            // console.log(chart_labels);
            // console.log(kpi_active_power);
            // console.log(kpi_insolation);
            // console.log(kpi_prod);


            // render KPI Chart
            const dataKPIday = {
              labels: chart_labels,
              datasets: [
              {
                type: 'line',
                label: 'PR (%)',
                yAxisID: 'A',
                // data: [{x:"08:45",y: 25},{x: "09:00",y: 45},{ x: "09:15",y: 65},{x: "09:30",y: 70},{x: "09:45",y: 91}],
                data: kpi_pr,
                fill: false,
                backgroundColor: '#7CAF57',
                borderColor: '#7CAF57',
                tension: 0.4,
                pointRadius: 1,
              },
                {
                type: 'line',
                label: 'Insolation (kWh/m2)',
                yAxisID: 'B',
                data: kpi_insolation,
                // data: [{x:"08:45",y: 12},{x: "09:00",y: 19},{ x: "09:15",y: 7},{x: "09:30",y: 9},{x: "09:45",y: 10}],
                fill: false,
                backgroundColor: '#F2BB46',
                borderColor: '#F2BB46',
                title: 'Insolation (kWh/m2)',
                tension: 0.4,
                pointRadius: 1,
              },      
              {
                type: 'bar',
                label: 'Production (kWh)',
                yAxisID: 'C',
                data: kpi_prod,
                // data: [{x: "09:00",y: 31},{x: "10:00",y: 55},{x: "11:00",y: 34}],
                backgroundColor: '#CA5952',
                borderColor: '#CA5952',
                barThickness:30,
              }
              ]
            };
            
                        
            const configKPIday = {
              data: dataKPIday,
              options: {
                enabled: true,
                scales: {       
                    A: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'PR (%)',
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
                        position: 'left',
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
                        }            
                    },        
                    x: {
                      title: {
                        display: true,
                        text: 'Date',
                        font: {
                            size: 13,
                            weight:'bold'
                        }            
                      },
                        // type: 'time',
                        // time: {
                        //   unit: 'second'
                        // }              
                      type: 'time',
                      time: {
                        parser: 'yyyy-MM-dd',
                        unit: 'day',
                        tooltipFormat: 'dd-MM',
                        displayFormats: {
                          day: 'dd-MM'
                        }                
                    }              
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        display: true,
                    },
                },
                maintainAspectRatio:false,
                responsive: true,
              },
            //   plugins: [white_back],
            };
            
            let chartStatus = Chart.getChart("month_kpi"); // <canvas> id
            if (chartStatus != undefined) {
              chartStatus.destroy();
            }
                                
            
            const month_kpi = new Chart(
              document.getElementById('month_kpi'),
              configKPIday
            );       
            
            $("#Month .custom_hide_card").css('display','block');
            
        },
        complete: function() { EES.btnReset(_btn); }
    });
    
}

//===========================================YEAR SCRIPT=========================================================

function get_year(){
    var _btn = document.querySelector('#Year .submit');
    EES.btnLoad(_btn, 'Loading…');

    let chart_labels = [];
    let kpi_insolation = [];
    let kpi_prod = [];
    let kpi_pr = [];
    
    let all_months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    
    $.ajax({
        type: "POST",
        url: "scripts/query_year",
        async : true,
        data: {
            "site": document.getElementById("sites_opt_year").value,
            "meter": document.getElementById("meters_opt_year").value,
            "year": document.getElementById("year_opt").value
        },
        success: function(data) {
            //production
            let prod = data.prod;
            
            let total_prod_year = 0;
            
            tbl_monthly.rows().remove().draw();
            
            for(var i = 0; i < prod.length; i++){
                
                total_prod_year += parseFloat(prod[i].production);
                
                chart_labels.push(all_months[prod[i].datetime-1]);
                
                kpi_prod.push({"x": all_months[prod[i].datetime - 1], "y": prod[i].production});
                
                tbl_monthly.row.add([all_months[prod[i].datetime-1], data.site_name, prod[i].meter_name, prod[i].production]).draw();
            }
            
            tbl_monthly.columns.adjust().draw();
            
            if(total_prod_year < 1000){
                document.getElementById('year_total_prod').innerHTML = total_prod_year.toFixed(2) + " kWh";
            }
            else{
                document.getElementById('year_total_prod').innerHTML = (total_prod_year/1000).toFixed(2) + " MWh";
            }
            
            // //Irradiance
            let irradiance = data.irradiance;
            let total_year_insolation = 0;
            let pr;

            // Build lookup: month number → production value (prod and irradiance may differ in length)
            var prodByMonth = {};
            for (var j = 0; j < prod.length; j++) {
                prodByMonth[prod[j].datetime] = parseFloat(prod[j].production) || 0;
            }
            
            tbl_ir_year.rows().remove().draw();
            
            for(var i = 0; i < irradiance.length; i++){
                
                total_year_insolation += parseFloat(irradiance[i].insolation);
                
                kpi_insolation.push({"x": all_months[irradiance[i].date - 1], "y": irradiance[i].insolation});

                var monthProd = prodByMonth[irradiance[i].date] || 0;
                pr = irradiance[i].insolation > 0
                    ? (monthProd / (parseFloat(irradiance[i].insolation) * data.site_capacity)) * 100
                    : 0;
                
                kpi_pr.push({"x": all_months[irradiance[i].date - 1], "y": pr});
                
                tbl_ir_year.row.add([all_months[irradiance[i].date - 1], data.site_name, irradiance[i].insolation]).draw();
                
            }
            
            tbl_ir_year.columns.adjust().draw();
            
            document.getElementById('year_total_insolation').innerHTML = total_year_insolation.toFixed(2) + " kWh/m<sup>2</sup>";
            
            // //PR Ratio
            let year_pr = (total_prod_year/(total_year_insolation * data.site_capacity)) * 100;
            document.getElementById('year_pr').innerHTML = year_pr.toFixed(0) + "%";
            
            //CO2 Avoided
            
            let year_co2_avoided = (total_prod_year * 0.966);
            document.getElementById('year_co2').innerHTML = year_co2_avoided.toFixed(0) + "Kg";
            
            //Active Power
            
            // let active_power = data.active_power;
            
            // tbl_power_day.rows().remove().draw();
            
            // for(var i = 0; i < active_power.length; i++){
                
                
            //     // kpi_active_power.push({"x": active_power[i].date, "y": active_power[i].active_power});
                
            //     tbl_power_year.row.add([active_power[i].date, data.site_name, active_power[i].meter_name, active_power[i].active_power]).draw();
                
            // }
            
            // tbl_power_day.columns.adjust().draw();
            
            // console.log(chart_labels);
            // console.log(kpi_active_power);
            // console.log(kpi_insolation);
            // console.log(kpi_prod);


            // render KPI Chart
            const dataKPIday = {
              labels: chart_labels,
              datasets: [
              {
                type: 'line',
                label: 'PR (%)',
                yAxisID: 'A',
                // data: [{x:"08:45",y: 25},{x: "09:00",y: 45},{ x: "09:15",y: 65},{x: "09:30",y: 70},{x: "09:45",y: 91}],
                data: kpi_pr,
                fill: false,
                backgroundColor: '#7CAF57',
                borderColor: '#7CAF57',
                tension: 0.4,
                pointRadius: 1,
              },
                {
                type: 'line',
                label: 'Insolation (kWh/m2)',
                yAxisID: 'B',
                data: kpi_insolation,
                // data: [{x:"08:45",y: 12},{x: "09:00",y: 19},{ x: "09:15",y: 7},{x: "09:30",y: 9},{x: "09:45",y: 10}],
                fill: false,
                backgroundColor: '#F2BB46',
                borderColor: '#F2BB46',
                title: 'Insolation (kWh/m2)',
                tension: 0.4,
                pointRadius: 1,
              },      
              {
                type: 'bar',
                label: 'Production (kWh)',
                yAxisID: 'C',
                data: kpi_prod,
                // data: [{x: "09:00",y: 31},{x: "10:00",y: 55},{x: "11:00",y: 34}],
                backgroundColor: '#CA5952',
                borderColor: '#CA5952',
                barThickness:30,
              }
              ]
            };
            
                        
            const configKPIday = {
              data: dataKPIday,
              options: {
                enabled: true,
                scales: {       
                    A: {
                        type: 'linear',
                        position: 'left',
                        title: {
                            display: true,
                            text: 'PR (%)',
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
                        position: 'left',
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
                        }            
                    },        
                    x: {
                      title: {
                        display: true,
                        text: 'Date',
                        font: {
                            size: 13,
                            weight:'bold'
                        }            
                      }
                        // type: 'time',
                        // time: {
                        //   unit: 'second'
                        // }              
                    //   type: 'time',
                    //   time: {
                    //     parser: 'yyyy-MM-dd',
                    //     unit: 'day',
                    //     tooltipFormat: 'dd-MM',
                    //     displayFormats: {
                    //       day: 'dd-MM'
                    //     }                
                    // }              
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        display: true,
                    },
                },
                maintainAspectRatio:false,
                responsive: true,
              },
            //   plugins: [white_back],
            };
            
            let chartStatus = Chart.getChart("year_kpi"); // <canvas> id
            if (chartStatus != undefined) {
              chartStatus.destroy();
            }
                                
            
            const year_kpi = new Chart(
              document.getElementById('year_kpi'),
              configKPIday
            );       
            
            $("#Year .custom_hide_card").css('display','block');
            
        },
        complete: function() { EES.btnReset(_btn); }
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


//===========================================CUSTOM QUERY SCRIPT=========================================================

//Create Custom chart

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
    
    
    const configKPI = {
      options: {
        // responsive: false,
        enabled: true,
        scales: {       
            A: {
                type: 'linear',
                position: 'left',
                title: {
                    display: true,
                    text: 'Active Power (kW)',
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
                    text: 'Irradiance (W/m2)',
                    font: {
                        size: 17,
                        weight: 'bold'
                    }                
                },
                grid:{
                    display:false
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
                }            
            },
            D: {
                type: 'linear',
                position: 'right',
                title: {
                    display: true,
                    text: 'Temperature (Â°C)',
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
              },
              type: 'time',
              parsing: true,
              time: {
                    parser: 'yyyy-MM-dd HH:mm:ss',
                    unit: 'hour',
                    tooltipFormat: 'HH:mm',
                    displayFormats: {
                      hour: 'dd-MM-yyyy HH:mm'
                    }                
                }
              
              
            },              
            },
            plugins: {
                zoom: zoomOptions,
                legend: {
                    position: 'bottom',
                    display: true
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
        // interaction: {
        //     mode: 'index'
        // },
      
    };
    
    // render init block
    custom_kpi = new Chart(
      document.getElementById('custom_chart'),
      configKPI
    );


function start_date_validate(){
    
    document.getElementById("end_date").value = "";
    
    document.getElementById("end_date").setAttribute("min", document.getElementById("start_date").value); 
    
}

var custom_kpi;
var color_count = 0;

function get_custom(){
    var _btn = document.querySelector('#Custom .btn-add-custom-chart');
    EES.btnLoad(_btn, 'Loading…');

    let chart_colors = ['#3366cc','#dc3912','#ff9900','#109618','#990099','#0099c6','#dd4477','#66aa00','#b82e2e','#316395','#994499','#22aa99','#aaaa11','#6633cc','#e67300','#8b0707','#651067','#329262','#5574a6','#3b3eac','#b77322','#16d620','#b91383','#f4359e','#9c5935','#a9c413','#2a778d','#668d1c','#bea413','#0c5922','#743411'];
    
    let chart_labels = [];
    let kpi_irradiance = [];
    let kpi_prod = [];
    let kpi_active = [];
    let kpi_ambientTemp = [];
    let kpi_panelTemp = [];
    
    site = document.getElementById("sites_opt_custom").value; 
    
    let inputs = document.getElementsByName('custom_meters');
    
    // let meters = [];
    let meters = "";
    let arr_meters = [];
    
    for(let i = 0; i < inputs.length; i++){
        
        if(inputs[i].checked){
            // meters.push(inputs[i].value);
            meters += inputs[i].value + ",";
            arr_meters.push(inputs[i].value);
        }
        
    }
    
    meters = meters.slice(0,-1);
    
    let params = document.getElementsByName('meter_params');
    let parameter = "";
    
    for(let i = 0; i < params.length; i++){
        
        if(params[i].checked){
            parameter = params[i].value;
        }
        
    }
    
    let input_start = document.getElementById("start_date").value;
    let input_end = document.getElementById("end_date").value;

    //Irradiance Parameter
    
    let irr_param = 0; 
    
    if(document.getElementById('irradiance_param').checked == true){
        irr_param = 1;
    }
    
    //Ambient Temperature Parameter
    
    let amb_param = 0;
    
    if(document.getElementById('ambientTemp_param').checked == true){
        amb_param = 1;
    }
    
    //Panel Temperature Parameter
    
    let panel_param = 0;
    
    if(document.getElementById('panelTemp_param').checked == true){
        panel_param = 1;
    }
    
    
    if(meters.length < 1 && irr_param == 0 && amb_param == 0 && panel_param == 0){
        EES.alert('No meter selected!', 'warning');
    }
    else if(parameter == "" && irr_param == 0 && amb_param == 0 && panel_param == 0){
        EES.alert('No parameter selected!', 'warning');
    }
    else if(input_start == ""){
        EES.alert('No start date selected!', 'warning');
    }
    else if(input_end == ""){
        EES.alert('No end date selected!', 'warning');
    }
    else{
        
        
        let start_date = new Date(input_start).toISOString().split('T')[0];
        let end_date = new Date(input_end).toISOString().split('T')[0];
        
        // console.log(site);
        // console.log(meters);
        // console.log(parameter);
        // console.log(start_date);
        // console.log(end_date);
        
        let meter_name;
        
        $.ajax({
            type: "POST",
            url: "scripts/query_custom",
            data: {
                
                "site": site,
                "meters": meters,
                "arr_meters": arr_meters,
                "param": parameter,
                "start_date": start_date,
                "end_date": end_date,
                "irradiance": irr_param,
                "ambientTemp": amb_param,
                "panelTemp": panel_param
            },
            success: function(json) {
                let data = json.data;
                
                console.log(data);
                
                if(parameter == "prod"){
                    let chartType = document.getElementById("chart_type").value;
                    
                    for(let count = 0; count < arr_meters.length; count++){
                        
                        let prod_dataset = {
                            type: chartType,
                            yAxisID: 'C',
                            tension: 0.4,
                            pointRadius: 1
                        };
                        
                        meter_name = "";
                        chart_labels = [];
                        kpi_prod = [];
                        
                        for(let i = 0; i < data.length; i++){
                            
                            if(parseInt(data[i].meter_id) == parseInt(arr_meters[count])){
                                meter_name = data[i].meter_name;
                                chart_labels.push(data[i].datetime);
                                kpi_prod.push({"x": data[i].datetime, "y": data[i].production});
                                tbl_custom_prod.row.add([data[i].datetime, json.site_name, data[i].meter_name, data[i].production]).draw();
                            }
                            
                        }
                        
                        // console.log(kpi_prod);
                        
                        let color_id = color_count % 31;
                        if(chartType == "bar"){
                            // fill: true,
                            prod_dataset['fill'] = true;
                            
                        }
                        prod_dataset['backgroundColor'] = chart_colors[color_id];
                        prod_dataset['borderColor'] = chart_colors[color_id];
                        prod_dataset['label'] = meter_name + " - Production";
                        prod_dataset['order'] = 3;
                        prod_dataset['data'] = kpi_prod;
                        
                        
                        addData(chart_labels, prod_dataset);
                        color_count += 1;
                        
                    }
        
                }
                else if(parameter == "a_power"){
                    
                    for(let count = 0; count < arr_meters.length; count++){
                        
                        let active_dataset = {
                            type: 'line',
                            yAxisID: 'A',
                            fill: false,
                            tension: 0.4,
                            pointRadius: 1
                        };
                        
                        meter_name = "";
                        chart_labels = [];
                        kpi_active = [];
                        
                        for(let i = 0; i < data.length; i++){
                            
                            if(parseInt(data[i].meter_id) == parseInt(arr_meters[count])){
                                meter_name = data[i].meter_name;
                                chart_labels.push(data[i].datetime);
                                kpi_active.push({"x": data[i].datetime, "y": data[i].active_power});
                                tbl_custom_active.row.add([data[i].datetime, json.site_name, data[i].meter_name, data[i].active_power]).draw();
                            }
                            
                        }
                        
                        // console.log(kpi_prod);
                        
                        let color_id = color_count % 31;
                        
                        active_dataset['backgroundColor'] = chart_colors[color_id];
                        active_dataset['borderColor'] = chart_colors[color_id];
                        active_dataset['label'] = meter_name + " - Active Power";
                        active_dataset['order'] = 2;
                        active_dataset['data'] = kpi_active;
                        
                        addData(chart_labels, active_dataset);
                        color_count += 1;
                        
                    }
                    
                }
                
                //get irradiance data if checked
                if(irr_param == 1){
                    
                    let irr_dataset = {
                        type: 'line',
                        yAxisID: 'B',
                        fill: false,
                        tension: 0.4,
                        pointRadius: 1
                    };
                    
                    kpi_irradiance = [];
                    
                    for(let i = 0; i < data.length; i++){
                        
                        if(parseInt(data[i].meter_id) == 99 && ("irradiance" in data[i])){
                            
                            chart_labels.push(data[i].datetime);
                            kpi_irradiance.push({"x": data[i].datetime, "y": data[i].irradiance});
                            tbl_custom_irradiance.row.add([data[i].datetime, json.site_name, data[i].irradiance]).draw();
                        }
                        
                    }
                    
                    // console.log(kpi_prod);
                    
                    let color_id = color_count % 31;
                    
                    irr_dataset['backgroundColor'] = chart_colors[color_id];
                    irr_dataset['borderColor'] = chart_colors[color_id];
                    irr_dataset['label'] = "Irradiance";
                    irr_dataset['order'] = 1;
                    irr_dataset['data'] = kpi_irradiance;
                    
                    addData(chart_labels, irr_dataset);
                    color_count += 1;
                    
                }
                
                //get Ambient Temperature data if checked
                if(amb_param == 1){
                    
                    let ambient_dataset = {
                        type: 'line',
                        yAxisID: 'D',
                        fill: false,
                        tension: 0.4,
                        pointRadius: 1
                    };
                
                kpi_ambientTemp = [];
                    
                    for(let i = 0; i < data.length; i++){
                        
                        if(parseInt(data[i].meter_id) == 99 && ("ambient_temp" in data[i])){
                            
                            chart_labels.push(data[i].datetime);
                            kpi_ambientTemp.push({"x": data[i].datetime, "y": data[i].ambient_temp});
                            // tbl_custom_irradiance.row.add([data[i].datetime, json.site_name, data[i].ambient_temp]).draw();
                        }
                        
                    }
                    
                    // console.log(kpi_prod);
                    
                    let color_id = color_count % 31;
                    
                    ambient_dataset['backgroundColor'] = chart_colors[color_id];
                    ambient_dataset['borderColor'] = chart_colors[color_id];
                    ambient_dataset['label'] = "Ambient Temperature";
                    ambient_dataset['order'] = 1;
                    ambient_dataset['data'] = kpi_ambientTemp;
                    
                    addData(chart_labels, ambient_dataset);
                    color_count += 1;
                    
                }
                
                
                //get Panel Temperature data if checked
                if(panel_param == 1){
                    
                    let panel_dataset = {
                        type: 'line',
                        yAxisID: 'D',
                        fill: false,
                        tension: 0.4,
                        pointRadius: 1
                    };
                
                kpi_panelTemp = [];
                    
                    for(let i = 0; i < data.length; i++){
                        
                        if(parseInt(data[i].meter_id) == 99 && ("panel_temp" in data[i])){
                            
                            chart_labels.push(data[i].datetime);
                            kpi_panelTemp.push({"x": data[i].datetime, "y": data[i].panel_temp});
                            // tbl_custom_irradiance.row.add([data[i].datetime, json.site_name, data[i].ambient_temp]).draw();
                        }
                        
                    }
                    
                    // console.log(kpi_prod);
                    
                    let color_id = color_count % 31;
                    
                    panel_dataset['backgroundColor'] = chart_colors[color_id];
                    panel_dataset['borderColor'] = chart_colors[color_id];
                    panel_dataset['label'] = "Panel Temperature";
                    panel_dataset['order'] = 1;
                    panel_dataset['data'] = kpi_panelTemp;
                    
                    addData(chart_labels, panel_dataset);
                    color_count += 1;
                    
                }
                
                
                
                
                
               show_zoom(); 
               
                for(let i = 0; i < inputs.length; i++){
                    inputs[i].checked = false;
                }
                
                for(let i = 0; i < params.length; i++){
                    params[i].checked = false;
                }
               document.getElementById('irradiance_param').checked = false;
               document.getElementById('ambientTemp_param').checked = false;
               document.getElementById('panelTemp_param').checked = false;
                // Keep parameter panels visible; only reset chart type until Production is selected again
                $("#chart_type").hide();
                $("#con_meter_parameters").show();
            },
            complete: function() { EES.btnReset(_btn); }
        });
    
    }
}


function resetZoomBtn(){
    custom_kpi.resetZoom();
}

function addData(label, newData) {
    
    // custom_kpi.data.labels.push(label);
    // custom_kpi.data.datasets.forEach((dataset) => {
    //     dataset.data.push(newData);
    // });
    
    custom_kpi.data.labels = label;
    custom_kpi.data.datasets.push(newData);
    custom_kpi.update();
}

function removeData() {
    custom_kpi.data.labels.pop();
    // custom_kpi.data.datasets.forEach((dataset) => {
    //     dataset.data.pop();
    // });
    custom_kpi.data.datasets.pop();
    custom_kpi.update();
}
 



// --- next block ---



  


// --- next block ---



function download() {
    const imageLink = document.createElement('a');
    const canvas = document.getElementById('myChart');
    imageLink.download = 'report.png';
    imageLink.href = canvas.toDataURL('image/png', 1);
    imageLink.click();
}

function updateAll(selectall) {
  let selectallcheckbox = document.getElementById('selectallcheckbox');
  let checkboxes = document.querySelectorAll('.datacheckbox');
  if (selectall.checked === false) {
    for (let i = 0; i < checkboxes.length; i++) {
      checkboxes[i].checked = false;
      myChart.hide(i);
    }
  };
  if (selectall.checked === true) {
    for (let i = 0; i < checkboxes.length; i++) {
      checkboxes[i].checked = true;
      myChart.show(i);
    }
  };      
};

function checkboxSelectAllChecker() {
  let selectallcheckbox = document.getElementById('selectallcheckbox');
  let checkboxes = document.querySelectorAll('.datacheckbox');

  let x = 0;
  for (let i = 0;i <= checkboxes.length -1; i++) {
    if (checkboxes[i].checked === true) {
      x++;
    }
  };

  if (x == checkboxes.length) {
    selectallcheckbox.checked = true;
  } else {
    selectallcheckbox.checked = false;
  }
};

function updateChart(dataset) {
  console.log(dataset.value);
  const isDataShown = myChart.isDatasetVisible(dataset.value);
  if (isDataShown === false) {
    myChart.show(dataset.value);        
  };
  if (isDataShown === true) {
    myChart.hide(dataset.value);        
  };   
  checkboxSelectAllChecker();   
};

function filterData() {
    const dates2 = [...dates];
    const startdate = document.getElementById('startdate');
    const enddate = document.getElementById('enddate');

    const indexstartdate = dates2.indexOf(startdate.value);
    const indexenddate = dates2.indexOf(enddate.value);

    const filterDate = dates2.slice(indexstartdate, indexenddate);

    myChart.config.data.labels = filterDate;
    myChart.update();

}



// --- next block ---


function downloadCustom() {
    const imageLink = document.createElement('a');
    const canvas = document.getElementById('custom_chart');
    imageLink.download = 'custom_chart.png';
    imageLink.href = canvas.toDataURL('image/png', 1);
    imageLink.click();
}