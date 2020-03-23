<?php
// A single-page PHP script which displays the current weather conditions for
// the configured location.

// require "autoload.php";
use Core\LocationWeather;

// returns an array[city_name, state_abbreviation]
function getLocation() {
    try {
    	$pdo = LocationWeather::connectToDB();
    	$stmt = $pdo->query('SELECT city, stateCode, countryCode FROM location');
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    catch (PDOException $e) {
      throw new Exception("Failed to connect to the database due: " . $e->getMessage());
    }
}

function getCurrentConditions($location, $lang, $unitsRequested) {
    $units = $unitsRequested === 'metric' ? 'metric' : 'imperial';
    $lang = $lang ?? 'en';

    // http://api.openweathermap.org/data/2.5/weather?q=London,uk&APPID=d99c1caebdb1f6b0ee4eec5dff899182
    $config = LocationWeather::getConfig();
    $url = $config['API_URL'] . "q={$location}&lang={$lang}&units={$units}&APPID=". $config['API_KEY'];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response);
    return $data;
}


// TODO: accept country in the url?
$loc = getLocation();
$locCountry = 'US';

// Example: 'Gainesville,FL,US';
$location = "{$loc['city']},{$loc['stateCode']},{$locCountry}";

$units = $_GET['units'] ?? null;
$lang = $_GET['lang'] ?? null;
$data = getCurrentConditions($location, $lang, $units);
$unitsSpeed = $units === 'metric' ? 'meter/sec' : 'miles/hour';
$unitsSymbol = $units === 'metric' ? 'C' : 'F';
$t1Format = $units === 'metric' ? "l G:i" : "l g:i a";
$t2Format = $units === 'metric' ? "jS F, Y" : "F jS, Y";

$now = time();
$t1 = date($t1Format, $now);
$t2 = date($t2Format, $now);
$conditions = ucwords($data->weather[0]->description);
$tz = $data->timezone/3600;
/*

  "weather": [
    {
      "id": 800,
      "main": "Clear",
      "description": "clear sky",
      "icon": "01d"
    }
  ],

  "main": {
    "temp": 57.52,
    "feels_like": 46.98,
    "temp_min": 55.4,
    "temp_max": 61,
    "pressure": 1022,
    "humidity": 40
  },
*/

$page = <<<END
<!doctype html>
<html>
    <head>
	<title>Weather Conditionis</title>

<style>

body {
    color: #999;
    font-family: Arial;
    font-size: 0.9em;
}

.container {
    border: solid 1px #efefef;
    border-radius: 5px;
    padding: 15px;
    width: 500px;
    margin: 0 auto;
}

.forecast {
    color: #333;
    font-size: 1.3em;
    font-weight: bold;
    margin: 10px 0px;
}

.weather-icon {
    margin-right: 20px;
    vertical-align: middle;
}

.condition {
    color: blue;
}

.time {
    padding: 10px;
    background: #eee;
    line-height: 25px;
}

.temp-min, .temp-max {
    font-weight: bold;
}

.temp-min {
    color: blue;
}

.temp-max {
    color: red;
}

</style>

    </head>
    <body>
        <div class="container">
            <h2>Weather conditions for {$data->name}, {$data->sys->country}</h2>
            <div class="time">
                <div> $t1 (UTC: $tz)</div>
                <div> $t2 </div>
            </div>

            <div class="forecast">
                <h4> It is currently: <span class="condition">$conditions </span>  and {$data->main->temp} &deg;{$unitsSymbol} </h4>
                <img class="weather-icon" src="http://openweathermap.org/img/w/{$data->weather[0]->icon}.png" />
                High: <span class="temp-max"> {$data->main->temp_max} &deg;{$unitsSymbol}</span>
                Low: <span class="temp-min"> {$data->main->temp_min} &deg;{$unitsSymbol}</span>
            </div>

            <div class="time">
                <div> Wind speed: {$data->wind->speed} $unitsSpeed </div>
                <div> Humidity: {$data->main->humidity} % </div>
                <div> Pressure: {$data->main->pressure} hPa </div>
            </div>
        </div>
    </body>
</html>

END;

echo $page;

// TODO: show the weather conditions for the previous twelve hours. Use the
// weather API to query and store the current conditions at a regular
// interval. Query and display this data on this page.
