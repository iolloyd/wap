// JavaScript Document
function blinking(){
	$('.mensajeipads').fadeIn(250).fadeOut(250, function(){
		blinking()
	})
}

function WingameStart(){
	$('.ipads').fadeIn(1000, function(){
		blinking()
	})
	$('#box').delay(3000).animate({'left':'390px'}, 1000, function(){
		$('#siguiente').fadeIn(300)
	})
}

function Step2(){
	$('#siguiente').fadeOut(300).hide;
	$('#box').animate({'left':'1000px'}, 600, function(){
		$('#box2').animate({'left':'390px'}, 600);
		$('#siguiente2').delay(600).fadeIn(600)
	});
	$('.seloperador').click(function(){
		var operador = $(this).attr('rel');
		$('#operador').attr('value', operador);
	})
}

function arrowBlinking() {
	$('.cursor').fadeIn(600).fadeOut(600, function(){
		arrowBlinking()
	})
}

function login(){
	$('#login').animate({'left':'390px'}, 1000, function(){
		$('#submitlogin, p#condiciones').delay(300).fadeIn(600)
		arrowBlinking()
	})
}

function finallystep(){
	$('#finallystep').animate({'left':'390px'}, 1000, function(){
		$('#finallystep').delay(2000).animate({'left':'1000px'}, 300);
		$('#finallyquestions').delay(2000).animate({'left':'390px'}, 1000);
		$('#siguiente2').delay(3000).fadeIn(600)
	})
}