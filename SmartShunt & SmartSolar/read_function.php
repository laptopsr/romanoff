<?php
	function directData()
	{
		global $conn, $systeminfo, $ilmoitus;

		// <-- SmartShunt
		/*
		{"VM":"13229","DM":"1","I":"105","P":"3","CE":"0","SOC":"1000","TTG":"-1","Alarm":"OFF","AR":"0","BMV":"SmartShunt500A/50mV",
		"FW":"0412","MON":"0","H1":"-163194","H2":"0","H3":"0","H4":"1","H5":"0","H6":"-170756","H7":"23830","H8":"28137","H9":"0","H10":"1",
		"H11":"4","H12":"0","H15":"11983","H16":"14083","H17":"423","H18":"460","PID":"0xA389","V":"26473","VM":"13230","DM":"1",
		"I":"109","P":"3","CE":"0","SOC":"1000","TTG":"-1","Alarm":"OFF","AR":"0"}*/
		$smartshunt	= [
			'V' => 0, 'I' => 0, 'P' => 0, 'SOC' => 0, 'VM' => 0, 'CE' => 0
		];

		$sm_usb0	= json_decode(@file_get_contents("/var/www/html/steca/direct_data_ttyUSB0.txt"), true);
		if(isset($sm_usb0['PID']) and isset($sm_usb0['SOC']) and isset($sm_usb0['H18']))
		{
			$smartshunt = $sm_usb0;
		}
		if(isset($sm_usb0['MPPT']) and isset($sm_usb0['VPV']) and isset($sm_usb0['PPV']))
		{
			$smartsolar = $sm_usb0;
		}
		// SmartShunt -->

		// <-- SmartSolar
		//"PID":"0xA056","FW":"161","SER#":"HQ2212X2VX4","V":"25230","I":"0","VPV":"5540","PPV":"0","CS":"0","MPPT":"0","OR":"0x00000001",
		//"ERR":"0","LOAD":"ON","H19":"1","H20":"0","H21":"0","H22":"1","H23":"9","HSDS":"6","PID":"0xA056",
		$smartsolar	= [
			'V' => 0, 'I' => 0, 'VPV' => 0, 'PPV' => 0, 'CS' => 0, 'MPPT' => 0
		];

		$sm_usb1	= json_decode(@file_get_contents("/var/www/html/steca/direct_data_ttyUSB1.txt"), true);
		if(isset($sm_usb1['MPPT']) and isset($sm_usb1['VPV']) and isset($sm_usb1['PPV']))
		{
			$smartsolar = $sm_usb1;
		}
		if(isset($sm_usb1['PID']) and isset($sm_usb1['SOC']) and isset($sm_usb1['H18']))
		{
			$smartshunt = $sm_usb1;
		}
		// SmartSolar -->
		
		/*
		echo '<pre>';
		print_r($smartsolar);
		echo '</pre>';
		exit;
		*/

		return ['smartshunt' => $smartshunt, 'smartsolar' => $smartsolar];
	}
?>
