<?php
// This script is used to serve data for the historical weather plot
require "autoload.php";
use Core\LocationWeather;

$locationID = $_GET['locationIDs'] ?? [1];
$periodBegin = $_GET['periodBegin'] ?? '';
$periodEnd = $_GET['periodEnd'] ?? '';

$data = LocationWeather::getLocationWeather((int)$locationID, $periodBegin, $periodEnd);
print json_encode($data);

