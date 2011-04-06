<? include('debug.php') ?>
<h1>Key pattern results</h1>
<p>Results for search pattern: <?=$pattern?></p>
<ul>
<? foreach ($results as $result): ?>

	<li><?= $result['key']?> <?= $result['val']?> <a href='/admin/remove_key?key=<?=$result['key']?>'>Remove</a></li>

<? endforeach ?>
</ul>
