<?php

$api_token = "796f09ec27e5b0ba3019897e0cdafd001f4cb733"; // Ia unul gratuit de pe https://aqicn.org
$cache_dir  = __DIR__ . '/cache';
$cache_file = $cache_dir . '/aqi_cache.json';
$cache_time = 3600; 

$judete = [
    "AB" => "Alba", "AG" => "Pitesti", "AR" => "Arad", "BC" => "Bacau", "BH" => "Oradea",
    "BN" => "Bistrita", "BR" => "Braila", "BV" => "Brasov", "BT" => "Botosani", "BZ" => "Buzau",
    "CJ" => "Cluj-Napoca", "CL" => "Calarasi", "CS" => "Resita", "CT" => "Constanta", "CV" => "Sfantu-Gheorghe",
    "DB" => "Targoviste", "DJ" => "Craiova", "GJ" => "Targu-Jiu", "GL" => "Galati", "GR" => "Giurgiu",
    "HD" => "Deva", "HR" => "Miercurea-Ciuc", "IL" => "Slobozia", "IS" => "Iasi", "MH" => "Drobeta",
    "MM" => "Baia-Mare", "MS" => "Targu-Mures", "NT" => "Piatra-Neamt", "OT" => "Slatina", "PH" => "Ploiesti",
    "SB" => "Sibiu", "SJ" => "Zalau", "SM" => "Satu-Mare", "SV" => "Suceava", "TL" => "Tulcea",
    "TM" => "Timisoara", "TR" => "Alexandria", "VL" => "Ramnicu-Valcea", "VN" => "Focsani", "VS" => "Vaslui",
    "IF" => "Otopeni", "B"  => "Bucharest"
];

$aqi_data = [];

if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
    $aqi_data = json_decode(file_get_contents($cache_file), true);
}

if (empty($aqi_data)) {
    foreach ($judete as $code => $city) {
        $url = "https://api.waqi.info/feed/" . urlencode($city) . "/?token=" . $api_token;
        $resp = @file_get_contents($url);
        if ($resp) {
            $json = json_decode($resp, true);
            if (isset($json['status']) && $json['status'] == "ok") {
                $aqi_data[$code] = [
                    'aqi' => $json['data']['aqi'],
                    'iaqi' => $json['data']['iaqi'] ?? [],
                    'city' => $city,
                    'time' => date("H:i")
                ];
            } else {
                $aqi_data[$code] = ['aqi' => '??', 'iaqi' => [], 'city' => $city, 'time' => '--'];
            }
        }
    }
    file_put_contents($cache_file, json_encode($aqi_data));
}


#echo json_encode($aqi_data);
?>
