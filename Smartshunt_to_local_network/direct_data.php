<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(isset($_POST['f_data']))
{
	$file 		= "/var/www/html/steca/direct_data.txt";
	$f_data 	= [];
	$PID_start 	= false;
	$i 			= 0;

	foreach(file($file) as $line)
	{
		$i++;
		if (strpos($line, 'Checksum') !== false){ continue; }

		if(!empty(trim($line)))
		{
			$line 		= trim($line);
			$stripped 	= preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $line);
			$expl 		= explode(' ', $stripped);
			//echo $stripped.'<br>';

			if($PID_start)
			{
				if (strpos($line, 'PID') !== false)
				{
					break;
				}

				$f_data[$expl[0]??$i] 	= $expl[1]??'';
			}
			if (strpos($line, 'PID') !== false)
			{
				$PID_start 	= true;
				$f_data[$expl[0]??$i] 	= $expl[1]??'';
			}
		}
	}

	$bd = '<div class="row">';
	$bd .= '<div class="col-sm-6">';
		$bd .= '<table class="table">';
		if(isset($f_data['SOC'])){ $bd .= '<tr><td><h2>Уровень заряда</td><td><h1 class="text-success">'.($f_data['SOC']/10).' %</h2></td></tr>'; }
		if(isset($f_data['V'])){ $bd .= '<tr><td><h2>Напряжение</td><td><h1 class="text-success">'.($f_data['V']/1000).' V</h2></td></tr>'; }
		if(isset($f_data['VM'])){ $bd .= '<tr><td><h2>Напряжение средней точки</td><td><h1 class="text-success">'.($f_data['VM']/1000).' V</h2></td></tr>'; }
		if(isset($f_data['I'])){ $bd .= '<tr><td><h2>ТОК</td><td><h1 class="text-success">'.($f_data['I']/1000).' A</h2></td></tr>'; }
		if(isset($f_data['P'])){ $bd .= '<tr><td><h2>Мощность</td><td><h1 class="text-success">'.($f_data['P']).' W</h2></td></tr>'; }
		$bd .= '
			</table>
			</div><div class="col-sm-6">
			<table class="table">
		';
		if(isset($f_data['H1'])){ $bd .= '<tr><td><h2>Самый глубокий разряд</td><td><h1 class="text-success">'.($f_data['H1']/1000).' Ah</h2></td></tr>'; }
		if(isset($f_data['H4'])){ $bd .= '<tr><td><h2>Количество циклов</td><td><h1 class="text-success">'.($f_data['H4']).'</h2></td></tr>'; }
		if(isset($f_data['H17'])){ $bd .= '<tr><td><h2>Разряженная энергия</td><td><h1 class="text-success">'.($f_data['H17']/100).' kWh</h2></td></tr>'; }
		if(isset($f_data['H18'])){ $bd .= '<tr><td><h2>Заряженная энергия</td><td><h1 class="text-success">'.($f_data['H18']/100).' kWh</h2></td></tr>'; }
		$bd .= '</table>';
	$bd .= '</div></div>';
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
				$("#contentBody").html(data);
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				console.log(XMLHttpRequest);
			}
		});
	}
	geter();
	setInterval(function() { geter()}, 5000);
});
</script>
