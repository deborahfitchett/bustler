=======
Bustler
=======
Bustler is a PHP/MySQL application using Christchurch's Metroinfo API to let you set up an alert for when you have to leave to reach your stop in time for your bus. Assuming your device's audio is functional, you'll hear a bird call once a minute when a bus is in your desired range.

Demo
----
http://deborahfitchett.com/toys/bustler/ demonstrates the functionality, including the ability to save favourite locations.

Configuration notes
-------------------

1. Edit db_handling.php with your username/password/database/host details.
2. You may want to edit index.php with table and field names; these are stored in variables $favTable, $favID, $favLabel, $favLink
3. Note, if renaming the stylesheet, that both index.php and ETA.php point to it.
4. If you want to use a different sound, this is invoked from within ETA.php

Potential limitations
---------------------
* Has only been tested on servers with PHP Version 5.2.17 / MySQL 5.1.49, and with PHP Version 5.4.4 / MySQL 5.5.25 