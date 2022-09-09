<?php

// Kötelező állomásnév paraméter ellenőrzése
$station = $_GET["station"] ?? "";
if($station==""){die("Állomás megadása kötelező");}

// oroszi.net féle MÁV api meghívása
$response = json_decode(file_get_contents("https://apiv2.oroszi.net/elvira/leaderboard?station={$station}"), true);

// Ha nem kapunk vissza állomásnevet, akkor az nem található
if($response["station"] == ""){
    die("Érvénytelen állomásnév!");
}

?><!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Utastájékoztató</title>
    <link rel="icon" type="image/png" sizes="16x16" href="assets/img/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/img/favicon-32x32.png">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/styles.min.css">
</head>

<body style="color: white;">
    <script>
        // Óra
        function updateTime(){
            var time = document.getElementById("time");
            var date = new Date();
            time.innerText = `${`${date.getHours()}`.padStart(2, "0")}:${`${date.getMinutes()}`.padStart(2, "0")}:${`${date.getSeconds()}`.padStart(2, "0")}`;
        }
        setInterval(updateTime, 1000);
    </script>
    <div>
        <div class="d-flex" style="background: #010141;">
            <h3 id="time" style="margin: 10px;">00:00:00</h3>
            <div style="width: 100%;">
                <h1 style="text-align: center;margin: 0px;width: 100%;"><?=$response["station"]?> vasútállomás</h1>
                <h4 style="text-align: center;margin-bottom: 0px;">Érkező és induló vonatok</h4>
            </div>
        </div>
        <div class="table-responsive" style="color: white;">
            <table class="table">
                <thead style="background: #010141;color: rgb(255,255,255);">
                    <tr>
                        <th>Érkezés</th>
                        <th>Indulás</th>
                        <th>Név / típus</th>
                        <th>Útvonal</th>
                        <th>Vonatszám</th>
                        <th>Vágány</th>
                    </tr>
                </thead>
                <tbody style="color: white;border-width: 0px;font-weight: bold;">
                <?php
                
                // Számláló a váltakozó színű sorokhoz
                $counter = 0;
                // Összes visszakapott vonat
                foreach ($response["trains"] as $train) {
                    // Csak azokat a vonatokat mutassa, amelyek később indulnak vagy érkeznek, mint a jelenlegi idő
                    if($train['schedule']['arrival'] != "" && strtotime($train['schedule']['arrival']) < strtotime("now")+7200){
                        continue;
                    }
                    if($train['schedule']['departure'] != "" && strtotime($train['schedule']['departure']) < strtotime("now")+7200){
                        continue;
                    }

                    // Késés vizsgálata
                    $delay = array();
                    if(isset($train['real'])){
                        // Kései érkezés vizsgálata
                        if($train['schedule']['arrival'] != ""){
                            // Valódi érkezés - tervezett érkezés másodpercben / 60
                            $delay["arrival"] = abs(strtotime($train['real']['arrival']) - strtotime($train['schedule']['arrival']))/60;
                        }
                        if($train['schedule']['departure'] != ""){
                            // Valódi indulás - tervezett indulás másodpercben / 60
                            $delay["departure"] = abs(strtotime($train['real']['departure']) - strtotime($train['schedule']['departure']))/60;
                        }
                    }

                    // Útvonal helyes megjelenítése
                    // Eredeti tartalom példa: 17:53 Budapest-Nyugati -- Szeged 20:15
                    // Végleges tartalom példa: Budapest-Nyugati - Szeged
                    $regex = "{(\d{2}:\d{2} (?:.)*)?--((?:.)* \d{2}:\d{2})?}";
                    preg_match($regex, $train['line'], $match);
                    if(count($match) == 3){
                        // Az az eset, amikor az a lekért állomás nem érkezési és nem is indulási állomás
                        if($match[1] != ""){
                            $route = substr($match[1], 6) . " - " . substr($match[2], 0, strlen($match[2])-6);
                        }
                        // Az az eset, amikor a lekért állomás az indulási állomás
                        else{
                            $route = $response["station"] . " - " . substr($match[2], 0, strlen($match[2])-5);
                        }
                    }
                    // Az az eset, amikor a lekért állomás a célállomás
                    else if(count($match) == 2){
                        $route = substr($match[1], 5) . " - " . $response["station"];
                    }

                    print("
                    <tr class='" . (($counter % 2) ? 'even' : 'odd') . "'>
                        <td>{$train['schedule']['arrival']}" . (isset($delay["arrival"]) ? " + {$delay["arrival"]}" : "-") . "</td>
                        <td>{$train['schedule']['departure']}" . (isset($delay["departure"]) ? " + {$delay["departure"]}" : "-") . "</td>
                        <td>{$train['info']['info']}</td>
                        <td>{$route}</td>
                        <td>{$train['info']['code']}</td>
                        <td>" . (isset($train['platform']) ? $train['platform'] : "") . "</td>
                    </tr>
                    ");
                    $counter++;
                    unset($delay);
                }
                
                ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>

</html>