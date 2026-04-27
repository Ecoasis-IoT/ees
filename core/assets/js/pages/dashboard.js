/**
 * Dashboard page — map initialisation and site data loading
 */
(function () {
    'use strict';

    // KPI helpers
    function setKpi(id, val) {
        var el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    function fmtNum(n) {
        var num = parseFloat(n) || 0;
        var v = num >= 1000
            ? (num / 1000).toFixed(1) + 'k'
            : num.toFixed(2);
        return v + ' kWh total';
    }

    // Map initialisation
    var map = new L.map('sites_map').setView([-20.2337508, 57.5510122], 10);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        zoomSnap: 0.1,
        fullscreenControl: true
    }).addTo(map);

    map.addControl(new L.Control.Fullscreen());

    // Sites production table + bar chart
    var chart_labels = [];
    var chart_prod   = [];

    $(function site_power() {
        $.ajax({
            type:     'POST',
            url:      'scripts/get_dashboard',
            dataType: 'json',
            data: {},
            success: function (data) {

                // Populate KPI cards
                var totalProd = 0;
                for (var k = 0; k < data.length; k++) {
                    totalProd += parseFloat(data[k].prod) || 0;
                }
                setKpi('kpi-total-sites', data.length);
                var prodEl = document.getElementById('kpi-total-prod');
                if (prodEl) {
                    prodEl.textContent = fmtNum(totalProd);
                }

                for (var i = 0; i < data.length; i++) {
                    var href = (data[i].dashboard_href || 'site-dashboard') + '?site=' + encodeURIComponent(data[i].id);
                    var row = "<tr><td><a href='" + href + "'>" + data[i].site_name + "</a></td><td>" + data[i].prod + "</td><td>" + data[i].active_power + "</td></tr>";

                    $('#tbl_site_prod tbody').append(row);

                    chart_labels.push(data[i].site_name);
                    chart_prod.push(data[i].prod);

                    var coordinates = data[i].location.split(',');
                    var marker = L.marker([coordinates[0], coordinates[1]])
                        .bindTooltip(data[i].site_name, { permanent: false, direction: 'right', offset: L.point(-14, -5) })
                        .addTo(map);
                    marker.bindPopup('<b>' + data[i].site_name + '</b><br>Production (kWh): ' + data[i].prod, { closeOnClick: false, autoClose: false });
                }

                var chart_data = {
                    labels: chart_labels,
                    datasets: [{
                        label: 'Production (kWh)',
                        data:  chart_prod,
                        backgroundColor: 'rgba(112,173,71,.75)',
                        borderColor:     '#70AD47',
                        borderWidth:     1,
                        borderRadius:    4
                    }]
                };

                var zoomOpts = {
                    pan: { enabled: true, modifierKey: 'ctrl' },
                    zoom: {
                        wheel: { enabled: true },
                        pinch: { enabled: true },
                        mode: 'xy'
                    }
                };

                var config = {
                    type: 'bar',
                    data: chart_data,
                    options: {
                        scales:  { y: { beginAtZero: true } },
                        maintainAspectRatio: false,
                        responsive: true,
                        plugins: { zoom: zoomOpts }
                    }
                };

                new Chart(document.getElementById('myChart'), config);
            }
        });
    });

}());
