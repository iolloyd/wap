<form name='' method='' action=''>
	<? foreach ($info as $k => $v): ?>
		<input name='<?=$k>' value='<?=$v?>'/>
	<? endforeach ?>

	<input name='submit' type='submit' '/>
</form>

