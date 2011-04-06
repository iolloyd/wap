<div>
<? foreach ($results as $title => $data): ?>
	<div style='float:left;width:390px'>
	<h2><?=$title?></h2>

	<? $last_date = ''?>
	<? foreach ($data as $k => $v): ?>

		<? 
		if (empty($v['timestamp'])) continue; 
		$date = substr($v['timestamp'], 0, 8);
		if($date != $last_date){
			$last_date = $date;
			echo '<br/>' .str_repeat('-', 50) . '<br/>';
		}
		?> 

		<? if (!empty($v['key'])) echo $v['key'].' ' ?>
		<?=substr($v['timestamp'], 0, 8).' '?>
		<?=substr($v['timestamp'], 8).' '?>
		<?=$v['responseMessage']?>&nbsp;
		<?=@$v['messageId']?> <br/>

	<? endforeach ?>
	</div>
<? endforeach ?>
</div>
