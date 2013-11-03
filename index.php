<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <link rel="stylesheet" type="text/css" href="style.css" />
<?php
  $s = preg_replace('/[^0-9]/','',$_GET['stop']);
  $r = preg_replace('/[^a-zA-Z0-9]/','+',$_GET['route']);
  $c = ($_GET['wheelchair'] == 1 || $_GET['wheelchair'] == true ? 1 : 0);
  $f = preg_replace('/[^0-9]/','',$_GET['max']);
  $l = preg_replace('/[^0-9]/','',$_GET['min']);
  $etaSource = 'ETA.php';
  $etaURL = $etaSource."?stop=".$s."&amp;route=".$r."&amp;wheelchair=".$c."&amp;min=".$l."&amp;max=".$f;
  include_once "db_handling.php";
  $favTable = "bustler_favourites";
  $favID = "favID";
  $favLabel = "favLabel";
  $favLink = "favLink";
?>
    <title>Route <?=$r?> at stop <?=$s?> between <?=$f?>-<?=$l?> minutes</title>
  </head>
  <body>
    <div id='about'>
      <h1>Bustler</h1>
      <p>You will hear a bird call once a minute when a bus is in your desired range.</p>
    </div>

    <div id='ETAs' class='bustler'>
      <h3>ETAs <span id="PlainView"><a href="<?=$etaURL?>">Plain view</a></span></h3>
      <div><object type="text/html" data="<?=$etaURL?>">
        <a href="<?=$etaURL?>">Data available from this page</a>
      </object></div>
    </div>

    <div id='config' class='bustler'>
      <h3>Configure</h3>
      <form id="Configure" action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="get">
        <fieldset>
          <label for="stop">Stop number:</label> <input type="text" size="5" name="stop" id="stop" value="<?=$s?>" autofocus /> (<a href="http://metroinfo.co.nz/map/" target="_blank">find on map</a>)<br/>
          <label for="route">Route number(s):</label> <input type="text" size="15" name="route" id="route" value="<?=$r?>" /> (eg 5+Oc+23)<br/>
          (Orbiter: clockwise=Oc, anticlockwise=Oa)<br/>
          <label for="wheelchair">Wheelchair access:</label> <select id="wheelchair" name="wheelchair">
             <option <?if ($c!=1) {echo "selected='selected'";}?> value="0">Not required</option>
             <option <?if ($c==1) {echo "selected='selected'";}?> value="1">Required</option></select><br/>
           <label for="max">First warning:</label> <input type="text" size="2" name="max"  id="max" value="<?=$f?>" /> minutes before bus arrives<br/>
           <label for="min">Final warning:</label> <input type="text" size="2" name="min" id="min" value="<?=$l?>" /> minutes before bus arrives<br/>
           <input type="submit" value="Configure" />
         </fieldset>
       </form>
    </div>

    <div id='favourites' class='bustler'>
      <h3>Favourites</h3>
      <form id="SaveFavourite" action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="post">
        <input type="submit" name="Favourite" value="Favourite this page as:" />
        <input type="text" size="30" name="Label" value="Stop <?=$s?>" />
      </form>
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
    $subLink = mysql_escape_string($_SERVER['PHP_SELF']."?stop=".$s."&route=".$r."&wheelchair=".$c."&min=".$l."&max=".$f);
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
    echo "      <h4>Current favourites:</h4>\n";
    echo "      <ul>\n";
    while ($row = mysql_fetch_row($result)) {
      $listID = $row[0];
      $listLabel = ($row[1] ? $row[1] : $row[0]);
      $listLink = $row[2];
      echo "        <li><a href='".htmlentities($listLink)."'>".htmlentities($listLabel)."</a>";
      echo "<form class='favourite' name='Delete ".$listID."' action='".htmlentities($_SERVER['REQUEST_URI'])."' method='post'>";
      echo "<input type='hidden' name='DeleteID' value='".$listID."'/><input type='submit' name='Delete' value='X'/></form></li>\n";
    }
    echo "      </ul>\n";
  }
?>
      <form id="Clear" action="<?php echo htmlentities($_SERVER['REQUEST_URI']); ?>" method="post">
        <input type="submit" name="Clear" value="Clear all favourites" onclick="return confirm('Delete all your records?')" />
      </form>
    </div>
  </body>
</html>