<?php
include('cache.php');
function sortArray() {
    $args = func_get_args();
    $data = array_shift($args);
    foreach ($args as $n => $field) {
        if (is_string($field)) {
            $tmp = array();
            foreach ($data as $key => $row) {
                $tmp[$key] = $row[$field];
				$args[$n] = $tmp;
			}
        }
    }
    $args[] = &$data;
    call_user_func_array('array_multisort', $args);
    return array_pop($args);
}

function parseStops($xml,$userLat,$userLong) {
	$stops = array();
	if (!$xml->Platform[0]) {
		return false;
	} else {
		foreach ($xml->Platform as $s) {
			if ((string) $s['PlatformNo']) {
				$details = array();
				$details['PlatformNo'] = (string) $s['PlatformNo'];
				$details['Name'] = (string) $s['Name'];
				$details['Direction'] = cardinalDir(((string) $s['BearingToRoad'] + 270) % 360);
				$details['RoadName'] = (string) $s['RoadName'];
				$aLat = (string) $s->Position['Lat'];
				$aLong = (string) $s->Position['Long'];
				$details['Lat'] = $aLat;
				$details['Long'] = $aLong;
				$details['LatDistance'] = $aLat - $userLat;
				$details['LongDistance'] = $aLong - $userLong;
				$details['CrowFlies'] = sqrt(pow($aLat - $userLat,2) + pow($aLong - $userLong,2));
				$stops[] = $details;
			}
		}
	$sorted = sortArray($stops, 'CrowFlies', SORT_ASC);
	return $sorted;
	}
}

function cardinalDir($bearing) {
	if ($bearing < 22.5 || $bearing >= 337.5) {
		return "N";
	} elseif ($bearing >= 292.5) {
		return "NW";
	} elseif ($bearing >= 247.5) {
		return "W";
	} elseif ($bearing >= 202.5) {
		return "SW";
	} elseif ($bearing >= 157.5) {
		return "S";
	} elseif ($bearing >= 112.5) {
		return "SE";
	} elseif ($bearing >= 67.5) {
		return "E";
	} elseif ($bearing >= 22.5) {
		return "NE";
	}
}

if((isset($_POST["lat"]) && isset($_POST["long"])) || (isset($_GET["lat"]) && isset($_GET["long"]))) {
	$userLat = $_POST["lat"] ? $_POST["lat"] : $_GET["lat"];
	$userLong = $_POST["long"] ? $_POST["long"] : $_GET["long"];
	$start = $_POST["start"] ? $_POST["start"] : ($_GET["start"] ? $_GET["start"] : 1);
	$end = $_POST["end"] ? $_POST["end"] : ($_GET["end"] ? $_GET["end"] : 10);

	$url = "http://rtt.metroinfo.org.nz/rtt/public/utility/file.aspx?ContentType=SQLXML&Name=JPPlatform";
	$cachetime = 1*24*60*60;
	$content = cachedCurl($url,$cachetime);
	$xml = simplexml_load_string($content);
	$sorted = parseStops($xml,$userLat,$userLong);
	$truncated = array();
	for ($i=$start-1; $i<$end; $i++) {
		$truncated[] = $sorted[$i];
	}
	echo json_encode($truncated);
}
?>