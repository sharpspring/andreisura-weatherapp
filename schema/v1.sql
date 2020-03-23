-- drop table location;
-- drop table locationWeather;

-- select * from location;
-- select * from locationWeather;


CREATE TABLE `location` (
   `id` bigint(20) NOT NULL AUTO_INCREMENT,
   `city` varchar(255) NOT NULL,
   `stateCode` char(2) NOT NULL,
   `countryCode` char(2) NOT NULL,
   PRIMARY KEY (`id`),
   UNIQUE KEY `city_state_country` (city, stateCode, countryCode)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `locationWeather` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `locationID` bigint(20) unsigned NOT NULL,
  `weatherTimestamp` timestamp NOT NULL,
  `weatherCondition` varchar(100),
  `tempMin` float DEFAULT NULL,
  `temp` float DEFAULT NULL,
  `tempMax` float DEFAULT NULL,
  `humidity` float DEFAULT NULL,
  `pressure` float DEFAULT NULL,
  `windSpeed` float DEFAULT NULL,
  `createTimestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `locationID` (`locationID`,`weatherTimestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO location (city, stateCode, countryCode) VALUES ('Gainesville', 'FL', 'US') ;

INSERT INTO locationWeather
    (locationID, weatherTimestamp, weatherCondition, tempMin, temp, tempMax)
VALUES
    (1, '2020-02-02 01:02:03', 'clear',  12, 15, 22),
    (1, '2020-02-02 02:02:03', 'mist',   15, 18, 21),
    (1, '2020-02-02 03:02:03', 'clouds', 12, 13, 19),
    (1, '2020-02-02 04:02:03', 'clear',  23, 27, 28),
    (1, '2020-02-02 05:02:03', 'mist',   6 , 10, 14),
    (1, '2020-02-02 06:02:03', 'clear',  22, 23, 28),
    (1, '2020-02-02 07:02:03', 'clear',  25, 30, 32),
    (1, '2020-02-02 08:02:03', 'clear',  20, 21, 29)
;

