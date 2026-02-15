<?php
include 'api.php';
$aqi_json = @file_get_contents('./cache/aqi_cache.json');
$aqi_data = json_decode($aqi_json, true) ?: [];

$coords = [
    "AB"=>["x"=>192,"y"=>181],"AG"=>["x"=>270,"y"=>261],"AR"=>["x"=>83,"y"=>166],"BC"=>["x"=>386,"y"=>161],
    "BH"=>["x"=>99,"y"=>113],"BN"=>["x"=>243,"y"=>78],"BR"=>["x"=>447,"y"=>275],"BV"=>["x"=>289,"y"=>225],
    "BT"=>["x"=>363,"y"=>29],"BZ"=>["x"=>382,"y"=>252],"CJ"=>["x"=>191,"y"=>135],"CL"=>["x"=>403,"y"=>345],
    "CS"=>["x"=>80,"y"=>270],"CT"=>["x"=>461,"y"=>366],"CV"=>["x"=>331,"y"=>201],"DB"=>["x"=>291,"y"=>303],
    "DJ"=>["x"=>190,"y"=>364],"GJ"=>["x"=>174,"y"=>292],"GL"=>["x"=>444,"y"=>212],"GR"=>["x"=>338,"y"=>367],
    "HD"=>["x"=>130,"y"=>227],"HR"=>["x"=>301,"y"=>167],"IL"=>["x"=>409,"y"=>318],"IS"=>["x"=>427,"y"=>90],
    "MH"=>["x"=>115,"y"=>310],"MM"=>["x"=>205,"y"=>48],"MS"=>["x"=>254,"y"=>148],"NT"=>["x"=>363,"y"=>117],
    "OT"=>["x"=>256,"y"=>348],"PH"=>["x"=>334,"y"=>276],"SB"=>["x"=>240,"y"=>201],"SJ"=>["x"=>159,"y"=>97],
    "SM"=>["x"=>137,"y"=>45],"SV"=>["x"=>318,"y"=>64],"TL"=>["x"=>519,"y"=>271],"TM"=>["x"=>48,"y"=>217],
    "TR"=>["x"=>272,"y"=>383],"VL"=>["x"=>212,"y"=>251],"VN"=>["x"=>389,"y"=>221],"VS"=>["x"=>446,"y"=>146],
    "IF"=>["x"=>350,"y"=>320],"B"=>["x"=>340,"y"=>333]
];

function getAqiColor($val) {
    if (!is_numeric($val)) return "#475569";
    if ($val <= 50)  return "#22c55e"; 
    if ($val <= 100) return "#eab308";
    if ($val <= 150) return "#f97316";
    return "#ef4444";
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Air Quality Live România</title>
    <style>
        body { background: #0f172a; color: white; font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; flex-direction: column; align-items: center; }
        
        /* Eliminăm padding-ul care cauza offset sau îl gestionăm corect */
        .map-container { 
            position: relative; 
            margin-top: 40px; 
            background: #1e293b; 
            padding: 0; /* Setat la 0 pentru aliniere perfectă cu coordonatele */
            border-radius: 20px; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
            border: 10px solid #1e293b; /* Adăugăm bordură în loc de padding */
        }

        .map-label {
            position: absolute; 
            padding: 5px 10px; 
            border-radius: 8px; 
            font-size: 13px; 
            font-weight: 800;
            color: white; 
            cursor: pointer; 
            transform: translate(-50%, -50%); 
            transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 2px solid rgba(255,255,255,0.2); 
            z-index: 10;
            box-shadow: 0 4px 10px rgba(0,0,0,0.5);
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        .map-label:hover { 
            transform: translate(-50%, -50%) scale(1.4); 
            z-index: 100; 
            box-shadow: 0 0 20px rgba(255,255,255,0.4);
            border-color: white;
        }

        img { display: block; border-radius: 10px; filter: brightness(0.8); }
        
        #modal { display:none; position:fixed; inset:0; background:rgba(15,23,42,0.9); z-index:999; justify-content:center; align-items:center; backdrop-filter: blur(8px); }
        .modal-content { background:#1e293b; padding:30px; border-radius:25px; width:340px; border: 1px solid #334155; text-align: center; position: relative; box-shadow: 0 30px 60px rgba(0,0,0,0.6); }
        .close { position: absolute; top: 15px; right: 20px; font-size: 30px; cursor: pointer; color: #94a3b8; line-height: 1; }
        .close:hover { color: white; }

        .pollutant-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 25px; }
        .pollutant-item { background: rgba(255,255,255,0.05); padding: 12px; border-radius: 15px; border: 1px solid rgba(255,255,255,0.1); }
        .p-name { font-size: 11px; color: #94a3b8; display: block; text-transform: uppercase; margin-bottom: 5px; }
        .p-val { font-size: 18px; font-weight: 700; color: #38bdf8; }
    </style>
</head>
<body>

    <h1 style="margin-top: 30px; font-weight: 800; letter-spacing: -1px;">CALITATE AER <span style="color:#38bdf8;">ROMÂNIA</span></h1>

    <div class="map-container">
        <?php foreach ($coords as $code => $pos): 
            $info = $aqi_data[$code] ?? ['aqi' => '??', 'iaqi' => [], 'city' => $code];
            $color = getAqiColor($info['aqi']);
        ?>
            <div class="map-label" 
                 style="left: <?php echo $pos['x']; ?>px; top: <?php echo $pos['y']; ?>px; background: <?php echo $color; ?>;"
                 onclick='openModal(<?php echo json_encode($info); ?>, "<?php echo $code; ?>")'>
                <?php echo $info['aqi']; ?>
            </div>
        <?php endforeach; ?>
        <img src="map2.gif" width="600" height="423">
    </div>

    <div id="modal" onclick="this.style.display='none'">
        <div class="modal-content" onclick="event.stopPropagation()">
            <span class="close" onclick="document.getElementById('modal').style.display='none'">&times;</span>
            <h2 id="mCity" style="margin:0; color:#38bdf8; font-size: 24px;">Oraș</h2>
            <div style="font-size: 64px; font-weight: 900; margin: 10px 0; line-height: 1;" id="mAqi">0</div>
            <div style="color: #94a3b8; font-size: 14px; margin-bottom: 20px;">AQI INDEX</div>
            
            <div id="mPollutants" class="pollutant-grid"></div>
        </div>
    </div>

    <script>
    function openModal(data, code) {
        document.getElementById('mCity').innerText = "Rapoarte " + (data.city || code);
        document.getElementById('mAqi').innerText = data.aqi;
        
        // Definim poluanții pe care îi vrem
        const keys = {
            pm25: "PM 2.5", 
            pm10: "PM 10", 
            no2: "NO2", 
            so2: "SO2", 
            o3: "O3", 
            co: "CO"
        };
        
        let html = "";
        for (let k in keys) {
            let val = (data.iaqi && data.iaqi[k]) ? data.iaqi[k].v : "N/A";
            html += `
                <div class="pollutant-item">
                    <span class="p-name">${keys[k]}</span>
                    <span class="p-val">${val}</span>
                </div>`;
        }
        
        document.getElementById('mPollutants').innerHTML = html;
        document.getElementById('modal').style.display = 'flex';
    }
    </script>
</body>
</html>
