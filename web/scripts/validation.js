function valida(f){
	var pasa=true, msg='Gelieve de volgende velden in te vullen om door te gaan.\n Kijk de volgende verplichte velden na:\n\n';
	
	if((f.telefono.value=="")||(f.telefono.value.length<10)){
			pasa=false;
			msg = msg + "\t - Telefoonnummer\n";
	} else {
		var stringtlfo=f.telefono.value.split("",2);
		var firstnumber = stringtlfo[0];
		var secondnumber = stringtlfo[1];
		var tlfo = firstnumber + secondnumber;
		if (tlfo !=06) {
			pasa=false;
			msg = msg + "\t - Telefoonnummer\n";
		}
	}
	
	if(pasa){
		window.onunload = function (){};
		f.submit();
	} else {
		alert(msg);
	}
}
