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
	
	public function changemodeTherm($multiId,$action) {
		$ids = explode('|', $multiId);
		$deviceid= $ids[0];
		$modid= $ids[1];
		$token = netatmoThermostat::getTokenfromNetatmo('write');
		$url_thermostat="https://api.netatmo.net/api/setthermpoint?access_token=" .  $token."&device_id=".$deviceid."&module_id=".$modid."&setpoint_mode=".$action;
		log::add('netatmoThermostat', 'debug',"Setting mode to : " . $action);
		$request = new com_http($url_thermostat);
		$result = $request->exec();
		$this->syncWithTherm($multiId);
    }
	
	public function changesetpointTherm($multiId,$setpoint) {
		$ids = explode('|', $multiId);
		$deviceid= $ids[0];
		$modid= $ids[1];
		$length=120;
		$endtime = time() + ($length * 60);
		$token = netatmoThermostat::getTokenfromNetatmo('write');
		$url_thermostat="https://api.netatmo.net/api/setthermpoint?access_token=" .  $token."&device_id=".$deviceid."&module_id=".$modid."&setpoint_mode=manual&setpoint_endtime=".$endtime."&setpoint_temp=".$setpoint;
		log::add('netatmoThermostat', 'debug',"Setting temperature to : " . $setpoint . " for " . $length . " minutes");
		$request = new com_http($url_thermostat);
		$result = $request->exec();
		$this->syncWithTherm($multiId);
    }
	
	public function changescheduleTherm($multiId,$scheduleid) {
		$ids = explode('|', $multiId);
		$deviceid= $ids[0];
		$modid= $ids[1];
		$token = netatmoThermostat::getTokenfromNetatmo('write');
		$url_thermostat="https://api.netatmo.net/api/switchschedule?access_token=" .  $token."&device_id=".$deviceid."&module_id=".$modid."&schedule_id=".$scheduleid;
		log::add('netatmoThermostat', 'debug',"Setting schedule to : " . $scheduleid);
		$request = new com_http($url_thermostat);
		$result = $request->exec();
		$this->syncWithTherm($multiId);
    }

    public function syncWithTherm($multiId) {
		$ids = explode('|', $multiId);
		$deviceid= $ids[0];
		$token = netatmoThermostat::getTokenfromNetatmo('read');
		$url_thermostat="https://api.netatmo.net/api/getthermostatsdata?access_token=" .  $token."&device_id=".$deviceid;
		$resultat_therm = @file_get_contents($url_thermostat);
        $json_therm = json_decode($resultat_therm,true);
		log::add('netatmoThermostat','debug',print_r($resultat_therm,true));
		$eqLogic = eqLogic::byLogicalId($multiId,'netatmoThermostat');
		$temperature_thermostat = $json_therm["body"]["devices"][0]["modules"][0]["measured"]["temperature"];
		$wifistatus=$json_therm["body"]["devices"][0]["wifi_status"];
		$batterie=$json_therm["body"]["devices"][0]["modules"][0]["battery_vp"];
		$rfstatus=$json_therm["body"]["devices"][0]["modules"][0]["rf_status"];
		$chaudierestate=$json_therm["body"]["devices"][0]["modules"][0]["therm_relay_cmd"];
		if ($chaudierestate != 0) {
			$chaudierestate = 1;
		}
		$planindex=0;
		$count=0;
		$listplanning='';
		foreach ($json_therm["body"]["devices"][0]["modules"][0]["therm_program_list"] as $plan) {
			$status=(isset($plan['selected'])) ? $plan['selected'] : "NO";
			if ($status == true){
				$planningname=$plan['name'];
				$planindex=$count;
			}
			$listplanning=$listplanning . $plan['name'] . ';' . $plan['program_id'] . '|';
			$count++;
		}
		$mode=$json_therm["body"]["devices"][0]["modules"][0]["setpoint"]["setpoint_mode"];
		$planning ='Aucun';
		$nextplanning ='Aucun';
		if ($mode=='away') {
			$consigne = $json_therm["body"]["devices"][0]["modules"][0]["therm_program_list"][$planindex]["zones"][2]["temp"];
			$mode = $json_therm["body"]["devices"][0]["modules"][0]["therm_program_list"][0]["zones"][2]["name"];
			$setpointmode_endtime=9999;
       } elseif ($mode=='hg') {
			$consigne = $json_therm["body"]["devices"][0]["modules"][0]["therm_program_list"][$planindex]["zones"][3]["temp"];
			$mode =$json_therm["body"]["devices"][0]["modules"][0]["therm_program_list"][$planindex]["zones"][3]["name"];
			$setpointmode_endtime=9999;
       } elseif ($mode=='manual') {
			$consigne = $json_therm["body"]["devices"][0]["modules"][0]["setpoint"]["setpoint_temp"];
			$setpointmode_endtime = date ( 'Hi' , $json_therm["body"]["devices"][0]["modules"][0]["setpoint"]["setpoint_endtime"] ) ;
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
				$minutes = $json_therm["body"]["devices"][0]["modules"][0]["therm_program_list"][$planindex]["timetable"][$i]["m_offset"];
				$planning_id = $json_therm["body"]["devices"][0]["modules"][0]["therm_program_list"][$planindex]["timetable"][($i-1)]["id"];
				$nextplanning_id = $json_therm["body"]["devices"][0]["modules"][0]["therm_program_list"][$planindex]["timetable"][($i)]["id"];
				if ($planning_id>2) {$planning_id=$planning_id-2;} 
				if ($nextplanning_id>2) {$nextplanning_id=$nextplanning_id-2;} // dans la table, il y a intercalage des modes 'absent' et 'HG' qui décalent la numérotation 
				$planning = $json_therm["body"]["devices"][0]["modules"][0]["therm_program_list"][$planindex]["zones"][$planning_id]["name"];
				$nextplanning = $json_therm["body"]["devices"][0]["modules"][0]["therm_program_list"][$planindex]["zones"][$nextplanning_id]["name"];
				$consigne = $json_therm["body"]["devices"][0]["modules"][0]["therm_program_list"][$planindex]["zones"][$planning_id]["temp"];
				$secondes = 60 * $minutes;
				$jour=floor($secondes/86400);
				$reste=$secondes%86400;
				$heure=floor($reste/3600);
				$reste=$reste%3600;
				$minute=floor($reste/60);
				$seconde=$reste%60;
				$zero='';
				$zeroh='';
				if ($minute<=9) {$zero='0';}
				if ($heure<=9) {$zeroh='0';}
				$i++;
			}
			$setpointmode_endtime=$zeroh.$heure.$zero.$minute;
			$mode = 'Programme';
		} else {
			$consigne = $json_therm["body"]["devices"][0]["modules"][0]["measured"]["setpoint_temp"];
			$setpointmode_endtime=9999;
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
						case 'Calendrier':
							$value=$planningname;
						break;
						case 'Liste Calendrier':
							$value=substr($listplanning, 0, -1);
						break;
						case 'Etat Chauffage':
							$value=$chaudierestate;
						break;
						case 'Heure Fin Mode en Cours':
							$value=$setpointmode_endtime;
						break;
						case 'Batterie':
							$value=$batterie;
						break;
			}
			$cmd->event($value);
			log::add('netatmoThermostat','debug','set:'.$cmd->getName().' to '. $value);
		}
    }
    
    public function syncWithNetatmo() {
        $token = netatmoThermostat::getTokenfromNetatmo('read');
        $url_devices = "https://api.netatmo.net/api/getthermostatsdata?access_token=" .  $token;
        $resultat_device = @file_get_contents($url_devices);
        $json_devices = json_decode($resultat_device,true);
		foreach ($json_devices['body']['devices'] as $thermostat) {
			$deviceid=$thermostat['_id'];
			$devicefirm=$thermostat['firmware'];
			$module_name=$thermostat['modules'][0]['module_name'];
			$modulefirm=$thermostat['modules'][0]['firmware'];
			$moduleid=$thermostat['modules'][0]['_id'];
			$multiId = $deviceid . '|' . $moduleid;
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
			
			$netatmoThermostatcmd = $this->getCmd(null, 'calendar');
			if (!is_object($netatmoThermostatcmd)) {
				$netatmoThermostatcmd = new netatmoThermostatcmd();
				$netatmoThermostatcmd->setName(__('Calendrier', __FILE__));
			}
			$netatmoThermostatcmd->setEqLogic_id($this->getId());
			$netatmoThermostatcmd->setLogicalId('calendar');
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
			
			$netatmoThermostatcmd = $this->getCmd(null, 'listcalendar');
			if (!is_object($netatmoThermostatcmd)) {
				$netatmoThermostatcmd = new netatmoThermostatcmd();
				$netatmoThermostatcmd->setName(__('Liste Calendrier', __FILE__));
			}
			$netatmoThermostatcmd->setEqLogic_id($this->getId());
			$netatmoThermostatcmd->setLogicalId('listcalendar');
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
			
			$netatmoThermostatcmd = $this->getCmd(null, 'batterie');
			if (!is_object($netatmoThermostatcmd)) {
				$netatmoThermostatcmd = new netatmoThermostatcmd();
				$netatmoThermostatcmd->setName(__('Batterie', __FILE__));
				$netatmoThermostatcmd->setIsHistorized(1);
			}
			$netatmoThermostatcmd->setEqLogic_id($this->getId());
			$netatmoThermostatcmd->setLogicalId('batterie');
			$netatmoThermostatcmd->setType('info');
			$netatmoThermostatcmd->setSubType('numeric');
			$netatmoThermostatcmd->setEventOnly(1);
			$netatmoThermostatcmd->save();
			
			$netatmoThermostatcmd = $this->getCmd(null, 'endsetpoint');
			if (!is_object($netatmoThermostatcmd)) {
				$netatmoThermostatcmd = new netatmoThermostatcmd();
				$netatmoThermostatcmd->setName(__('Heure Fin Mode en Cours', __FILE__));
				$netatmoThermostatcmd->setIsHistorized(0);
			}
			$netatmoThermostatcmd->setEqLogic_id($this->getId());
			$netatmoThermostatcmd->setLogicalId('endsetpoint');
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
			
			$netatmoThermostatcmd = $this->getCmd(null, 'heatstatus');
			if (!is_object($netatmoThermostatcmd)) {
				$netatmoThermostatcmd = new netatmoThermostatcmd();
				$netatmoThermostatcmd->setName(__('Etat Chauffage', __FILE__));
				$netatmoThermostatcmd->setIsHistorized(1);
			}
			$netatmoThermostatcmd->setEqLogic_id($this->getId());
			$netatmoThermostatcmd->setLogicalId('heatstatus');
			$netatmoThermostatcmd->setType('info');
			$netatmoThermostatcmd->setSubType('binary');
			$netatmoThermostatcmd->setEventOnly(1);
			$netatmoThermostatcmd->save();
            
            $refresh = $this->getCmd(null, 'refresh');
            if (!is_object($refresh)) {
                $refresh = new netatmoThermostatcmd();
                $refresh->setLogicalId('refresh');
                $refresh->setIsVisible(1);
                $refresh->setName(__('Rafraichir', __FILE__));
            }
            $refresh->setType('action');
            $refresh->setSubType('other');
            $refresh->setEqLogic_id($this->getId());
            $refresh->save();
            
            $away = $this->getCmd(null, 'away');
            if (!is_object($away)) {
                $away = new netatmoThermostatcmd();
                $away->setLogicalId('away');
                $away->setIsVisible(1);
                $away->setName(__('Absent', __FILE__));
            }
            $away->setType('action');
            $away->setSubType('other');
            $away->setEqLogic_id($this->getId());
            $away->save();
            
            $program = $this->getCmd(null, 'program');
            if (!is_object($program)) {
                $program = new netatmoThermostatcmd();
                $program->setLogicalId('program');
                $program->setIsVisible(1);
                $program->setName(__('Programme', __FILE__));
            }
            $program->setType('action');
            $program->setSubType('other');
            $program->setEqLogic_id($this->getId());
            $program->save();
            
            $hg = $this->getCmd(null, 'hg');
            if (!is_object($hg)) {
                $hg = new netatmoThermostatcmd();
                $hg->setLogicalId('hg');
                $hg->setIsVisible(1);
                $hg->setName(__('Hors-gel', __FILE__));
            }
            $hg->setType('action');
            $hg->setSubType('other');
            $hg->setEqLogic_id($this->getId());
            $hg->save();
            
            $off = $this->getCmd(null, 'off');
            if (!is_object($off)) {
                $off = new netatmoThermostatcmd();
                $off->setLogicalId('off');
                $off->setIsVisible(1);
                $off->setName(__('Eteindre', __FILE__));
            }
            $off->setType('action');
            $off->setSubType('other');
            $off->setEqLogic_id($this->getId());
            $off->save();
            
            $max = $this->getCmd(null, 'max');
            if (!is_object($max)) {
                $max = new netatmoThermostatcmd();
                $max->setLogicalId('max');
                $max->setIsVisible(1);
                $max->setName(__('Max', __FILE__));
            }
            $max->setType('action');
            $max->setSubType('other');
            $max->setEqLogic_id($this->getId());
            $max->save();
			
			$consigneset = $this->getCmd(null, 'consigneset');
            if (!is_object($consigneset)) {
                $consigneset = new netatmoThermostatcmd();
                $consigneset->setLogicalId('consigneset');
                $consigneset->setIsVisible(1);
                $consigneset->setName(__('Réglage Consigne', __FILE__));
            }
            $consigneset->setType('action');
            $consigneset->setSubType('slider');
            $consigneset->setEqLogic_id($this->getId());
            $consigneset->save();
			
			$dureeset = $this->getCmd(null, 'dureeset');
            if (!is_object($dureeset)) {
                $dureeset = new netatmoThermostatcmd();
                $dureeset->setLogicalId('dureeset');
                $dureeset->setIsVisible(1);
                $dureeset->setName(__('Réglage Durée', __FILE__));
            }
            $dureeset->setType('action');
            $dureeset->setSubType('slider');
            $dureeset->setEqLogic_id($this->getId());
            $dureeset->save();
	}

	//public function toHtml($_version = 'dashboard') {

    //}
}

class netatmoThermostatCmd extends cmd {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	public function execute($_options = array()) {
		if ($this->getType() == '') {
			return '';
		}
		$eqLogic = $this->getEqlogic();
		$action= $this->getLogicalId();
		if ($action == 'refresh') {
			$eqLogic->syncWithTherm($eqLogic->getLogicalId());
		} elseif ($action == 'away' || $action == 'hg' || $action == 'off' || $action == 'program' || $action == 'max') {
			$eqLogic->changemodeTherm($eqLogic->getLogicalId(),$action);
		} elseif ($action == 'consigneset') {
			$temperatureset = $_options['slider'];
			$eqLogic->changesetpointTherm($eqLogic->getLogicalId(),$temperatureset);
		}
	}

	/*     * **********************Getteur Setteur*************************** */
}

?>