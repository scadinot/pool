<?php
	// test
	if (!isConnect('admin')) {
		throw new Exception('{{401 - Accès non autorisé}}');
	}
	sendVarToJS('eqType', 'pool');
	$eqLogics = eqLogic::byType('pool');
?>

<div class="row row-overflow">
    <div class="col-lg-2 col-md-3 col-sm-4">
        <div class="bs-sidebar">
            <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
                <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter une piscine}}</a>
                <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
                <?php
					foreach ($eqLogics as $eqLogic) {
						echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
					}
				?>
			</ul>
		</div>
	</div>

	<div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
		<legend>{{Mes piscines}}
		</legend>
		<div class="eqLogicThumbnailContainer">
			<div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
				<center>
					<i class="fa fa-plus-circle" style="font-size : 7em;color:#849ed2;"></i>
				</center>
				<span style="font-size : 1.1em;position:relative; top : 23px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#849ed2"><center>Ajouter</center></span>
			</div>
			<?php
				foreach ($eqLogics as $eqLogic) {
					echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >';
					echo "<center>";
					echo '<img src="plugins/pool/doc/images/pool_icon.png" height="105" width="95" />';
					echo "</center>";
					echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
					echo '</div>';
				}
			?>
		</div>
	</div>

    <div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
		<div class="row">

			<div class="col-sm-6">
				<form class="form-horizontal">
					<fieldset>
						<legend><i class="fa fa-arrow-circle-left eqLogicAction cursor" data-action="returnToThumbnailDisplay"></i> {{Général}}
							<i class='fa fa-cogs eqLogicAction pull-right cursor expertModeVisible' data-action='configure'></i>
						</legend>
						<div class="form-group">
							<label class="col-sm-4 control-label">{{Nom de l'équipement piscine}}</label>
							<div class="col-sm-4">
								<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
								<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement piscine}}"/>
							</div>
							</div>
						<div class="form-group">
							<label class="col-sm-4 control-label" >{{Objet parent}}</label>
							<div class="col-sm-4">
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
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
							</div>
						</div>
					</fieldset>
				</form>
			</div>

			<div class="col-sm-6">
				<form class="form-horizontal">
					<fieldset>

						<legend>{{Configuration}}
							<a class="btn btn-xs btn-default pull-right eqLogicAction" data-action="copy"><i class="fa fa-files-o"></i> {{Dupliquer}}</a>
						</legend>

						<div class="form-group"  style="display: none" > <!-- -->
							<label class="col-sm-4 control-label">{{Chauffage}}</label>
							<div class="col-sm-4">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgChauffage" placeholder="" >
									<option value="disabled">{{Inactif}}</option>
									<option value="enabled">{{Actif}}</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-4 control-label">{{Traitement}}</label>
							<div class="col-sm-4">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgTraitement" placeholder="" >
									<option value="disabled">{{Inactif}}</option>
									<option value="enabled">{{Actif}}</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-4 control-label">{{Surpresseur}}</label>
							<div class="col-sm-4">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgSurpresseur" placeholder="" >
									<option value="disabled">{{Inactif}}</option>
									<option value="enabled">{{Actif}}</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-4 control-label">{{Filtre à sable}}</label>
							<div class="col-sm-4">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgFiltreSable" placeholder="" >
									<option value="disabled">{{Inactif}}</option>
									<option value="enabled">{{Actif}}</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-4 control-label">{{Hivernage}}</label>
							<div class="col-sm-4">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgHivernage" placeholder="" >
									<option value="disabled">{{Inactif}}</option>
									<option value="enabled">{{Actif}}</option>
									<option value="widget">{{Widget}}</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-4 control-label">{{Asservissement externe}}</label>
							<div class="col-sm-4">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgAsservissementExterne" placeholder="" >
									<option value="disabled">{{Inactif}}</option>
									<option value="enabled">{{Actif}}</option>
								</select>
							</div>
						</div>

					</fieldset>
				</form>
			</div>

		</div>

		<form class="form-horizontal">
			<fieldset>
				<div class="alert alert-info">
					{{Veuillez ajouter votre sonde de température}}
				</div>

				<div class="form-group">
					<label class="col-sm-2 control-label">{{Température de l'eau}}</label>
					<div class="col-sm-9">
						<input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="temperature_water" data-concat="1"/>
					</div>
					<div class="col-sm-1">
						<a class="btn btn-default btn-sm listCmdInfo"><i class="fa fa-list-alt"></i></a>
					</div>
				</div>

				<div class="form-group expertModeVisible">
					<label class="col-sm-2 control-label">{{Borne de température inférieure}}</label>
					<div class="col-sm-2">
						<input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="temperature_water_min" />
					</div>
					<label class="col-sm-2 control-label">{{Borne de température supérieure}}</label>
					<div class="col-sm-2">
						<input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="temperature_water_max" />
					</div>
				</div>

				<div class="form-group cfgExterieur enabled">
					<label class="col-sm-2 control-label">{{Température extérieure}}</label>
					<div class="col-sm-9">
						<input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="temperature_outdoor" data-concat="1"/>
					</div>
					<div class="col-sm-1">
						<a class="btn btn-default btn-sm listCmdInfo"><i class="fa fa-list-alt"></i></a>
					</div>
				</div>

			</fieldset>
		</form>

		<hr/>

		<ul class="nav nav-tabs">
			<li class="active"><a href="#configureFiltration" data-toggle="tab">{{Filtration}}</a></li>
			<li><a href="#configureTempFiltration" data-toggle="tab">{{Temps de filtration}}</a></li>
			<li class="cfgChauffage enabled"><a href="#configureChauffage" data-toggle="tab">{{Chauffage}}</a></li>
			<li class="cfgTraitement enabled"><a href="#configureTraitement" data-toggle="tab">{{Traitement}}</a></li>
			<li class="cfgSurpresseur enabled"><a href="#configureSurpresseur" data-toggle="tab">{{Surpresseur}}</a></li>
			<li class="cfgFiltreSable enabled"><a href="#configureFiltreSable" data-toggle="tab">{{Filtre à sable}}</a></li>
			<li class="cfgHivernage enabled"><a href="#configureHivernage" data-toggle="tab">{{Hivernage}}</a></li>
			<li class="cfgAsservissementExterne enabled"><a href="#configureAsservissement" data-toggle="tab">{{Asservissement externe}}</a></li>
			<li class="expertModeVisible"><a href="#configureAdvanced" data-toggle="tab">{{Configuration avancée}}</a></li>
		</ul>

		<div class="tab-content">

			<div class="tab-pane active" id="configureFiltration">
				<br/><br/>
				<form class="form-horizontal">
					<fieldset>
						<legend>
							{{Pour démarrer la filtration je dois ?}}
							<a class="btn btn-default btn-xs pull-right addAction" data-type="filtrationOn" style="position: relative; top : 5px;"><i class="fa fa-plus-circle"></i> {{Ajouter une action}}</a>
						</legend>
						<div id="div_filtrationOn"></div>
					</fieldset>
				</form>
				<br/>
				<form class="form-horizontal">
					<fieldset>
						<legend>
							{{Pour arrêter la filtration je dois ?}}
							<a class="btn btn-default btn-xs pull-right addAction" data-type="filtrationStop" style="position: relative; top : 5px;"><i class="fa fa-plus-circle"></i> {{Ajouter une action}}</a>
						</legend>
						<div id="div_filtrationStop"></div>
					</fieldset>
				</form>
				<br/>
				<form class="form-horizontal">
					<fieldset>
						<legend>
							{{Asservissement chauffage solaire}}
							<a class="btn btn-default btn-xs pull-right addAsservissement" data-type="asservissement" style="position: relative; top : 5px;"><i class="fa fa-plus-circle"></i> {{Ajouter asservissement}}</a>
						</legend>
						<div id="div_asservissement"></div>
					</fieldset>
				</form>
			</div>

			<div class="tab-pane" id="configureTempFiltration">
				<br/><br/>
				<form class="form-horizontal">
					<fieldset>

						<div class="form-group">
							<label class="col-sm-2 control-label">{{Choix méthode de calcul}}</label>
							<div class="col-sm-2">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="methodeCalcul" placeholder="">
									<option value="1">{{Courbe}}</option>
									<option value="2">{{Temp / 2}}</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">{{Ajustement du temps de filtration}}</label>
							<div class="col-sm-2">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="coefficientAjustement" placeholder="">
									<option value="3"> 30%</option>
									<option value="3.5"> 35%</option>
									<option value="4"> 40%</option>
									<option value="4.5"> 45%</option>
									<option value="5"> 50%</option>
									<option value="5.5"> 55%</option>
									<option value="6"> 60%</option>
									<option value="6.5"> 65%</option>
									<option value="7"> 70%</option>
									<option value="7.5"> 75%</option>
									<option value="8"> 80%</option>
									<option value="8.5"> 85%</option>
									<option value="9"> 90%</option>
									<option value="9.5"> 95%</option>
									<option value="10">100%</option>
									<option value="10.5">105%</option>
									<option value="11">110%</option>
									<option value="11.5">115%</option>
									<option value="12">120%</option>
									<option value="12.5">125%</option>
									<option value="13">130%</option>
									<option value="13.5">135%</option>
									<option value="14">140%</option>
									<option value="14.5">145%</option>
									<option value="15">150%</option>
									<option value="15.5">155%</option>
									<option value="16">160%</option>
									<option value="16.5">165%</option>
									<option value="17">170%</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">{{Horaire pivot de filtration}}</label>
							<div class="col-sm-2">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="datePivot" placeholder="">
									<option value="01:00">01:00</option>
									<option value="02:00">02:00</option>
									<option value="03:00">03:00</option>
									<option value="04:00">04:00</option>
									<option value="05:00">05:00</option>
									<option value="06:00">06:00</option>
									<option value="07:00">07:00</option>
									<option value="08:00">08:00</option>
									<option value="09:00">09:00</option>
									<option value="10:00">10:00</option>
									<option value="11:00">11:00</option>
									<option value="12:00">12:00</option>
									<option value="13:00">13:00</option>
									<option value="14:00">14:00</option>
									<option value="15:00">15:00</option>
									<option value="16:00">16:00</option>
									<option value="17:00">17:00</option>
									<option value="18:00">18:00</option>
									<option value="19:00">19:00</option>
									<option value="20:00">20:00</option>
									<option value="21:00">21:00</option>
									<option value="22:00">22:00</option>
									<option value="23:00">23:00</option>
									<option value="24:00">24:00</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">{{Temps de coupure (segmentation de la filtration)}}</label>
							<div class="col-sm-2">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="pausePivot" placeholder="">
									<option value="0">{{Sans}}</option>
									<option value="30">00:30</option>
									<option value="60">01:00</option>
									<option value="90">01:30</option>
									<option value="120">02:00</option>
									<option value="150">02:30</option>
									<option value="180">03:00</option>
									<option value="210">03:30</option>
									<option value="240">04:00</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">{{Répartition du temps de filtration autour de l'horaire pivot}}</label>
							<div class="col-sm-2">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="distributionDatePivot" placeholder="">
									<option value="1">1/2 - 1/2</option>
									<option value="2">1/3 - 2/3</option>
								</select>
							</div>
						</div>

					</fieldset>
				</form>
			</div>

			<div class="tab-pane" id="configureChauffage">
				<br/><br/>
				<form class="form-horizontal">
					<fieldset>
						<legend>
							{{Pour démarrer le chauffage je dois ?}}
							<a class="btn btn-default btn-xs pull-right addAction" data-type="chauffageOn" style="position: relative; top : 5px;"><i class="fa fa-plus-circle"></i> {{Ajouter une action}}</a>
						</legend>
						<div id="div_chauffageOn"></div>
					</fieldset>
				</form>
				<br/>
				<form class="form-horizontal">
					<fieldset>
						<legend>
							{{Pour arrêter le chauffage je dois ?}}
							<a class="btn btn-default btn-xs pull-right addAction" data-type="chauffageStop" style="position: relative; top : 5px;"><i class="fa fa-plus-circle"></i> {{Ajouter une action}}</a>
						</legend>
						<div id="div_chauffageStop"></div>
					</fieldset>
				</form>
			</div>

			<div class="tab-pane" id="configureTraitement">
				<br/><br/>
				<form class="form-horizontal">
					<fieldset>
						<legend>
							{{Pour démarrer le traitement je dois ?}}
							<a class="btn btn-default btn-xs pull-right addAction" data-type="traitementOn" style="position: relative; top : 5px;"><i class="fa fa-plus-circle"></i> {{Ajouter une action}}</a>
						</legend>
						<div id="div_traitementOn"></div>
					</fieldset>
				</form>
				<br/>
				<form class="form-horizontal">
					<fieldset>
						<legend>
							{{Pour arrêter le traitement je dois ?}}
							<a class="btn btn-default btn-xs pull-right addAction" data-type="traitementStop" style="position: relative; top : 5px;"><i class="fa fa-plus-circle"></i> {{Ajouter une action}}</a>
						</legend>
						<div id="div_traitementStop"></div>
					</fieldset>
				</form>
			</div>

			<div class="tab-pane" id="configureSurpresseur">
				<br/><br/>
				<form class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-2 control-label">{{Temps de fonctionnement du surpresseur (min)}}</label>
						<div class="col-sm-2">
							<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="surpresseurDuree" placeholder="" >
								<option value="10">10</option>
								<option value="20">20</option>
								<option value="30">30</option>
								<option value="40">40</option>
								<option value="50">50</option>
								<option value="60">60</option>
								<option value="70">70</option>
								<option value="80">80</option>
								<option value="90">90</option>
								<option value="100">100</option>
								<option value="110">110</option>
								<option value="120">120</option>
							</select>
						</div>
					</div>
				</form>
				<br/>
				<form class="form-horizontal">
					<fieldset>
						<legend>
							{{Pour démarrer le surpresseur je dois ?}}
							<a class="btn btn-default btn-xs pull-right addAction" data-type="surpresseurOn" style="position: relative; top : 5px;"><i class="fa fa-plus-circle"></i> {{Ajouter une action}}</a>
						</legend>
						<div id="div_surpresseurOn"></div>
					</fieldset>
				</form>
				<br/>
				<form class="form-horizontal">
					<fieldset>
						<legend>
							{{Pour arrêter le surpresseur je dois ?}}
							<a class="btn btn-default btn-xs pull-right addAction" data-type="surpresseurStop" style="position: relative; top : 5px;"><i class="fa fa-plus-circle"></i> {{Ajouter une action}}</a>
						</legend>
						<div id="div_surpresseurStop"></div>
					</fieldset>
				</form>
			</div>

			<div class="tab-pane" id="configureFiltreSable">
				<br/><br/>
				<form class="form-horizontal">
					<div class="form-group">
						<label class="col-sm-2 control-label">{{Temps de lavage du filtre à sable (min)}}</label>
						<div class="col-sm-2">
							<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="lavageDuree" placeholder="" >
								<option value="1">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
								<option value="6">6</option>
								<option value="7">7</option>
								<option value="8">8</option>
							</select>
						</div>
					</div>
					<br/>
					<div class="form-group">
						<label class="col-sm-2 control-label">{{Temps de rinçage du filtre à sable (min)}}</label>
						<div class="col-sm-2">
							<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="rincageDuree" placeholder="" >
								<option value="0">0</option>
								<option value="1">1</option>
								<option value="2">2</option>
								<option value="3">3</option>
								<option value="4">4</option>
								<option value="5">5</option>
								<option value="6">6</option>
								<option value="7">7</option>
								<option value="8">8</option>
							</select>
						</div>
					</div>
				</form>
			</div>

			<div class="tab-pane" id="configureHivernage">
				<br/><br/>
				<form class="form-horizontal">
					<fieldset>

						<div class="form-group">
							<label class="col-sm-2 control-label">{{Heure de lever du soleil}}</label>
							<div class="col-sm-9">
								<input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="lever_soleil" data-concat="1"/>
							</div>
							<div class="col-sm-1">
								<a class="btn btn-default btn-sm listCmdInfo"><i class="fa fa-list-alt"></i></a>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">{{Heure prédéfinie}}</label>
							<div class="col-sm-2">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="datePivotHivernage" placeholder="">
									<option value="01:00">01:00</option>
									<option value="02:00">02:00</option>
									<option value="03:00">03:00</option>
									<option value="04:00">04:00</option>
									<option value="05:00">05:00</option>
									<option value="06:00">06:00</option>
									<option value="07:00">07:00</option>
									<option value="08:00">08:00</option>
									<option value="09:00">09:00</option>
									<option value="10:00">10:00</option>
									<option value="11:00">11:00</option>
									<option value="12:00">12:00</option>
									<option value="13:00">13:00</option>
									<option value="14:00">14:00</option>
									<option value="15:00">15:00</option>
									<option value="16:00">16:00</option>
									<option value="17:00">17:00</option>
									<option value="18:00">18:00</option>
									<option value="19:00">19:00</option>
									<option value="20:00">20:00</option>
									<option value="21:00">21:00</option>
									<option value="22:00">22:00</option>
									<option value="23:00">23:00</option>
									<option value="24:00">24:00</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">{{Choix de l'heure pivot de filtration (2/3 - 1/3)}}</label>
							<div class="col-sm-2">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="choixHeureFiltrationHivernage" placeholder="" >
									<option value="1">{{Heure de lever du soleil}}</option>
									<option value="2">{{Heure prédéfinie}}</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">{{Temps de filtration minimum}}</label>
							<div class="col-sm-2">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="tempsDeFiltrationMinimum" placeholder="" >
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
								</select>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">{{Filtration 5mn toutes les 3 heures}}</label>
							<div class="col-sm-9">
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="filtration_5mn_3h" checked/>{{Actif}}</label>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">{{Filtration permanente si température extérieure inférieure à}}</label>
							<div class="col-sm-2">
								<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="temperatureSecurite" placeholder="" >
									<option value="-4">-4</option>
									<option value="-3">-3</option>
									<option value="-2">-2</option>
									<option value="-1">-1</option>
									<option value="0">0</option>
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
									<option value="4">4</option>
								</select>
							</div>
						</div>

					</fieldset>
				</form>
			</div>

			<div class="tab-pane" id="configureAsservissement">
				<br/><br/>
				<form class="form-horizontal">
					<fieldset>
						<legend>
							{{Arrêt total}}
							<a class="btn btn-default btn-xs pull-right addArretTotal" data-type="arretTotal" style="position: relative; top : 5px;"><i class="fa fa-plus-circle"></i> {{Ajouter arrêt total}}</a>
						</legend>
						<div id="div_arretTotal"></div>
					</fieldset>
				</form>
				<br/>
				<form class="form-horizontal">
					<fieldset>
						<legend>
							{{Marche forcée}}
							<a class="btn btn-default btn-xs pull-right addMarcheForcee" data-type="marcheForcee" style="position: relative; top : 5px;"><i class="fa fa-plus-circle"></i> {{Ajouter marche forcée}}</a>
						</legend>
						<div id="div_marcheForcee"></div>
					</fieldset>
				</form>
			</div>

			<div class="tab-pane" id="configureAdvanced">
				<br/><br/>
				<form class="form-horizontal">
					<fieldset>

						<div class='form-group'>
							<label class="col-sm-2 control-label">{{Délai max entre 2 relevés de température (min)}}</label>
							<div class="col-sm-2">
								<input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="maxTimeUpdateTemp" title="{{Délai maximum entre 2 relévés de température}}"/>
							</div>
						</div>

						<div class="form-group">
							<label class="col-sm-2 control-label">{{Afficher bouton reset calcul sur le widget}}</label>
							<div class="col-sm-9">
								<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="display_reset" checked/>{{Actif}}</label>
							</div>
						</div>

						<div class="cfgAsservissementExterne_hide enabled">
							<div class="form-group">
								<label class="col-sm-2 control-label">{{Désactiver marche forcée au début du cycle de filtration}}</label>
								<div class="col-sm-9">
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="disable_marcheForcee" checked/>{{Actif}}</label>
								</div>
							</div>
						</div>

						<div class='form-group'>
							<label class="col-sm-2 control-label">{{Cron de répétition de commande}}</label>
							<div class="col-sm-2">
								<input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="repeat_commande_cron" title="{{Cron de renvoi des commandes de filtration, surpresseur et traitement. Si vos équipements ne démarrent ou ne s'arrêtent pas correctement mettez en place cette vérification}}"/>
							</div>
							<div class="col-sm-1">
								<i class="fa fa-question-circle cursor floatright" id="bt_cronGenerator"></i>
							</div>
						</div>

					</fieldset>
				</form>
			</div>

		</div>

		<hr/>

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

<?php include_file('desktop', 'pool', 'js', 'pool');?>
<?php include_file('core', 'plugin.template', 'js');?>