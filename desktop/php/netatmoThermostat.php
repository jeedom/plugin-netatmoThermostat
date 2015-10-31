<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'netatmoThermostat');
$eqLogics = eqLogic::byType('netatmoThermostat');
?>

<div class="row row-overflow">
  <div class="col-sm-2">
    <div class="bs-sidebar">
      <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
        <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
        <?php
foreach ($eqLogics as $eqLogic) {
	$opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
	echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '" style="' . $opacity . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
}
?>
     </ul>
   </div>
 </div>
 <div class="col-lg-10 col-sm-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
  <legend>{{Mes Thermostats}}
  </legend>
  <?php
if (count($eqLogics) == 0) {
	echo "<br/><br/><br/><center><span style='color:#767676;font-size:1.2em;font-weight: bold;'>{{Vous n'avez pas encore de thermostat Netatmo, aller sur Général -> Plugin et cliquez sur synchroniser pour commencer}}</span></center>";
} else {
	?>
   <div class="eqLogicThumbnailContainer">
    <?php
foreach ($eqLogics as $eqLogic) {
		$opacity = '';
        if ($eqLogic->getIsEnable() != 1) {
            $opacity = 'opacity:0.3;';
        }
        echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
        echo "<center>";
        echo '<img src="plugins/netatmoThermostat/doc/images/thermostat.png" height="105" width="95" />';
        echo "</center>";
        echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
        echo '</div>';
	}
	?>
 </div>
 <?php }
?>
</div>
<div class="col-sm-10 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
 <div class="row">
  <div class="col-sm-6">
    <form class="form-horizontal">
     <fieldset>
      <legend><i class="fa fa-arrow-circle-left eqLogicAction cursor" data-action="returnToThumbnailDisplay"></i> {{Général}}<i class='fa fa-cogs eqLogicAction pull-right cursor expertModeVisible' data-action='configure'></i></legend>
      <div class="form-group">
        <label class="col-sm-4 control-label">{{Nom du Thermsotat Netatmo}}</label>
        <div class="col-sm-6">
          <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
          <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom du Thermsotat Netatmo}}"/>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-4 control-label" >{{Objet parent}}</label>
        <div class="col-sm-6">
          <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
            <option value="">{{Aucun}}</option>
            <?php
foreach (object::all() as $object) {
	echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
}
?>
         </select>
       </div>
     </div>
     <div class="form-group">
       <label class="col-sm-4 control-label"></label>
       <div class="col-sm-8">
         <input type="checkbox" class="eqLogicAttr bootstrapSwitch" data-label-text="{{Activer}}" data-l1key="isEnable" checked/>
         <input type="checkbox" class="eqLogicAttr bootstrapSwitch" data-label-text="{{Visible}}" data-l1key="isVisible" checked/>
       </div>
     </div>
	  <legend><i class="fa fa-info"></i>  {{Information}}</legend>
     <div class="form-group">
      <label class="col-sm-4 control-label">{{Identifiant}}</label>
      <div class="col-sm-6">
        <input disabled class="eqLogicAttr configuration form-control" data-l1key="logicalId"/>
      </div>
      </div>
       <div class="form-group">
      <label class="col-sm-4 control-label">{{Firmware}}</label>
	  <div class="col-sm-2">
        <input disabled class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="modulefirm"/>
      </div>
      <div class="col-sm-2">
        <input disabled class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="devicefirm"/>
      </div>
    </div>
	 <legend><i class="fa fa-wrench"></i>  {{Configuration}}</legend>
                <div class="form-group">
                    <label class="col-lg-4 control-label">{{Durée Mode Max/Manuel par défaut}}</label>
                    <div class="col-lg-4">
                        <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="maxdefault" placeholder="{{Durée en minutes (60 par défaut)}}"/>
                    </div>
  </fieldset>
</form>
</div>
<div class="col-sm-6">
  <center>
    <img src="plugins/netatmoThermostat/doc/images/thermostat.png" style="height : 400px;" />
  </center>
</div>
</div>
 <div class="form-actions" align="right">
                    <a class="btn btn-danger eqLogicAction" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
                    <a class="btn btn-success eqLogicAction" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
                </div>

<legend><i class="fa fa-list-alt"></i>  {{Tableau de commandes}}</legend>
<table id="table_cmd" class="table table-bordered table-condensed">
  <thead>
    <tr>
      <th>{{Nom}}</th><th>{{Option}}</th><th>{{Action}}</th>
    </tr>
  </thead>
  <tbody>
  </tbody>
</table>

<form class="form-horizontal">
  <fieldset>
    <div class="form-actions">
      <a class="btn btn-danger eqLogicAction" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
      <a class="btn btn-success eqLogicAction" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
    </div>
  </fieldset>
</form>

</div>
</div>

<?php include_file('desktop', 'netatmoThermostat', 'js', 'netatmoThermostat');?>
<?php include_file('core', 'plugin.template', 'js');?>