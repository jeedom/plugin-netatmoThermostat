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
    
    public function syncWithNetatmo() {
        $token = netatmoThermostat::getTokenfromNetatmo('read');
        $url_devices = "https://api.netatmo.net/api/devicelist?access_token=" .  $token ."&app_type=app_thermostat";
        $resulat_device = @file_get_contents($url_devices);
        $json_devices = json_decode($resulat_device,true);
		log::add('netatmoThermostat', 'debug', print_r($resulat_device, true));
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

	public function toHtml($_version = 'dashboard') {

    }
}

class netatmoThermostatCmd extends cmd {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */

	/*     * *********************Methode d'instance************************* */

	public function dontRemoveCmd() {
		return true;
	}

	public function execute($_options = array()) {

	}

	/*     * **********************Getteur Setteur*************************** */
}

?>