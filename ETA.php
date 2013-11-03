<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="refresh" content="60">
    <link rel="stylesheet" type="text/css" href="style.css" />
<?php
  $Stop = preg_replace('/[^0-9]/','',$_GET['stop']);
  $WantedRoutes = preg_replace('/[^a-zA-Z0-9]/','+',$_GET['route']);
  $Wheelchair = ($_GET['wheelchair'] == 1 || $_GET['wheelchair'] == true ? 1 : 0);
  $FirstCall = preg_replace('/[^0-9]/','',$_GET['max']);
  $LastCall = preg_replace('/[^0-9]/','',$_GET['min']);
?>
    <title>Route <?=$WantedRoutes?> at stop <?=$Stop?> between <?=$FirstCall?>-<?=$LastCall?> minutes</title>
  </head>
  <body>

<?php
  $theAPI = "http://rtt.metroinfo.org.nz/rtt/public/utility/file.aspx?ContentType=SQLXML&Name=JPRoutePositionET2&PlatformNo=";
  $theURL = $theAPI.$Stop;
  $thePath = $Stop.".xml";
  $theXML = simplexml_load_file($theURL);
  $WantedRoutes = explode("+",$WantedRoutes);

  function getETAs($theXML,$WantedRoutes,$Wheelchair) {
    $theETAs = array();
    if (!$theXML->Platform[0]) {
      return "invalid";
    } elseif (!$theXML->Platform[0]->Route[0]) {
      return "empty";
    } else {
      foreach ($theXML->Platform[0]->Route as $APIRoute) {
        for ($i=0; $i<count($WantedRoutes); $i++) {
          if ((string) $APIRoute['RouteNo'] == $WantedRoutes[$i]) {
            $Name = htmlentities((string) $APIRoute['Name']);
            foreach ($APIRoute->Destination[0]->Trip as $Trip) {
              if ($Wheelchair!=1 || (string) $Trip['WheelchairAccess'] == 'true') {
                $theETAs[] = array($WantedRoutes[$i],$Name,(string) $Trip['ETA']);
              }
            }
          }
        }
      }
      if (!$theETAs) {
        return "nonwanted";
      } else {
        return $theETAs;
      }
    }
  }

  function numOrder($a, $b) {
    return strnatcmp($a[2], $b[2]);
  }

  function displayETAs($theXML,$WantedRoutes,$Wheelchair,$FirstCall,$LastCall) {
    $theETAs = getETAs($theXML,$WantedRoutes,$Wheelchair);
    $theAlert = false;
    if ($theETAs == "invalid") {
      echo "<p id='bus' class='nobus'>Invalid stop number</p>";
    } elseif ($theETAs == "empty") {
      echo "<p id='bus' class='nobus'>No buses are coming to that stop in the next 30 minutes.</p>";
    } elseif ($theETAs == "nonwanted") {
      echo "<p id='bus' class='nobus'>No buses from the selected route(s) are coming to that stop in the next 30 minutes.</p>";
    } elseif ($WantedRoutes[0]=="") {
      echo "<p id='bus' class='nobus'>Please specify a route.</p>";
    } else {
      usort($theETAs, 'numOrder');
      echo "  <table id='bus' class='buses'>\n";
      echo "    <thead><tr><td>#</td><td>Name</td><td>ETA</td></tr></thead>\n";
      for ($i=0; $i<count($theETAs); $i++) {
        $Class = ($FirstCall < $theETAs[$i][2] ? 'notyet' : ($LastCall <= $theETAs[$i][2] ? 'ready' : 'toolate'));
        if ($Class == 'ready' ) {
          $theAlert = true;
        }
        echo "    <tr class='$Class'>";
        echo "<td class='TripNo'>".$theETAs[$i][0]."</td>";
        echo "<td class='TripName'>".$theETAs[$i][1]."</td>";
        echo "<td class='TripETA'>".$theETAs[$i][2]."</td>";
        echo "</tr>\n";
      }
      echo "  </table>\n";
    }
  return $theAlert;
  }

  function displayAlert($theAlert) {
    if ($theAlert) {
      echo "  <audio preload='auto' autoplay>\n";
      echo "    <source src='bird.ogg' />\n";
      echo "    <p class='ready'>Time to leave!</p>\n";
      echo "  </audio>\n";
    }
  }

  echo "<div id='ETA'>\n";
  $theAlert = displayETAs($theXML,$WantedRoutes,$Wheelchair,$FirstCall,$LastCall);
  displayAlert($theAlert);
  echo "</div>\n";

?>

</body>
</html>