<table>
	<? foreach ($users as $u): ?>
		<? echo '<pre>';print_r($u) ?>
		<tr>
		<? foreach ($fields_to_show as $f):?>
				<td><?=helpers::unCamelize($f)?></td>
				<td></td>
		<? endforeach ?>
		</tr>
		<tr>
		<? foreach ($fields_to_show as $f):?>
				<td>
					<? if(!empty($u[$f])) echo $u[$f] ?>
				</td>
		<? endforeach ?>
				<td>
					<?= helpers::editLink('users', $u) ?>
				</td>
				<td>
					<form name='<?=$u['id']?>' method="POST" action="/status/check">
						<input type='hidden' value=''>
						<a onclick='this.form[<?$u['id']?>].submit();return false'/>
					</form>
				</td>
		</tr>
	<? endforeach ?>
</table>
