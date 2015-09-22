<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class netatmoThermostat extends eqLogic {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	public function getTokenfromNetatmo($type) {
         $token_url = "https://api.netatmo.net/oauth2/token";
         $scope = $type .'_thermostat';
         $postdata = http_build_query(
               array(
                    'grant_type' => "password",
                    'client_id' => config::byKey('client_id', 'netatmoThermostat'),
                    'client_secret' => config::byKey('client_secret', 'netatmoThermostat'),
                    'username' => config::byKey('username', 'netatmoThermostat'),
                    'password' => config::byKey('password', 'netatmoThermostat'),
                    'scope' => $scope
                )
         );
         $opts = array('http' =>
                array(
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $postdata
                )
        );
        $context  = stream_context_create($opts);
        $response = file_get_contents($token_url, false, $context);
        $params = null;
        $params = json_decode($response, true);
        if ($params['access_token']) {
            log::add('netatmoThermostat', 'debug', 'Returning Token');
            return $params['access_token'];
        } else {
            return 'Error';
        }
    }
	
	public function actionOnTherm($multiId,$action_type) {
		$token = netatmoThermostat::getTokenfromNetatmo('write');
    }
	
    public function syncWithTherm($multiId) {
		$ids = explode('|', $multiId);
		$devid= $ids[0];
		$modid= $ids[1];
		$token = netatmoThermostat::getTokenfromNetatmo('read');
		$url_thermostat="http://api.netatmo.net/api/getthermstate?access_token=" .  $token ."&device_id=".$devid."&module_id=".$modid;
		$resulat_therm = @file_get_contents($url_thermostat);
        $json_therm = json_decode($resulat_therm,true);
		log::add('netatmoThermostat', 'debug', print_r($resulat_therm, true));
		$eqLogic = eqLogic::byLogicalId($multiId,'netatmoThermostat');
		$temperature_thermostat = $json_therm["body"]["measured"]["temperature"];
		$wifistatus=$json_therm["body"]["wifi_status"];
		$rfstatus=$json_therm["body"]["rf_status"];
		$mode=$json_therm["body"]["setpoint"]["setpoint_mode"];
		$planning ='Aucun';
		if ($mode=='away') {
			$consigne = $json_therm["body"]["therm_program"]["zones"][2]["temp"];
			$mode = $json_therm["body"]["therm_program"]["zones"][2]["name"];
       } elseif ($mode=='hg') {
			$consigne = $json_therm["body"]["therm_program"]["zones"][3]["temp"];
			$mode = $json_therm["body"]["therm_program"]["zones"][3]["name"];
       } elseif ($mode=='manual') {
			$consigne = $json_therm["body"]["measured"]["setpoint_temp"];
			$setpointmode_endtime1 =  $json_therm["body"]["setpoint"]["setpoint_endtime"];
			$mode = 'Manuel';
		} elseif ($mode=='program') {
			$day=date('w',time())-1;
			$hour=date('H',time());
			$min=date('i',time());
			if ($day == -1) {$day=6;};
			$temps=($day*86400)+($hour*3600)+($min*60);
			$i=0;
			$secondes=0;
			while($secondes<=$temps){
				$minutes = $json_therm["body"]["therm_program"]["timetable"][$i]["m_offset"];
				$planning_id = $json_therm["body"]["therm_program"]["timetable"][($i-1)]["id"];
				$nextplanning_id = $json_therm["body"]["therm_program"]["timetable"][($i)]["id"];
				if ($planning_id>2) {$planning_id=$planning_id-2;} 
				if ($nextplanning_id>2) {$nextplanning_id=$nextplanning_id-2;} // dans la table, il y a intercalage des modes 'absent' et 'HG' qui décalent la numérotation 
				$planning = $json_therm["body"]["therm_program"]["zones"][$planning_id]["name"];
				$nextplanning = $json_therm["body"]["therm_program"]["zones"][$nextplanning_id]["name"];
				$consigne = $json_therm["body"]["therm_program"]["zones"][$planning_id]["temp"];
				$secondes = 60 * $minutes;
				$jour=floor($secondes/86400);
				$reste=$secondes%86400;
				$heure=floor($reste/3600);
				$reste=$reste%3600;
				$minute=floor($reste/60);
				$seconde=$reste%60;
				$zero='';
				if ($minute<=9) {$zero='0';}
				$i++;
			}
			$mode = 'Programme';
		} else {
			$consigne = $json_therm["body"]["measured"]["setpoint_temp"];
		}
		foreach ($eqLogic->getCmd('info') as $cmd) {
			switch ($cmd->getName()) {
						case 'Température':
							$value=$temperature_thermostat;
						break;
						case 'Mode':
							$value=$mode;
						break;
						case 'Signal Wifi':
							$value=$wifistatus;
						break;
						case 'Signal RF':
							$value=$rfstatus;
						break;
						case 'Consigne':
							$value=$consigne;
						break;
						case 'Planning':
							$value=$planning;
						break;
						case 'Planning suivant':
							$value=$nextplanning;
						break;
			}
			$cmd->event($value);
			log::add('netatmoThermostat','debug','set:'.$cmd->getName().' to '. $value);
		}
    }
    
    public function syncWithNetatmo() {
        $token = netatmoThermostat::getTokenfromNetatmo('read');
        $url_devices = "https://api.netatmo.net/api/devicelist?access_token=" .  $token ."&app_type=app_thermostat";
        $resulat_device = @file_get_contents($url_devices);
        $json_devices = json_decode($resulat_device,true);
		foreach ($json_devices['body']['modules'] as $thermostat) {
			$id=$thermostat['_id']; 
			$module_name=$thermostat['module_name'];
			$device=$thermostat['main_device'];
			$multiId = $device . '|' . $id;
			$eqLogic = netatmoThermostat::byLogicalId($multiId, 'netatmoThermostat');
			if (!is_object($eqLogic)) {
				$eqLogic = new self();
                foreach (object::all() as $object) {
                    if (stristr($module_name,$object->getName())){
                        $eqLogic->setObject_id($object->getId());
                        break;
                    }
                }
				$eqLogic->setLogicalId($multiId);
				$eqLogic->setCategory('heating', 1);
				$eqLogic->setName('Netatmo '.$module_name);
				$eqLogic->setEqType_name('netatmoThermostat');
				$eqLogic->setIsVisible(1);
				$eqLogic->setIsEnable(1);
				$eqLogic->save();
				$eqLogic->syncWithTherm($multiId);
			}
		}
	}

	public static function cron15() {
	}

	/*     * *********************Methode d'instance************************* */

	public function postSave() {
            $netatmoThermostatcmd = $this->getCmd(null, 'consigne');
			if (!is_object($netatmoThermostatcmd)) {
				$netatmoThermostatcmd = new netatmoThermostatcmd();
				$netatmoThermostatcmd->setName(__('Consigne', __FILE__));
				$netatmoThermostatcmd->setIsHistorized(1);
			}
			$netatmoThermostatcmd->setEqLogic_id($this->getId());
			$netatmoThermostatcmd->setLogicalId('consigne');
			$netatmoThermostatcmd->setUnite('°C');
			$netatmoThermostatcmd->setType('info');
			$netatmoThermostatcmd->setSubType('numeric');
			$netatmoThermostatcmd->setEventOnly(1);
			$netatmoThermostatcmd->save();
            
            $netatmoThermostatcmd = $this->getCmd(null, 'temperature');
			if (!is_object($netatmoThermostatcmd)) {
				$netatmoThermostatcmd = new netatmoThermostatcmd();
				$netatmoThermostatcmd->setName(__('Température', __FILE__));
				$netatmoThermostatcmd->setIsHistorized(1);
			}
			$netatmoThermostatcmd->setEqLogic_id($this->getId());
			$netatmoThermostatcmd->setLogicalId('temperature');
			$netatmoThermostatcmd->setUnite('°C');
			$netatmoThermostatcmd->setType('info');
			$netatmoThermostatcmd->setSubType('numeric');
			$netatmoThermostatcmd->setEventOnly(1);
			$netatmoThermostatcmd->save();
			
			$netatmoThermostatcmd = $this->getCmd(null, 'mode');
			if (!is_object($netatmoThermostatcmd)) {
				$netatmoThermostatcmd = new netatmoThermostatcmd();
				$netatmoThermostatcmd->setName(__('Mode', __FILE__));
			}
			$netatmoThermostatcmd->setEqLogic_id($this->getId());
			$netatmoThermostatcmd->setLogicalId('mode');
			$netatmoThermostatcmd->setType('info');
			$netatmoThermostatcmd->setSubType('string');
			$netatmoThermostatcmd->setEventOnly(1);
			$netatmoThermostatcmd->save();
			
			$netatmoThermostatcmd = $this->getCmd(null, 'planning');
			if (!is_object($netatmoThermostatcmd)) {
				$netatmoThermostatcmd = new netatmoThermostatcmd();
				$netatmoThermostatcmd->setName(__('Planning', __FILE__));
			}
			$netatmoThermostatcmd->setEqLogic_id($this->getId());
			$netatmoThermostatcmd->setLogicalId('planning');
			$netatmoThermostatcmd->setType('info');
			$netatmoThermostatcmd->setSubType('string');
			$netatmoThermostatcmd->setEventOnly(1);
			$netatmoThermostatcmd->save();
			
			$netatmoThermostatcmd = $this->getCmd(null, 'nextplanning');
			if (!is_object($netatmoThermostatcmd)) {
				$netatmoThermostatcmd = new netatmoThermostatcmd();
				$netatmoThermostatcmd->setName(__('Planning suivant', __FILE__));
			}
			$netatmoThermostatcmd->setEqLogic_id($this->getId());
			$netatmoThermostatcmd->setLogicalId('nextplanning');
			$netatmoThermostatcmd->setType('info');
			$netatmoThermostatcmd->setSubType('string');
			$netatmoThermostatcmd->setEventOnly(1);
			$netatmoThermostatcmd->save();
			
			$netatmoThermostatcmd = $this->getCmd(null, 'rfstatus');
			if (!is_object($netatmoThermostatcmd)) {
				$netatmoThermostatcmd = new netatmoThermostatcmd();
				$netatmoThermostatcmd->setName(__('Signal RF', __FILE__));
				$netatmoThermostatcmd->setIsHistorized(1);
			}
			$netatmoThermostatcmd->setEqLogic_id($this->getId());
			$netatmoThermostatcmd->setLogicalId('rfstatus');
			$netatmoThermostatcmd->setUnite('%');
			$netatmoThermostatcmd->setType('info');
			$netatmoThermostatcmd->setSubType('numeric');
			$netatmoThermostatcmd->setEventOnly(1);
			$netatmoThermostatcmd->save();
			
			$netatmoThermostatcmd = $this->getCmd(null, 'wifistatus');
			if (!is_object($netatmoThermostatcmd)) {
				$netatmoThermostatcmd = new netatmoThermostatcmd();
				$netatmoThermostatcmd->setName(__('Signal Wifi', __FILE__));
				$netatmoThermostatcmd->setIsHistorized(1);
			}
			$netatmoThermostatcmd->setEqLogic_id($this->getId());
			$netatmoThermostatcmd->setLogicalId('wifistatus');
			$netatmoThermostatcmd->setType('info');
			$netatmoThermostatcmd->setUnite('%');
			$netatmoThermostatcmd->setSubType('numeric');
			$netatmoThermostatcmd->setEventOnly(1);
			$netatmoThermostatcmd->save();
            
            $refresh = $this->getCmd(null, 'refresh');
            if (!is_object($refresh)) {
                $refresh = new wazeintimeCmd();
                $refresh->setLogicalId('refresh');
                $refresh->setIsVisible(1);
                $refresh->setName(__('Rafraichir', __FILE__));
            }
            $refresh->setType('action');
            $refresh->setSubType('other');
            $refresh->setEqLogic_id($this->getId());
            $refresh->save();
	}

	//public function toHtml($_version = 'dashboard') {

    //}
}

class netatmoThermostatCmd extends cmd {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	public function dontRemoveCmd() {
		return true;
	}

	public function execute($_options = array()) {
		$eqLogic = $this->getEqlogic();
		$eqLogic->syncWithTherm($eqLogic->getLogicalId());
	}

	/*     * **********************Getteur Setteur*************************** */
}

?>