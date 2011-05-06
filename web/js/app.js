var checkForReply = function(){
	alert('hey');
	var f = function(response){
		$('blackness').innerHTML = response;
	};
	var url   = 'http://mega-quiz.nl/aj/checkForReply';
	var reply = Ajax.call(url, f);
	return reply ? reply : checkForReply();

}

var qtoggle = function(i){
	document.getElementById('q'+(i)).style.display = 'none';
	document.getElementById('q'+(i+1)).style.display = 'block';
}

var redirect = function(i){
	if (window.location.href.match(/local/)) {
		alert('local');
		window.location = 'http://local.megaquiz/main/addphone';
	} else {
		window.location = 'http://mega-quiz.nl/main/addphone';
	}

}

addLoadEvent(function(){checkForReply;});
