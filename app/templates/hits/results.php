<dl>
<? 
	foreach ($results as $p){
		$key = $p[0];
		$data = $p[1];
		echo "<dt>$key</dt>";
		echo "<dd>";
		ksort($data);
		foreach ($data as $k => $v){
			echo "$k <span style='color:#370'>$v </span>";
		}
		echo "</dd>";
	}
?>
</dl>
