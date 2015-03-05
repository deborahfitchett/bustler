var start;
var end;
if (navigator.geolocation) {
	document.getElementById("geolocate").style.display = "inline-block";
}

function locateSuccess(loc) {
	var theCoords = [loc.coords.latitude,loc.coords.longitude];
	exchangeData(theCoords);
}

function locateFail(loc) {
	document.getElementById("geoButton").onclick = function() { geoLocate(start, end); };
	document.getElementById("geoButton").value = "Timed out: try again";
	return false;
}

function exchangeData(theCoords) {
	var geoReq = new XMLHttpRequest();
	geoReq.onload = reqListener;
	geoReq.open("post", "geolocate.php");
	geoReq.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
	geoReq.send('lat='+theCoords[0]+'&long='+theCoords[1]+'&start='+start+'&end='+end);
}

function reqListener () {
	var busArray = eval('(' + this.responseText + ')');
	displayStops(busArray);
}

function displayStops(busArray) {
	if (start==1) {
		var theRow = document.createElement("tr");
		theRow.innerHTML = "<th>Stop name</th><th>Heading</th><th>On road</th>";
		document.getElementById("theETAtable").appendChild(theRow);
	}
	for (i=0;i<busArray.length;i++) {
		var theRow = document.createElement("tr");
		var re = /(\??&?stop=)\d+/;
		if (location.href.match(re)) {
			var theURL = location.href.replace(re, "$1" + busArray[i]['PlatformNo']);
		} else if (location.href.match(/\?/)==null) {
			var theURL = location.href + "?stop=" + busArray[i]['PlatformNo'];
		} else {
			var theURL = location.href + "&stop=" + busArray[i]['PlatformNo'];
		}
		theURL = theURL.replace(/(#[^?]*)/, "") + "#settings";
		theRow.innerHTML = "<td class='bustler-stop'><a href='"+theURL+"'>"+busArray[i]['Name']+"</a></td><td>"+busArray[i]['Direction']+"</td><td>"+busArray[i]['RoadName']+"</td>";
		document.getElementById("theETAtable").appendChild(theRow);
	}
	document.getElementById("geoButton").onclick = function() { geoLocate(start+end, end+end); };
	document.getElementById("geoButton").value = "More";
}

function geoLocate (s,e) {
	if (navigator.geolocation) {
		start = s;
		end = e;
		document.getElementById("geoButton").value = "Searching...";
		document.getElementById("geoButton").onclick = function() { };
		navigator.geolocation.getCurrentPosition(locateSuccess, locateFail,{maximumAge:60000, timeout:3000});
	} else {
		document.getElementById("geoButton").value = "Function unavailable.";
	}
}