<div id="header">
	<div class="contentHeader">
    </div>
</div>

<div id="page">
  <div class="manolito"></div>
  <div class="ipads"></div>
  <div class="mensajeipads">Supera el tiempo de Manolito y llévate el iPad2 GRATIS</div>
  <form name="juganar" id="juganar" action="">
    <div id="step1">
    <div id="box">
    	<div class="top"></div>
        <div class="content">
        	<h4>CONTESTA A LAS SIGUIENTES PREGUNTAS SOBRE ESPAÑA:</h4>
            <h5>Preguntas para evaluar el conocimiento sobre lugares, costumbres e historia de España</h5>
        	<table class="questions">
				<? foreach ($questions as $index => $q): ?>
            	<tr>
                	<td class="question"><?=$q['question']?>?</td>
                    <td>
                    	<table class="answers">
                        	<tr>
								<? foreach ($q['options'] as $option): ?>
									<td><input type="radio" name="question<?=$index?>" value="<?=$option?>"></td>
									<td><p><?=$option?></p></td>
								<? endforeach ?>
                            </tr>
                        </table>
                    </td>
                </tr>
				<? endforeach ?>
            </table>
        </div>
        <div class="bottom"></div>
    </div>
    <a href="javascript:Step2();" class="siguiente" id="siguiente" style="display:none">Siguiente</a>
    
    <div id="box2">
    	<div class="top"></div>
        <div class="content">
        	<h4>CONTESTA A LAS SIGUIENTES PREGUNTAS SOBRE TI:</h4>
            <h5>Preguntas para evaluar el conocimiento que tienes sobre ti mismo</h5>
        	<table class="questions">
            	<tr>
               	  <td width="220"><label for="telefono">¿Cuál es tu número de movil?</label></td>
                    <td>
                    	<input type="text" name="telefono" id="telefono" />
                    </td>
                </tr>
                <tr>
                	<td width="220"><label>¿Cuál es tu operador?</label></td>
                    <td>
                    	<table class="answers">
                        	<tr>
                            	<td><a href="#" class="seloperador" rel="vodafone"><img src="images/vodafone.png" width="46" height="46" alt="vodafone" /></a></td>	
                                <td><a href="#" class="seloperador" rel="orange"><img src="images/orange.png" width="46" height="46" alt="orange" /></a></td>
                                <td><a href="#" class="seloperador" rel="movistar"><img src="images/movistar.png" width="46" height="46" alt="movistar" /></a></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <p class="note">Ten cerca tu móvil, ya que te mandaremos un sms con el código promocional que deberás introducir en el Paso 3 para conseguir tu iPad2.</p>
            <input type="hidden" id="operador" class="operador" />
      </div>
        <div class="bottom"></div>
    </div>
    <input type="submit" class="siguiente" id="siguiente2" value="Enviar" style="display:none" />
    </div>
</form>    
</div>
<div id="bottomPage"></div>
