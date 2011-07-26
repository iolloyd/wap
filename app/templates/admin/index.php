<h4>Sales Yesterday</h4>
<table>
  <tr>
  <? foreach (array_keys($sales_yesterday) as $x): ?>
      <td><?=$x?></td>
  <? endforeach ?>
  </tr>
  <tr>
  <? foreach (array_values($sales_yesterday) as $x): ?>
      <td><?=$x?></td>
  <? endforeach ?>
  </tr>
</table>

<h4>Sales Today</h4>
<table>
  <tr>
  <? foreach (array_keys($sales_today) as $x): ?>
      <td><?=$x?></td>
  <? endforeach ?>
  </tr>
  <tr>
  <? foreach (array_values($sales_today) as $x): ?>
      <td><?=$x?></td>
  <? endforeach ?>
  </tr>
</table>
