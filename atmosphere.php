<?php
// Configuration du contexte pour les requêtes HTTP
$opts = [
    'http' => [
        'proxy' => 'www-cache:3128',
        'request_fulluri' => true
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
];
$context = stream_context_create($opts);

// Récupération de l'adresse IP
$ip = $_SERVER['REMOTE_ADDR'];

// Récupération des coordonnées géographiques
$geoData = json_decode(file_get_contents('http://ip-api.com/json/' . $ip, false, $context));
$lat = strval($geoData->lat);
$lon = strval($geoData->lon);

// Récupération des données météo
$bruteMeteo = file_get_contents("https://www.infoclimat.fr/public-api/gfs/xml?_ll={$lat},{$lon}&_auth=ARsDFFIsBCZRfFtsD3lSe1Q8ADUPeVRzBHgFZgtuAH1UMQNgUTNcPlU5VClSfVZkUn8AYVxmVW0Eb1I2WylSLgFgA25SNwRuUT1bPw83UnlUeAB9DzFUcwR4BWMLYwBhVCkDb1EzXCBVOFQoUmNWZlJnAH9cfFVsBGRSPVs1UjEBZwNkUjIEYVE6WyYPIFJjVGUAZg9mVD4EbwVhCzMAMFQzA2JRMlw5VThUKFJiVmtSZQBpXGtVbwRlUjVbKVIuARsDFFIsBCZRfFtsD3lSe1QyAD4PZA%3D%3D&_c=19f3aa7d766b6ba91191c8be71dd1ab2", false, $context);
$xmlMeteo = simplexml_load_string($bruteMeteo);

// XSLT Transformation
$xsl = new DOMDocument();
$xsl->load('meteo.xsl');
$processor = new XSLTProcessor();
$processor->importStylesheet($xsl);
$dateDemain = date('Y-m-d', strtotime('+1 day'));
$heures = [
    'heureMatin' => "{$dateDemain} 07:00:00",
    'heureMidi' => "{$dateDemain} 13:00:00",
    'heureSoir' => "{$dateDemain} 19:00:00"
];

foreach ($heures as $key => $value) {
    $processor->setParameter('', $key, $value);
}

$htmlMeteo = $processor->transformToXML($xmlMeteo);

// Récupération de la localisation personnalisée
$customLocalisation = file_get_contents('https://api-adresse.data.gouv.fr/search/?q=7+pl+de+la+goulotte&postcode=54136', false, $context);
$jsonLocalisation = json_decode($customLocalisation);
$latCustomLocalisation = $jsonLocalisation->features[0]->geometry->coordinates[1];
$lonCustomLocalisation = $jsonLocalisation->features[0]->geometry->coordinates[0];

// Affichage du résultat
echo <<<HTML
<!DOCTYPE HTML>
<html lang="fr">
<head>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="atmosphere.css"/>
    <title>atmosphere</title>
</head>
<body>
$htmlMeteo
<div id="qualiteAir">
    <p id="airQuality">Qualité de l'air</p>
</div>
<div id="map"></div>

<div id="links">
<div class="link"><p>Github : </p><a href="https://github.com/4n0m4lie/Atmosphere">https://github.com/4n0m4lie/Atmosphere</a></div>
<div class="link"><p>Géolocalisation : </p><a href="http://ip-api.com/json/$ip">http://ip-api.com/json/$ip</a></div>
<div class="link"><p>Météo : </p><a href=https://www.infoclimat.fr/public-api/gfs/xml?_ll=$lat,$lon&_auth=ARsDFFIsBCZRfFtsD3lSe1Q8ADUPeVRzBHgFZgtuAH1UMQNgUTNcPlU5VClSfVZkUn8AYVxmVW0Eb1I2WylSLgFgA25SNwRuUT1bPw83UnlUeAB9DzFUcwR4BWMLYwBhVCkDb1EzXCBVOFQoUmNWZlJnAH9cfFVsBGRSPVs1UjEBZwNkUjIEYVE6WyYPIFJjVGUAZg9mVD4EbwVhCzMAMFQzA2JRMlw5VThUKFJiVmtSZQBpXGtVbwRlUjVbKVIuARsDFFIsBCZRfFtsD3lSe1QyAD4PZA%3D%3D&_c=19f3aa7d766b6ba91191c8be71dd1ab2">https://www.infoclimat.fr/public-api/gfs/xml</a></div>
<div class="link"><p>Localisation custom : </p><a href="https://api-adresse.data.gouv.fr/search/?q=7+pl+de+la+goulotte&postcode=54136">https://api-adresse.data.gouv.fr/search/?q=7+pl+de+la+goulotte&postcode=54136</a></div>
</div>
<script>
    const timestampNow = Date.now();
    
    fetch('https://services3.arcgis.com/Is0UwT37raQYl9Jj/arcgis/rest/services/ind_grandest/FeatureServer/0/query?where=lib_zone%3D%27Nancy%27&objectIds=&time=&geometry=&geometryType=esriGeometryEnvelope&inSR=&spatialRel=esriSpatialRelIntersects&resultType=none&distance=0.0&units=esriSRUnit_Meter&returnGeodetic=false&outFields=*&returnGeometry=true&featureEncoding=esriDefault&multipatchOption=xyFootprint&maxAllowableOffset=&geometryPrecision=&outSR=&datumTransformation=&applyVCSProjection=false&returnIdsOnly=false&returnUniqueIdsOnly=false&returnCountOnly=false&returnExtentOnly=false&returnQueryGeometry=false&returnDistinctValues=false&cacheHint=false&orderByFields=&groupByFieldsForStatistics=&outStatistics=&having=&resultOffset=&resultRecordCount=&returnZ=false&returnM=false&returnExceededLimitFeatures=true&quantizationParameters=&sqlFormat=none&f=pjson&token=')
        .then(response => response.json())
        .then(data => {
            for(let temp of data['features']){
               let dateEcheance = new Date(temp['attributes']['date_ech']);
               let aujourdHui = new Date();
               if (dateEcheance.getDate() === aujourdHui.getDate() && dateEcheance.getMonth() === aujourdHui.getMonth() && dateEcheance.getFullYear() === aujourdHui.getFullYear()) {
                        document.getElementById('airQuality').style.backgroundColor = temp['attributes'].coul_qual;
                   }
            }
        });

    let lat = $lat;
    let long = $lon;
    let latCustom = $latCustomLocalisation;
    let longCustom = $lonCustomLocalisation;

    const map = L.map('map').setView([lat, long], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 11 }).addTo(map);
    L.marker([lat, long]).addTo(map)
        .bindPopup('Votre position géographique actuelle.')
        .openPopup();
    L.marker([latCustom, longCustom]).addTo(map)
        .bindPopup('Localisation custom')
        .openPopup();
    
    // Charger les données de difficultés de circulation
    fetch('cifs_waze_v2.json')
        .then(response => response.json())
        .then(data => {
            data.incidents.forEach(incident => {
                let polyline = incident.location.polyline.split(" ");
                let lat = parseFloat(polyline[0]);
                let long = parseFloat(polyline[1]);
                let type = incident.type; 
                let description = incident.description;
                let starttime = new Date(incident.starttime).toLocaleString();
                let endtime = new Date(incident.endtime).toLocaleString();
                let locationDescription = incident.location.location_description;

                L.marker([lat, long]).addTo(map)
                    .bindPopup("Type: " + type + "<br>Description: " + description + "<br>Lieu: " + locationDescription + "<br>Début: " + starttime + "<br>Fin: " + endtime);
            });
        })
        .catch(error => console.error('Erreur lors du chargement de difficulté.json:', error));
</script>
</body>
</html>
HTML;