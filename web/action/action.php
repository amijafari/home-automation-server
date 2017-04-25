<?php

define(STATE_CACHE_PATH,	'STATE');
define(LIRC_CONF_FILE_PATH,	__DIR__ . '/../conf/atp_ac.conf');
define(LIRC_RELOAD_CMD,		'sudo service lirc reload');
define(LIRC_SEND_CMD,		'sudo irsend SEND_ONCE ATP_AC');

global $lirc_conf_file_path;

if (isset($_POST['state'])) {

	$curState = json_decode($_POST['state']);

	//print_r($curState);
	//print_r(json_encode($curState));

	$power = $curState->powerStatus;
	$mode = $curState->mode;
	$fanSpeed = $curState->fanSpeed;
	$temperature = $curState->temperature;
	$timerOn = $curState->timerOn;
	$timerOff = $curState->timerOff;

	// we nedd it to use when calling lirc
	$cmdName = 'P_' . $power . '_' .
				'M_' . $mode . '_' .
				'F_' . $fanSpeed . '_' .
				'T_' . $temperature . '_' .
				'ON_' . ($timerOn == '' ? '0' : $timerOn) . '_' .
				'OFF_' . ($timerOff == '' ? '0' : $timerOff);

	$cmd = '';

	if ($power == 'OFF' && empty($timerOn)) {
		$cmd = '01111011100001001110000000011111';
		$cmdName = 'POWER_OFF';
	}
	else {
		switch ($fanSpeed) {
			case 'LOW':
				$cmd .= '10011111';
				break;
			case 'MED':
				$cmd .= '01011111';
				break;
			case 'HIGH':
				$cmd .= '00111111';
				break;
		}

		$cmd .= onesComplement($cmd);

		switch ($mode) {
			case 'HEAT':
			case 'FAN':
				$cmd .= '111001';
				
				if (empty($timerOn)) {
					$cmd .= '0000011011';
				}
				else {
					$cmd .= '111';

					$half = ($timerOn-floor($timerOn));

					// convert timer hour to binary and add 0 padding to get 5 digits
					if ($half > 0) {
						$cmd .= str_pad(base_convert(floor($timerOn), 10, 2), 5, '0', STR_PAD_LEFT);
						$cmd .= '01'; // if timer has a half
					}
					else {
						$cmd .= str_pad(base_convert(floor($timerOn)-1, 10, 2), 5, '0', STR_PAD_LEFT);
						$cmd .= '11';
					}
				}

				break;

			// I couldn't found any logical pattern for temperature :|  
			case 'COOL':
				switch ($temperature) {
					case '17':
						$cmd .= '0000000011111'; break;
					case '18':
						$cmd .= '0001000011101'; break;
					case '19':
						$cmd .= '0011000011001'; break;
					case '20':
						$cmd .= '0010000011011'; break;
					case '21':
						$cmd .= '0110000010011'; break;
					case '22':
						$cmd .= '0111000010001'; break;
					case '23':
						$cmd .= '0101000010101'; break;
					case '24':
						$cmd .= '0100000010111'; break;
					case '25':
						$cmd .= '1100000000111'; break;
				 	case '26':
						$cmd .= '1101000000101'; break;
					case '27':
						$cmd .= '1001000001101'; break;
					case '28':
						$cmd .= '1000000001111'; break;
					case '29':
						$cmd .= '1010000001011'; break;
					case '30':
						$cmd .= '1011000001001'; break;
				}

				$cmd .= '111';
				break;
		}
	}

	$res = array(
		'status' => '', 
		'msg' => '',
		'cmd' => '',
		'bin' => '',
	);

	if (strlen($cmd) != 32) {
		$res['status'] = 'error';
		$res['msg'] = 'command did not recognized';
	}
	else {
		$hexCMD = getHex($cmd);

		$res = array(
			'status' => 'success', 
			'cmd' => strtoupper($hexCMD),
			'bin' => $cmd
		);

		if (addCMDtoCONF($cmd, $cmdName)) {
			if (transmit($cmd, $cmdName)) {
				file_put_contents(STATE_CACHE_PATH, $_POST['state']);
			}
			else {
				$res['status'] = 'error';
				$res['msg'] = 'transmittion error';
			}
		}
		else {
			$res['status'] = 'error';
			$res['msg'] = 'updating config file error';
		}
	}

	echo json_encode($res);

}
else {
	echo file_get_contents(STATE_CACHE_PATH);
}

function onesComplement($text) {
    for ($i = 0; $i < strlen($text); $i++){
        $text[$i] = intval($text[$i]) == 1 ? 0 : 1;
    }
    
    return $text;
}

function getHex($binary) {
	return dechex(bindec($binary));
}

function transmit($cmd, $cmdName) {
	//$hexCMD = strtoupper(getHex($cmd));
	exec(LIRC_SEND_CMD . ' ' . $cmdName . ' 2>&1', $out, $ret);
	//print_r($out);
	return ($ret == 0);
}

function addCMDtoCONF($cmd, $cmdName) {
	$hexCMD = strtoupper(getHex($cmd));

	$conf = file_get_contents(LIRC_CONF_FILE_PATH);

	if (!strpos($conf, $cmdName)) {
		$conf = str_replace('end codes', "\t" . $cmdName . "\t\t0x" . $hexCMD . "\n\tend codes", $conf);
		file_put_contents(LIRC_CONF_FILE_PATH, $conf);

		exec(LIRC_RELOAD_CMD, $out, $ret);

		return ($ret == 0);
	}

	//echo '<pre>' . $conf . '</per>';

	return true;
}

?>