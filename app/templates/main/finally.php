<div id="header">
    <div class="contentHeader">
    </div>
</div>

<div id="page">
  <div class="manolito"></div>
  <div class="ipads"></div>
  <form name="juganar" id="juganar" action="/main/calification" method='POST'>
    <input type='hidden' name='start_time' value='<?=$start_time?>'/>
    <input type='hidden' name='q1' value='<?=$q1?>'/>
    <input type='hidden' name='q2' value='<?=$q2?>'/>
    <input type='hidden' name='q3' value='<?=$q3?>'/>
    <div id="step1">
    
    <div id="finallystep">
        <div class="top"></div>
        <div class="content">
            <h4>VALIDANDO EL C&Oacute;DIGO DE PARTICIPACI&Oacute;N:</h4>
            <h5>Por favor, espera unos segundos y continua contestando las preguntas.</h5>
            <div class="loader"></div>
            <p class="small">En unos instantes serás redirigido al cuestionario y podrás comprobar si realmente eres más listo que Manolito. </p>
        </div>
        <div class="bottom"></div>
    </div>
    
    <div id="finallyquestions">
        <div class="top"></div>
        <div class="content">
            <h4>CONTESTA A LAS SIGUIENTES PREGUNTAS SOBRE INVENTOS:</h4>
            <h5>Detalles y preguntas sobre las cosas más curiosas que te puedas imaginar. El saber no ocupa lugar.</h5>
            <table summary='questions' class="questions">
                <tr>
                    <td class="question">¿En qué año se inventó la máquina de coser?</td>
                    <td>
                        <table summary='question options' class="answers">
                            <tr>
                                <td><input type="radio" name="question4" value="1851"></td>
                                <td><p>1851</p></td>
                                <td><input type="radio" name="question4" value="1855"></td>
                                <td><p>1855</p></td>
                                <td><input type="radio" name="question4" value="1860"></td>
                                <td><p>1860</p></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="question">¿En qué siglo aparecieron las primeras lavadoras?</td>
                    <td>
                        <table class="answers">
                            <tr>
                                <td><input type="radio" name="question5" value="XV"></td>
                                <td><p>XV</p></td>
                                <td><input type="radio" name="question5" value="XVI"></td>
                                <td><p>XVI</p></td>
                                <td><input type="radio" name="question5" value="XIX"></td>
                                <td><p>XIX</p></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="question">¿De qué año data el primer teléfono de Edison?</td>
                    <td>
                        <table class="answers">
                            <tr>
                                <td><input type="radio" name="question6" value="1878"></td>
                                <td><p>1878</p></td>
                                <td><input type="radio" name="question6" value="1879"></td>
                                <td><p>1879</p></td>
                                <td><input type="radio" name="question6" value="1890"></td>
                                <td><p>1890</p></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        <div class="bottom"></div>
    </div>
    <input type="submit" class="siguiente" id="siguiente2" value="Enviar" style="display:none" />
    </div>
</form>    
</div>
<div id="bottomPage"></div>
<script type="text/javascript">
$(document).ready(function(){
    finallystep()
})
</script>

