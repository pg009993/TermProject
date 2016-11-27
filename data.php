<?php
// object which gets a states abbreviation from the name
$statecodes = array("alabama" => "AL", "alaska" => "AK", "american samoa" => "AS", "arizona" => "AZ", "arkansas" => "AR", "california" => "CA", "colorado" => "CO", "connecticut" => "CT", "delaware" => "DE", "district of columbia" => "DC", "federated states of micronesia" => "FM", "florida" => "FL", "georgia" => "GA", "guam" => "GU", "hawaii" => "HI", "idaho" => "ID", "illinois" => "IL", "indiana" => "IN", "iowa" => "IA", "kansas" => "KS", "kentucky" => "KY", "louisiana" => "LA", "maine" => "ME", "marshall islands" => "MH", "maryland" => "MD", "massachusetts" => "MA", "michigan" => "MI", "minnesota" => "MN", "mississippi" => "MS", "missouri" => "MO", "montana" => "MT", "nebraska" => "NE", "nevada" => "NV", "new hampshire" => "NH", "new jersey" => "NJ", "new mexico" => "NM", "new york" => "NY", "north carolina" => "NC", "north dakota" => "ND", "northern mariana islands" => "MP", "ohio" => "OH", "oklahoma" => "OK", "oregon" => "OR", "palau" => "PW", "pennsylvania" => "PA", "puerto rico" => "PR", "rhode island" => "RI", "south carolina" => "SC", "south dakota" => "SD", "tennessee" => "TN", "texas" => "TX", "utah" => "UT", "vermont" => "VT", "virgin islands" => "VI", "virginia" => "VA", "washington" => "WA", "west virginia" => "WV", "wisconsin" => "WI", "wyoming" => "WY");

if (isset($_GET['requesting'])) {
    $file = file_get_contents("list.json"); // opens the file containing the list of datasets
    $list = json_decode($file, true); // decodes the file contents into a PHP object

    // checks what type of request is being made, two valid types: list and data
    switch ($_GET['requesting']) {
        case 'list': // list of the datasets is being requested
            // add all keys in the list to an array
            $result = array();
            foreach ($list as $key => $value) {
                array_push($result, $key);
            }
            // create comma separated string of the keys and echo it
            echo implode(",", $result);
            break;
        case 'data': // a particular dataset is being requested
            if (isset($list[$_GET['dataset']])) { // ensure that the dataset named is a valid datset in the list
                $dataset = $list[$_GET['dataset']];

                // create the url string from the api root and the parameters
                $url = "http://data.cdc.gov/resource/" . $dataset['id'] . '.json?$select=' . $dataset['stateColumn'] . "," . $dataset['dataColumn'];

                // adds the filter values to the url string
                $filters = $dataset['filters'];
                foreach ($filters as $key => $value) {
                    $url = $url . "&" . $key . "=" . $value;
                }

                // create a cURL request to query the API
                $curl = curl_init();

                curl_setopt($curl, CURLOPT_URL, $url);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

                $response = curl_exec($curl);

                if (!$response) {
                    die('Error: ' . curl_error($curl) . ' Code: ' . curl_errno($curl));
                }

                curl_close($curl);

                //decodes the cURL response
                $response = json_decode($response, true);
                
                // the section below iterates through the response, which is a numbered object array
                // and converts it to a name-value array, where the state code (ex: GA) is the key
                // and an object containing the data is the value
                // additionally the min and max data values are calculated for use below
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

                // uses the min and max data values to calculate the color value which should be displayed for each state
                foreach ($result as $key => $tuple) {
                    $result[$key]['fillColor'] = 'rgb(' . intval((($tuple['data']) / ($max - $min) * 175) + 80) . ', 40, 40)';
                }

                // encodes and echos the result
                echo json_encode($result);
            } else { // if the dataset was not in the list, die
                die('Error: invalid dataset requested');
            }



            break;
        default: // if an invalid requesting value was sent, die
            die('Error: invalid requesting value');
            break;
    }
} else { // if the requesting variable was not set, die
    die('Error: requesting variable not set');
}
?>