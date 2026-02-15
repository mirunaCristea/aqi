<?php
// --- CONFIGURARE ---
$api_token = "796f09ec27e5b0ba3019897e0cdafd001f4cb733"; // RECOMANDARE: Obține un token gratuit de pe https://aqicn.org
$cache_file = __DIR__ . '/cache/aqi_cache.json';;
$cache_time = 3600; // 1 oră în secunde

// Mapare Cod Județ -> Oraș pentru API
$judete_config = [
    "AB" => ["nume" => "Alba Iulia", "x" => 192, "y" => 181],
    "AG" => ["nume" => "Pitesti",    "x" => 270, "y" => 261],
    "AR" => ["nume" => "Arad",       "x" => 83,  "y" => 166],
    "BC" => ["nume" => "Bacau",      "x" => 386, "y" => 161],
    "BH" => ["nume" => "Oradea",     "x" => 99,  "y" => 113],
    "BN" => ["nume" => "Bistrita",   "x" => 243, "y" => 78],
    "BR" => ["nume" => "Braila",     "x" => 447, "y" => 275],
    "BV" => ["nume" => "Brasov",     "x" => 289, "y" => 225],
    "BT" => ["nume" => "Botosani",   "x" => 363, "y" => 29],
    "BZ" => ["nume" => "Buzau",      "x" => 382, "y" => 252],
    "CJ" => ["nume" => "Cluj-Napoca","x" => 191, "y" => 135],
    "CL" => ["nume" => "Calarasi",   "x" => 403, "y" => 345],
    "CS" => ["nume" => "Resita",     "x" => 80,  "y" => 270],
    "CT" => ["nume" => "Constanta",  "x" => 461, "y" => 366],
    "CV" => ["nume" => "Sfantu Gheorghe", "x" => 331, "y" => 201],
    "DB" => ["nume" => "Targoviste", "x" => 291, "y" => 303],
    "DJ" => ["nume" => "Craiova",    "x" => 190, "y" => 364],
    "GJ" => ["nume" => "Targu Jiu",  "x" => 174, "y" => 292],
    "GL" => ["nume" => "Galati",     "x" => 444, "y" => 212],
    "GR" => ["nume" => "Giurgiu",    "x" => 338, "y" => 367],
    "HD" => ["nume" => "Deva",       "x" => 130, "y" => 227],
    "HR" => ["nume" => "Miercurea Ciuc", "x" => 301, "y" => 167],
    "IL" => ["nume" => "Slobozia",   "x" => 409, "y" => 318],
    "IS" => ["nume" => "Iasi",       "x" => 427, "y" => 90],
    "MH" => ["nume" => "Drobeta-Turnu Severin", "x" => 115, "y" => 310],
    "MM" => ["nume" => "Baia Mare",  "x" => 205, "y" => 48],
    "MS" => ["nume" => "Targu Mures","x" => 254, "y" => 148],
    "NT" => ["nume" => "Piatra Neamt","x" => 363, "y" => 117],
    "OT" => ["nume" => "Slatina",    "x" => 256, "y" => 348],
    "PH" => ["nume" => "Ploiesti",   "x" => 334, "y" => 276],
    "SB" => ["nume" => "Sibiu",      "x" => 240, "y" => 201],
    "SJ" => ["nume" => "Zalau",      "x" => 159, "y" => 97],
    "SM" => ["nume" => "Satu Mare",  "x" => 137, "y" => 45],
    "SV" => ["nume" => "Suceava",    "x" => 318, "y" => 64],
    "TL" => ["nume" => "Tulcea",     "x" => 519, "y" => 271],
    "TM" => ["nume" => "Timisoara",  "x" => 48,  "y" => 217],
    "TR" => ["nume" => "Alexandria", "x" => 272, "y" => 383],
    "VL" => ["nume" => "Ramnicu Valcea", "x" => 212, "y" => 251],
    "VN" => ["nume" => "Focsani",    "x" => 389, "y" => 221],
    "VS" => ["nume" => "Vaslui",     "x" => 446, "y" => 146],
    "IF" => ["nume" => "Otopeni",    "x" => 350, "y" => 320],
    "B"  => ["nume" => "Bucharest",  "x" => 340, "y" => 333],
];

// --- LOGICA CACHE & API ---
$aqi_data = [];
if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
    $aqi_data = json_decode(file_get_contents($cache_file), true);
} else {
    foreach ($judete_config as $code => $info) {
        $url = "https://api.waqi.info" . urlencode($info['nume']) . "/?token=" . $api_token;
        $resp = @file_get_contents($url);
        if ($resp) {
            $json = json_decode($resp, true);
            $aqi_data[$code] = ($json['status'] == "ok") ? $json['data']['aqi'] : "??";
        } else {
            $aqi_data[$code] = "??";
        }
        usleep(50000); // Mică pauză să nu blocăm serverul API
    }
    file_put_contents($cache_file, json_encode($aqi_data));
}

// Funcție pentru culoarea fundalului în funcție de AQI
function getAqiColor($val) {
    if (!is_numeric($val)) return "#ccc";
    if ($val <= 50)  return "#009966"; // Bun (Verde)
    if ($val <= 100) return "#ffde33"; // Moderat (Galben)
    if ($val <= 150) return "#ff9933"; // Nesănătos pt sensibili (Portocaliu)
    return "#cc0033"; // Rău (Roșu)
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Harta AQI Romania</title>
    <style>
        .map-container {
            position: relative;
            display: inline-block;
            font-family: Arial, sans-serif;
        }
        .map-label {
            position: absolute;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            color: white;
            text-shadow: 1px 1px 1px #000;
            pointer-events: none; /* Click-ul trece prin label către hartă */
            min-width: 20px;
            text-align: center;
            border: 1px solid rgba(0,0,0,0.2);
        }
        img { display: block; }
    </style>
</head>
<body>

<div class="map-container">
    <map name="fpMap1">
    <!-- Etichetele generate dinamic -->
    <?php foreach ($judete_config as $code => $info): 
        $val = $aqi_data[$code];
        $bg = getAqiColor($val);
    ?>
        <span class="map-label" style="top: <?=$info['y']?>px; left: <?=$info['x']?>px; background-color: <?=$bg?>;">
            <?=$val?>
        </span>
    <?php endforeach; ?>

    <!-- Imaginea Hartă -->
    <img border="0" height="423" id="img1" src="map2.gif" usemap="#fpMap1" width="600">
    </map>
</div>

</body>
</html>