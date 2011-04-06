<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Play Time</title>
	<link href="/css/main.css" rel="stylesheet" type="text/css" />
	<script type='text/javascript'>
		var qtoggle = function(i){
			document.getElementById('q'+(i)).style.display = 'none';
			document.getElementById('q'+(i+1)).style.display = 'block';
		}
	</script>
</head>

<body>

<div id="contenedor">
<div id="contenido">
	<span id='clock'></span>
<div id='questions' style="text-align:center">
<form id="f1" name="f1" action="/main/results" method="POST">
	<input type='hidden' name='telefono' value='<?=$_POST['telefono']?>'/>
	
	<? $i=-1; foreach ($questions as $q):?>
			<? $display = $i == 0 ? 'block' : 'none';?>
			<div id='q<?=++$i?>' style='display:<?=$display?>' class="questions">

				<h2 class="question"> <?=$q['question'] ?> </h2>
				
				<? foreach ($q['options'] as $k => $v): ?>
					<label><?=$v?> <input type='radio' name='q<?=$i?>' value='<?=$v?>'/></label>
				<? endforeach ?>
			<br/>
			<? if ($i == 9){ ?>
				<input type='submit' name='submit' value='stop the clock'/>
			<?} else {?>
				<a href='#' onclick='qtoggle(<?=$i?>);return false'>Next</a>
			<? }?>
			<br/> 
			</div>
			
	<? endforeach ?>
</form>
</div>
</div>
<div id="textoLegal">
	<p>
	Dit is een quiz abonnementsdienst. De deelnemer registreert zich met zijn mobiele telefoonnummer voor het topquizen spel en ontvangt dan een SMS met de vraag om de deelname te bevestigen. Pas dan is de dienst geactiveerd. Daarna ontvangt de deelnemer per SMS o.a. een wachtwoord waarmee online kan worden ingelogd om te spelen. Daarna beantwoordt de deelnemer 10 weet-en wiskundige vragen. Iedere deelnemer heeft een keer per week de kans om deel te nemen en de beste tijd van de maand te bereiken. De prijs wordt eenmaal per maand uitgereikt. De snelste deelnemer, die de meeste vragen juist beantwoord heeft, wint de prijs. Enkel en alleen de tijd voor het beantwoorden op de 10-Quizvragen, n\341 een succesvolle registratie, wordt tot de resultaten berekend. De kosten bedragen EUR3,00 per bericht, 3 berichten per week. Stoppen? Stuur STOP naar 4400. Om u aan te melden voor en gebruik te kunnen maken van de dienst moet u 16 jaar of ouder zijn en/of toestemming hebben van (een van de) ouders en/of de betalingsgemachtigde. Als u zich aanmeldt en/of de dienst gebruikt, onderkent en bevestigt u dat u de Algemene Voorwaarden heeft gelezen, dat u deze heeft geaccepteerd en dat u voldoet aan de voor uw situatie geldende voorwaarden als hierboven genoemd. topquizen werkt volgens de Gedragscode SMS-Dienstverlening en de Reclamecode SMS-Dienstverlening.  one2one Media AG, Hauptstrasse 49, 8750 Glarus; Swizerland.One2One Media AG is onder het handelsregisternummer CH 160.3.005.040-7 het Kantongerecht van Glarus (Switzerland) geregistreerd. Info: info@megaquizen.nl <a href="mailto:info@mega-quiz.nl">MegaQuiz</a>
	</p>
</div>
</div>
</body>
<script type="text/javascript">
	var s = 0,
	    d = 0;
	var clock = document.getElementById('clock');
	window.setInterval(function(){
		clock.innerHTML = s++/100;
	}, 1);

</script>
</html>
