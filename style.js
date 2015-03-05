setUpToggles();
if (location.href.match(/#/)) {
	var id = location.href.replace(/.*#(.*)/, "$1");
	showDiv(id);
	prettifyImg(id);
}


function setUpToggles() {
	var topArray = getElementsByClass ('to-top');
	for (i=0; i<topArray.length; i++) {
		topArray[i].style.display = 'none';
	}
	var divArray = getElementsByClass ('bustler');
	for (i=0; i<divArray.length; i++) {
		divArray[i].style.display = 'none';
		divArray[i].style.marginBottom = '0';
	}
	document.getElementById('nav').style.marginBottom = '0';
	var listArray = getElementsByClass('nava');
	for (i=0; i<listArray.length; i++) {
		var id = listArray[i].id.replace(/^to-/,"");
		listArray[i].href = 'javascript:void(0);';
		listArray[i].onclick = toggleHandler(id);
	}
	var aArray = getElementsByClass("eta-fix");
	for (i=0; i<aArray.length; i++) {
		aArray[i].onclick = etaHandler();
	}
}

function etaHandler () {
	return function () {
		var id = "etas";
		showDiv(id);
		prettifyImg(id);
	}
}

function showDiv(id) {
	var divArray = getElementsByClass ('bustler');
	for (i=0; i<divArray.length; i++) {
		divArray[i].style.display = 'none';
	}
	var element = document.getElementById(id);
	element.style.display = 'block';
}

function prettifyImg(id) {
      var theImages = getElementsByClass("navimg");
      for (i=0; i<theImages.length; i++) {
        if (theImages[i].id === "img-" + id) {
          theImages[i].style.backgroundColor = "blue";
          theImages[i].style.color = "white";
          theImages[i].src = theImages[i].src.replace(/(.*)w\.png/, "$1b.png");
        } else {
          theImages[i].style.backgroundColor = "white";
          theImages[i].style.color = "blue";
          theImages[i].src = theImages[i].src.replace(/(.*)b\.png/, "$1w.png");
        }
      }
}

function toggleHandler(id) {
  return function() {
    if (!location.href.match(id)) {
      location.href="#" + id;
      showDiv(id);
      prettifyImg(id);
    }
  }
}

function getElementsByClass (string,containerId) {
  var classElements = new Array();
  ( containerId === undefined ) ? containerId = document : containerId = document.getElementById(containerId);
  var allElements = containerId.getElementsByTagName('*');
  for (var i = 0; i < allElements.length; i++) {
    var multiClass = allElements[i].className.split(' ');
    for (var j = 0; j < multiClass.length; j++)
      if (multiClass[j] === string)
        classElements[classElements.length] = allElements[i];
  }
  return classElements;
}