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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
	include_file('desktop', '404', 'php');
	die();
}
?>
<form class="form-horizontal">
    <fieldset>
     <div class="form-group">
        <label class="col-xs-12 col-sm-3 control-label">{{Client ID}}</label>
        <div class="col-xs-11 col-sm-7">
            <input type="text" class="configKey form-control" data-l1key="client_id" placeholder="Client ID"/>
        </div>
    </div>
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 control-label">{{Client secret}}</label>
        <div class="col-xs-11 col-sm-7">
            <input type="text" class="configKey form-control" data-l1key="client_secret" placeholder="Client Secret"/>
        </div>
    </div>
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 control-label">{{Nom d'utilisateur}}</label>
        <div class="col-xs-11 col-sm-7">
            <input type="text" class="configKey form-control" data-l1key="username" placeholder="Nom d'utilisateur"/>
        </div>
    </div>
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 control-label">{{Mot de passe}}</label>
        <div class="col-xs-11 col-sm-7">
            <input type="password" class="configKey form-control" data-l1key="password" placeholder="Mot de passe"/>
        </div>
    </div>
	<?php
		try {
			$hasweather = plugin::byId('netatmoWeather');
			$haswelcome = plugin::byId('netatmoWelcome');
		} catch (Exception $e) {
		}
		if (($hasweather && $hasweather->isActive()) || ($haswelcome && $haswelcome->isActive())){
			echo '<div class="form-group">
			<label class="col-xs-12 col-sm-3 control-label">{{Récupérer les infos du plugin}}</label>';
			if (($haswelcome && $haswelcome->isActive())){
				echo '<div class="col-xs-11 col-sm-7">
					<a class="btn btn-success" id="bt_getFromWelcome"><i class="fas fa-random"></i> {{Netatmo Welcome}}</a>
					</div>';
			}
			if (($hasweather && $hasweather->isActive())){
				echo '<div class="col-xs-11 col-sm-7">
					<a class="btn btn-success" id="bt_getFromWeather"><i class="fas fa-random"></i> {{Netatmo Station}}</a>
					</div>';
			}
			echo '</div>';
		}
    ?>
    <div class="form-group">
        <label class="col-xs-12 col-sm-3 control-label">{{Synchroniser}}</label>
        <div class="col-xs-11 col-sm-7">
        <a class="btn btn-warning" id="bt_syncWithNetatmoThermostat"><i class='fas fa-sync'></i> {{Synchroniser mes Thermostats}}</a>
        </div>
    </div>
</fieldset>
</form>

<script>
    $('#bt_syncWithNetatmoThermostat').on('click', function () {
        $.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/netatmoThermostat/core/ajax/netatmoThermostat.ajax.php", // url du fichier php
            data: {
                action: "syncWithNetatmo",
            },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) { // si l'appel a bien fonctionné
                if (data.state != 'ok') {
                    $('#div_alert').showAlert({message: data.result, level: 'danger'});
                    return;
                }
                $('#div_alert').showAlert({message: '{{Synchronisation réussie}}', level: 'success'});
            }
        });
    });
	$('#bt_getFromWelcome').on('click', function () {
	bootbox.confirm('{{Cela récupérera les identifiants configurés dans le plugin Netatmo Welcome, il faudra sauver avant de lancer la synchronisation. Voulez vous procéder ? }}', function (result) {
      if (result) {
		$.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/netatmoThermostat/core/ajax/netatmoThermostat.ajax.php", // url du fichier php
            data: {
                action: "getFromWelcome",
            },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) { // si l'appel a bien fonctionné
				if (data.state != 'ok') {
                    $('#div_alert').showAlert({message: data.result, level: 'danger'});
                    return;
                }
				console.log(data.result[0]);
				$('.configKey[data-l1key=client_id]').empty().val(data.result[0]);
				$('.configKey[data-l1key=client_secret]').empty().val(data.result[1]);
				$('.configKey[data-l1key=username]').empty().val(data.result[2]);
				$('.configKey[data-l1key=password]').empty().val(data.result[3]);
                $('#div_alert').showAlert({message: '{{Synchronisation réussie}}', level: 'success'});
            }
        });
    };
	});
	});
	$('#bt_getFromWeather').on('click', function () {
	bootbox.confirm('{{Cela récupérera les identifiants configurés dans le plugin Netatmo Weather, il faudra sauver avant de lancer la synchronisation. Voulez vous procéder ? }}', function (result) {
      if (result) {
		$.ajax({// fonction permettant de faire de l'ajax
            type: "POST", // methode de transmission des données au fichier php
            url: "plugins/netatmoThermostat/core/ajax/netatmoThermostat.ajax.php", // url du fichier php
            data: {
                action: "getFromWeather",
            },
            dataType: 'json',
            error: function (request, status, error) {
                handleAjaxError(request, status, error);
            },
            success: function (data) { // si l'appel a bien fonctionné
				if (data.state != 'ok') {
                    $('#div_alert').showAlert({message: data.result, level: 'danger'});
                    return;
                }
				console.log(data.result[0]);
				$('.configKey[data-l1key=client_id]').empty().val(data.result[0]);
				$('.configKey[data-l1key=client_secret]').empty().val(data.result[1]);
				$('.configKey[data-l1key=username]').empty().val(data.result[2]);
				$('.configKey[data-l1key=password]').empty().val(data.result[3]);
                $('#div_alert').showAlert({message: '{{Synchronisation réussie}}', level: 'success'});
            }
        });
    };
	});
	});
</script>
