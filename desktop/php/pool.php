<?php
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('pool');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
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
        <legend><i class="fa fa-cog"></i>  {{Gestion}}</legend>
        <div class="eqLogicThumbnailContainer">
            <div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
                <center>
                <i class="fa fa-plus-circle" style="font-size : 5em;color:#849ed2;"></i>
                </center>
                <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;;color:#849ed2"><center>{{Ajouter}}</center></span>
            </div>
            <div class="cursor eqLogicAction" data-action="gotoPluginConf" style="background-color : #ffffff; height : 120px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;">
                <center>
                <i class="fa fa-wrench" style="font-size : 5em;color:#767676;"></i>
                </center>
                <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;color:#849ed2"><center>{{Configuration}}</center></span>
            </div>
        </div>
        <legend><i class="icon loisir-beach4"></i>  {{Mes piscines}}</legend>
        <div class="eqLogicThumbnailContainer">
            <?php
                foreach ($eqLogics as $eqLogic) {
	                $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
	                echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
	                echo "<center>";
	                echo '<img src="' . $plugin->getPathImgIcon() . '" height="105" width="95" />';
	                echo "</center>";
	                echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
	                echo '</div>';
                }
            ?>
        </div>
    </div>

    <div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">

        <a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
        <a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
        <a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a>
        <a class="btn btn-default eqLogicAction pull-right" data-action="copy"><i class="fa fa-files-o"></i> {{Dupliquer}}</a>

        <hr/>

        <ul class="nav nav-tabs" role="tablist">

            <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>

            <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab">{{Equipement}}</a></li>
            <li role="presentation"><a href="#configureFiltration" data-toggle="tab">{{Filtration}}</a></li>
            <li role="presentation" class="cfgChauffage enabled"><a href="#configureChauffage" data-toggle="tab">{{Chauffage}}</a></li>
            <li role="presentation" class="cfgTraitement enabled"><a href="#configureTraitement" data-toggle="tab">{{Traitement}}</a></li>
            <li role="presentation" class="cfgSurpresseur enabled"><a href="#configureSurpresseur" data-toggle="tab">{{Surpresseur}}</a></li>
            <li role="presentation" class="cfgFiltreSable enabled"><a href="#configureFiltreSable" data-toggle="tab">{{Filtre à sable}}</a></li>
            <li role="presentation" class="cfgHivernage enabled"><a href="#configureHivernage" data-toggle="tab">{{Hivernage}}</a></li>
            <li role="presentation" class="cfgAsservissementExterne enabled"><a href="#configureAsservissement" data-toggle="tab">{{Asservissement externe}}</a></li>
            <li role="presentation"><a href="#configureAdvanced" data-toggle="tab">{{Configuration avancée}}</a></li>

        </ul>

        <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">

            <div role="tabpanel" class="tab-pane active" id="eqlogictab">
                <br/><br/>

                <div class="row">

                    <div class="col-sm-6">
                        <form class="form-horizontal">
                            <fieldset>
                                <!-- id / name -->
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">{{Nom de l'équipement piscine}}</label>
                                    <div class="col-sm-4">
                                        <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                                        <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement piscine}}"/>
                                    </div>
                                </div>
                                <!-- object_id -->
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
                                <!-- category -->
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">{{Catégorie}}</label>
                                    <div class="col-sm-8">
                                        <?php
                                            foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                                                echo '<label class="checkbox-inline">';
                                                echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                                                echo '</label>';
                                            }
                                        ?>
                                    </div>
                                </div>
                                <!-- isEnable / isVisible -->
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">{{Etat}}</label>
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
                            <!-- Chauffage -->
                            <div class="form-group" style="display: none"> <!-- -->
                                <label class="col-sm-4 control-label">{{Chauffage}}</label>
                                <div class="col-sm-4">
                                    <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgChauffage" placeholder="" >
                                        <option value="disabled">{{Inactif}}</option>
                                        <option value="enabled">{{Actif}}</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Traitement -->
                            <div class="form-group">
                                <label class="col-sm-4 control-label">{{Traitement}}</label>
                                <div class="col-sm-4">
                                    <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgTraitement" placeholder="" >
                                        <option value="disabled">{{Inactif}}</option>
                                        <option value="enabled">{{Actif}}</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Surpresseur -->
                            <div class="form-group">
                                <label class="col-sm-4 control-label">{{Surpresseur}}</label>
                                <div class="col-sm-4">
                                    <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgSurpresseur" placeholder="" >
                                        <option value="disabled">{{Inactif}}</option>
                                        <option value="enabled">{{Actif}}</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Filtre à sable -->
                            <div class="form-group">
                                <label class="col-sm-4 control-label">{{Filtre à sable}}</label>
                                <div class="col-sm-4">
                                    <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgFiltreSable" placeholder="" >
                                        <option value="disabled">{{Inactif}}</option>
                                        <option value="enabled">{{Actif}}</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Hivernage -->
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
                            <!-- Asservissement externe -->
                            <div class="form-group">
                                <label class="col-sm-4 control-label">{{Asservissement externe}}</label>
                                <div class="col-sm-4">
                                    <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="cfgAsservissementExterne" placeholder="" >
                                        <option value="disabled">{{Inactif}}</option>
                                        <option value="enabled">{{Actif}}</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>

                </div>

                <form class="form-horizontal">
                    <fieldset>
                        <!-- alert-info -->
                        <div class="alert alert-info">
                            {{Veuillez ajouter votre sonde de température}}
                        </div>
                        <!-- temperature_water -->
                        <div class="form-group">
                            <label class="col-sm-2 control-label">{{Température de l'eau}}</label>
                            <div class="col-sm-9">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="temperature_water" data-concat="1"/>
                            </div>
                            <div class="col-sm-1">
                                <a class="btn btn-default btn-sm listCmdInfo"><i class="fa fa-list-alt"></i></a>
                            </div>
                        </div>
                        <!-- temperature_water_min / temperature_water_max -->
                        <div class="form-group">
                            <label class="col-sm-2 control-label">{{Borne de température inférieure}}</label>
                            <div class="col-sm-2">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="temperature_water_min" />
                            </div>
                            <label class="col-sm-2 control-label">{{Borne de température supérieure}}</label>
                            <div class="col-sm-2">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="temperature_water_max" />
                            </div>
                        </div>
                        <!-- temperature_outdoor -->
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

            </div>

            <div role="tabpanel" class="tab-pane" id="configureFiltration">
                <br/>

                <form class="form-horizontal">
                    <legend>
                        {{Paramètres de filtration}}
                    </legend>

                    <div class="row">
                        <div class="col-sm-6">
                            <form class="form-horizontal">
                                <!-- methodeCalcul -->
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">{{Choix méthode de calcul}}</label>
                                    <div class="col-sm-4">
                                        <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="methodeCalcul" placeholder="">
                                            <option value="1">{{Courbe}}</option>
                                            <option value="2">{{Temp / 2}}</option>
                                        </select>
                                    </div>
                                </div>
                                <!-- coefficientAjustement -->
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">{{Ajustement du temps de filtration}}</label>
                                    <div class="col-sm-4">
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
                                <!-- disable_marcheForcee -->
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">{{Désactiver marche forcée au début du cycle de filtration}}</label>
                                    <div class="col-sm-4">
                                        <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="disable_marcheForcee" checked/>{{Actif}}</label>
                                    </div>
                                </div>
                                <!-- Activate HC/HP -->
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">{{Activer HC/HP}}</label>
                                    <div class="col-sm-4">
                                        <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="Activate_HCHP" checked/>{{Actif}}</label>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-sm-6">
                            <form class="form-horizontal">
                                <div class="horairePivot">
                                    <!-- datePivot -->
                                    <div class="form-group">
                                    <label class="col-sm-4 control-label">{{Horaire pivot de filtration}}</label>
                                    <div class="col-sm-4">
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
                                    <!-- pausePivot -->
                                    <div class="form-group">
                                    <label class="col-sm-4 control-label">{{Temps de coupure (segmentation de la filtration)}}</label>
                                    <div class="col-sm-4">
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
                                    <!-- distributionDatePivot -->
                                    <div class="form-group">
                                    <label class="col-sm-4 control-label">{{Répartition du temps de filtration autour de l'horaire pivot}}</label>
                                    <div class="col-sm-4">
                                        <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="distributionDatePivot" placeholder="">
                                            <option value="1">1/2 - 1/2</option>
                                            <option value="2">1/3 - 2/3</option>
                                        </select>
                                    </div>
                                </div>
                                </div>
                                <div class="heureCreuse">
                                    <!-- Debut HC Journée -->
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">{{Début des heures creuses en journée}}</label>
                                        <div class="col-sm-4">
                                            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="DebutHCJournee" placeholder="">
                                                <option value="00:00">00:00</option>
                                                <option value="00:30">00:30</option>
                                                <option value="01:00">01:00</option>
                                                <option value="01:30">01:30</option>
                                                <option value="02:00">02:00</option>
                                                <option value="02:30">02:30</option>
                                                <option value="03:00">03:00</option>
                                                <option value="03:30">03:30</option>
                                                <option value="04:00">04:00</option>
                                                <option value="04:30">04:30</option>
                                                <option value="05:00">05:00</option>
                                                <option value="05:30">05:30</option>
                                                <option value="06:00">06:00</option>
                                                <option value="06:30">06:30</option>
                                                <option value="07:00">07:00</option>
                                                <option value="07:30">07:30</option>
                                                <option value="08:00">08:00</option>
                                                <option value="08:30">08:30</option>
                                                <option value="09:00">09:00</option>
                                                <option value="09:30">09:30</option>
                                                <option value="10:00">10:00</option>
                                                <option value="10:30">10:30</option>
                                                <option value="11:00">11:00</option>
                                                <option value="11:30">11:30</option>
                                                <option value="12:00">12:00</option>
                                                <option value="12:30">12:30</option>
                                                <option value="13:00">13:00</option>
                                                <option value="13:30">13:30</option>
                                                <option value="14:00">14:00</option>
                                                <option value="14:30">14:30</option>
                                                <option value="15:00">15:00</option>
                                                <option value="15:30">15:30</option>
                                                <option value="16:00">16:00</option>
                                                <option value="16:30">16:30</option>
                                                <option value="17:00">17:00</option>
                                                <option value="17:30">17:30</option>
                                                <option value="18:00">18:00</option>
                                                <option value="18:30">18:30</option>
                                                <option value="19:00">19:00</option>
                                                <option value="19:30">19:30</option>
                                                <option value="20:00">20:00</option>
                                                <option value="20:30">20:30</option>
                                                <option value="21:00">21:00</option>
                                                <option value="21:30">21:30</option>
                                                <option value="22:00">22:00</option>
                                                <option value="22:30">22:30</option>
                                                <option value="23:00">23:00</option>
                                                <option value="23:30">23:30</option>
                                                <option value="24:00">24:00</option>
                                                <option value="24:30">24:30</option>
                                            </select>
                                        </div>
                                    </div>
                                    <!-- fin HC Journée -->
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">{{Fin des heures creuses en journée}}</label>
                                        <div class="col-sm-4">
                                            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="FinHCJournee" placeholder="">
                                                <option value="00:00">00:00</option>
                                                <option value="00:30">00:30</option>
                                                <option value="01:00">01:00</option>
                                                <option value="01:30">01:30</option>
                                                <option value="02:00">02:00</option>
                                                <option value="02:30">02:30</option>
                                                <option value="03:00">03:00</option>
                                                <option value="03:30">03:30</option>
                                                <option value="04:00">04:00</option>
                                                <option value="04:30">04:30</option>
                                                <option value="05:00">05:00</option>
                                                <option value="05:30">05:30</option>
                                                <option value="06:00">06:00</option>
                                                <option value="06:30">06:30</option>
                                                <option value="07:00">07:00</option>
                                                <option value="07:30">07:30</option>
                                                <option value="08:00">08:00</option>
                                                <option value="08:30">08:30</option>
                                                <option value="09:00">09:00</option>
                                                <option value="09:30">09:30</option>
                                                <option value="10:00">10:00</option>
                                                <option value="10:30">10:30</option>
                                                <option value="11:00">11:00</option>
                                                <option value="11:30">11:30</option>
                                                <option value="12:00">12:00</option>
                                                <option value="12:30">12:30</option>
                                                <option value="13:00">13:00</option>
                                                <option value="13:30">13:30</option>
                                                <option value="14:00">14:00</option>
                                                <option value="14:30">14:30</option>
                                                <option value="15:00">15:00</option>
                                                <option value="15:30">15:30</option>
                                                <option value="16:00">16:00</option>
                                                <option value="16:30">16:30</option>
                                                <option value="17:00">17:00</option>
                                                <option value="17:30">17:30</option>
                                                <option value="18:00">18:00</option>
                                                <option value="18:30">18:30</option>
                                                <option value="19:00">19:00</option>
                                                <option value="19:30">19:30</option>
                                                <option value="20:00">20:00</option>
                                                <option value="20:30">20:30</option>
                                                <option value="21:00">21:00</option>
                                                <option value="21:30">21:30</option>
                                                <option value="22:00">22:00</option>
                                                <option value="22:30">22:30</option>
                                                <option value="23:00">23:00</option>
                                                <option value="23:30">23:30</option>
                                                <option value="24:00">24:00</option>
                                                <option value="24:30">24:30</option>
                                            </select>
                                        </div>
                                    </div>
                                    <!-- debut HC Nuit -->
                                    <div class="form-group">
                                        <label class="col-sm-4 control-label">{{Début des heures creuses nuit}}</label>
                                        <div class="col-sm-4">
                                            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="DebutHCNuit" placeholder="01:00">
                                                <option value="00:00">00:00</option>
                                                <option value="00:30">00:30</option>
                                                <option value="01:00">01:00</option>
                                                <option value="01:30">01:30</option>
                                                <option value="02:00">02:00</option>
                                                <option value="02:30">02:30</option>
                                                <option value="03:00">03:00</option>
                                                <option value="03:30">03:30</option>
                                                <option value="04:00">04:00</option>
                                                <option value="04:30">04:30</option>
                                                <option value="05:00">05:00</option>
                                                <option value="05:30">05:30</option>
                                                <option value="06:00">06:00</option>
                                                <option value="06:30">06:30</option>
                                                <option value="07:00">07:00</option>
                                                <option value="07:30">07:30</option>
                                                <option value="08:00">08:00</option>
                                                <option value="08:30">08:30</option>
                                                <option value="09:00">09:00</option>
                                                <option value="09:30">09:30</option>
                                                <option value="10:00">10:00</option>
                                                <option value="10:30">10:30</option>
                                                <option value="11:00">11:00</option>
                                                <option value="11:30">11:30</option>
                                                <option value="12:00">12:00</option>
                                                <option value="12:30">12:30</option>
                                                <option value="13:00">13:00</option>
                                                <option value="13:30">13:30</option>
                                                <option value="14:00">14:00</option>
                                                <option value="14:30">14:30</option>
                                                <option value="15:00">15:00</option>
                                                <option value="15:30">15:30</option>
                                                <option value="16:00">16:00</option>
                                                <option value="16:30">16:30</option>
                                                <option value="17:00">17:00</option>
                                                <option value="17:30">17:30</option>
                                                <option value="18:00">18:00</option>
                                                <option value="18:30">18:30</option>
                                                <option value="19:00">19:00</option>
                                                <option value="19:30">19:30</option>
                                                <option value="20:00">20:00</option>
                                                <option value="20:30">20:30</option>
                                                <option value="21:00">21:00</option>
                                                <option value="21:30">21:30</option>
                                                <option value="22:00">22:00</option>
                                                <option value="22:30">22:30</option>
                                                <option value="23:00">23:00</option>
                                                <option value="23:30">23:30</option>
                                                <option value="24:00">24:00</option>
                                                <option value="24:30">24:30</option>
                                            </select>
                                        </div>
                                    </div>
                                    <!-- fin HC Nuit -->
                                    <div class="form-group">
                                        <div class="col-sm-8 alert alert-info">
                                            {{Pas de réglage de fin des heures creuses nuit.}}
                                            <br/>
                                            {{Assomption 8h creuses par jour.}}
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </form>

                <!-- alert-info -->
                <div class="alert alert-info">
                    {{Utilisez le bouton [Reset] pour appliquer immédiatement les nouveaux paramètres de filtration}}
                </div>

                <br/>

                <!-- filtrationOn -->
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
                <!-- filtrationStop -->
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
                <!-- asservissement -->
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

            <div role="tabpanel" class="tab-pane" id="configureChauffage">
                <br/>
                <!-- chauffageOn -->
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
                <!-- chauffageStop -->
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

            <div role="tabpanel" class="tab-pane" id="configureTraitement">
                <br/>
                <!-- traitementOn -->
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
                <!-- traitementStop -->
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

            <div role="tabpanel" class="tab-pane" id="configureSurpresseur">
                <br/>
                <!-- surpresseurDuree -->
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
                <!-- surpresseurOn -->
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
                <!-- surpresseurStop -->
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

            <div role="tabpanel" class="tab-pane" id="configureFiltreSable">
                <br/>
                <form class="form-horizontal">
                    <!-- lavageDuree -->
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
                    <!-- rincageDuree -->
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

            <div role="tabpanel" class="tab-pane" id="configureHivernage">
                <br/>
                <form class="form-horizontal">
                    <!-- lever_soleil -->
                    <div class="form-group">
                        <fieldset>
                            <label class="col-sm-2 control-label">{{Heure de lever du soleil}}</label>
                            <div class="col-sm-9">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="lever_soleil" data-concat="1"/>
                            </div>
                            <div class="col-sm-1">
                                <a class="btn btn-default btn-sm listCmdInfo"><i class="fa fa-list-alt"></i></a>
                            </div>
                        </fieldset>
                    </div>
                    <br/>
                    <!-- datePivotHivernage -->
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
                    <!-- choixHeureFiltrationHivernage -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label">{{Choix de l'heure pivot de filtration (2/3 - 1/3)}}</label>
                        <div class="col-sm-2">
                            <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="choixHeureFiltrationHivernage" placeholder="" >
                                <option value="1">{{Heure de lever du soleil}}</option>
                                <option value="2">{{Heure prédéfinie}}</option>
                            </select>
                        </div>
                    </div>
                    <!-- tempsDeFiltrationMinimum -->
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
                    <!-- filtration_5mn_3h -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label">{{Filtration 5mn toutes les 3 heures}}</label>
                        <div class="col-sm-9">
                            <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="filtration_5mn_3h" checked/>{{Actif}}</label>
                        </div>
                    </div>
                    <!-- temperatureSecurite -->
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
                    <!-- traitement_hivernage -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label">{{Activer le traitement pendant l'hivernage}}</label>
                        <div class="col-sm-9">
                            <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="traitement_hivernage" checked/>{{Actif}}</label>
                        </div>
                    </div>
                </form>
            </div>

            <div role="tabpanel" class="tab-pane" id="configureAsservissement">
                <br/>
                <!-- arretTotal -->
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
                <!-- marcheForcee -->
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

            <div role="tabpanel" class="tab-pane" id="configureAdvanced">
                <br/>
                <form class="form-horizontal">
                    <!-- sondeLocalTechnique -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label">{{Sonde de température dans local technique}}</label>
                        <div class="col-sm-9">
                            <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="sondeLocalTechnique" checked/>{{Actif}}</label>
                        </div>
                    </div>
                    <!-- sondeLocalTechniquePause -->
                    <div class="form-group">
                        <div class="sondeLocalTechnique enabled">
                            <label class="col-sm-2 control-label">{{Pause avant relevé de température}}</label>
                            <div class="col-sm-2">
                                <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="sondeLocalTechniquePause" placeholder="" >
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">5</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                    <option value="11">11</option>
                                    <option value="12">12</option>
                                    <option value="13">13</option>
                                    <option value="14">14</option>
                                    <option value="15">15</option>
                                    <option value="16">15</option>
                                    <option value="17">17</option>
                                    <option value="18">18</option>
                                    <option value="19">19</option>
                                    <option value="20">20</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <!-- maxTimeUpdateTemp -->
                    <div class='form-group'>
                        <fieldset>
                            <label class="col-sm-2 control-label">{{Délai max entre 2 relevés de température (min)}}</label>
                            <div class="col-sm-2">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="maxTimeUpdateTemp" title="{{Délai maximum entre 2 relévés de température}}"/>
                            </div>
                        </fieldset>
                    </div>
                    <!-- display_reset -->
                    <div class="form-group">
                        <label class="col-sm-2 control-label">{{Afficher bouton reset calcul sur le widget}}</label>
                        <div class="col-sm-9">
                            <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="display_reset" checked/>{{Actif}}</label>
                        </div>
                    </div>
                    <!-- repeat_commande_cron -->
                    <div class='form-group'>
                        <fieldset>
                            <label class="col-sm-2 control-label">{{Cron de répétition de commande}}</label>
                            <div class="col-sm-2">
                                <input type="text" class="eqLogicAttr form-control tooltips" data-l1key="configuration" data-l2key="repeat_commande_cron" title="{{Cron de renvoi des commandes de filtration, surpresseur et traitement. Si vos équipements ne démarrent ou ne s'arrêtent pas correctement mettez en place cette vérification}}"/>
                            </div>
                            <div class="col-sm-1">
                                <i class="fa fa-question-circle cursor floatright" id="bt_cronGenerator"></i>
                            </div>
                        </fieldset>
                    </div>
                </form>
            </div>

        </div>
    </div>

</div>

<?php include_file('desktop', 'pool', 'js', 'pool');?>
<?php include_file('core', 'plugin.template', 'js');?>