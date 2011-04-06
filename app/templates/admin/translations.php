<script type='text/javascript'>
var show = function(e){
	var details = document.getElementById('addit');
	details.style.display = details.style.display=='none'?'':'none';
}
</script>

<ul>
	<a href='' onclick='show(event);return false'>Add Translation</a><br/>
	<div id='addit' style='display:none'>
	<form name='translation' method='post' action='/admin/addtranslation'>
	<? helpers::showform('translation', '/admin/addtranslation') ?>
	</form>
	</div>
	<div>Search Results</div>
	<? foreach ($results as $k => $data): ?>
		<form name='form_<?=$k?>' action='/admin/addtranslation' method='post'>

			<? foreach ($data as $k => $v): ?>
				<? if ($k == 'submit' || $k == 'id') continue; ?>
				<label><?=$k ?></label> 
				<input name='<?=$k?>' value='<?=$v?>'/>
			<? endforeach ?>

			<input name='submit' type='submit' value='update'/>

		</form>
	<br/>
	<? endforeach ?>
</ul>
