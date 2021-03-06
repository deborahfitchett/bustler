<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<link rel="icon" type="image/png" href="../../images/favicon.png" />
		<link rel="stylesheet" type="text/css" href="style.css" />
<?php
	date_default_timezone_set('Pacific/Auckland');
	$s = preg_replace('/[^0-9]/','',$_GET['stop']);
	$r = preg_replace('/[^a-zA-Z0-9]/','+',$_GET['route']);
	$c = ($_GET['wheelchair'] == 1 || $_GET['wheelchair'] == true ? 1 : 0);
	$f = preg_replace('/[^0-9]/','',$_GET['max']);
	$l = preg_replace('/[^0-9]/','',$_GET['min']);
	$etaSource = 'ETA.php';
	$etaURL = $etaSource."?stop=".$s."&amp;route=".$r."&amp;wheelchair=".$c."&amp;min=".$l."&amp;max=".$f;
	include_once "../db_handling.php";
	$favTable = "bustler_favourites";
	$favID = "favID";
	$favLabel = "favLabel";
	$favLink = "favLink";
?>
		<title>Bustler</title>
	</head>
	<body id='top'>
		<div id='header'>
			<h1><a href="<?php echo preg_replace('/\?.*/','',$_SERVER['REQUEST_URI']); ?>">Bustler</a></h1>
			<div id="nav">
				<ul>
					<li class="navlist"><a class="nava" id="to-favourites" href="#favourites"><img class="navimg" id="img-favourites" src="images/favourites80w.png" alt="Favourites" title="Favourites" /></a></li>
					<li class="navlist"><a class="nava" id="to-settings" href="#settings"><img class="navimg" id="img-settings" src="images/settings80w.png" alt="Settings" title="Settings" /></a></li>
					<li class="navlist"><a class="nava" id="to-etas" href="#etas"><img class="navimg" id="img-etas" src="images/etas80w.png" alt="ETAs" title="ETAs" /></a></li>
					<li class="navlist"><a class="nava" id="to-about" href="#about"><img class="navimg" id="img-about" src="images/about80w.png" alt="About" title="About" /></a></li>
				</ul>
			</div>
		</div>

		<div id='favourites' class='bustler'>
			<h2>Favourites <a class='to-top' href="#top">^</a></h2>
<?php
	if(isset($_POST["Favourite"])) {		// add a favourite
	// create table if it doesn't exist
		$query = "SELECT 1 FROM $favTable";
		$favtable_exists = doQuery($query);
		if(!$favtable_exists) {
			$query = "CREATE TABLE $favTable
				(
				$favID int NOT NULL AUTO_INCREMENT,
				PRIMARY KEY($favID),
				$favLabel varchar(50),
				$favLink varchar(200)
				)";
			doQuery($query);
		}
	// add item to $favTable
		$subLabel = mysql_escape_string($_POST['Label']);
		$subLink = mysql_escape_string("?stop=".$s."&route=".$r."&wheelchair=".$c."&min=".$l."&max=".$f);
		$query = "INSERT INTO $favTable ($favLabel,$favLink) VALUES ('$subLabel', '$subLink')";
		doQuery($query);
	}
	if(isset($_POST["Clear"])) {			// delete $favTable if desired
		$query = "DROP TABLE $favTable";
		doQuery($query);
	}
	if(isset($_POST["Delete"])) {			// delete individual favourites
		$DeleteID = mysql_escape_string($_POST["DeleteID"]);
		$query = "DELETE FROM $favTable WHERE $favID = $DeleteID";
		doQuery($query);
	}
	$query = "SELECT * FROM $favTable";		// list the items in $favTable
	if ($result = doQuery($query)) {
		echo "			<ul>\n";
		while ($row = mysql_fetch_row($result)) {
			$listID = $row[0];
			$listLabel = ($row[1] ? $row[1] : $row[0]);
			$listLink = $_SERVER['PHP_SELF'].$row[2];
			echo "				<li><form class='favourite' name='Delete ".$listID."' action='".htmlentities($_SERVER['REQUEST_URI'])."#favourites' method='post'>";
			echo "<input type='hidden' name='DeleteID' value='".$listID."'/><input type='submit' name='Delete' class='bustler-x' value='X'/></form>";
			echo "<a class='eta-fix' href='".htmlentities($listLink)."#etas'>".htmlentities($listLabel)."</a></li>\n";
		}
		echo "			</ul>\n";
	} else {
		echo "			<p>No favourites have been saved yet.</p>\n";
	}
?>
			<form id="Clear" action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="post">
				<input id="clearall" class="bustler-x" type="submit" name="Clear" value="Clear all favourites" onclick="return confirm('Delete all your records?')" />
			</form>
		</div>

		<div id='settings' class='bustler'>
			<h2>Settings <a class='to-top' href="#top">^</a></h2>
			<div id="locate">
				<table id="theETAtable"></table>
				<form id="geolocate">
					<input id="geoButton" type="button" name="Geolocate" value="Find bus stops near me" onclick="javascript:geoLocate(1,6);" />
				</form>
				<p id="maplocate"><a href="http://metroinfo.co.nz/map/" target="_blank">Find bus stops on map</a></p>
			</div>
			<form id="Settings" action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>#etas" method="get">
				<fieldset>
					<label for="stop">Stop number:</label> <input type="text" size="5" name="stop" id="stop" value="<?=$s?>" /><br/>
					<label for="route">Route number(s):</label> <input type="text" size="15" name="route" id="route" value="<?=$r?>" /> (eg 5+B; leave blank for all)<br/>
					<label for="wheelchair">Wheelchair access:</label> <select id="wheelchair" name="wheelchair">
						 <option <?if ($c!=1) {echo "selected='selected'";}?> value="0">Not required</option>
						 <option <?if ($c==1) {echo "selected='selected'";}?> value="1">Required</option></select><br/>
					<label for="max">First warning:</label> <input type="text" size="2" name="max"	id="max" value="<?=$f?>" /> minutes before bus arrives<br/>
					<label for="min">Final warning:</label> <input type="text" size="2" name="min" id="min" value="<?=$l?>" /> minutes before bus arrives<br/>
					<input id="configure" class="eta-fix" type="submit" value="Go" />
				</fieldset>
			</form>
		</div>

		<div id='etas' class='bustler'>
			<h2>ETAs <a class='to-top' href="#top">^</a></h2>
			<form id="SaveFavourite" action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>#favourites" method="post">
				<fieldset>
					<label for="Label">Save favourite as:</label>
					<input type="text" name="Label" id="Label" value="Stop <?=$s?>" />
					<input type="submit" name="Favourite" value="Save" />
				</fieldset>
			</form>
			<div><object type="text/html" data="<?=$etaURL?>">
				<a href="<?=$etaURL?>">View ETAs</a>
			</object></div>
		</div>

		<div id='about' class='bustler'>
			<h2>About <a class='to-top' href="#top">^</a></h2>
			<p>Bustler alerts you with a bird call when a bus is in the desired range from your bus stop.</p>
			<h3>Data</h3>
			<p><a href="http://data.ecan.govt.nz/Catalogue/Method?MethodId=62">Bus Stop Platforms</a> and <a href="http://data.ecan.govt.nz/Catalogue/Method?MethodId=74">Route Position ETA by Platform No (version 2)</a> by <a href="http://www.metroinfo.co.nz/Pages/developer-resource.aspx">Environment Canterbury</a> are used under <a href="http://creativecommons.org/licenses/by/3.0/nz/">Creative Commons Attribution</a> license.</p>
			<h3>Source code</h3>
			<p>Available on <a href="https://github.com/deborahfitchett/bustler">GitHub</a> under <a href="https://github.com/deborahfitchett/bustler/blob/master/LICENSE.txt">MIT license</a>.</p>
			<h3>Contact</h3>
			<p>Kudos, questions, complaints to <a href="http://deborahfitchett.com/contact/">Deborah Fitchett</a>.</p>
			<h3>Last updated</h3>
			<p>2015-03-04</p>
		</div>

		<script type='text/javascript' src='geolocate.js'></script>
		<script type='text/javascript' src='style.js'></script>
	</body>
</html>