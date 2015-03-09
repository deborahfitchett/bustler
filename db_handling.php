<?php
  function getDB () {
    $username = "USER";
    $password = "PASS"; 
    $host = "HOST";
    $db="DATABASE";
    $link = mysql_connect($host, $username, $password);
    $array = array();
    $array[] = $db;
    $array[] = $link;
    return $array;
  }

  function testDB () {
    $DBarray = getDB();
    if (!$DBarray[1]) {
      return mysql_error();
    }
    $db_selected = mysql_select_db($DBarray[0], $DBarray[1]);
    if (!$db_selected) {
      return mysql_error();
    }
  }

  function doQuery ($query,$success="",$failure="") {
    if (!$dbError = testDB()) {
      $DBarray = getDB();
      mysql_select_db($DBarray[0], $DBarray[1]);
      $result = mysql_query($query, $DBarray[1]);
      if (!$result && $failure) {
        echo "<p>$failure: " . handleFailure(mysql_error()) . "</p>\n";
      } elseif ($success) {
        echo "<p>$success.</p>\n";
      }
      mysql_close($DBarray[1]);
      return $result;
    } elseif ($failure) {
      echo "<p>$failure: " . handleFailure($dbError) . "</p>\n";
    }
  }

  function handleFailure ($error) {
    if ($_SERVER['SERVER_NAME'] === "localhost" ) {
      return htmlspecialchars($error);
    } else {
      return "Function temporarily unavailable, please try again later.";
    }
  }
?>