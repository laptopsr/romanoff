<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(isset($_POST['f_data']))
{
	include	'conn.php';
	include	'Koti_3.php';

	$bd  = '';
	$pow = '';
	$Koti = new Koti();

	$smartsolar		= $Koti->directData()['smartsolar'];
	$smartshunt		= $Koti->directData()['smartshunt'];

	//$bd .= json_encode($smartsolar);

	$bd .= (isset($smartsolar['PPV'])) ? 
					'<div class="alert bg-info text-white text-center"><h6>SmartSolar: <span class="orange_victron">'.($smartsolar['PPV']).'W</span></h6></div>' 
					: 
					''; 

	if(isset($smartsolar['VPV']) and isset($smartsolar['PPV']))
	{
		$bd .= '
		<div class="row">
			<div class="col-sm-6">
				<table class="table">
					<tr>
						<td><h6 class="text-white">Вольт на панелях</h6></td><td align="right"><h5 class="orange_victron">'.($smartsolar['VPV']/1000).'V</h5></td>
					</tr>
					<tr>
						<td><h6 class="text-white">Вольт система</h6></td><td align="right"><h5 class="orange_victron">'.($smartsolar['V']/1000).'</h5></td>
					</tr>
					<tr>
						<td><h6 class="text-white">CS</h6></td><td align="right"><h5 class="orange_victron">'.($smartsolar['CS']).'</h5></td>
					</tr>
					<tr>
						<td><h6 class="text-white">H20</h6></td><td align="right"><h5 class="orange_victron">'.($smartsolar['H20']).'</h5></td>
					</tr>
					<tr>
						<td><h6 class="text-white">H22</h6></td><td align="right"><h5 class="orange_victron">'.($smartsolar['H22']).'</h5></td>
					</tr>
					<tr>
						<td><h6 class="text-white">HSDS</h6></td><td align="right"><h5 class="orange_victron">'.($smartsolar['HSDS']).'</h5></td>
					</tr>
				</table>
				</div><div class="col-sm-6">
				<table class="table">
					<tr>
						<td><h6 class="text-white">Выработка</h6></td><td align="right"><h5 class="orange_victron">'.round($smartsolar['PPV'], 2).'W</h5></td>
					</tr>
					<tr>
						<td><h6 class="text-white">Заряд Ампер</h6></td><td align="right"><h5 class="orange_victron">'.($smartsolar['I']/1000).'</h5></td>
					</tr>
					<tr>
						<td><h6 class="text-white">H19</h6></td><td align="right"><h5 class="orange_victron">'.($smartsolar['H19']).'</h5></td>
					</tr>
					<tr>
						<td><h6 class="text-white">H21 Сегодня</h6></td><td align="right"><h5 class="orange_victron">'.($smartsolar['H21']).'</h5></td>
					</tr>
					<tr>
						<td><h6 class="text-white">H23 Вчера</h6></td><td align="right"><h5 class="orange_victron">'.($smartsolar['H23']).'</h5></td>
					</tr>
					<tr>
						<td><h6 class="text-white">MPPT</h6></td><td align="right"><h5 class="orange_victron">'.($smartsolar['MPPT']).'</h5></td>
					</tr>
				</table>
			</div>
		</div>';
	}

	if(isset($smartshunt['P'])){ $pow = '<span class="orange_victron">'.($smartshunt['P']).' W</span>'; }

	$bd .= (isset($smartshunt['I']) and $smartshunt['I'] < 0) ? 
					'<div class="alert bg-danger text-white text-center"><h6>SmartShunt: '.$pow.'</h6></div>' 
					: 
					'<div class="alert bg-info text-white text-center"><h6>SmartShunt: '.$pow.'</h6></div>'; 

	if(isset($smartshunt['SOC']) and isset($smartshunt['H18']))
	{
		$bd .= '
		<div class="row">
			<div class="col-sm-6">
				<table class="table">
					<tr>
						<td><h6 class="text-white">Уровень заряда</h6></td><td align="right"><h5 class="orange_victron">'.($smartshunt['SOC']/10).'%</h5></td>
					</tr>
					<tr>
						<td><h6 class="text-white">Напряжение</h6></td><td align="right"><h5 class="orange_victron">'.round($smartshunt['V']/1000, 2).'V</h5></td>
					</tr>
					<tr>
						<td><h6 class="text-white">Напряжение средней точки</h6></td><td align="right"><h5 class="orange_victron">'.round($smartshunt['VM']/1000, 2).'V</h5></td>
					</tr>
					<tr>
						<td><h6 class="text-white">ТОК</h6></td><td align="right"><h5 class="orange_victron">'.round($smartshunt['I']/1000, 2).'A</h5></td>
					</tr>
					<tr>
						<td><h6 class="text-white">Потреблено Ah</h6></td><td align="right"><h5 class="orange_victron">'.($smartshunt['CE']/1000).'Ah</h5></td>
					</tr>
				</table>
				</div><div class="col-sm-6">
				<table class="table">
					<tr>
						<td><h6 class="text-white">Самый глубокий разряд</h6></td><td align="right"><h5 class="orange_victron">'.round($smartshunt['H1']/1000, 2).'Ah</h5></td>
					</tr>
					<tr>
						<td><h6 class="text-white">Всего Ah использовано</h6></td><td align="right"><h5 class="orange_victron">'.round($smartshunt['H6']/1000, 2).'Ah</h5></td>
					</tr>
					<tr>
						<td><h6 class="text-white">Количество циклов</h6></td><td align="right"><h5 class="orange_victron">'.($smartshunt['H4']).'</h5></td>
					</tr>
					<tr>
						<td><h6 class="text-white">Разряженная энергия</h6></td><td align="right"><h5 class="orange_victron">'.($smartshunt['H17']/100).'kWh</h5></td>
					</tr>
					<tr>
						<td><h6 class="text-white">Заряженная энергия</h6></td><td align="right"><h5 class="orange_victron">'.($smartshunt['H18']/100).'kWh</h5s></td>
					</tr>
				</table>
			</div>
		</div>';
	}
	echo json_encode($bd);
	exit;
}
?>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

		<script src="https://code.highcharts.com/highcharts.js"></script>

		<title>Inverter Webinterface</title>
	</head>
	<style>
	body{
		background-color: rgba(56,125,197,.9);
	}
	.orange_victron{
		color: orange;
		font-weight: bold;
	}
	</style>
<body class="">
<?php
/*
echo '<pre>';
print_r($f_data);
echo '</pre>';
*/
?>
<div class="container-fluid" style="margin-top: 10px">
	<div id="contentBody"></div>
</div>

<script type="text/javascript">
$(document).ready(function(){

	var geter = function(){
        $.ajax({
			url: 'direct_data.php',
			type: 'POST',
			data: { f_data : true },
			success: function(data){
				data = JSON.parse(data);
				//console.log("upd.");
				if(data.length > 0)
				{
					$("#contentBody").html(data);
				}
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				console.log(XMLHttpRequest);
			}
		});
	}
	geter();
	setInterval(function() { geter()}, 3000);
});
</script>
