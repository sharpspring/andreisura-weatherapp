#!/usr/bin/env php

<?php
require "autoload.php";
use Core\LocationWeather;


$now = new DateTime('now');
echo $now->format('Y-m-d h:i:s') . "\n";

$location = LocationWeather::getLastLocation();
LocationWeather::fetchAndStoreWeatherConditions($location);
