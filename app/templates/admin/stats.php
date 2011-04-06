<script>
$(document).ready(function(){ 
	$("#date_from").datepicker();
	$("#date_to").datepicker(); 
})
</script>

<form name='stats' method='post' action='/admin/stats'>
	<input name='date_from' id='date_from'/>
	<input name='date_to' id='date_to'/>
	<input name='submit' type='submit' value='Show'/>
</form>
