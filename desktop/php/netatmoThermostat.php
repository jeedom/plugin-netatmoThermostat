<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('netatmoThermostat');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
 <div class="col-xs-12 eqLogicThumbnailDisplay">
   <legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
   <div class="eqLogicThumbnailContainer">
  <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
      <i class="fas fa-wrench"></i>
    <br/>
    <span>{{Configuration}}</span>
  </div>
  <div class="cursor logoSecondary" id="bt_healthnetatmothermostat">
      <i class="fas fa-medkit"></i>
    <br/>
    <span>{{Santé}}</span>
  </div>
</div>
  <legend><i class="icon jeedom-thermo-chaud"></i>  {{Mes Thermostats}}</legend>
	<div class="input-group" style="margin:5px;">
	<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic"/>
	<div class="input-group-btn">
		<a id="bt_resetSearch" class="btn roundedRight" style="width:30px"><i class="fas fa-times"></i></a>
	</div>
</div>
		<div class="eqLogicThumbnailContainer">
<?php
foreach ($eqLogics as $eqLogic) {
	$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
	echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
	echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
	echo '<br>';
	echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
	echo '</div>';
}
?>
</div>
</div>
<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
				</a><a class="btn btn-sm btn-default eqLogicAction" data-action="copy"><i class="fas fa-copy"></i><span class="hidden-xs">  {{Dupliquer}}</span>
				</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
				</a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}
				</a>
			</span>
		</div>
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a href="" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
		</ul>
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<br/>
 <div class="row">
	 <div class="col-lg-7">
		 <form class="form-horizontal">
			 <fieldset>
				 <legend><i class="fas fa-wrench"></i> {{Général}}</legend>
				 <div class="form-group">
					 <label class="col-xs-12 col-sm-3 control-label">{{Nom de l'équipement}}</label>
					 <div class="col-xs-11 col-sm-7">
						 <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;"/>
						 <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
					 </div>
				 </div>
				 <div class="form-group">
					 <label class="col-xs-12 col-sm-3 control-label" >{{Objet parent}}</label>
					 <div class="col-xs-11 col-sm-7">
						 <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
							 <option value="">{{Aucun}}</option>
							 <?php
							 $options = '';
							 foreach ((jeeObject::buildTree(null, false)) as $object) {
								 $options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
							 }
							 echo $options;
							 ?>
						 </select>
					 </div>
				 </div>
				 <div class="form-group">
					 <label class="col-xs-12 col-sm-3 control-label">{{Catégorie}}</label>
					 <div class="col-sm-9">
						 <?php
						 foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
							 echo '<label class="checkbox-inline">';
							 echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
							 echo '</label>';
						 }
						 ?>
					 </div>
				 </div>
				 <div class="form-group">
					 <label class="col-xs-12 col-sm-3 control-label">{{Options}}</label>
					 <div class="col-xs-11 col-sm-7">
						 <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
						 <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
					 </div>
				 </div>

				 <br/>
				 <legend><i class="fas fa-cogs"></i> {{Paramètres}}</legend>
     <div class="form-group">
      <label class="col-xs-12 col-sm-3 control-label">{{Identifiant}}</label>
      <div class="col-xs-11 col-sm-7">
        <input disabled class="eqLogicAttr configuration form-control" data-l1key="logicalId"/>
      </div>
      </div>
       <div class="form-group">
      <label class="col-xs-12 col-sm-3 control-label">{{Firmware}}</label>
	  <div class="col-sm-2">
        <input disabled class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="modulefirm"/>
      </div>
      <div class="col-sm-2">
        <input disabled class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="devicefirm"/>
      </div>
    </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 control-label">{{Durée Mode Max/Manuel par défaut}}</label>
                    <div class="col-xs-11 col-sm-7">
                        <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="maxdefault" placeholder="{{Durée en minutes (60 par défaut)}}"/>
                    </div>
														 <br/>
  </fieldset>
</form>
</div>

<div class="col-lg-5">
	<form class="form-horizontal">
		<fieldset>
			<legend><i class="fas fa-info"></i> {{Informations}}</legend>
			<div class="form-group">
				<label class="col-sm-3"></label>
				<div class="col-sm-7 text-center">
					<img name="icon_visu" src="plugins/netatmoThermostat/docs/images/thermostat.png" style="max-width:160px;"/>
				</div>
			</div>
		</fieldset>
	</form>
</div>
</div>

</div>
			<div role="tabpanel" class="tab-pane" id="commandtab">
<table id="table_cmd" class="table table-bordered table-condensed">
  <thead>
    <tr>
      <th>{{Nom}}</th><th>{{Option}}</th><th>{{Action}}</th>
    </tr>
  </thead>
  <tbody>
  </tbody>
</table>

</div>
</div>
</div>
</div>

<?php include_file('desktop', 'netatmoThermostat', 'js', 'netatmoThermostat');?>
<?php include_file('core', 'plugin.template', 'js');?>
