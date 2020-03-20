<?php
// This script is used to plot historical data
require "autoload.php";
use Core\LocationWeather;

$locationID = $_GET['locationIDs'] ?? [1];
$periodBegin = $_GET['periodBegin'] ?? '';
$periodEnd = $_GET['periodEnd'] ?? '';



