<?php
namespace Core;

use DateTime;
use Exception;
use PDO;
use PDOException;

/*
desc location;
+-------------+--------------+------+-----+---------+----------------+
| Field       | Type         | Null | Key | Default | Extra          |
+-------------+--------------+------+-----+---------+----------------+
| id          | bigint(20)   | NO   | PRI | NULL    | auto_increment |
| city        | varchar(255) | NO   | MUL | NULL    |                |
| stateCode   | char(2)      | NO   |     | NULL    |                |
| countryCode | char(2)      | NO   |     | NULL    |                |
+-------------+--------------+------+-----+---------+----------------+
4 rows in set (0.01 sec)

desc locationWeather;
+------------------+---------------------+------+-----+---------------------+----------------+
| Field            | Type                | Null | Key | Default             | Extra          |
+------------------+---------------------+------+-----+---------------------+----------------+
| id               | bigint(20) unsigned | NO   | PRI | NULL                | auto_increment |
| locationID       | bigint(20) unsigned | NO   | MUL | NULL                |                |
| weatherDate      | date                | NO   |     | NULL                |                |
| weatherCondition | varchar(100)        | YES  |     | NULL                |                |
| tempMax          | float               | YES  |     | NULL                |                |
| tempMin          | float               | YES  |     | NULL                |                |
| humidity         | float               | YES  |     | NULL                |                |
| pressure         | float               | YES  |     | NULL                |                |
| windSpeed        | float               | YES  |     | NULL                |                |
| createTimestamp  | timestamp           | NO   |     | current_timestamp() |                |
+------------------+---------------------+------+-----+---------------------+----------------+
*/

define('MYSQL_USERNAME', 'weatherapp');
// define('MYSQL_USERNAME', 'root');
define('MYSQL_PASSWORD', 'checking*weather2gnv');

// define('MYSQL_HOST', '127.0.0.1');
define('MYSQL_HOST', 'mariadb');
define('MYSQL_DB', 'weatherapp');

define('API_KEY', 'd99c1caebdb1f6b0ee4eec5dff899182');
define('API_URL', 'http://api.openweathermap.org/data/2.5/weather?');

class LocationWeather
{
    // https://websitebeaver.com/php-pdo-prepared-statements-to-prevent-sql-injection
    // To prevent leaking passwords change production php.ini and restart Nginx
    //      display_errors = Off
    //      log_errors = On
    public static function connectToDB()
    {
        $dsn = 'mysql:host=' . MYSQL_HOST . ';dbname=' . MYSQL_DB;
        $options = [
            PDO::ATTR_EMULATE_PREPARES => false, // turn off emulation mode for "real" prepared statements
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //turn on errors in the form of exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, //make the default fetch be an associative array
        ];

        try {
            return new PDO($dsn, MYSQL_USERNAME, MYSQL_PASSWORD, $options);
        } catch (PDOException $e) {
            throw new Exception("Failed to connect to: " . MYSQL_HOST . '/' . MYSQL_DB . ' due: ' . $e->getMessage());
        }
    }

    // concats the pieces of the location array
    public static function formatLocation($location)
    {
        return implode(',', [$location['city'], $location['stateCode'], $location['countryCode']]);
    }

    public static function getLocation(int $locationID)
    {
        try {
            $pdo = self::connectToDB();
            $sql = "SELECT id, city, stateCode, countryCode
                    FROM location
                    WHERE id = :id";

            $stmt = $pdo->prepare($sql)->execute([':id' => $locationID]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Failed to read data due: {$e->getMessage()}");
        }
    }

    public static function getLastLocation()
    {
        try {
            $pdo = self::connectToDB();
            $sql = "SELECT id, city, stateCode, countryCode
                    FROM location
                    ORDER BY id DESC LIMIT 1";

            // $stmt = $pdo->prepare($sql)->execute([]);
            $stmt = $pdo->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            // var_dump($result);
            return $result;
        } catch (PDOException $e) {
            throw new Exception("Failed to read data due: {$e->getMessage()}");
        }
    }


    // public static function getLocationWeather(int $locationID, ?string $periodBegin, ?string $periodEnd)
    public static function getLocationWeather(int $locationID, string $periodBegin='', string $periodEnd='')
    {
        try {
            $pdo = self::connectToDB();
            $sql = "SELECT
                        id, locationID, weatherTimestamp, weatherCondition
                        , tempMin, temp, tempMax, humidity, pressure, windSpeed
                        , createTimestamp 
                    FROM
                        locationWeather
                    WHERE
                        locationID = :locationID
                        AND weatherTimestamp BETWEEN :begin AND :end";

            $lastMonth = date("Y-m-d H:i:s", strtotime( '-1 month' ));
            $today = date("Y-m-d H:i:s", strtotime( 'now' ));

            $begin = $periodBegin ?? $lastMonth;
            $end = $periodEnd ?? $today;


            $stm = $pdo->prepare($sql);

            $stm->execute([':locationID' => $locationID, ':begin' => $lastMonth, ':end' => $today]);
            $result = $stm->fetchAll();
            return $result;
        } catch (Exception $e) {
            throw new Exception("Failed to read data due: {$e->getMessage()}");
        }
    }


    /**
     * @param location array
     */
    public static function getCurrentConditions($location, $lang, $unitsRequested)
    {
        $units = $unitsRequested === 'metric' ? 'metric' : 'imperial';
        $lang = $lang ?? 'en';

        $locationStr = self::formatLocation($location);

        // http://api.openweathermap.org/data/2.5/weather?q=London,uk&APPID=d99c1caebdb1f6b0ee4eec5dff899182
        $url = API_URL . "q={$locationStr}&lang={$lang}&units={$units}&APPID=" . API_KEY;

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

    public static function fetchAndStoreWeatherConditions($location)
    {
        $data = self::getCurrentConditions($location, $lang='en', $unitsRequested='imperial');
        var_dump($data->main);

        try {
            $pdo = self::connectToDB();
            $sql = "INSERT INTO locationWeather
                        (locationID, weatherTimestamp, weatherCondition
                        , tempMin, temp, tempMax, humidity, pressure, windSpeed
                        )
                    VALUES
                        (?, ?, ?, 
                         ?, ?, ?, ?, ?, ?)";

            $today = date("Y-m-d H:i:s", strtotime( 'now' ));

            $stm = $pdo->prepare($sql);
            $result = $stm->execute([
                $location['id'],
                $now,
                ucwords($data->weather[0]->description),
                $data->main->temp_min,
                $data->main->temp,
                $data->main->temp_max,
                $data->main->humidity,
                $data->main->pressure,
                $data->wind->speed]
            );
            print("\n========result: $result");
        } catch (Exception $e) {
            throw new Exception("Failed to read data due: {$e->getMessage()}");
        }
    }

}
