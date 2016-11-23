<?php

error_reporting(2047);
ini_set("display_errors", 1);

$statecodes = array("alabama" => "AL", "alaska" => "AK", "american samoa" => "AS", "arizona" => "AZ", "arkansas" => "AR", "california" => "CA", "colorado" => "CO", "connecticut" => "CT", "delaware" => "DE", "district of columbia" => "DC", "federated states of micronesia" => "FM", "florida" => "FL", "georgia" => "GA", "guam" => "GU", "hawaii" => "HI", "idaho" => "ID", "illinois" => "IL", "indiana" => "IN", "iowa" => "IA", "kansas" => "KS", "kentucky" => "KY", "louisiana" => "LA", "maine" => "ME", "marshall islands" => "MH", "maryland" => "MD", "massachusetts" => "MA", "michigan" => "MI", "minnesota" => "MN", "mississippi" => "MS", "missouri" => "MO", "montana" => "MT", "nebraska" => "NE", "nevada" => "NV", "new hampshire" => "NH", "new jersey" => "NJ", "new mexico" => "NM", "new york" => "NY", "north carolina" => "NC", "north dakota" => "ND", "northern mariana islands" => "MP", "ohio" => "OH", "oklahoma" => "OK", "oregon" => "OR", "palau" => "PW", "pennsylvania" => "PA", "puerto rico" => "PR", "rhode island" => "RI", "south carolina" => "SC", "south dakota" => "SD", "tennessee" => "TN", "texas" => "TX", "utah" => "UT", "vermont" => "VT", "virgin islands" => "VI", "virginia" => "VA", "washington" => "WA", "west virginia" => "WV", "wisconsin" => "WI", "wyoming" => "WY");

if (isset($_GET['requesting'])) {
    $file = file_get_contents("list.json");
    $list = json_decode($file, true);

    switch ($_GET['requesting']) {
        case 'list':
            $result = array();
            foreach ($list as $key => $value) {
                array_push($result, $key);
            }
            echo implode(",", $result);
            break;
        case 'data':
            if (isset($list[$_GET['dataset']])) {
                $dataset = $list[$_GET['dataset']];

                $url = "http://data.cdc.gov/resource/" . $dataset['id'] . '.json?$select=' . $dataset['stateColumn'] . "," . $dataset['dataColumn'];

                $filters = $dataset['filters'];
                foreach ($filters as $key => $value) {
                    $url = $url . "&" . $key . "=" . $value;
                }


                $curl = curl_init();

                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                $response = curl_exec($curl);

                if (!$response) {
                    die('Error: ' . curl_error($curl) . ' Code: ' . curl_errno($curl));
                }

                curl_close($curl);


                $response = json_decode($response, true);
                $result = array();

                $max = -INF;
                $min = INF;

                foreach ($response as $tuple) {
                    if (isset($tuple[$dataset['stateColumn']]) && isset($tuple[$dataset['dataColumn']])) {
                        $state = strtolower($tuple[$dataset['stateColumn']]);
                        $data = floatval($tuple[$dataset['dataColumn']]);
                        if (isset($statecodes[$state])) {
                            $result[$statecodes[$state]] = array();
                            $result[$statecodes[$state]]['data'] = $data;
                            if ($data > $max) {
                                $max = $data;
                            }
                            if ($data < $min) {
                                $min = $data;
                            }
                        }
                    }
                }


                foreach ($result as $key => $tuple) {
                    $result[$key]['fillColor'] = 'rgb(' . intval((($tuple['data']) / ($max - $min) * 175) + 80) . ', 40, 40)';
                }

                echo json_encode($result);
            } else {
                die('Error: invalid dataset requested');
            }



            break;
        default:
            die('Error: invalid requesting value');
            break;
    }
} else {
    die('Error: requesting variable not set');
}
?>