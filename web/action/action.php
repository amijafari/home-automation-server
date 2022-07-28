<?php

define('STATE_CACHE_PATH',	'STATE');
define('LIRC_CONF_FILE_PATH',	__DIR__ . '/../conf/atp_ac.conf');
define('LIRC_RELOAD_CMD',	'sudo systemctl reload lirc');
define('LIRC_SEND_CMD',		'sudo irsend SEND_ONCE ATP_AC');

global $lirc_conf_file_path;

$DEV_MODE = false;

if (!isset($_POST['state'])) {

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
		$tempCmd = '';
		switch ($temperature) {
			case '17':
				$tempCmd = '0000'; break;
			case '18':
				$tempCmd = '0001'; break;
			case '19':
				$tempCmd = '0011'; break;
			case '20':
				$tempCmd = '0010'; break;
			case '21':
				$tempCmd = '0110'; break;
			case '22':
				$tempCmd = '0111'; break;
			case '23':
				$tempCmd = '0101'; break;
			case '24':
				$tempCmd = '0100'; break;
			case '25':
				$tempCmd = '1100'; break;
			case '26':
				$tempCmd = '1101'; break;
			case '27':
				$tempCmd = '1001'; break;
			case '28':
				$tempCmd = '1000'; break;
			case '29':
				$tempCmd = '1010'; break;
			case '30':
				$tempCmd = '1011'; break;
		}

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
				$cmd .= '1110';
				
				if (empty($timerOn)) {
					$cmd .= '010000011011';
				}
				else {
					$cmd .= '01111';
				}

				break;

			case 'COOL':
				$cmd .= $tempCmd;

				if (empty($timerOn)) {
					$cmd .= '0000';
					$cmd .= onesComplement($tempCmd);
					$cmd .= '1111';
				}
				else {
					$cmd .= '0011';
					$cmd .= ($temperature > 24) ? '1' : '0';
				}

				break;
			
			case 'HEAT':
				$cmd .= $tempCmd;

				if (empty($timerOn)) {
					$cmd .= '1100';
					$cmd .= onesComplement($tempCmd);
					$cmd .= '0011';
				}
				else {
					$cmd .= '1111';
					$cmd .= ($temperature > 24) ? '1' : '0';
				}

				break;
		}

		// add timer
		if (!empty($timerOn)) {
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
			if ($DEV_MODE) {
				$out = array();
			} else {
				$out = transmit($cmd, $cmdName);
			}

			if (count($out) == 0) {
				$curState->modifiedDate = time()*1000;
				file_put_contents(STATE_CACHE_PATH, json_encode($curState));
			}
			else {
				$res['status'] = 'error';
				$res['msg'] = $out[1];
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
	return $out;
}

function addCMDtoCONF($cmd, $cmdName) {
	$hexCMD = strtoupper(getHex($cmd));

	$conf = file_get_contents(LIRC_CONF_FILE_PATH);

	if (!strpos($conf, $cmdName)) {
		$conf = str_replace('end codes', "\t" . $cmdName . "\t\t0x" . $hexCMD . "\n\tend codes", $conf);
		$w = file_put_contents(LIRC_CONF_FILE_PATH, $conf);
		if ($w > 0) {
			exec(LIRC_RELOAD_CMD, $out, $ret);

			return ($ret == 0);
		}
		else {
			return false;
		}
	}

	//echo '<pre>' . $conf . '</per>';

	return true;
}

?>
