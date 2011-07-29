<pre>
<? foreach ($sms_responses as $sms): ?>

	<? $id = $sms['messageId'] ?>
	<a href='#' class='openbox' box='<?=$id?>'>
		<?=$id?>
	</a>
	<div id="<?=$id?>" style='visibility:hidden'>
		<? print_r($sms) ?>
	</div>

<? endforeach ?>

<script type='text/javascript'>
	function click(x){
		document.getByElementId(x.getAttribute('box')).style.visibility=''; 
		return false;
	};
	var elms = document.getElementsByClassName('openbox');
    for(var x in elms) {x.onclick = function(){click(x);};};
</script>
