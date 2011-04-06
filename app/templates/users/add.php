<form name='' method='POST' action='/users/edit/<?=$id?>'>
	<dl>
		<? foreach ($user as $k => $v): ?>
			<dt>
				<?=$k?>
			</dt>
			<dd>
				<input name='<?=$k?>' value='$<?=$v?>'/>
			</dd>
		<? endforeach ?>
	</dl>
</form>
