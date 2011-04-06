<form name='user_save' method='post' action='/users/add/<?=$id?>'>
	<dl>
		<? foreach ($user as $k => $v): ?>
			<dt>
				<?=ucfirst($k)?>
			</dt>
			<dd>
				<input name='<?=$k?>' value='<?=$v?>'/>
			</dd>

			<dt></dt>
		<? endforeach ?>
			<dd>
				<input name='submit' value='submit' type='submit'>
			</dd>
	</dl>
</form>
