<!DOCTYPE html>
<html lang="en" style="background:none;">
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="refresh" content="60" />
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
	include('cache.php');
	$theAPI = "http://rtt.metroinfo.org.nz/rtt/public/utility/file.aspx?ContentType=SQLXML&Name=JPRoutePositionET2&PlatformNo=";
	$theURL = $theAPI.$Stop;

	function getETAs($theXML,$WantedRoutes,$Wheelchair) {
		$theETAs = array();
		if (!$theXML->Platform[0]) {
			return "invalid";
		} elseif (!$theXML->Platform[0]->Route[0]) {
			return "empty";
		} else {
			foreach ($theXML->Platform[0]->Route as $APIRoute) {
				for ($i=0; $i<count($WantedRoutes); $i++) {
					if ((string) $APIRoute['RouteNo'] == $WantedRoutes[$i] || !$WantedRoutes[$i]) {
						$Name = htmlentities((string) $APIRoute['Name']);
						foreach ($APIRoute->Destination[0]->Trip as $Trip) {
							if ($Wheelchair!=1 || (string) $Trip['WheelchairAccess'] == 'true') {
								$theETAs[] = array((string) $APIRoute['RouteNo'],$Name,(string) $Trip['ETA']);
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

	function writeBlurb($WantedRoutes,$Wheelchair,$theURL,$FirstCall,$LastCall) {
		$Stop = preg_replace('/.*=/','',$theURL);
		$ToListRoutes = array_filter($WantedRoutes);
		echo "	<p id='blurb'>Listing ";
		if (count($ToListRoutes)==0) {
				echo "<strong>any</strong> bus ";
		} else {
			echo "the ";
			$j=0;
			foreach ($ToListRoutes as $i) {
				$j++;
				if ($j === 1) {
					echo "#<strong>".$i."</strong>";
				} elseif ($j === count($ToListRoutes)) {
					echo " and #<strong>".$i."</strong>";
				} else {
					echo ", #<strong>".$i."</strong>";
				}
			}
			echo " bus";
			if ($j>1) {
				echo "es";
			}
		}
		if ($Wheelchair==1) {
				echo " with space for wheelchairs";
		}
		echo " arriving at stop #<strong>".$Stop."</strong> in the next 60 minutes";
		if ($FirstCall > 0) {
			if (!$LastCall) { $LastCall = 0; }
			echo ", with alerts once a minute when a bus is between <strong>".$LastCall."-".$FirstCall."</strong> minutes away";
		}
		echo ".</p>\n";
	}
	
	function displayETAs($theURL,$WantedRoutes,$Wheelchair,$FirstCall,$LastCall) {
		$theXML = simplexml_load_file($theURL);
		$theETAs = getETAs($theXML,$WantedRoutes,$Wheelchair);
		$theAlert = false;
		if ($theETAs == "invalid") {
			echo "<p class='nobus'>No real-time data found. Either:</p><ul><li class='nobus'>that stop number doesn't exist;</li><li class='nobus'>your network connection is down; or</li><li class='nobus'>Environment Canterbury's data is temporarily unavailable.</li></ul>";
		} elseif ($theETAs == "empty") {
			echo "<p class='nobus'>No buses are coming to that stop in the next 60 minutes.</p>";
		} elseif ($theETAs == "nonwanted") {
			echo "<p class='nobus'>No buses from the selected route(s) are coming to that stop in the next 30 minutes.</p>";
		} else {
			usort($theETAs, 'numOrder');
			writeBlurb($WantedRoutes,$Wheelchair,$theURL,$FirstCall,$LastCall);
			echo "	<table id='bus' class='buses'>\n";
			echo "		<tr><th>#</th><th>Route Name</th><th>Due</th></tr>\n";
			for ($i=0; $i<count($theETAs); $i++) {
				$Class = ($FirstCall < $theETAs[$i][2] ? 'notyet' : ($LastCall <= $theETAs[$i][2] ? 'ready' : 'toolate'));
				if ($Class == 'ready' ) {
					$theAlert = true;
				}
				echo "		<tr class='$Class'>";
				echo "<td class='TripNo'>".$theETAs[$i][0]."</td>";
				echo "<td class='TripName'>".$theETAs[$i][1]."</td>";
				echo "<td class='TripETA'>".$theETAs[$i][2]." mins</td>";
				echo "</tr>\n";
			}
			echo "	</table>\n";
		}
	return $theAlert;
	}

	function displayAlert($theAlert) {
		if ($theAlert) {
			echo "	<audio preload='auto' autoplay='true' hidden='true'>\n";
			echo "		<source src='audio/bird.ogg' type='audio/ogg' />\n";
			echo "		<source src='audio/bird.mp3' type='audio/mpeg' />\n";
			echo "		<p class='ready'>Time to leave!</p>\n";
			echo "	</audio>\n";
		}
	}

	echo "<div id='ETA'>\n";
	if ($Stop != "") {
		$WantedRoutes = preg_replace('/Or\+/','Oa+Oc+',$WantedRoutes);
		$WantedRoutes = preg_replace('/Or$/','Oa+Oc',$WantedRoutes);
		$WantedRoutes = explode("+",$WantedRoutes);
		$theAlert = displayETAs($theURL,$WantedRoutes,$Wheelchair,$FirstCall,$LastCall);
		displayAlert($theAlert);
	} else {
		echo "<p class='nobus'>Please specify a stop number.</p>\n";
	}
	echo "</div>\n";
	echo "<div id='attribution' style='display:none;'>Uses real-time <a href='http://data.ecan.govt.nz/Catalogue/Method?MethodId=74'>data from Environment Canterbury</a> under <a href='http://creativecommons.org/licenses/by/3.0/nz/'><img src='../../images/ccby.png' alt='Creative Commons BY' title='Creative Commons BY' /></a> license.</div>";
?>

</body>
</html>