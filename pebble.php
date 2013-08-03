
<?php
$indigo_url = "http://[Indigo Web Control Address]/devices";
$indigo_username_password = "[Indigo Username]:[Indigo Password]";
$payload = json_decode(file_get_contents('php://input'), true);

//Load all devices from Indigo
$url = $indigo_url.".xml";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
curl_setopt($ch, CURLOPT_USERPWD, $indigo_username_password);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
$output = curl_exec($ch);
$info = curl_getinfo($ch);
curl_close($ch);
$devices = new SimpleXMLElement($output);
$thermostat_device = 35;

if ($payload)
{
  $button="";
	switch((int)$payload[2]){
		case 1:
			$url = $indigo_url."/".rawurlencode($devices->device[((int)$payload[1]+ 1)]).".xml";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERPWD, $indigo_username_password);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
			$output = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);
			$lights = new SimpleXMLElement($output);
			if($lights->typeSupportsOnOff=="True")				//check to see if its a light
			{
				if ((int)$payload[1] < (count($devices)-1))		//Check to see if its the end of the list

				{
// 				echo '{"0":"'.ucwords($lights->name).'", "1":"'.$lights->brightness.'%", "2":"'.((int)$payload[1]+1).'"}';
				if ($lights->typeSupportsDim=="True")
						{
							echo '{"0":"'.ucwords($lights->name).'", "1":"'.$lights->brightness.'%", "2":"'.((int)$payload[1]+1).'"}';
						}
					else
						{
							if ($lights->isOn=="False"){$state="Off";} else {$state="ON";}
							echo '{"0":"'.ucwords($lights->name).'", "1":"'.$state.'", "2":"'.((int)$payload[1]+1).'"}';
						}				
				}

				else											//If it is, start from the first item again
				{
					$url = $indigo_url."/".rawurlencode($devices->device[0]).".xml";
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
					curl_setopt($ch, CURLOPT_USERPWD, $indigo_username_password);
					curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
					$output = curl_exec($ch);
					$info = curl_getinfo($ch);
					curl_close($ch);
					$lights = new SimpleXMLElement($output);
					echo '{"0":"'.ucwords($lights->name).'", "1":"'.$lights->brightness.'%", "2":"0"}';

				}
			}
			else												//if not, go to the next item that is a light
			{
				$next_device=(int)$payload[1];
				do {
					$next_device = $next_device +1;				//Test next device
					if ($next_device==(count($devices)-1))		//Loop back if max has been reached
						{
						$next_device=0;
						}
					$url = $indigo_url."/".rawurlencode($devices->device[$next_device]).".xml";
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
					curl_setopt($ch, CURLOPT_USERPWD, $indigo_username_password);
					curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
					$output = curl_exec($ch);
					$info = curl_getinfo($ch);
					curl_close($ch);
					$lights = new SimpleXMLElement($output);
 					} 
 					while (($lights->typeSupportsHVAC=="False") AND ($lights->typeSupportsOnOff=="False")); //Check to see if its a light or thermostat, if not, loop again.
					if ($lights->typeSupportsHVAC=="True") //Set Thermometer
					{
						echo '{"0":"c: '.$lights->setpointCool.' h: '.$lights->setpointHeat.'", "1":"'.$lights->inputTemperatureVals.'° F", "2":"'.$next_device.'"}';
					}
					else //Set Lights
					{
							if ($lights->typeSupportsDim=="True")
						{
							echo '{"0":"'.ucwords($lights->name).'", "1":"'.$lights->brightness.'%", "2":"'.$next_device.'"}';
						}
						else
						{
							if ($lights->isOn=="False"){$state="Off";} else {$state="ON";}
							echo '{"0":"'.ucwords($lights->name).'", "1":"'.$state.'", "2":"'.$next_device.'"}';
						}
					}
			}
			break;
		case 2:
			if ((int)$payload[1]==$thermostat_device)
			{
				$url = $indigo_url."/".rawurlencode($devices->device[(int)$payload[1]])."?_method=put&setpointCool=up&setpointHeat=up";
			}
			else
			{
				$url = $indigo_url."/".rawurlencode($devices->device[(int)$payload[1]])."?isOn=1&_method=put";
			}
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERPWD, $indigo_username_password);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
			$output = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);
			$url = $indigo_url."/".rawurlencode($devices->device[(int)$payload[1]]).".xml";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERPWD, $indigo_username_password);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
			$output = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);
			$lights = new SimpleXMLElement($output);
				if ($lights->typeSupportsHVAC=="True") //Set Thermometer
					{
						echo '{"0":"c: '.$lights->setpointCool.' h: '.$lights->setpointHeat.'", "1":"'.$lights->inputTemperatureVals.'° F", "2":"'.((int)$payload[1]).'"}';
					}
					else //Set Lights
					{
					if ($lights->typeSupportsDim=="True")
							{
								echo '{"0":"'.ucwords($lights->name).'", "1":"'.$lights->brightness.'%", "2":"'.((int)$payload[1]).'"}';
							}
						else
							{
								if ($lights->isOn=="False"){$state="Off";} else {$state="ON";}
								echo '{"0":"'.ucwords($lights->name).'", "1":"'.$state.'", "2":"'.((int)$payload[1]).'"}';
							}
					}				
			break;
		case 3:
			if ((int)$payload[1]==$thermostat_device)
			{
				$url = $indigo_url."/".rawurlencode($devices->device[(int)$payload[1]])."?_method=put&setpointCool=dn&setpointHeat=dn";
			}
			else
			{
				$url = $indigo_url."/".rawurlencode($devices->device[(int)$payload[1]])."?isOn=0&_method=put";
			}			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERPWD, $indigo_username_password);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
			$output = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);
			$url = $indigo_url."/".rawurlencode($devices->device[(int)$payload[1]]).".xml";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERPWD, $indigo_username_password);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
			$output = curl_exec($ch);
			$info = curl_getinfo($ch);
			curl_close($ch);
			$lights = new SimpleXMLElement($output);
				if ($lights->typeSupportsHVAC=="True") //Set Thermometer
					{
						echo '{"0":"c: '.$lights->setpointCool.' h: '.$lights->setpointHeat.'", "1":"'.$lights->inputTemperatureVals.'° F", "2":"'.((int)$payload[1]).'"}';
					}
					else //Set Lights
					{

					if ($lights->typeSupportsDim=="True")
							{
								echo '{"0":"'.ucwords($lights->name).'", "1":"'.$lights->brightness.'%", "2":"'.((int)$payload[1]).'"}';
							}
						else
							{
								if ($lights->isOn="False"){$state="Off";} else {$state="ON";}
								echo '{"0":"'.ucwords($lights->name).'", "1":"'.$state.'", "2":"'.((int)$payload[1]).'"}';
							}
					}	
			break;
	 }
}
else 
{
		$url = $indigo_url."/".rawurlencode($devices->device[$thermostat_device]).".xml";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERPWD, $indigo_username_password);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		$lights = new SimpleXMLElement($output);
		echo '{"0":"c: '.$lights->setpointCool.' h: '.$lights->setpointHeat.'", "1":"'.$lights->inputTemperatureVals.'° F", "2":"'.$thermostat_device.'"}';
}
?>

