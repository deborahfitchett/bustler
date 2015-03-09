=======
Bustler
=======
Bustler is a PHP/MySQL application using Christchurch's Metroinfo API to let you set up an alert for when you have to leave to reach your stop in time for your bus. Geolocation finds your nearest stops, and favourites can be saved (to cookies or database, depending on configuration). Assuming your device's audio is functional, you'll hear a bird call once a minute when a bus is in your desired range.

Demo
----
http://deborahfitchett.com/toys/bustler/ demonstrates the functionality including the ability to save favourite locations using cookies.
http://deborahfitchett.com/toys/bustler/index-database.php demonstrates the functionality saving favourites with a MySQL database.

Configuration notes
-------------------

You can store favourites either with cookies or in a MySQL database:

1. With cookies: use index-cookies.php. You won't need index-database.php or db_handling.php.
2. In a MySQL database: use index-database.php (you may want to edit the table and field names in variables $favTable, $favID, $favLabel, $favLink) and db_handling.php (you'll need to add your username/password/database/host details). You won't need index-cookies.php.
3. Note, if renaming the stylesheet, that both index-cookies.php/index-database.php and ETA.php point to it.
4. If you want to use a different sound, this is invoked from within ETA.php

Potential limitations
---------------------

* Has only been tested on servers with PHP Version 5.2.17 / MySQL 5.1.49, and with PHP Version 5.4.4 / MySQL 5.5.25
* Currently ETA.php refreshes once every 60 seconds; if traffic in your area is particularly prone to unpredictability you might want to make this more frequent.

Data
----
Real-time data is sourced from [Metro/ECan's API](http://data.ecan.govt.nz/Catalogue/Method?MethodId=74) and used under Creative Commons Attribution license.
