<ul>
<? foreach ($users as $u): ?>
	<li><? echo $u['id'], $u['email'] ?></li>
<? endforeach ?>
</ul>
