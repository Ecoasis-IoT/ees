<html>

<head>
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
     
     <style>
         
         #map { height: 600px; width: 600px}
         
     </style>
     
</head>
    


<body>
    
    <div id="map"></div>


<script>
    
    var locations = [
      ["<b>Phoenix Mall</b><br>Production: 343 kWh", -20.25701654715424, 57.58117710244664],
      ["<b>Bo'Valon Mall</b><br>Production: 301 kWh", -20.423935455283544, 57.697808864391945],
      ["<b>Riche Terre Mall</b><br>Production: 313 kWh", -20.130185776851476, 57.52166048376673],
      ["<b>Plaisance Catering</b><br>Production: 424 kWh", -20.431078110707045, 57.67028039542038],
      ["<b>Home and Leisure</b><br>Production: 303 kWh", -20.22653300090083, 57.498257108906685],
      ["<b>Moka City</b><br>Production: 390 kWh", -20.23154224148942, 57.50824329541379]
    ];    
    
    var map = L.map('map',{ attributionControl:false },{zoomDelta: 0.9},
    {zoomSnap: 0}).setView([-20.25701654715424, 57.58117710244664], 10);
    
    
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    },{color: "#ff7800", weight: 1}).addTo(map);
        

    for (var i = 0; i < locations.length; i++) {
      marker = new L.marker([locations[i][1], locations[i][2]])
        .bindPopup(locations[i][0])
        .addTo(map);
    }    
    
</script>

  
</body>




</html>