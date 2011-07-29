<div id="header">
    <div class="contentHeader">
    </div>
</div>

<div id="page">
  <div class="manolito"></div>
  <div class="ipads"></div>
  <div class="mensajeipads">Supera el tiempo de Manolito y llévate el iPad2 GRATIS</div>
  <form name="juganar" id="juganar" action="/main/login" method="POST">
    <input type='hidden' name='start_time' value='<?=$start_time?>'/>
    <div id="step1">
        <div id="box">
            <div class="top"></div>
            <div class="content">
                <h4>CONTESTA A LAS SIGUIENTES PREGUNTAS SOBRE ESPAÑA:</h4>
                <h5>Preguntas para evaluar el conocimiento sobre lugares, costumbres e historia de España</h5>
                <table summary='first questions' class="questions">
                    <tr>
                        <td class="question">¿Cuál de estos países no tiene frontera con España?</td>
                        <td>
                            <table summary='questions' class="answers">
                                <tr>
                                    <td><input type="radio" name="question1" value="francia"></td>
                                    <td><p>Francia</p></td>
                                    <td><input type="radio" name="question1" value="andorra"></td>
                                    <td><p>Andorra</p></td>
                                    <td><input type="radio" name="question1" value="italia"></td>
                                    <td><p>Italia</p></td>
                                    <td><input type="radio" name="question1" value="portugal"></td>
                                    <td><p>Portugal</p></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="question">¿Cuál es la segunda ciudad con más habitantes?</td>
                        <td>
                            <table summary='next stage' class="answers">
                                <tr>
                                    <td><input type="radio" name="question2" value="barcelona"></td>
                                    <td><p>Barcelona</p></td>
                                    <td><input type="radio" name="question2" value="bilbao"></td>
                                    <td><p>Bilbao</p></td>
                                    <td><input type="radio" name="question2" value="zaragoza"></td>
                                    <td><p>Zaragoza</p></td>
                                    <td><input type="radio" name="question2" value="valencia"></td>
                                    <td><p>Valencia</p></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td class="question">¿Cuál es el río con mayor recorrido dentro de España?</td>
                        <td>
                            <table summary='question options' class="answers">
                                <tr>
                                    <td><input type="radio" name="question3" value="tajo"></td>
                                    <td><p>Tajo</p></td>
                                    <td><input type="radio" name="question3" value="ebro"></td>
                                    <td><p>Ebro</p></td>
                                    <td><input type="radio" name="question3" value="duero"></td>
                                    <td><p>Duero</p></td>
                                    <td><input type="radio" name="question3" value="guadalquivir"></td>
                                    <td><p>Guadalquivir</p></td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="bottom"></div>
        </div>

        <a href="javascript:Step2();" class="siguiente" id="siguiente" style="display:none">Siguiente</a>
        
        <div id="box2">
            <div class="top"></div>
            <div class="content">
                <h4>TU N&Uacute;MERO DE MOVIL:</h4>
                <table summary='questions' class="questions">
                    <tr> <td width="220"><label for="telefono">¿Cuál es tu número de movil?</label></td> <td> <input type="text" name="telefono" id="telefono" pattern='\d{6,9}' /> </td> </tr>
                    <tr> <td width="220"><label for="telefono">¿Tienes una contraseña?</label></td> <td> <input type="text" name="contrasenya" id="contrasenya" /> </td> </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input type="submit" style="margin:10px 0 0 0" id="" value="Enviar" />
                        </td>
                    </tr>
                </table>
            </div>
            <div class="bottom"></div>
        </div>
    </div>
</form>    
</div>
<div id="bottomPage"></div>
<script type="text/javascript">
$(document).ready(function(){
    WingameStart()
})
</script>
