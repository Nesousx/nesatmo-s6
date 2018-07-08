<?php

// Netatmo SDK, cf. https://github.com/Netatmo/Netatmo-API-PHP

require_once("/Netatmo-API-PHP/src/Netatmo/autoload.php");
use Netatmo\Clients\NAWSApiClient;
use Netatmo\Exceptions\NAClientException;

// Get token, replace the 4 variables (NETATMO_*) by yours, if using Docker declare them as environment variables

$config = array();
$config['client_id'] = $_SERVER["NETATMO_CLIENT_ID"];
$config['client_secret'] = $_SERVER["NETATMO_CLIENT_SECRET"];
$config['scope'] = 'read_station';
$client = new NAWSApiClient($config);

$username = $_SERVER["NETATMO_CLIENT_USERNAME"];
$pwd = $_SERVER["NETATMO_CLIENT_PASSWORD"];
$client->setVariable('username', $username);
$client->setVariable('password', $pwd);
try
{
    $tokens = $client->getAccessToken();
    $refresh_token = $tokens['refresh_token'];
    $access_token = $tokens['access_token'];
}
catch(Netatmo\Exceptions\NAClientException $ex)
{
    echo "An error occcured while trying to retrive your tokens \n";
}

// Retrieve the full data in an array

$data = $client->getData(NULL, TRUE);

// Format the data (by phenxdesign, cf. https://twitter.com/phenxdesign

// Make payload from device data
function payload($device, $measure, $sensor) {
	if ($sensor == $device["module_name"]) {
	    $module = $device;
	}
	else {
    	// On cherche parmi les modules pour trouver celui qui s'appelle $sensor
    	$modules = array_filter($device["modules"], function($mod) use($sensor) {
    		return $mod["module_name"] == $sensor;
    	});
    	
    	if (count($modules) == 0) return null;
    	
    	$module = $modules[0];
	}
	
	// On cherche parmi les valeurs, on a besoin de ça pour que ça matche même si c'est pas la bonne casse
	$values = array_filter($module["dashboard_data"], function($data, $key) use($measure) {
		return strtolower($key) == $measure;
	}, ARRAY_FILTER_USE_BOTH);
	
	if (count($values) == 0) return null;
	
	$value = reset($values);
	$ts = $module["dashboard_data"]["time_utc"];
	$station_name = $device["station_name"];
	
	// Payload finale
	return "{$measure},sensor={$sensor},station={$station_name} value={$value} {$ts}000000000";
}

// Ici la liste de mesures qu'on veut, depuis les sondes qu'on veut
$measures = array(
    "temperature",
    "pressure",
    "absolutepressure",
    "min_temp",
    "max_temp",
    "humidity",
    "noise",
    "battery_percent",
    "battery_vp",
    "co2",
    "windstrength",
    "windangle",
    "guststrength",
    "gustsangle",
    "rain"
);

$sensors = array(
    "outdoor",
    "indoor",
);

$post_body = "";

foreach($sensors as $_sensor) {
    foreach($measures as $_measure) {
		foreach($data["devices"] as $_device) {
			$_payload = payload($_device, $_measure, $_sensor);
			
			if ($_payload == null) {
				continue;
			}
			
			$post_body .= "$_payload\n";
		}
    }
}

// Send data to InfluxDB, replace the 5 variables (INFLUX_*) by yours, if using Docker declare them as environment variables
$influx_user = $_SERVER["INFLUX_USER"];                          
$influx_pass = $_SERVER["INFLUX_PASS"];                          
$influx_url = $_SERVER["INFLUX_URL_WITH_PORT"];                                                                  
$influx_db = $_SERVER["INFLUX_DB"];                              
                                                                 
$url = "$influx_url/write?db=$influx_db";                        
                                                                 
$curl = curl_init();                                             
                                                                 
curl_setopt($curl, CURLOPT_URL, $url);                           
curl_setopt($curl, CURLOPT_USERPWD, "$influx_user:$influx_pass");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);        
curl_setopt($curl, CURLOPT_POST, true);                  
curl_setopt($curl, CURLOPT_POSTFIELDS, $post_body);
                           
$return = curl_exec($curl);
curl_close($curl); 
