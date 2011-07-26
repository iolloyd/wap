<style>
	.fleft {
		float: left;
		padding:0 10px 10px 0;
	}
</style>

<div class='fleft'>
<h3>Stats</h3>
	<dl>
		<dt>Visits to main page</dt>
		<dd> <?= $visits?: 0?></dd>

		<dt>Sent sms</dt>
		<dd><?= $sms ?: 0?> <?= $sms ? '('.round($sms/$visits*100,1).'%)' : '' ?></dd>

		<dt>Sales</dt>
		<dd><?= $sales ?: 0?> <?= $sales ? '('.round($sales/$visits*100,1).'%)' : '' ?></dd>
	</dl>
</div>
<div class='fleft'>
<h3>Fails</h3>
<dl>
    <dt>Ident create session     </dt> <dd><?= $fails_ident_create_session ?: 0?></dd>
    <dt>Ident check status       </dt> <dd><?= $fails_ident_check_status   ?: 0?></dd>
    <dt>Subscription auth        </dt> <dd><?= $fails_subscription_auth    ?: 0?></dd>
    <dt>Subscription final       </dt> <dd><?= $fails_subscription_final   ?: 0?></dd>
    <dt>Oneshot Check Status     </dt> <dd><?= $fails_oneshot_checkstatus  ?: 0?></dd>
    <dt>Oneshot Authorize Payment</dt> <dd><?= $fails_authorize_payment    ?: 0?></dd>
    <dt>Oneshot Finalize Session </dt> <dd><?= $fails_oneshot_finalize     ?: 0?></dd>
</dl>

</div>
<div class='fleft'>
	<h3>Sent to Vodafone</h3>
	<dl>
		<dt>Sent to vodafone</dt> <dd><?= $vodafone ?: 0 ?></dd>
	</dl>
</div>
<br clear='all'/>

<h3>Visits</h3>

<div class='fleft cright'>
	<h4>Browser</h4>
	<div id='browser_pie_chart'></div>
	<script type='text/javascript' src='https://www.google.com/jsapi'></script>
	<script type='text/javascript'>
		google.load('visualization', '1', {'packages':['corechart']});
		google.setOnLoadCallback(function(){
			var data = new google.visualization.DataTable();
			data.addColumn('string', 'Topping');
			data.addColumn('number', 'Slices');
			data.addRows(<?=$chart_browsers?>);
			var chart = new google.visualization.PieChart(document.getElementById('browser_pie_chart'));
			chart.draw(data, {width: 400, height: 240});
		});
	</script>
</div>

<? if($browser_fails): ?>
	<div class='fleft'>
		<h4>Browser Fails</h4>
		<dl>
		<? foreach ($browser_fails as $browser): ?>
			<dt><?=$browser[0]?></dt> <dd><?=$browser[1]?></dd>
		<? endforeach ?>
		</dl>
	</div>
<? endif ?>

<div class='fleft'>
<h4>Platform</h4>
<div id='platform_pie_chart'></div>
<script type='text/javascript' src='https://www.google.com/jsapi'></script>
<script type='text/javascript'>
	google.load('visualization', '1', {'packages':['corechart']});
	google.setOnLoadCallback(function(){
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Topping');
		data.addColumn('number', 'Slices');
		data.addRows(<?=$chart_platforms?>);
		var chart = new google.visualization.PieChart(document.getElementById('platform_pie_chart'));
		chart.draw(data, {width: 400, height: 240});
	});
</script>
</div>
<? if($platform_fails): ?>
<div class='fleft'>
<h4>Platform Fails</h4>
<dl>
<? foreach ($platform_fails as $browser): ?>
    <dt><?=$browser[0]?></dt> <dd><?=$browser[1]?></dd>
<? endforeach ?>
</dl>
</div>
<? endif ?>

<div class='fleft'>
<h4>Operators</h4>
<div id='operator_pie_chart'></div>
<script type='text/javascript' src='https://www.google.com/jsapi'></script>
<script type='text/javascript'>
	google.load('visualization', '1', {'packages':['corechart']});
	google.setOnLoadCallback(function(){
		var data = new google.visualization.DataTable();
		data.addColumn('string', 'Topping');
		data.addColumn('number', 'Slices');
		data.addRows(<?=$chart_operators?>);
		var chart = new google.visualization.PieChart(document.getElementById('operator_pie_chart'));
		chart.draw(data, {width: 400, height: 240});
	});
</script>
</div>
