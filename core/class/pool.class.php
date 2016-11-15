<?php

/* This file is part of Jeedom.
    *
    * Jeedom is free software: you can redistribute it and/or modify
    * it under the terms of the GNU General Public License as published by
    * the Free Software Foundation, either version 3 of the License, or
    * (at your option) any later version.
    *
    *
    * Jeedom is distributed in the hope that it will be useful,
    * but WITHOUT ANY WARRANTY; without even the implied warranty of
    * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
    * GNU General Public License for more details.
    *
    * You should have received a copy of the GNU General Public License
    * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
    *
*/

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class pool extends eqLogic
{

    /* ************************Methode static*************************** */

    // Test Master

    public static function asservissement($_option)
    {
        // log::add('pool', 'debug', 'asservissement() begin');

        $pool = pool::byId($_option['pool_id']);
        if (is_object($pool)) {
            if ($pool->getIsEnable() == 1) {

                // log::add('pool', 'debug', $pool->getHumanName());

                $asservissements = $pool->getConfiguration('asservissement');
                foreach ($asservissements as $asservissement) {
                    if ('#' . $_option['event_id'] . '#' == $asservissement['cmd']) {
                        if ($_option['value'] == 0) {
                            $pool->asservissementOff($asservissement);
                        }
                        if ($_option['value'] == 1) {
                            $pool->asservissementOn($_option['event_id']);
                        }
                    }
                }
            }
        }

        // log::add('pool', 'debug', 'asservissement() end');
    }

    public static function arretTotal($_option)
    {
        // log::add('pool', 'debug', 'arretTotal() begin');

        $pool = pool::byId($_option['pool_id']);
        if (is_object($pool)) {
            if ($pool->getIsEnable() == 1) {

                // log::add('pool', 'debug', $pool->getHumanName());

                if ($pool->getConfiguration('cfgAsservissementExterne', 'enabled') == 'enabled') {

                    $arretTotals = $pool->getConfiguration('arretTotal');
                    foreach ($arretTotals as $arretTotal) {
                        if ('#' . $_option['event_id'] . '#' == $arretTotal['cmd']) {
                            if ($_option['value'] == 0) {
                                $pool->arretTotalOff($arretTotal);
                            }
                            if ($_option['value'] == 1) {
                                $pool->arretTotalOn($_option['event_id']);
                            }
                        }
                    }
                }
            }
        }

        // log::add('pool', 'debug', 'arretTotal() end');
    }

    public static function marcheForcee($_option)
    {
        // log::add('pool', 'debug', 'marcheForcee() begin');

        $pool = pool::byId($_option['pool_id']);
        if (is_object($pool)) {
            if ($pool->getIsEnable() == 1) {

                // log::add('pool', 'debug', $pool->getHumanName());

                if ($pool->getConfiguration('cfgAsservissementExterne', 'enabled') == 'enabled') {

                    $marcheForcees = $pool->getConfiguration('marcheForcee');
                    foreach ($marcheForcees as $marcheForcee) {
                        if ('#' . $_option['event_id'] . '#' == $marcheForcee['cmd']) {
                            if ($_option['value'] == 0) {
                                $pool->marcheForceeOff($marcheForcee);
                            }
                            if ($_option['value'] == 1) {
                                $pool->marcheForceeOn($_option['event_id']);
                            }
                        }
                    }
                }
            }
        }

        // log::add('pool', 'debug', 'marcheForcee() end');
    }

    public static function pull()
    {
        // log::add('pool', 'debug', 'pull() begin');

        foreach (pool::byType('pool') as $pool) {

            if ($pool->getIsEnable() == 1) {

                // log::add('pool', 'debug', $pool->getHumanName());

                if ($pool->getCmd(null, 'filtrationSurpresseur')->execCmd() == 1) {
                    $timeFin = $pool->getCmd(null, 'filtrationTempsRestant')->execCmd();
                    $timeRestant = $timeFin - time();

                    if ($timeRestant > 0) {
                        $pool->getCmd(null, 'surpresseurStatus')->event(date('i:s', $timeRestant));
                    } else {
                        $pool->executePoolStop();
                    }
                }

                if ($pool->getCmd(null, 'filtrationLavageEtat')->execCmd() == 2) {
                    $timeFin = $pool->getCmd(null, 'filtrationTempsRestant')->execCmd();
                    $timeRestant = $timeFin - time();

                    if ($timeRestant > 0) {
                        $pool->getCmd(null, 'filtreSableLavageStatus')->event(__('Lavage', __FILE__) . ' : ' . date('i:s', $timeRestant));
                    } else {
                        $pool->executeFiltreSableLavageOn();
                    }
                }
                if ($pool->getCmd(null, 'filtrationLavageEtat')->execCmd() == 4) {
                    $timeFin = $pool->getCmd(null, 'filtrationTempsRestant')->execCmd();
                    $timeRestant = $timeFin - time();

                    if ($timeRestant > 0) {
                        $pool->getCmd(null, 'filtreSableLavageStatus')->event(__('Rinçage', __FILE__) . ' : ' . date('i:s', $timeRestant));
                    } else {
                        $pool->executeFiltreSableLavageOn();
                    }
                }
            }
        }

        // log::add('pool', 'debug', 'pull() end');
    }

    public static function deamon_info()
    {
        $return = array();
        $return['log'] = '';
        $return['state'] = 'nok';
        $cron = cron::byClassAndFunction('pool', 'pull');
        if (is_object($cron) && $cron->running()) {
            $return['state'] = 'ok';
        }
        $return['launchable'] = 'ok';
        return $return;
    }

    public static function deamon_start($_debug = false)
    {
        self::deamon_stop();
        $deamon_info = self::deamon_info();
        if ($deamon_info['launchable'] != 'ok') {
            throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
        }
        $cron = cron::byClassAndFunction('pool', 'pull');
        if (!is_object($cron)) {
            throw new Exception(__('Tache cron introuvable', __FILE__));
        }
        $cron->run();
    }

    public static function deamon_stop()
    {
        $cron = cron::byClassAndFunction('pool', 'pull');
        if (!is_object($cron)) {
            throw new Exception(__('Tache cron introuvable', __FILE__));
        }
        $cron->halt();
    }

    public static function cron()
    {
        // log::add('pool', 'debug', 'cron() begin');

        foreach (pool::byType('pool') as $pool) {
            if ($pool->getIsEnable() == 1) {

                // log::add('pool', 'debug', $pool->getHumanName());

                ///////////////////////////////////////////////////////////////////////////////////

                $temperature_water = $pool->getTemperatureWater();
                $temperature_outdoor = $pool->getTemperatureOutdoor();
                $lever_soleil = $pool->getLeverSoleil();

                ///////////////////////////////////////////////////////////////////////////////////

                if ($pool->getConfiguration('repeat_commande_cron') != '') {
                    try {
                        $cron = $pool->getConfiguration('repeat_commande_cron'); // '*/5 * * * *'
                        // log::add('pool', 'debug', '$cron:' . $cron);

                        $c = new Cron\CronExpression($cron, new Cron\FieldFactory);
                        if ($c->isDue()) {

                            // log::add('pool', 'debug', $pool->getHumanName() . 'cron >> refresh');

                            $pool->refreshFiltration();
                            $pool->refreshSurpresseur();
                            $pool->refreshTraitement();
                            $pool->refreshChauffage();
                        }
                    } catch (Exception $e) {
                        log::add('pool', 'error', $pool->getHumanName() . ' : ' . $e->getMessage());
                    }
                }


                ///////////////////////////////////////////////////////////////////////////////////

                if ($pool->getHivernage()) {
                    $pool->calculateStatusFiltrationHivernage($temperature_water, $temperature_outdoor, $lever_soleil);
                } else {
                    $pool->calculateStatusFiltration($temperature_water);
                }

                ///////////////////////////////////////////////////////////////////////////////////

                $pool->activatingDevices();

                ///////////////////////////////////////////////////////////////////////////////////

            }
        }

        // log::add('pool', 'debug', 'cron() end');
    }

    /* **********************Methode d'instance************************* */

    public function stopDaemon()
    {
        // log::add('pool', 'debug', 'stopDaemon() begin');

        foreach (pool::byType('pool') as $pool) {
            // log::add('pool', 'debug', 'update :' . $pool->getHumanName());
            $pool->save();
        }

        $cron = cron::byClassAndFunction('pool', 'pull');
        $cron->stop();

        // log::add('pool', 'debug', 'stopDaemon() end');
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function activatingDevices()
    {
        // log::add('pool', 'debug', $this->getHumanName() . 'activatingDevices() begin');

        // log::add('pool', 'debug', $this->getHumanName() . 'FiltrationLavage=' . $this->getCmd(null, 'filtrationLavage')->execCmd());
        // log::add('pool', 'debug', $this->getHumanName() . 'FiltrationLavageEtat=' . $this->getCmd(null, 'filtrationLavageEtat')->execCmd());
        // log::add('pool', 'debug', $this->getHumanName() . 'FiltrationTemperature=' . $this->getCmd(null, 'filtrationTemperature')->execCmd());
        // log::add('pool', 'debug', $this->getHumanName() . 'FiltrationSolaire=' . $this->getCmd(null, 'filtrationSolaire')->execCmd());
        // log::add('pool', 'debug', $this->getHumanName() . 'FiltrationHivernage=' . $this->getCmd(null, 'filtrationHivernage')->execCmd());
        // log::add('pool', 'debug', $this->getHumanName() . 'FiltrationSurpresseur=' . $this->getCmd(null, 'filtrationSurpresseur')->execCmd());
        // log::add('pool', 'debug', $this->getHumanName() . 'ArretTotal=' . $this->getCmd(null, 'arretTotal')->execCmd());
        // log::add('pool', 'debug', $this->getHumanName() . 'MarcheForcee=' . $this->getCmd(null, 'marcheForcee')->execCmd());

        // Verifie si la configuration de l'asservissement externe est correcte
        if ($this->getConfiguration('cfgAsservissementExterne', 'enabled') == 'enabled') {

            // log::add('pool', 'debug', $this->getHumanName() . 'cfgAsservissementExterne == enabled');

            if ($this->getCmd(null, 'arretTotal')->execCmd() == 1) {
                $bFound = false;
                $arretTotals = $this->getConfiguration('arretTotal');
                foreach ($arretTotals as $arretTotal) {
                    $bFound = true;
                }
                // Pas de commande on remet la cmd 'arretTotal' à zero
                if ($bFound == false) {
                    // log::add('pool', 'debug', $this->getHumanName() . '$this->getCmd(null, \'arretTotal\')->event(0)');
                    $this->getCmd(null, 'arretTotal')->event(0);
                }
            }

            if ($this->getCmd(null, 'marcheForcee')->execCmd() == 1) {
                $bFound = false;
                $marcheForcees = $this->getConfiguration('marcheForcee');
                foreach ($marcheForcees as $marcheForcee) {
                    $bFound = true;
                }
                // Pas de commande on remet la cmd 'marcheForcee' à zero
                if ($bFound == false) {
                    // log::add('pool', 'debug', $this->getHumanName() . '$this->getCmd(null, \'marcheForcee\')->event(0);');
                    $this->getCmd(null, 'marcheForcee')->event(0);
                }
            }

        }

        if ($this->getCmd(null, 'arretTotal')->execCmd() == 0) {

            if ($this->getCmd(null, 'marcheForcee')->execCmd() == 1) {

                // Marche forcée, filtration desactivée
                $status = __('Actif', __FILE__);
                $status = $this->getStatusHivernage($status);
                $this->getCmd(null, 'asservissementStatus')->event($status);

            } else {

                // Mode Auto, filtration pendant les plages programmées
                $status = __('Auto', __FILE__);
                $status = $this->getStatusHivernage($status);
                $this->getCmd(null, 'asservissementStatus')->event($status);
            }

        } else {

            // Arret total, prioritaire > (tout est stoppé)
            $status = __('Inactif', __FILE__);
            $status = $this->getStatusHivernage($status);
            $this->getCmd(null, 'asservissementStatus')->event($status);

        }

        if ($this->getCmd(null, 'arretTotal')->execCmd() == 0) {
            if ($this->getCmd(null, 'filtrationLavage')->execCmd() == 0) {
                if ($this->getCmd(null, 'filtrationTemperature')->execCmd() == 1
                    || $this->getCmd(null, 'filtrationSolaire')->execCmd() == 1
                    || $this->getCmd(null, 'filtrationHivernage')->execCmd() == 1
                    || $this->getCmd(null, 'filtrationSurpresseur')->execCmd() == 1
                    || $this->getCmd(null, 'marcheForcee')->execCmd() == 1
                ) {
                    $this->filtrationOn();

                    if ($this->getCmd(null, 'filtrationTemperature')->execCmd() == 1
                        || $this->getCmd(null, 'marcheForcee')->execCmd() == 1
                    ) {
                        sleep(2);
                        $this->traitementOn();
                    }

                    if ($this->getCmd(null, 'filtrationSurpresseur')->execCmd() == 1) {
                        sleep(2);
                        $this->surpresseurOn();
                    } else {
                        $this->surpresseurStop();
                    }
                } else {
                    if ($this->getCmd(null, 'traitement')->execCmd() == '1') {
                        $this->traitementStop();
                        sleep(2);
                    }

                    if ($this->getCmd(null, 'surpresseur')->execCmd() == '1') {
                        $this->surpresseurStop();
                        sleep(2);
                    }

                    $this->filtrationStop();
                }
            }

            if ($this->getCmd(null, 'filtrationLavage')->execCmd() == 1) {
                $this->traitementStop();
                $this->surpresseurStop();
                $this->filtrationStop();
            }

            if ($this->getCmd(null, 'filtrationLavage')->execCmd() == 2) {
                $this->traitementStop();
                $this->surpresseurStop();
                $this->filtrationOn();
            }
        } else {
            $this->traitementStop();
            $this->surpresseurStop();
            $this->filtrationStop();
            $this->chauffageStop();
        }

        // log::add('pool', 'debug', $this->getHumanName() . 'activatingDevices() end');
    }

    public function processingTime($dureeHeures)
    {
        // Arrondi en minutes
        $dureeHeures = floor($dureeHeures * 60) / 60;

        // La durée ne peut pas etre superieure à 24 H
        $dureeHeures = min($dureeHeures, 24.00);

        // Conversion en secondes pour les calculs
        $filtrationSecondes = $dureeHeures * 3600.0;

        // conversion en hh:mm pour l'affichage
        $hh = floor($dureeHeures);
        $mm = floor(($dureeHeures * 60) - ($hh * 60));

        $filtrationTime = str_pad($hh, 2, 0, STR_PAD_LEFT) . ':' . str_pad($mm, 2, 0, STR_PAD_LEFT);

        return array($filtrationSecondes, $filtrationTime);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function getCoefficientAjustement()
    {
        switch ($this->getConfiguration('coefficientAjustement', '10')) {
            case '3':
                $coeff = 0.3;
                break;
            case '3.5':
                $coeff = 0.35;
                break;
            case '4':
                $coeff = 0.4;
                break;
            case '4.5':
                $coeff = 0.45;
                break;
            case '5':
                $coeff = 0.5;
                break;
            case '5.5':
                $coeff = 0.55;
                break;
            case '6':
                $coeff = 0.6;
                break;
            case '6.5':
                $coeff = 0.65;
                break;
            case '7':
                $coeff = 0.7;
                break;
            case '7.5':
                $coeff = 0.75;
                break;
            case '8':
                $coeff = 0.8;
                break;
            case '8.5':
                $coeff = 0.85;
                break;
            case '9':
                $coeff = 0.9;
                break;
            case '9.5':
                $coeff = 0.95;
                break;
            case '10':
                $coeff = 1.0;
                break;
            case '10.5':
                $coeff = 1.05;
                break;
            case '11':
                $coeff = 1.1;
                break;
            case '11.5':
                $coeff = 1.15;
                break;
            case '12':
                $coeff = 1.2;
                break;
            case '12.5':
                $coeff = 1.25;
                break;
            case '13':
                $coeff = 1.3;
                break;
            case '13.5':
                $coeff = 1.35;
                break;
            case '14':
                $coeff = 1.4;
                break;
            case '14.5':
                $coeff = 1.45;
                break;
            case '15':
                $coeff = 1.5;
                break;
            case '15.5':
                $coeff = 1.55;
                break;
            case '16':
                $coeff = 1.6;
                break;
            case '16.5':
                $coeff = 1.65;
                break;
            case '17':
                $coeff = 1.7;
                break;
        }

        return $coeff;
    }

    public function calculateTimeFiltrationWithCurve($temperature_water)
    {
        // Pour assurer un temps minimum de filtration la temperature de calcul est forcée a 10°C
        $temperature = max($temperature_water, 10.0);

        // Calcul suivant l'équation
        // y = (0.00335 * temperature^3) + (-0.14953 * temperature^2) + (2.43489 * temperature) -10.72859

        $a = 0.00335;
        $b = -0.14953;
        $c = 2.43489;
        $d = -10.72859;

        // Coefficient d'ajustement de la courbe (suivant config)
        $coeff = $this->getCoefficientAjustement();

        // log::add('pool', 'debug', $this->getHumanName() . 'coefficientAjustement=[' . $coeff . ']');

        $a *= $coeff;
        $b *= $coeff;
        $c *= $coeff;
        $d *= $coeff;

        $dureeHeures = ($a * pow($temperature, 3)) + ($b * pow($temperature, 2)) + ($c * $temperature) + $d;
        // log::add('pool', 'debug', $this->getHumanName() . '$dureeHeures=[' . $dureeHeures . ']');

        return $dureeHeures;
    }

    public function calculateTimeFiltrationWithTemperatureReducedByHalf($temperature_water)
    {
        // Calcul simplifié
        $dureeHeures = $temperature_water / 2.0;

        // Coefficient d'ajustement de la courbe (suivant config)
        $coeff = $this->getCoefficientAjustement();

        // log::add('pool', 'debug', $this->getHumanName() . 'coefficientAjustement=[' . $coeff . ']');

        $dureeHeures *= $coeff;
        // log::add('pool', 'debug', $this->getHumanName() . '$dureeHeures=[' . $dureeHeures . ']');

        return $dureeHeures;
    }

    public function calculateTimeFiltration($temperature_water, $flgTomorrow)
    {
        // log::add('pool', 'debug', $this->getHumanName() . 'calculateTimeFiltration() begin');

        $temperatureCalcul = $this->getCmd(null, 'temperatureMaxi')->execCmd();

        // log::add('pool', 'debug', $this->getHumanName() . '$temperatureCalcul=(' . $temperatureCalcul . ')');

        // Si pas de temperature maxi precedente on prend la temperature courante
        if ($temperatureCalcul == '' || $temperatureCalcul == 0) {
            $temperatureCalcul = $temperature_water;
        }

        // log::add('pool', 'debug', $this->getHumanName() . '$temperatureCalcul=' . $temperatureCalcul);

        // Choix du type de calcul (suivant config)
        switch ($this->getConfiguration('methodeCalcul', '1')) {
            case '1':
                $dureeHeures = $this->calculateTimeFiltrationWithCurve($temperatureCalcul);
                break;

            case '2':
                $dureeHeures = $this->calculateTimeFiltrationWithTemperatureReducedByHalf($temperatureCalcul);
                break;
        }

        list($filtrationSecondes, $filtrationTime) = $this->processingTime($dureeHeures);

        // datePivot (suivant config)
        $datePivot = $this->getConfiguration('datePivot', '13:00');
        // log::add('pool', 'debug', $this->getHumanName() . '$datePivot=' . $datePivot);

        $filtrationPivot = strtotime($datePivot);

        // log::add('pool', 'debug', $this->getHumanName() . '$flgTomorrow=' . $flgTomorrow);

        // la plage doit-elle etre celle de demain ?
        if ($flgTomorrow == true) {
            if ($filtrationPivot < time()) {
                log::add('pool', 'info', $this->getHumanName() . '+1 day');
                $filtrationPivot = strtotime("+1 day", $filtrationPivot);
            }
        }

        $pausePivot = $this->getConfiguration('pausePivot', '0') * 60; // Temps de pause en secondes
        $filtrationSecondes += $pausePivot; // Ajoute le temps de pause au temps de filtration

        // Repartition de la filtration
        switch ($this->getConfiguration('distributionDatePivot', '1')) {
            case '1':
                // 1/2 <> 1/2
                // log::add('pool', 'debug', $this->getHumanName() . 'distributionDatePivot= 1/2 <> 1/2');
                $filtrationDebut = $filtrationPivot - ($filtrationSecondes / 2.0);
                $filtrationFin = $filtrationPivot + ($filtrationSecondes / 2.0);

                $filtrationPauseDebut = $filtrationPivot - ($pausePivot / 2.0);
                $filtrationPauseFin = $filtrationPivot + ($pausePivot / 2.0);
                break;

            case '2':
                // 1/3 <> 2/3
                // log::add('pool', 'debug', $this->getHumanName() . 'distributionDatePivot= 1/3 <> 2/3');
                $filtrationDebut = $filtrationPivot - ($filtrationSecondes / 3.0);
                $filtrationFin = $filtrationPivot + (($filtrationSecondes / 3.0) * 2.0);

                $filtrationPauseDebut = $filtrationPivot - ($pausePivot / 3.0);
                $filtrationPauseFin = $filtrationPivot + (($pausePivot / 3.0) * 2.0);
                break;

        }

        // Memorise les resultats du calcul
        $this->getCmd(null, 'filtrationTime')->event($filtrationTime);

        if ($this->getConfiguration('pausePivot', '0') != 0) {
            $this->getCmd(null, 'filtrationSchedule')->event(
                date("H:i", $filtrationDebut) . '-'
                . date("H:i", $filtrationPauseDebut) . ' '
                . date("H:i", $filtrationPauseFin) . '-'
                . date("H:i", $filtrationFin) . ' : '
                . $temperatureCalcul . '°C');
        } else {
            $this->getCmd(null, 'filtrationSchedule')->event(
                date("H:i", $filtrationDebut) . '-'
                . date("H:i", $filtrationFin) . ' : '
                . $temperatureCalcul . '°C');
        }

        $this->getCmd(null, 'filtrationDebut')->event($filtrationDebut);
        $this->getCmd(null, 'filtrationFin')->event($filtrationFin);
        $this->getCmd(null, 'filtrationPauseDebut')->event($filtrationPauseDebut);
        $this->getCmd(null, 'filtrationPauseFin')->event($filtrationPauseFin);

        $this->getCmd(null, 'calculateStatus')->event(1); // 1 >> calcul effectué

        if ($flgTomorrow == true) {
            $this->getCmd(null, 'temperatureMaxi')->event(0); // reset temperature maxi
        }

        log::add('pool', 'info', $this->getHumanName() . '$filtrationTime=' . $filtrationTime);

        log::add('pool', 'info', $this->getHumanName() . '$filtrationDebut=' . date("H:i d-m-Y", $filtrationDebut));

        if ($this->getConfiguration('pausePivot', '0') != 0) {
            log::add('pool', 'info', $this->getHumanName() . '$filtrationPauseDebut=' . date("H:i d-m-Y", $filtrationPauseDebut));
            log::add('pool', 'info', $this->getHumanName() . '$filtrationPauseFin=' . date("H:i d-m-Y ", $filtrationPauseFin));
        }

        log::add('pool', 'info', $this->getHumanName() . '$filtrationFin=' . date("H:i d-m-Y ", $filtrationFin));

        // log::add('pool', 'debug', $this->getHumanName() . 'calculateTimeFiltration() end');
    }

    public function calculateStatusFiltration($temperature_water)
    {
        // log::add('pool', 'debug', $this->getHumanName() . 'calculateStatusFiltration() begin');

        $filtrationTemperature = 0;

        $filtrationDebut = $this->getCmd(null, 'filtrationDebut')->execCmd();
        // log::add('pool', 'debug', $this->getHumanName() . '$filtrationDebut=' . date("d-m-Y H:i", $filtrationDebut));

        if ($this->getConfiguration('pausePivot', '0') != 0) {
            $filtrationPauseDebut = $this->getCmd(null, 'filtrationPauseDebut')->execCmd();
            // log::add('pool', 'debug', $this->getHumanName() . '$filtrationPauseDebut=' . date("d-m-Y H:i", $filtrationPauseDebut));
            $filtrationPauseFin = $this->getCmd(null, 'filtrationPauseFin')->execCmd();
            // log::add('pool', 'debug', $this->getHumanName() . '$filtrationPauseFin=' . date("d-m-Y H:i", $filtrationPauseFin));
        }

        $filtrationFin = $this->getCmd(null, 'filtrationFin')->execCmd();
        // log::add('pool', 'debug', $this->getHumanName() . '$filtrationFin=' . date("d-m-Y H:i", $filtrationFin));

        $timeNow = time();
        // log::add('pool', 'debug', $this->getHumanName() . '$timeNow=' . date("d-m-Y H:i", $timeNow));

        if ($filtrationDebut == 0 || $filtrationFin == 0) {
            // log::add('pool', 'debug', $this->getHumanName() . ' $filtrationDebut=' . $filtrationDebut . ' $filtrationFin=' . $filtrationFin);

            // Le calcul n'a jamais ete lancé, on le lance maintenant
            $this->calculateTimeFiltration($temperature_water, false);

            // Verifie si la plage calculée est passée
            $filtrationFin = $this->getCmd(null, 'filtrationFin')->execCmd();
            $timeNow = time();

            // log::add('pool', 'debug', $this->getHumanName() . '$filtrationFin=' . date("H:i d-m-Y", $filtrationFin));
            // log::add('pool', 'debug', $this->getHumanName() . '$timeNow=' . date("H:i d-m-Y", $timeNow));

            if ($timeNow > $filtrationFin) {
                // On est apres la plage de filtration, relancer le calcul pour la plage de demain
                $this->calculateTimeFiltration($temperature_water, true);
            }

        } else {

            if ($timeNow > $filtrationDebut && $timeNow < $filtrationFin) {

                // On est dans la plage de filtration

                // Si Asservissment interne et marche forcee active on repasse en manuel
                if ($this->getConfiguration('cfgAsservissementExterne', 'enabled') == 'disabled') {
                    if ($this->getConfiguration('disable_marcheForcee', '0') == '1') {
                        if ($this->getCmd(null, 'marcheForcee')->execCmd() == 1) {
                            // log::add('pool', 'debug', $this->getHumanName() . 'disable_marcheForcee');
                            $this->getCmd(null, 'marcheForcee')->event(0);
                        }
                    }
                }

                if ($this->getCmd(null, 'calculateStatus')->execCmd() != 0) {
                    $this->getCmd(null, 'calculateStatus')->event(0); // 0 >> debut plage de filtration, reset du flag calcul
                }

                // Active la filtration
                $filtrationTemperature = 1;

                // Pause de filtration
                if ($this->getConfiguration('pausePivot', '0') != 0) {
                    if ($timeNow > $filtrationPauseDebut && $timeNow < $filtrationPauseFin) {
                        // Desactive la filtration
                        $filtrationTemperature = 0;
                    }
                }

                // Determine la temperature maxi pour le prochain calcul
                $temperatureMaxi = $this->getCmd(null, 'temperatureMaxi')->execCmd();

                // log::add('pool', 'debug', $this->getHumanName() . '$temperatureMaxi=' . $temperatureMaxi);
                // log::add('pool', 'debug', $this->getHumanName() . '$temperature_water=' . $temperature_water);

                if ($temperature_water > $temperatureMaxi) {
                    $this->getCmd(null, 'temperatureMaxi')->event($temperature_water);
                }

            }

            $calculateStatus = $this->getCmd(null, 'calculateStatus')->execCmd();
            // log::add('pool', 'debug', $this->getHumanName() . '$calculateStatus=' . $calculateStatus);

            if ($timeNow > $filtrationFin && $calculateStatus == 0) {
                // On est apres la plage de filtration, relancer le calcul pour la plage de demain
                $this->calculateTimeFiltration($temperature_water, true);
            }

        }

        if ($this->getCmd(null, 'filtrationTemperature')->execCmd() != $filtrationTemperature) {
            $this->getCmd(null, 'filtrationTemperature')->event($filtrationTemperature);
        }

        if ($this->getCmd(null, 'filtrationHivernage')->execCmd() != 0) {
            $this->getCmd(null, 'filtrationHivernage')->event(0);
        }

        // log::add('pool', 'debug', $this->getHumanName() . 'calculateStatusFiltration() end');
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function getHivernage()
    {
        $flgHivernage = false;

        switch ($this->getConfiguration('cfgHivernage', 'enabled')) {
            case 'enabled':
                $flgHivernage = true;
                break;
            case 'disabled':
                $flgHivernage = false;
                break;
            case 'widget':
                if ($this->getCmd(null, 'hivernageWidgetStatus')->execCmd() == 1) {
                    $flgHivernage = true;
                } else {
                    $flgHivernage = false;
                }
                break;
        }

        return $flgHivernage;
    }

    public function getStatusHivernage($status)
    {
        switch ($this->getConfiguration('cfgHivernage')) {
            case 'disabled':
                $status = $status . ' ' . __('Saison', __FILE__);
                break;
            case 'enabled':
                $status = $status . ' ' . __('Hivernage', __FILE__);
                break;
            case 'widget':
                if ($this->getCmd(null, 'hivernageWidgetStatus')->execCmd() == 1) {
                    $status = $status . ' ' . __('Hivernage', __FILE__);
                } else {
                    $status = $status . ' ' . __('Saison', __FILE__);
                }
                break;
        }
        return $status;
    }

    public function calculateTimeFiltrationWithTemperatureHivernage($temperature_water)
    {
        // Filtration (temperature / 3)
        $dureeHeures = ($temperature_water / 3.0);

        // Au moins 3 heures
        $dureeHeures = max($dureeHeures, $this->getConfiguration('tempsDeFiltrationMinimum', '3'));

        // log::add('pool', 'debug', $this->getHumanName() . '$dureeHeures=[' . $dureeHeures . ']');

        return $dureeHeures;
    }

    public function calculateTimeFiltrationHivernage($temperature_water, $lever_soleil, $flgTomorrow)
    {
        // log::add('pool', 'debug', $this->getHumanName() . 'calculateTimeFiltrationHivernage() begin');

        $temperatureCalcul = $this->getCmd(null, 'temperatureMaxi')->execCmd();

        // log::add('pool', 'debug', $this->getHumanName() . '$temperatureCalcul=(' . $temperatureCalcul . ')');

        // Si pas de temperature maxi precedente on prend la temperature courante
        if ($temperatureCalcul == '' || $temperatureCalcul == 0) {
            $temperatureCalcul = $temperature_water;
        }

        // log::add('pool', 'debug', $this->getHumanName() . '$temperatureCalcul=' . $temperatureCalcul);

        $dureeHeures = $this->calculateTimeFiltrationWithTemperatureHivernage($temperatureCalcul);

        list($filtrationSecondes, $filtrationTime) = $this->processingTime($dureeHeures);


        // Choix de l'heure de filtration (suivant config)
        switch ($this->getConfiguration('choixHeureFiltrationHivernage', '1')) {
            case '1':
                // log::add('pool', 'debug', $this->getHumanName() . '$lever_soleil=' . $lever_soleil);
                $filtrationPivot = strtotime($lever_soleil);
                break;

            case '2':
                // datePivotHivernage (suivant config)
                $datePivotHivernage = $this->getConfiguration('datePivotHivernage', '06:00');
                // log::add('pool', 'debug', $this->getHumanName() . '$datePivotHivernage=' . $datePivotHivernage);
                $filtrationPivot = strtotime($datePivotHivernage);
                break;
        }

        // log::add('pool', 'debug', $this->getHumanName() . '$flgTomorrow=' . $flgTomorrow);

        // la plage doit-elle etre celle de demain ?
        if ($flgTomorrow == true) {
            if ($filtrationPivot < time()) {
                log::add('pool', 'info', $this->getHumanName() . '+1 day');
                $filtrationPivot = strtotime("+1 day", $filtrationPivot);
            }
        }

        // Repartition de la filtration
        // 2/3 <> 1/3
        // log::add('pool', 'debug', $this->getHumanName() . 'distributionDatePivot= 2/3 <> 1/3');
        $filtrationDebut = $filtrationPivot - (($filtrationSecondes / 3.0) * 2.0);
        $filtrationFin = $filtrationPivot + ($filtrationSecondes / 3.0);

        // Memorise les resultats du calcul
        $this->getCmd(null, 'filtrationTime')->event($filtrationTime);
        $this->getCmd(null, 'filtrationSchedule')->event('* ' . date("H:i", $filtrationDebut) . ' <> ' . date("H:i", $filtrationFin) . ' : ' . $temperatureCalcul . '°C');

        $this->getCmd(null, 'filtrationDebut')->event($filtrationDebut);
        $this->getCmd(null, 'filtrationFin')->event($filtrationFin);

        $this->getCmd(null, 'calculateStatus')->event(1); // 1 >> calcul effectué

        if ($flgTomorrow == true) {
            $this->getCmd(null, 'temperatureMaxi')->event(0); // reset temperature maxi
        }

        log::add('pool', 'info', $this->getHumanName() . '$filtrationTime=' . $filtrationTime);

        log::add('pool', 'info', $this->getHumanName() . '$filtrationDebut=' . date("H:i d-m-Y", $filtrationDebut));
        log::add('pool', 'info', $this->getHumanName() . '$filtrationFin=' . date("H:i d-m-Y ", $filtrationFin));

        // log::add('pool', 'debug', $this->getHumanName() . 'calculateTimeFiltrationHivernage() end');
    }

    public function calculateStatusFiltrationHivernage($temperature_water, $temperature_outdoor, $lever_soleil)
    {
        // log::add('pool', 'debug', $this->getHumanName() . 'calculateStatusFiltrationHivernage() begin');

        $filtrationHivernage = 0;

        $filtrationDebut = $this->getCmd(null, 'filtrationDebut')->execCmd();
        // log::add('pool', 'debug', $this->getHumanName() . '$filtrationDebut=' . date("d-m-Y H:i", $filtrationDebut));
        $filtrationFin = $this->getCmd(null, 'filtrationFin')->execCmd();
        // log::add('pool', 'debug', $this->getHumanName() . '$filtrationFin=' . date("d-m-Y H:i", $filtrationFin));

        $timeNow = time();
        // log::add('pool', 'debug', $this->getHumanName() . '$timeNow=' . date("d-m-Y H:i", $timeNow));

        if ($filtrationDebut == 0 || $filtrationFin == 0) {
            // log::add('pool', 'debug', $this->getHumanName() . ' $filtrationDebut=' . $filtrationDebut . ' $filtrationFin=' . $filtrationFin);

            // Le calcul n'a jamais ete lancé, on le lance maintenant
            $this->calculateTimeFiltrationHivernage($temperature_water, $lever_soleil, false);

            // Verifie si la plage calculée est passée
            $filtrationFin = $this->getCmd(null, 'filtrationFin')->execCmd();
            $timeNow = time();

            // log::add('pool', 'debug', $this->getHumanName() . '$filtrationFin=' . date("H:i d-m-Y", $filtrationFin));
            // log::add('pool', 'debug', $this->getHumanName() . '$timeNow=' . date("H:i d-m-Y", $timeNow));

            if ($timeNow > $filtrationFin) {
                // On est apres la plage de filtration, relancer le calcul pour la plage de demain
                $this->calculateTimeFiltrationHivernage($temperature_water, $lever_soleil, true);
            }

        } else {

            if ($timeNow > $filtrationDebut && $timeNow < $filtrationFin) {

                // On est dans la plage de filtration

                // Si Asservissment interne et marche forcee active on repasse en manuel
                if ($this->getConfiguration('cfgAsservissementExterne', 'enabled') == 'disabled') {
                    if ($this->getConfiguration('disable_marcheForcee', '0') == '1') {
                        if ($this->getCmd(null, 'marcheForcee')->execCmd() == 1) {
                            // log::add('pool', 'debug', $this->getHumanName() . 'disable_marcheForcee');
                            $this->getCmd(null, 'marcheForcee')->event(0);
                        }
                    }
                }

                if ($this->getCmd(null, 'calculateStatus')->execCmd() != 0) {
                    $this->getCmd(null, 'calculateStatus')->event(0); // 0 >> debut plage de filtration, reset du flag calcul
                }

                // Active la filtration
                $filtrationHivernage = 1;

                // Determine la temperature maxi pour le prochain calcul
                $temperatureMaxi = $this->getCmd(null, 'temperatureMaxi')->execCmd();

                // log::add('pool', 'debug', $this->getHumanName() . '$temperatureMaxi=' . $temperatureMaxi);
                // log::add('pool', 'debug', $this->getHumanName() . '$temperature_water=' . $temperature_water);

                if ($temperature_water > $temperatureMaxi) {
                    $this->getCmd(null, 'temperatureMaxi')->event($temperature_water);
                }

            }

            $calculateStatus = $this->getCmd(null, 'calculateStatus')->execCmd();
            // log::add('pool', 'debug', $this->getHumanName() . '$calculateStatus=' . $calculateStatus);

            if ($timeNow > $filtrationFin && $calculateStatus == 0) {
                // On est apres la plage de filtration, relancer le calcul pour la plage de demain
                $this->calculateTimeFiltrationHivernage($temperature_water, $lever_soleil, true);
            }

        }

        // Securité gel sur temperature exterieure < 0
        if ($temperature_outdoor < $this->getConfiguration('temperatureSecurite', '0')) {
            // log::add('pool', 'debug', $this->getHumanName() . 'Securité gel sur temperature exterieure < ' . $this->getConfiguration('temperatureSecurite', '0'));

            $filtrationHivernage = 1;
        }

        // 5mn toutes les 3H
        if ($this->getConfiguration('filtration_5mn_3h', '0') == '1') {
            // log::add('pool', 'debug', $this->getHumanName() . '5mn toutes les 3H');

            if ($timeNow >= '0200' && $timeNow <= '0205') {
                $filtrationHivernage = 1;
            } else if ($timeNow >= '0500' && $timeNow <= '0505') {
                $filtrationHivernage = 1;
            } else if ($timeNow >= '0800' && $timeNow <= '0805') {
                $filtrationHivernage = 1;
            } else if ($timeNow >= '1100' && $timeNow <= '1105') {
                $filtrationHivernage = 1;
            } else if ($timeNow >= '1400' && $timeNow <= '1405') {
                $filtrationHivernage = 1;
            } else if ($timeNow >= '1700' && $timeNow <= '1705') {
                $filtrationHivernage = 1;
            } else if ($timeNow >= '2000' && $timeNow <= '2005') {
                $filtrationHivernage = 1;
            } else if ($timeNow >= '2300' && $timeNow <= '2305') {
                $filtrationHivernage = 1;
            }
        }

        if ($this->getCmd(null, 'filtrationHivernage')->execCmd() != $filtrationHivernage) {
            $this->getCmd(null, 'filtrationHivernage')->event($filtrationHivernage);
        }

        if ($this->getCmd(null, 'filtrationTemperature')->execCmd() != 0) {
            $this->getCmd(null, 'filtrationTemperature')->event(0);
        }

        // log::add('pool', 'debug', $this->getHumanName() . 'calculateStatusFiltrationHivernage() end');
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function evaluateTemperatureWater()
    {
        // log::add('pool', 'debug', $this->getHumanName() . 'evaluateTemperatureWater() begin');

        preg_match_all("/#([0-9]*)#/", $this->getConfiguration('temperature_water'), $matches);
        foreach ($matches[1] as $cmd_id) {
            if (is_numeric($cmd_id)) {
                $cmd = cmd::byId($cmd_id);
                if (is_object($cmd) && $cmd->getType() == 'info') {
                    $cmd->execCmd();
                    break;
                }
            }
        }

        $temperature_water = round(jeedom::evaluateExpression($this->getConfiguration('temperature_water')), 1);

        // log::add('pool', 'debug', $this->getHumanName() . 'temperature_water=' . $temperature_water);
        // log::add('pool', 'debug', $this->getHumanName() . 'evaluateTemperatureWater() end');

        return $temperature_water;
    }

    public function getTemperatureWater()
    {
        $temperature = $this->getCmd(null, 'temperature');
        $temperature_water = $temperature->execCmd();

        // log::add('pool', 'debug', $this->getHumanName() . 'temperature_water=' . $temperature_water);

        if ($this->getConfiguration('maxTimeUpdateTemp') != '') {
            if ($temperature->getCollectDate() != '' && strtotime($temperature->getCollectDate()) < strtotime('-' . $this->getConfiguration('maxTimeUpdateTemp') . ' minutes' . date('Y-m-d H:i:s'))) {
                log::add('pool', 'error', $this->getHumanName() . __(" : Attention il n'y a pas eu de mise à jour de la température depuis ", __FILE__) . $this->getConfiguration('maxTimeUpdateTemp') . __(' mn', __FILE__));

                // log::add('pool', 'debug', $this->getHumanName() . 'maxTimeUpdateTemp > evaluateTemperatureWater');

                // La temperature n'a pas ete mise à jour depuis le temps definit par la config, on force la lecture
                $temperature_water = $this->evaluateTemperatureWater();
            }
        } else {
            if ($temperature_water == '') {

                // log::add('pool', 'debug', $this->getHumanName() . 'temperature_water == \'\' > evaluateTemperatureWater');

                // La temperature n'a jamais ete mise a jour, on force la lecture
                $temperature_water = $this->evaluateTemperatureWater();
            }
        }

        if ($this->getConfiguration('temperature_water_min') != '' && is_numeric($this->getConfiguration('temperature_water_min')) && $this->getConfiguration('temperature_water_min') > $temperature_water) {
            log::add('pool', 'error', $this->getHumanName() . __(" : Attention la température de l'eau est en dessous du seuil autorisé : ", __FILE__));
        }
        if ($this->getConfiguration('temperature_water_max') != '' && is_numeric($this->getConfiguration('temperature_water_max')) && $this->getConfiguration('temperature_water_max') < $temperature_water) {
            log::add('pool', 'error', $this->getHumanName() . __(" : Attention la température de l'eau est au dessus du seuil autorisé : ", __FILE__));
        }

        return $temperature_water;
    }

    public function evaluateTemperatureOutdoor()
    {
        // log::add('pool', 'debug', $this->getHumanName() . 'evaluateTemperatureOutdoor() begin');

        preg_match_all("/#([0-9]*)#/", $this->getConfiguration('temperature_outdoor'), $matches);
        foreach ($matches[1] as $cmd_id) {
            if (is_numeric($cmd_id)) {
                $cmd = cmd::byId($cmd_id);
                if (is_object($cmd) && $cmd->getType() == 'info') {
                    $cmd->execCmd();
                    break;
                }
            }
        }

        $temperature_outdoor = round(jeedom::evaluateExpression($this->getConfiguration('temperature_outdoor')), 1);

        // log::add('pool', 'debug', $this->getHumanName() . 'temperature_outdoor=(' . $temperature_outdoor . ')');

        // log::add('pool', 'debug', $this->getHumanName() . 'evaluateTemperatureOutdoor() end');

        return $temperature_outdoor;
    }

    public function getTemperatureOutdoor()
    {
        if ($this->getConfiguration('cfgChauffage', 'enabled') == 'enabled' || $this->getHivernage()) {
            // log::add('pool', 'debug', $this->getHumanName() . 'hivernage=enabled');

            $temperature = $this->getCmd(null, 'temperature_outdoor');
            $temperature_outdoor = $temperature->execCmd();

            if ($temperature_outdoor == '') {
                $temperature_outdoor = $this->evaluateTemperatureOutdoor();
            }

            // log::add('pool', 'debug', $this->getHumanName() . 'temperature_outdoor=(' . $temperature_outdoor . ')');

        } else {
            // log::add('pool', 'debug', $this->getHumanName() . 'hivernage=disabled');

            $temperature_outdoor = null;
        }

        return $temperature_outdoor;
    }

    public function evaluateLeverSoleil()
    {
        // log::add('pool', 'debug', $this->getHumanName() . 'evaluateLeverSoleil() begin');

        preg_match_all("/#([0-9]*)#/", $this->getConfiguration('lever_soleil'), $matches);
        foreach ($matches[1] as $cmd_id) {
            if (is_numeric($cmd_id)) {
                $cmd = cmd::byId($cmd_id);
                if (is_object($cmd) && $cmd->getType() == 'info') {
                    $cmd->execCmd();
                    break;
                }
            }
        }

        $lever_soleil = round(jeedom::evaluateExpression($this->getConfiguration('lever_soleil')), 1);

        // log::add('pool', 'debug', $this->getHumanName() . 'lever_soleil=(' . $lever_soleil . ')');

        // log::add('pool', 'debug', $this->getHumanName() . 'evaluateLeverSoleil() end');

        return $lever_soleil;
    }

    public function getLeverSoleil()
    {
        if ($this->getHivernage()) {
            // log::add('pool', 'debug', $this->getHumanName() . 'hivernage=enabled');

            $heure = $this->getCmd(null, 'lever_soleil');
            $lever_soleil = $heure->execCmd();

            if ($lever_soleil == '') {
                $lever_soleil = $this->evaluateLeverSoleil();
            }

            if (strlen($lever_soleil) == 3) {
                $lever_soleil = '0' . $lever_soleil;
            }

            // log::add('pool', 'debug', $this->getHumanName() . 'lever_soleil=(' . $lever_soleil . ')');

        } else {
            // log::add('pool', 'debug', $this->getHumanName() . 'hivernage=disabled');

            $lever_soleil = null;
        }

        return $lever_soleil;
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function executeSurpresseurOn()
    {
        // log::add('pool', 'debug', $this->getHumanName() . 'executeSurpresseurOn begin');

        if ($this->getCmd(null, 'filtrationSurpresseur')->execCmd() == 0
            && $this->getCmd(null, 'filtrationLavageEtat')->execCmd() == 0
        ) {
            $timeFin = time() + ($this->getConfiguration('surpresseurDuree', '10') * 60);
            $this->getCmd(null, 'filtrationTempsRestant')->event($timeFin);

            $timeRestant = $timeFin - time();
            $this->getCmd(null, 'surpresseurStatus')->event(date('i:s', $timeRestant));

            $this->getCmd(null, 'filtrationSurpresseur')->event(1);
            $this->activatingDevices();
        }

        // log::add('pool', 'debug', $this->getHumanName() . 'executeSurpresseurOn end');
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function executeFiltreSableLavageOn()
    {
        // log::add('pool', 'debug', $this->getHumanName() . 'executeFiltreSableLavageOn begin');

        if ($this->getCmd(null, 'filtrationSurpresseur')->execCmd() == 0) {

            // log::add('pool', 'debug', $this->getHumanName() . 'filtrationLavageEtat=(' . $this->getCmd(null, 'filtrationLavageEtat')->execCmd() . ')');

            switch ($this->getCmd(null, 'filtrationLavageEtat')->execCmd()) {
                case '':
                case '0':
                    // log::add('pool', 'debug', $this->getHumanName() . 'case 0');

                    $this->getCmd(null, 'filtrationLavageEtat')->event(1); // Arrêt, mettre la vanne sur la position lavage

                    $this->getCmd(null, 'filtreSableLavageStatus')->event(__('Arrêt, position lavage', __FILE__));

                    $this->getCmd(null, 'filtrationLavage')->event(1);
                    $this->activatingDevices();
                    break;

                case '1':
                    // log::add('pool', 'debug', $this->getHumanName() . 'case 1');

                    if ($this->getConfiguration('rincageDuree', '2') == '0') {
                        // Si le temps de rinçage est == 0 on passe directement à la fin
                        $this->getCmd(null, 'filtrationLavageEtat')->event(4); // Lavage en cours...
                    } else {
                        $this->getCmd(null, 'filtrationLavageEtat')->event(2); // Lavage en cours...
                    }

                    $timeFin = time() + ($this->getConfiguration('lavageDuree', '2') * 60);
                    $this->getCmd(null, 'filtrationTempsRestant')->event($timeFin);

                    $timeRestant = $timeFin - time();
                    $this->getCmd(null, 'filtreSableLavageStatus')->event(__('Lavage', __FILE__) . ' : ' . date('i:s', $timeRestant));

                    $this->getCmd(null, 'filtrationLavage')->event(2);

                    $this->activatingDevices();
                    break;

                case '2':
                    // log::add('pool', 'debug', $this->getHumanName() . 'case 2');

                    $this->getCmd(null, 'filtrationLavageEtat')->event(3); // Arrêt, mettre la vanne sur la position rinçage

                    $this->getCmd(null, 'filtreSableLavageStatus')->event(__('Arrêt, position rinçage', __FILE__));

                    $this->getCmd(null, 'filtrationLavage')->event(1);

                    $this->activatingDevices();
                    break;

                case '3':
                    // log::add('pool', 'debug', $this->getHumanName() . 'case 3');

                    $this->getCmd(null, 'filtrationLavageEtat')->event(4); // Rinçage en cours...

                    $timeFin = time() + ($this->getConfiguration('rincageDuree', '2') * 60);
                    $this->getCmd(null, 'filtrationTempsRestant')->event($timeFin);

                    $timeRestant = $timeFin - time();
                    $this->getCmd(null, 'filtreSableLavageStatus')->event(__('Rinçage', __FILE__) . ' : ' . date('i:s', $timeRestant));

                    $this->getCmd(null, 'filtrationLavage')->event(2);

                    $this->activatingDevices();
                    break;

                case '4':
                    // log::add('pool', 'debug', $this->getHumanName() . 'case 4');

                    $this->getCmd(null, 'filtrationLavageEtat')->event(5); // Arrêt, mettre la vanne sur la position filtration

                    $this->getCmd(null, 'filtreSableLavageStatus')->event(__('Arrêt, position filtration', __FILE__));

                    $this->getCmd(null, 'filtrationLavage')->event(1);

                    $this->activatingDevices();
                    break;

                case '5':
                    // log::add('pool', 'debug', $this->getHumanName() . 'case 5');

                    $this->getCmd(null, 'filtrationLavageEtat')->event(0);

                    $this->getCmd(null, 'filtreSableLavageStatus')->event(__('Arrêté', __FILE__));

                    $this->getCmd(null, 'filtrationLavage')->event(0);

                    $this->activatingDevices();
                    break;

            }

        }

        // log::add('pool', 'debug', $this->getHumanName() . 'executeFiltreSableLavageOn end');
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function executePoolStop()
    {
        // log::add('pool', 'debug', $this->getHumanName() . 'executePoolStop begin');

        if ($this->getCmd(null, 'filtrationSurpresseur')->execCmd() == 1) {
            $this->getCmd(null, 'filtrationSurpresseur')->event(0);
            $this->activatingDevices();
        }

        if ($this->getCmd(null, 'filtrationLavageEtat')->execCmd() != 0) {
            $this->getCmd(null, 'filtrationLavageEtat')->event(0);
            $this->getCmd(null, 'filtrationLavage')->event(0);
            $this->getCmd(null, 'filtreSableLavageStatus')->event(__('Arrêté', __FILE__));
            $this->activatingDevices();
        }

        // log::add('pool', 'debug', $this->getHumanName() . 'executePoolStop end');
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function executeResetCalcul()
    {
        // log::add('pool', 'debug', $this->getHumanName() . 'executeResetCalcul begin');

        if ($this->getHivernage()) {

            $temperature_water = $this->getTemperatureWater();
            $temperature_outdoor = $this->getTemperatureOutdoor();
            $lever_soleil = $this->getLeverSoleil();

            $this->calculateTimeFiltrationHivernage($temperature_water, $lever_soleil, false);

            // Verifie si la plage calculée est passée
            $filtrationFin = $this->getCmd(null, 'filtrationFin')->execCmd();
            $timeNow = time();

            // log::add('pool', 'debug', $this->getHumanName() . '$filtrationFin=' . date("H:i d-m-Y", $filtrationFin));
            // log::add('pool', 'debug', $this->getHumanName() . '$timeNow=' . date("H:i d-m-Y", $timeNow));

            if ($timeNow > $filtrationFin) {
                // On est apres la plage de filtration, relancer le calcul pour la plage de demain
                $this->calculateTimeFiltrationHivernage($temperature_water, $lever_soleil, true);
            }

            $this->calculateStatusFiltrationHivernage($temperature_water, $temperature_outdoor, $lever_soleil);

        } else {

            $temperature_water = $this->getTemperatureWater();
            $this->calculateTimeFiltration($temperature_water, false);

            // Verifie si la plage calculée est passée
            $filtrationFin = $this->getCmd(null, 'filtrationFin')->execCmd();
            $timeNow = time();

            // log::add('pool', 'debug', $this->getHumanName() . '$filtrationFin=' . date("H:i d-m-Y", $filtrationFin));
            // log::add('pool', 'debug', $this->getHumanName() . '$timeNow=' . date("H:i d-m-Y", $timeNow));

            if ($timeNow > $filtrationFin) {
                // On est apres la plage de filtration, relancer le calcul pour la plage de demain
                $this->calculateTimeFiltration($temperature_water, true);
            }

            $this->calculateStatusFiltration($temperature_water);

        }

        // log::add('pool', 'debug', $this->getHumanName() . 'executeResetCalcul end');
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function refreshFiltration()
    {
        $cmdFiltration = $this->getCmd(null, 'filtration');

        // log::add('pool', 'debug', $this->getHumanName() . 'refreshFiltration() filtration=[' . $cmdFiltation->execCmd() . ']');

        switch ($cmdFiltration->execCmd()) {
            case '1':
                $this->filtrationOn(true);
                break;
            case '0':
                $this->filtrationStop(true);
                break;
        }
    }

    public function filtrationOn($_repeat = false)
    {
        $cmdFiltration = $this->getCmd(null, 'filtration');

        if (!$_repeat && $cmdFiltration->execCmd() == '1') {
            // log::add('pool', 'debug', $this->getHumanName() . ' : Action filtrationOn filtration=[' . $cmdFiltration->execCmd() . ']');
            return;
        }
        // log::add('pool', 'debug', $this->getHumanName() . ' : Action filtrationOn filtration=[' . $cmdFiltration->execCmd() . ']');

        foreach ($this->getConfiguration('filtrationOn') as $action) {
            try {
                $cmd = cmd::byId(str_replace('#', '', $action['cmd']));
                if (is_object($cmd) && $this->getId() == $cmd->getEqLogic_id()) {
                    continue;
                }
                $options = array();
                if (isset($action['options'])) {
                    $options = $action['options'];
                }
                scenarioExpression::createAndExec('action', $action['cmd'], $options);
            } catch (Exception $e) {
                log::add('pool', 'error', $this->getHumanName() . __(' : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
            }
        }

        $this->getCmd(null, 'filtrationStatus')->event(__('Actif', __FILE__));

        $cmdFiltration->setCollectDate('');
        $cmdFiltration->event(1);
    }

    public function filtrationStop($_repeat = false)
    {
        $cmdFiltration = $this->getCmd(null, 'filtration');

        if (!$_repeat && $cmdFiltration->execCmd() == '0') {
            // log::add('pool', 'debug', $this->getHumanName() . ' : Action filtrationStop filtration=[' . $cmdFiltration->execCmd() . ']');
            return;
        }
        // log::add('pool', 'debug', $this->getHumanName() . ' : Action filtrationStop filtration=[' . $cmdFiltration->execCmd() . ']');

        foreach ($this->getConfiguration('filtrationStop') as $action) {
            try {
                $cmd = cmd::byId(str_replace('#', '', $action['cmd']));
                if (is_object($cmd) && $this->getId() == $cmd->getEqLogic_id()) {
                    continue;
                }
                $options = array();
                if (isset($action['options'])) {
                    $options = $action['options'];
                }
                scenarioExpression::createAndExec('action', $action['cmd'], $options);
            } catch (Exception $e) {
                log::add('pool', 'error', $this->getHumanName() . __(' : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
            }
        }

        $this->getCmd(null, 'filtrationStatus')->event(__('Arrêté', __FILE__));

        $cmdFiltration->setCollectDate('');
        $cmdFiltration->event(0);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function refreshSurpresseur()
    {
        $cmdSurpresseur = $this->getCmd(null, 'surpresseur');

        if ($this->getConfiguration('cfgSurpresseur', 'enabled') == 'enabled') {
            // log::add('pool', 'debug', $this->getHumanName() . 'surpresseur=enabled');

            // log::add('pool', 'debug', $this->getHumanName() . 'refreshSurpresseur() surpresseur=[' . $cmdSurpresseur->execCmd() . ']');

            switch ($cmdSurpresseur->execCmd()) {
                case '1':
                    $this->surpresseurOn(true);
                    break;
                case '0':
                    $this->surpresseurStop(true);
                    break;
            }
        } else {
            // log::add('pool', 'debug', $this->getHumanName() . 'surpresseur=disabled');
        }
    }

    public function surpresseurOn($_repeat = false)
    {
        $cmdSurpresseur = $this->getCmd(null, 'surpresseur');

        if (!$_repeat && $cmdSurpresseur->execCmd() == '1') {
            // log::add('pool', 'debug', $this->getHumanName() . ' : Action surpresseurOn surpresseur=[' . $cmdSurpresseur->execCmd() . ']');
            return;
        }
        // log::add('pool', 'debug', $this->getHumanName() . ' : Action surpresseurOn surpresseur=[' . $cmdSurpresseur->execCmd() . ']');

        foreach ($this->getConfiguration('surpresseurOn') as $action) {
            try {
                $cmd = cmd::byId(str_replace('#', '', $action['cmd']));
                if (is_object($cmd) && $this->getId() == $cmd->getEqLogic_id()) {
                    continue;
                }
                $options = array();
                if (isset($action['options'])) {
                    $options = $action['options'];
                }
                scenarioExpression::createAndExec('action', $action['cmd'], $options);
            } catch (Exception $e) {
                log::add('pool', 'error', $this->getHumanName() . __(' : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
            }
        }

        $this->getCmd(null, 'surpresseurStatus')->event(__('Actif', __FILE__));

        $cmdSurpresseur->setCollectDate('');
        $cmdSurpresseur->event(1);
    }

    public function surpresseurStop($_repeat = false)
    {
        $cmdSurpresseur = $this->getCmd(null, 'surpresseur');

        if (!$_repeat && $cmdSurpresseur->execCmd() == '0') {
            // log::add('pool', 'debug', $this->getHumanName() . ' : Action surpresseurStop surpresseur=[' . $cmdSurpresseur->execCmd() . ']');
            return;
        }
        // log::add('pool', 'debug', $this->getHumanName() . ' : Action surpresseurStop surpresseur=[' . $cmdSurpresseur->execCmd() . ']');

        foreach ($this->getConfiguration('surpresseurStop') as $action) {
            try {
                $cmd = cmd::byId(str_replace('#', '', $action['cmd']));
                if (is_object($cmd) && $this->getId() == $cmd->getEqLogic_id()) {
                    continue;
                }
                $options = array();
                if (isset($action['options'])) {
                    $options = $action['options'];
                }
                scenarioExpression::createAndExec('action', $action['cmd'], $options);
            } catch (Exception $e) {
                log::add('pool', 'error', $this->getHumanName() . __(' : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
            }
        }

        $this->getCmd(null, 'surpresseurStatus')->event(__('Arrêté', __FILE__));

        $cmdSurpresseur->setCollectDate('');
        $cmdSurpresseur->event(0);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function refreshTraitement()
    {
        $cmdTraitement = $this->getCmd(null, 'traitement');

        if ($this->getConfiguration('cfgTraitement', 'enabled') == 'enabled') {
            // log::add('pool', 'debug', $this->getHumanName() . 'traitement=enabled');

            // log::add('pool', 'debug', $this->getHumanName() . 'refreshTraitement() traitement=[' . $cmdTraitement->execCmd() . ']');

            switch ($cmdTraitement->execCmd()) {
                case '1':
                    $this->traitementOn(true);
                    break;
                case '0':
                    $this->traitementStop(true);
                    break;
            }
        } else {
            // log::add('pool', 'debug', $this->getHumanName() . 'traitement=disabled');
        }
    }

    public function traitementOn($_repeat = false)
    {
        $cmdTraitement = $this->getCmd(null, 'traitement');

        if (!$_repeat && $cmdTraitement->execCmd() == '1') {
            // log::add('pool', 'debug', $this->getHumanName() . ' : Action traitementOn traitement=[' . $cmdTraitement->execCmd() . ']');
            return;
        }
        // log::add('pool', 'debug', $this->getHumanName() . ' : Action traitementOn traitement=[' . $cmdTraitement->execCmd() . ']');

        foreach ($this->getConfiguration('traitementOn') as $action) {
            try {
                $cmd = cmd::byId(str_replace('#', '', $action['cmd']));
                if (is_object($cmd) && $this->getId() == $cmd->getEqLogic_id()) {
                    continue;
                }
                $options = array();
                if (isset($action['options'])) {
                    $options = $action['options'];
                }
                scenarioExpression::createAndExec('action', $action['cmd'], $options);
            } catch (Exception $e) {
                log::add('pool', 'error', $this->getHumanName() . __(' : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
            }
        }

        $this->getCmd(null, 'traitementStatus')->event(__('Actif', __FILE__));

        $cmdTraitement->setCollectDate('');
        $cmdTraitement->event(1);
    }

    public function traitementStop($_repeat = false)
    {
        $cmdTraitement = $this->getCmd(null, 'traitement');

        if (!$_repeat && $cmdTraitement->execCmd() == '0') {
            // log::add('pool', 'debug', $this->getHumanName() . ' : Action traitementStop traitement=[' . $cmdTraitement->execCmd() . ']');
            return;
        }
        // log::add('pool', 'debug', $this->getHumanName() . ' : Action traitementStop traitement=[' . $cmdTraitement->execCmd() . ']');

        foreach ($this->getConfiguration('traitementStop') as $action) {
            try {
                $cmd = cmd::byId(str_replace('#', '', $action['cmd']));
                if (is_object($cmd) && $this->getId() == $cmd->getEqLogic_id()) {
                    continue;
                }
                $options = array();
                if (isset($action['options'])) {
                    $options = $action['options'];
                }
                scenarioExpression::createAndExec('action', $action['cmd'], $options);
            } catch (Exception $e) {
                log::add('pool', 'error', $this->getHumanName() . __(' : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
            }
        }

        $this->getCmd(null, 'traitementStatus')->event(__('Arrêté', __FILE__));

        $cmdTraitement->setCollectDate('');
        $cmdTraitement->event(0);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function refreshChauffage()
    {
        $cmdChauffage = $this->getCmd(null, 'chauffage');

        if ($this->getConfiguration('cfgChauffage', 'enabled') == 'enabled') {
            // log::add('pool', 'debug', $this->getHumanName() . 'Chauffage=enabled');

            // log::add('pool', 'debug', $this->getHumanName() . 'refreshChauffage() chauffage=[' . $cmdChauffage->execCmd() . ']');

            switch ($cmdChauffage->execCmd()) {
                case '1':
                    $this->chauffageOn(true);
                    break;
                case '0':
                    $this->chauffageStop(true);
                    break;
            }
        } else {
            // log::add('pool', 'debug', $this->getHumanName() . 'chauffage=disabled');
        }
    }

    public function chauffageOn($_repeat = false)
    {
        $cmdChauffage = $this->getCmd(null, 'chauffage');

        if (!$_repeat && $cmdChauffage->execCmd() == '1') {
            // log::add('pool', 'debug', $this->getHumanName() . ' : Action chauffageOn chauffage=[' . $cmdChauffage->execCmd() . ']');
            return;
        }
        // log::add('pool', 'debug', $this->getHumanName() . ' : Action chauffageOn chauffage=[' . $cmdChauffage->execCmd() . ']');

        foreach ($this->getConfiguration('chauffageOn') as $action) {
            try {
                $cmd = cmd::byId(str_replace('#', '', $action['cmd']));
                if (is_object($cmd) && $this->getId() == $cmd->getEqLogic_id()) {
                    continue;
                }
                $options = array();
                if (isset($action['options'])) {
                    $options = $action['options'];
                }
                scenarioExpression::createAndExec('action', $action['cmd'], $options);
            } catch (Exception $e) {
                log::add('pool', 'error', $this->getHumanName() . __(' : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
            }
        }

        $this->getCmd(null, 'chauffageStatus')->event(__('Actif', __FILE__));

        $cmdChauffage->setCollectDate('');
        $cmdChauffage->event(1);
    }

    public function chauffageStop($_repeat = false)
    {
        $cmdChauffage = $this->getCmd(null, 'chauffage');

        if (!$_repeat && $cmdChauffage->execCmd() == '0') {
            // log::add('pool', 'debug', $this->getHumanName() . ' : Action chauffageStop chauffage=[' . $cmdChauffage->execCmd() . ']');
            return;
        }
        // log::add('pool', 'debug', $this->getHumanName() . ' : Action chauffageStop chauffage=[' . $cmdChauffage->execCmd() . ']');

        foreach ($this->getConfiguration('chauffageStop') as $action) {
            try {
                $cmd = cmd::byId(str_replace('#', '', $action['cmd']));
                if (is_object($cmd) && $this->getId() == $cmd->getEqLogic_id()) {
                    continue;
                }
                $options = array();
                if (isset($action['options'])) {
                    $options = $action['options'];
                }
                scenarioExpression::createAndExec('action', $action['cmd'], $options);
            } catch (Exception $e) {
                log::add('pool', 'error', $this->getHumanName() . __(' : Erreur lors de l\'éxecution de ', __FILE__) . $action['cmd'] . __('. Détails : ', __FILE__) . $e->getMessage());
            }
        }

        $this->getCmd(null, 'chauffageStatus')->event(__('Arrêté', __FILE__));

        $cmdChauffage->setCollectDate('');
        $cmdChauffage->event(0);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function asservissementOff($asservissement)
    {
        // log::add('pool', 'debug', $this->getHumanName() . ' : asservissementOff');

        $flg = 0;

        $asservissements = $this->getConfiguration('asservissement');
        foreach ($asservissements as $asservissement_state) {
            $cmd = cmd::byId(str_replace('#', '', $asservissement_state['cmd']));
            if (is_object($cmd) && $cmd->execCmd() == 1) {
                $flg = 1;
            }
        }

        if ($flg == 0) {
            $this->getCmd(null, 'filtrationSolaire')->event(0);
            $this->activatingDevices();
        }
    }

    public function asservissementOn($_trigger_id)
    {
        // log::add('pool', 'debug', $this->getHumanName() . ' : asservissementOn');

        $flg = 0;

        $asservissements = $this->getConfiguration('asservissement');
        foreach ($asservissements as $asservissement) {
            if ('#' . $_trigger_id . '#' == $asservissement['cmd']) {
                $flg = 1;
            }
        }

        if ($flg == 1) {
            $this->getCmd(null, 'filtrationSolaire')->event(1);
            $this->activatingDevices();
        }

        return true;
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function arretTotalOff($arretTotal)
    {
        // log::add('pool', 'debug', $this->getHumanName() . ' : arretTotalOff');

        $flg = 0;

        $arretTotals = $this->getConfiguration('arretTotal');
        foreach ($arretTotals as $arretTotal_state) {
            $cmd = cmd::byId(str_replace('#', '', $arretTotal_state['cmd']));
            if (is_object($cmd) && $cmd->execCmd() == 1) {
                $flg = 1;
            }
        }

        if ($flg == 0) {
            $this->getCmd(null, 'arretTotal')->event(0);
            $this->activatingDevices();
        }
    }

    public function arretTotalOn($_trigger_id)
    {
        // log::add('pool', 'debug', $this->getHumanName() . ' : arretTotalOn');

        $flg = 0;

        $arretTotals = $this->getConfiguration('arretTotal');
        foreach ($arretTotals as $arretTotal) {
            if ('#' . $_trigger_id . '#' == $arretTotal['cmd']) {
                $flg = 1;
            }
        }

        if ($flg == 1) {
            $this->getCmd(null, 'arretTotal')->event(1);
            $this->activatingDevices();
        }

        return true;
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function marcheForceeOff($marcheForcee)
    {
        // log::add('pool', 'debug', $this->getHumanName() . ' : marcheForceeOff');

        $flg = 0;

        $marcheForcees = $this->getConfiguration('marcheForcee');
        foreach ($marcheForcees as $marcheForcee_state) {
            $cmd = cmd::byId(str_replace('#', '', $marcheForcee_state['cmd']));
            if (is_object($cmd) && $cmd->execCmd() == 1) {
                $flg = 1;
            }
        }

        if ($flg == 0) {
            $this->getCmd(null, 'marcheForcee')->event(0);
            $this->activatingDevices();
        }
    }

    public function marcheForceeOn($_trigger_id)
    {
        // log::add('pool', 'debug', $this->getHumanName() . ' : marcheForceeOn');

        $flg = 0;

        $marcheForcees = $this->getConfiguration('marcheForcee');
        foreach ($marcheForcees as $marcheForcee) {
            if ('#' . $_trigger_id . '#' == $marcheForcee['cmd']) {
                $flg = 1;
            }
        }

        if ($flg == 1) {
            $this->getCmd(null, 'marcheForcee')->event(1);
            $this->activatingDevices();
        }

        return true;
    }


    ///////////////////////////////////////////////////////////////////////////////////////////

    public function executeAsservissementActif()
    {
        // log::add('pool', 'debug', $this->getHumanName() . 'executeAsservissementActif begin');

        $this->getCmd(null, 'marcheForcee')->event(1);
        $this->getCmd(null, 'arretTotal')->event(0);
        $this->activatingDevices();

        // log::add('pool', 'debug', $this->getHumanName() . 'executeAsservissementActif end');
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function executeAsservissementAuto()
    {
        // log::add('pool', 'debug', $this->getHumanName() . 'executeAsservissementAuto begin');

        $this->getCmd(null, 'marcheForcee')->event(0);
        $this->getCmd(null, 'arretTotal')->event(0);
        $this->activatingDevices();

        // log::add('pool', 'debug', $this->getHumanName() . 'executeAsservissementAuto end');
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function executeAsservissementInactif()
    {
        // log::add('pool', 'debug', $this->getHumanName() . 'executeAsservissementInactif begin');

        $this->getCmd(null, 'marcheForcee')->event(0);
        $this->getCmd(null, 'arretTotal')->event(1);
        $this->activatingDevices();

        // log::add('pool', 'debug', $this->getHumanName() . 'executeAsservissementInactif end');
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function executeSaison()
    {
        // log::add('pool', 'debug', $this->getHumanName() . 'executeSaison begin');

        $this->getCmd(null, 'hivernageWidgetStatus')->event(0);
        $this->activatingDevices();
        $this->executeResetCalcul();

        // log::add('pool', 'debug', $this->getHumanName() . 'executeSaison end');
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function executeHivernage()
    {
        // log::add('pool', 'debug', $this->getHumanName() . 'executeHivernage begin');

        $this->getCmd(null, 'hivernageWidgetStatus')->event(1);
        $this->activatingDevices();
        $this->executeResetCalcul();

        // log::add('pool', 'debug', $this->getHumanName() . 'executeHivernage end');
    }

    ///////////////////////////////////////////////////////////////////////////////////////////

    public function preInsert()
    {

    }

    public function postInsert()
    {

    }

    public function preRemove()
    {
        $cron = cron::byClassAndFunction('pool', 'pull', array('pool_id' => intval($this->getId())));
        if (is_object($cron)) {
            $cron->stop();
            $cron->remove();
        }

        $listener = listener::byClassAndFunction('pool', 'asservissement', array('pool_id' => intval($this->getId())));
        if (is_object($listener)) {
            $listener->remove();
        }

        $listener = listener::byClassAndFunction('pool', 'arretTotal', array('pool_id' => intval($this->getId())));
        if (is_object($listener)) {
            $listener->remove();
        }

        $listener = listener::byClassAndFunction('pool', 'marcheForcee', array('pool_id' => intval($this->getId())));
        if (is_object($listener)) {
            $listener->remove();
        }
    }

    public function preSave()
    {
        // log::add('pool', 'debug', $this->getHumanName() . 'preSave() begin');

        if ($this->getConfiguration('datePivot') == '') {
            $this->setConfiguration('datePivot', '13:00');
        }

        if ($this->getConfiguration('pausePivot') == '') {
            $this->setConfiguration('pausePivot', '0');
        }

        if ($this->getConfiguration('surpresseurDuree') == '') {
            $this->setConfiguration('surpresseurDuree', '20');
        }

        if ($this->getConfiguration('lavageDuree') == '') {
            $this->setConfiguration('lavageDuree', '2');
        }

        if ($this->getConfiguration('rincageDuree') == '') {
            $this->setConfiguration('rincageDuree', '3');
        }

        if ($this->getConfiguration('distributionDatePivot') == '') {
            $this->setConfiguration('distributionDatePivot', '1');
        }

        if ($this->getConfiguration('coefficientAjustement') == '') {
            $this->setConfiguration('coefficientAjustement', '10');
        }

        if ($this->getConfiguration('methodeCalcul') == '') {
            $this->setConfiguration('methodeCalcul', '1');
        }

        if ($this->getConfiguration('tempsDeFiltrationMinimum') == '') {
            $this->setConfiguration('tempsDeFiltrationMinimum', '3');
        }

        if ($this->getConfiguration('datePivotHivernage') == '') {
            $this->setConfiguration('datePivotHivernage', '06:00');
        }

        if ($this->getConfiguration('choixHeureFiltrationHivernage') == '') {
            $this->setConfiguration('choixHeureFiltrationHivernage', '1');
        }

        if ($this->getConfiguration('disable_marcheForcee') == '') {
            $this->setConfiguration('disable_marcheForcee', '0');
        }

        if ($this->getConfiguration('cfgChauffage') == '') {
            $this->setConfiguration('cfgChauffage', 'disabled');
        }

        if ($this->getConfiguration('cfgHivernage') == '') {
            $this->setConfiguration('cfgHivernage', 'disabled');
        }

        // log::add('pool', 'debug', $this->getHumanName() . 'preSave() end');
    }

    public function postSave()
    {
        // log::add('pool', 'debug', $this->getHumanName() . 'postSave() begin');

        $order = 0;

        if ($this->getIsEnable() == 1) {
            ///////////////////////////////////////////////////////////////////////////////////////

            // temperature
            {
                $temperature = $this->getCmd(null, 'temperature');
                if (!is_object($temperature)) {
                    $temperature = new poolCmd();
                    $temperature->setTemplate('dashboard', 'tile');
                    $temperature->setTemplate('mobile', 'badge');
                    // $temperature->setDisplay('parameters', array('displayHistory' => 'display : none;'));
                }
                $temperature->setEqLogic_id($this->getId());
                $temperature->setName(__('Température', __FILE__));
                $temperature->setType('info');
                $temperature->setSubType('numeric');
                $temperature->setLogicalId('temperature');
                $temperature->setOrder($order++);
                $temperature->setUnite('°C');
                $temperature->setIsVisible(1);
                $temperature->setIsHistorized(1);

                $value = '';
                preg_match_all("/#([0-9]*)#/", $this->getConfiguration('temperature_water'), $matches);
                foreach ($matches[1] as $cmd_id) {
                    if (is_numeric($cmd_id)) {
                        $cmd = cmd::byId($cmd_id);
                        if (is_object($cmd) && $cmd->getType() == 'info') {
                            $value .= '#' . $cmd_id . '#';
                            break;
                        }
                    }
                }
                $temperature->setValue($value);
                $temperature->save();
                $temperature->event($temperature->execute());
            }

            ///////////////////////////////////////////////////////////////////////////////////////

            // temperature_outdoor
            {
                $temperature_outdoor = $this->getCmd(null, 'temperature_outdoor');
                if (!is_object($temperature_outdoor)) {
                    $temperature_outdoor = new poolCmd();
                    $temperature_outdoor->setTemplate('dashboard', 'tile');
                    $temperature_outdoor->setTemplate('mobile', 'badge');
                    // $temperature_outdoor->setDisplay('parameters', array('displayHistory' => 'display : none;'));
                }
                $temperature_outdoor->setEqLogic_id($this->getId());
                $temperature_outdoor->setName(__('Température extérieure', __FILE__));
                $temperature_outdoor->setType('info');
                $temperature_outdoor->setSubType('numeric');
                $temperature_outdoor->setLogicalId('temperature_outdoor');
                $temperature_outdoor->setOrder($order++);
                $temperature_outdoor->setUnite('°C');

                if ($this->getConfiguration('cfgChauffage', 'enabled') == 'enabled'
                    || $this->getConfiguration('cfgHivernage', 'enabled') == 'enabled'
                    || $this->getConfiguration('cfgHivernage', 'enabled') == 'widget'
                ) {
                    $temperature_outdoor->setIsVisible(1);
                } else {
                    $temperature_outdoor->setIsVisible(0);
                }

                $temperature_outdoor->setIsHistorized(1);

                $value = '';
                preg_match_all("/#([0-9]*)#/", $this->getConfiguration('temperature_outdoor'), $matches);
                foreach ($matches[1] as $cmd_id) {
                    if (is_numeric($cmd_id)) {
                        $cmd = cmd::byId($cmd_id);
                        if (is_object($cmd) && $cmd->getType() == 'info') {
                            $value .= '#' . $cmd_id . '#';
                            break;
                        }
                    }
                }
                $temperature_outdoor->setValue($value);
                $temperature_outdoor->save();
            }

            ///////////////////////////////////////////////////////////////////////////////////////

            // filtrationTime
            {
                $filtrationTime = $this->getCmd(null, 'filtrationTime');
                if (!is_object($filtrationTime)) {
                    $filtrationTime = new poolCmd();
                    $filtrationTime->setTemplate('dashboard', 'tile');
                    $filtrationTime->setTemplate('mobile', 'badge');
                    $filtrationTime->setType('info');
                    $filtrationTime->setSubType('string');
                    $filtrationTime->setValue('00:00');
                }
                $filtrationTime->setEqLogic_id($this->getId());
                $filtrationTime->setName(__('Temps filtration', __FILE__));
                $filtrationTime->setType('info');
                $filtrationTime->setSubType('string');
                $filtrationTime->setLogicalId('filtrationTime');
                $filtrationTime->setIsVisible(1);
                $filtrationTime->setOrder($order++);
                $filtrationTime->setIsHistorized(0);
                $filtrationTime->save();
            }

            ///////////////////////////////////////////////////////////////////////////////////////

            // filtrationSchedule
            {
                $filtrationSchedule = $this->getCmd(null, 'filtrationSchedule');
                if (!is_object($filtrationSchedule)) {
                    $filtrationSchedule = new poolCmd();
                }
                $filtrationSchedule->setEqLogic_id($this->getId());
                $filtrationSchedule->setName(__('Horaires:', __FILE__));
                $filtrationSchedule->setType('info');
                $filtrationSchedule->setSubType('string');
                $filtrationSchedule->setLogicalId('filtrationSchedule');
                $filtrationSchedule->setIsVisible(1);
                $filtrationSchedule->setOrder($order++);
                $filtrationSchedule->setIsHistorized(0);
                $filtrationSchedule->save();
            }

            ///////////////////////////////////////////////////////////////////////////////////////

            // resetCalcul
            {
                $resetCalcul = $this->getCmd(null, 'resetCalcul');
                if (!is_object($resetCalcul)) {
                    $resetCalcul = new poolCmd();
                }
                $resetCalcul->setEqLogic_id($this->getId());
                $resetCalcul->setName(__('Reset', __FILE__));
                $resetCalcul->setType('action');
                $resetCalcul->setSubType('other');
                $resetCalcul->setLogicalId('resetCalcul');
                $resetCalcul->setOrder($order++);

                if ($this->getConfiguration('display_reset', '0') == '1') {
                    $resetCalcul->setIsVisible(1);
                } else {
                    $resetCalcul->setIsVisible(0);
                }

                $resetCalcul->save();
            }

            ///////////////////////////////////////////////////////////////////////////////////////

            // filtrationStatus
            {
                $filtrationStatus = $this->getCmd(null, 'filtrationStatus');
                if (!is_object($filtrationStatus)) {
                    $filtrationStatus = new poolCmd();
                }
                $filtrationStatus->setEqLogic_id($this->getId());
                $filtrationStatus->setName(__('Filtration:', __FILE__));
                $filtrationStatus->setType('info');
                $filtrationStatus->setSubType('string');
                $filtrationStatus->setLogicalId('filtrationStatus');
                $filtrationStatus->setIsVisible(1);
                $filtrationStatus->setOrder($order++);
                $filtrationStatus->setIsHistorized(0);
                $filtrationStatus->save();
            }

            // filtration
            {
                $filtration = $this->getCmd(null, 'filtration');
                if (!is_object($filtration)) {
                    $filtration = new poolCmd();
                }
                $filtration->setEqLogic_id($this->getId());
                $filtration->setName('Filtration');
                $filtration->setType('info');
                $filtration->setSubType('binary');
                $filtration->setLogicalId('filtration');
                $filtration->setIsVisible(0);
                $filtration->setOrder($order++);
                $filtration->setIsHistorized(1);
                $filtration->save();
            }

            ///////////////////////////////////////////////////////////////////////////////////////

            // chauffageStatus
            {
                $chauffageStatus = $this->getCmd(null, 'chauffageStatus');
                if (!is_object($chauffageStatus)) {
                    $chauffageStatus = new poolCmd();
                }
                $chauffageStatus->setEqLogic_id($this->getId());
                $chauffageStatus->setName(__('Chauffage:', __FILE__));
                $chauffageStatus->setType('info');
                $chauffageStatus->setSubType('string');
                $chauffageStatus->setLogicalId('chauffageStatus');
                if ($this->getConfiguration('cfgChauffage', 'enabled') == 'enabled') {
                    $chauffageStatus->setIsVisible(1);
                } else {
                    $chauffageStatus->setIsVisible(0);
                }
                $chauffageStatus->setOrder($order++);
                $chauffageStatus->setIsHistorized(0);
                $chauffageStatus->save();
            }

            // chauffage
            {
                $chauffage = $this->getCmd(null, 'chauffage');
                if (!is_object($chauffage)) {
                    $chauffage = new poolCmd();
                }
                $chauffage->setEqLogic_id($this->getId());
                $chauffage->setName('Chauffage');
                $chauffage->setType('info');
                $chauffage->setSubType('binary');
                $chauffage->setLogicalId('chauffage');
                $chauffage->setIsVisible(0);
                $chauffage->setOrder($order++);
                $chauffage->setIsHistorized(0);
                $chauffage->save();
            }

            ///////////////////////////////////////////////////////////////////////////////////////

            // traitementStatus
            {
                $traitementStatus = $this->getCmd(null, 'traitementStatus');
                if (!is_object($traitementStatus)) {
                    $traitementStatus = new poolCmd();
                }
                $traitementStatus->setEqLogic_id($this->getId());
                $traitementStatus->setName(__('Traitement:', __FILE__));
                $traitementStatus->setType('info');
                $traitementStatus->setSubType('string');
                $traitementStatus->setLogicalId('traitementStatus');
                if ($this->getConfiguration('cfgTraitement', 'enabled') == 'enabled') {
                    $traitementStatus->setIsVisible(1);
                } else {
                    $traitementStatus->setIsVisible(0);
                }
                $traitementStatus->setOrder($order++);
                $traitementStatus->setIsHistorized(0);
                $traitementStatus->save();
            }

            // traitement
            {
                $traitement = $this->getCmd(null, 'traitement');
                if (!is_object($traitement)) {
                    $traitement = new poolCmd();
                }
                $traitement->setEqLogic_id($this->getId());
                $traitement->setName('Traitement');
                $traitement->setType('info');
                $traitement->setSubType('binary');
                $traitement->setLogicalId('traitement');
                $traitement->setIsVisible(0);
                $traitement->setOrder($order++);
                $traitement->setIsHistorized(0);
                $traitement->save();
            }

            ///////////////////////////////////////////////////////////////////////////////////////

            // asservissement / hivernage Status
            {
                $asservissementStatus = $this->getCmd(null, 'asservissementStatus');
                if (!is_object($asservissementStatus)) {
                    $asservissementStatus = new poolCmd();
                }
                $asservissementStatus->setEqLogic_id($this->getId());
                $asservissementStatus->setName(__('Mode:', __FILE__));
                $asservissementStatus->setType('info');
                $asservissementStatus->setSubType('string');
                $asservissementStatus->setLogicalId('asservissementStatus');

                if ($this->getConfiguration('cfgAsservissementExterne', 'enabled') == 'disabled'
                    || $this->getConfiguration('cfgHivernage', 'enabled') == 'widget'
                ) {
                    $asservissementStatus->setIsVisible(1);
                } else {
                    $asservissementStatus->setIsVisible(0);
                }
                $asservissementStatus->setOrder($order++);
                $asservissementStatus->setIsHistorized(0);
                $asservissementStatus->save();
            }

            ///////////////////////////////////////////////////////////////////////////////////////

            // asservissementActif
            {
                $asservissementActif = $this->getCmd(null, 'asservissementActif');
                if (!is_object($asservissementActif)) {
                    $asservissementActif = new poolCmd();
                }
                $asservissementActif->setEqLogic_id($this->getId());
                $asservissementActif->setName(__('Actif', __FILE__));
                $asservissementActif->setType('action');
                $asservissementActif->setSubType('other');
                $asservissementActif->setLogicalId('asservissementActif');
                $asservissementActif->setOrder($order++);

                if ($this->getConfiguration('cfgAsservissementExterne', 'enabled') == 'disabled') {
                    $asservissementActif->setIsVisible(1);
                } else {
                    $asservissementActif->setIsVisible(0);
                }

                $asservissementActif->save();
            }

            // asservissementAuto
            {
                $asservissementAuto = $this->getCmd(null, 'asservissementAuto');
                if (!is_object($asservissementAuto)) {
                    $asservissementAuto = new poolCmd();
                }
                $asservissementAuto->setEqLogic_id($this->getId());
                $asservissementAuto->setName(__('Auto', __FILE__));
                $asservissementAuto->setType('action');
                $asservissementAuto->setSubType('other');
                $asservissementAuto->setLogicalId('asservissementAuto');
                $asservissementAuto->setOrder($order++);

                if ($this->getConfiguration('cfgAsservissementExterne', 'enabled') == 'disabled') {
                    $asservissementAuto->setIsVisible(1);
                } else {
                    $asservissementAuto->setIsVisible(0);
                }

                $asservissementAuto->save();
            }

            // asservissementInactif
            {
                $asservissementInactif = $this->getCmd(null, 'asservissementInactif');
                if (!is_object($asservissementInactif)) {
                    $asservissementInactif = new poolCmd();
                }
                $asservissementInactif->setEqLogic_id($this->getId());
                $asservissementInactif->setName(__('Inactif', __FILE__));
                $asservissementInactif->setType('action');
                $asservissementInactif->setSubType('other');
                $asservissementInactif->setLogicalId('asservissementInactif');
                $asservissementInactif->setDisplay('forceReturnLineAfter', 1);
                $asservissementInactif->setOrder($order++);

                if ($this->getConfiguration('cfgAsservissementExterne', 'enabled') == 'disabled') {
                    $asservissementInactif->setIsVisible(1);
                } else {
                    $asservissementInactif->setIsVisible(0);
                }

                $asservissementInactif->save();
            }

            ///////////////////////////////////////////////////////////////////////////////////////

            // saison
            {
                $saison = $this->getCmd(null, 'saison');
                if (!is_object($saison)) {
                    $saison = new poolCmd();
                }
                $saison->setEqLogic_id($this->getId());
                $saison->setName(__('Saison', __FILE__));
                $saison->setType('action');
                $saison->setSubType('other');
                $saison->setLogicalId('saison');
                $saison->setOrder($order++);

                if ($this->getConfiguration('cfgHivernage', 'enabled') == 'widget') {
                    $saison->setIsVisible(1);
                } else {
                    $saison->setIsVisible(0);
                }

                $saison->save();
            }

            // hivernage
            {
                $hivernage = $this->getCmd(null, 'hivernage');
                if (!is_object($hivernage)) {
                    $hivernage = new poolCmd();
                }
                $hivernage->setEqLogic_id($this->getId());
                $hivernage->setName(__('Hivernage', __FILE__));
                $hivernage->setType('action');
                $hivernage->setSubType('other');
                $hivernage->setLogicalId('hivernage');
                $hivernage->setDisplay('forceReturnLineAfter', 1);
                $hivernage->setOrder($order++);

                if ($this->getConfiguration('cfgHivernage', 'enabled') == 'widget') {
                    $hivernage->setIsVisible(1);
                } else {
                    $hivernage->setIsVisible(0);
                }

                $hivernage->save();
            }
            ///////////////////////////////////////////////////////////////////////////////////////

            // surpresseurStatus
            {
                $surpresseurStatus = $this->getCmd(null, 'surpresseurStatus');
                if (!is_object($surpresseurStatus)) {
                    $surpresseurStatus = new poolCmd();
                }
                $surpresseurStatus->setEqLogic_id($this->getId());
                $surpresseurStatus->setName(__('Surpresseur:', __FILE__));
                $surpresseurStatus->setType('info');
                $surpresseurStatus->setSubType('string');
                $surpresseurStatus->setLogicalId('surpresseurStatus');

                if ($this->getConfiguration('cfgSurpresseur', 'enabled') == 'enabled') {
                    $surpresseurStatus->setIsVisible(1);
                } else {
                    $surpresseurStatus->setIsVisible(0);
                }

                $surpresseurStatus->setOrder($order++);
                $surpresseurStatus->setIsHistorized(0);
                $surpresseurStatus->save();
            }

            // surpresseurOn
            {
                $surpresseurOn = $this->getCmd(null, 'surpresseurOn');
                if (!is_object($surpresseurOn)) {
                    $surpresseurOn = new poolCmd();
                }
                $surpresseurOn->setEqLogic_id($this->getId());
                $surpresseurOn->setName(__('On', __FILE__));
                $surpresseurOn->setType('action');
                $surpresseurOn->setSubType('other');
                $surpresseurOn->setLogicalId('surpresseurOn');
                $surpresseurOn->setOrder($order++);

                if ($this->getConfiguration('cfgSurpresseur', 'enabled') == 'enabled') {
                    $surpresseurOn->setIsVisible(1);
                } else {
                    $surpresseurOn->setIsVisible(0);
                }

                $surpresseurOn->save();
            }

            // surpresseur
            {
                $surpresseur = $this->getCmd(null, 'surpresseur');
                if (!is_object($surpresseur)) {
                    $surpresseur = new poolCmd();
                }
                $surpresseur->setEqLogic_id($this->getId());
                $surpresseur->setName('Surpresseur');
                $surpresseur->setType('info');
                $surpresseur->setSubType('binary');
                $surpresseur->setLogicalId('surpresseur');
                $surpresseur->setIsVisible(0);
                $surpresseur->setOrder($order++);
                $surpresseur->setIsHistorized(0);
                $surpresseur->save();
            }

            ///////////////////////////////////////////////////////////////////////////////////////

            // filtreSableLavageStatus
            {
                $filtreSableLavageStatus = $this->getCmd(null, 'filtreSableLavageStatus');
                if (!is_object($filtreSableLavageStatus)) {
                    $filtreSableLavageStatus = new poolCmd();
                }
                $filtreSableLavageStatus->setEqLogic_id($this->getId());
                $filtreSableLavageStatus->setName(__('Filtre à sable:', __FILE__));
                $filtreSableLavageStatus->setType('info');
                $filtreSableLavageStatus->setSubType('string');
                $filtreSableLavageStatus->setLogicalId('filtreSableLavageStatus');

                if ($this->getConfiguration('cfgFiltreSable', 'enabled') == 'enabled') {
                    $filtreSableLavageStatus->setIsVisible(1);
                } else {
                    $filtreSableLavageStatus->setIsVisible(0);
                }

                $filtreSableLavageStatus->setOrder($order++);
                $filtreSableLavageStatus->setIsHistorized(0);
                $filtreSableLavageStatus->save();
            }

            // filtreSableLavageOn
            {
                $filtreSableLavageOn = $this->getCmd(null, 'filtreSableLavageOn');
                if (!is_object($filtreSableLavageOn)) {
                    $filtreSableLavageOn = new poolCmd();
                }
                $filtreSableLavageOn->setEqLogic_id($this->getId());
                $filtreSableLavageOn->setName(__('Start', __FILE__));
                $filtreSableLavageOn->setType('action');
                $filtreSableLavageOn->setSubType('other');
                $filtreSableLavageOn->setLogicalId('filtreSableLavageOn');
                $filtreSableLavageOn->setOrder($order++);

                if ($this->getConfiguration('cfgFiltreSable', 'enabled') == 'enabled') {
                    $filtreSableLavageOn->setIsVisible(1);
                } else {
                    $filtreSableLavageOn->setIsVisible(0);
                }

                $filtreSableLavageOn->save();
            }

            ///////////////////////////////////////////////////////////////////////////////////////

            // poolStop
            {
                $poolStop = $this->getCmd(null, 'poolStop');
                if (!is_object($poolStop)) {
                    $poolStop = new poolCmd();
                }
                $poolStop->setEqLogic_id($this->getId());
                $poolStop->setName(__('Stop', __FILE__));
                $poolStop->setType('action');
                $poolStop->setSubType('other');
                $poolStop->setLogicalId('poolStop');
                $poolStop->setDisplay('forceReturnLineBefore', 1);
                $poolStop->setOrder($order++);
                if ($this->getConfiguration('cfgSurpresseur', 'enabled') == 'enabled'
                    || $this->getConfiguration('cfgFiltreSable', 'enabled') == 'enabled'
                ) {
                    $poolStop->setIsVisible(1);
                } else {
                    $poolStop->setIsVisible(0);
                }
                $poolStop->save();
            }

            ///////////////////////////////////////////////////////////////////////////////////////

            // lever_soleil
            {
                $lever_soleil = $this->getCmd(null, 'lever_soleil');
                if (!is_object($lever_soleil)) {
                    $lever_soleil = new poolCmd();
                    $lever_soleil->setTemplate('dashboard', 'badge');
                    $lever_soleil->setTemplate('mobile', 'badge');
                }
                $lever_soleil->setEqLogic_id($this->getId());
                $lever_soleil->setName(__('Lever du soleil', __FILE__));
                $lever_soleil->setType('info');
                $lever_soleil->setSubType('numeric');
                $lever_soleil->setLogicalId('lever_soleil');
                $lever_soleil->setOrder($order++);
                $lever_soleil->setUnite('');
                $lever_soleil->setIsVisible(0);
                $lever_soleil->setIsHistorized(0);

                $value = '';
                preg_match_all("/#([0-9]*)#/", $this->getConfiguration('lever_soleil'), $matches);
                foreach ($matches[1] as $cmd_id) {
                    if (is_numeric($cmd_id)) {
                        $cmd = cmd::byId($cmd_id);
                        if (is_object($cmd) && $cmd->getType() == 'info') {
                            $value .= '#' . $cmd_id . '#';
                            break;
                        }
                    }
                }
                $lever_soleil->setValue($value);
                $lever_soleil->save();
            }

            ///////////////////////////////////////////////////////////////////////////////////////

            // filtrationTemperature
            {
                $filtrationTemperature = $this->getCmd(null, 'filtrationTemperature');
                if (!is_object($filtrationTemperature)) {
                    $filtrationTemperature = new poolCmd();
                }
                $filtrationTemperature->setEqLogic_id($this->getId());
                $filtrationTemperature->setName('filtrationTemperature');
                $filtrationTemperature->setType('info');
                $filtrationTemperature->setSubType('numeric');
                $filtrationTemperature->setLogicalId('filtrationTemperature');
                $filtrationTemperature->setIsVisible(0);
                $filtrationTemperature->setOrder($order++);
                $filtrationTemperature->setIsHistorized(0);
                $filtrationTemperature->save();
            }

            // filtrationLavage
            {
                $filtrationLavage = $this->getCmd(null, 'filtrationLavage');
                if (!is_object($filtrationLavage)) {
                    $filtrationLavage = new poolCmd();
                }
                $filtrationLavage->setEqLogic_id($this->getId());
                $filtrationLavage->setName('filtrationLavage');
                $filtrationLavage->setType('info');
                $filtrationLavage->setSubType('numeric');
                $filtrationLavage->setLogicalId('filtrationLavage');
                $filtrationLavage->setIsVisible(0);
                $filtrationLavage->setOrder($order++);
                $filtrationLavage->setIsHistorized(0);
                $filtrationLavage->save();
            }

            // filtrationSolaire
            {
                $filtrationSolaire = $this->getCmd(null, 'filtrationSolaire');
                if (!is_object($filtrationSolaire)) {
                    $filtrationSolaire = new poolCmd();
                }
                $filtrationSolaire->setEqLogic_id($this->getId());
                $filtrationSolaire->setName('filtrationSolaire');
                $filtrationSolaire->setType('info');
                $filtrationSolaire->setSubType('numeric');
                $filtrationSolaire->setLogicalId('filtrationSolaire');
                $filtrationSolaire->setIsVisible(0);
                $filtrationSolaire->setOrder($order++);
                $filtrationSolaire->setIsHistorized(0);
                $filtrationSolaire->save();
            }

            // filtrationHivernage
            {
                $filtrationHivernage = $this->getCmd(null, 'filtrationHivernage');
                if (!is_object($filtrationHivernage)) {
                    $filtrationHivernage = new poolCmd();
                }
                $filtrationHivernage->setEqLogic_id($this->getId());
                $filtrationHivernage->setName('filtrationHivernage');
                $filtrationHivernage->setType('info');
                $filtrationHivernage->setSubType('numeric');
                $filtrationHivernage->setLogicalId('filtrationHivernage');
                $filtrationHivernage->setIsVisible(0);
                $filtrationHivernage->setOrder($order++);
                $filtrationHivernage->setIsHistorized(0);
                $filtrationHivernage->save();
            }

            // filtrationSurpresseur
            {
                $filtrationSurpresseur = $this->getCmd(null, 'filtrationSurpresseur');
                if (!is_object($filtrationSurpresseur)) {
                    $filtrationSurpresseur = new poolCmd();
                }
                $filtrationSurpresseur->setEqLogic_id($this->getId());
                $filtrationSurpresseur->setName('filtrationSurpresseur');
                $filtrationSurpresseur->setType('info');
                $filtrationSurpresseur->setSubType('numeric');
                $filtrationSurpresseur->setLogicalId('filtrationSurpresseur');
                $filtrationSurpresseur->setIsVisible(0);
                $filtrationSurpresseur->setOrder($order++);
                $filtrationSurpresseur->setIsHistorized(0);
                $filtrationSurpresseur->save();
            }

            // filtrationTempsRestant
            {
                $filtrationTempsRestant = $this->getCmd(null, 'filtrationTempsRestant');
                if (!is_object($filtrationTempsRestant)) {
                    $filtrationTempsRestant = new poolCmd();
                }
                $filtrationTempsRestant->setEqLogic_id($this->getId());
                $filtrationTempsRestant->setName('filtrationTempsRestant');
                $filtrationTempsRestant->setType('info');
                $filtrationTempsRestant->setSubType('numeric');
                $filtrationTempsRestant->setLogicalId('filtrationTempsRestant');
                $filtrationTempsRestant->setIsVisible(0);
                $filtrationTempsRestant->setOrder($order++);
                $filtrationTempsRestant->setIsHistorized(0);
                $filtrationTempsRestant->save();
            }

            // filtrationLavageEtat
            {
                $filtrationLavageEtat = $this->getCmd(null, 'filtrationLavageEtat');
                if (!is_object($filtrationLavageEtat)) {
                    $filtrationLavageEtat = new poolCmd();
                }
                $filtrationLavageEtat->setEqLogic_id($this->getId());
                $filtrationLavageEtat->setName('filtrationLavageEtat');
                $filtrationLavageEtat->setType('info');
                $filtrationLavageEtat->setSubType('numeric');
                $filtrationLavageEtat->setLogicalId('filtrationLavageEtat');
                $filtrationLavageEtat->setIsVisible(0);
                $filtrationLavageEtat->setOrder($order++);
                $filtrationLavageEtat->setIsHistorized(0);
                $filtrationLavageEtat->save();
            }

            // filtrationDebut
            {
                $filtrationDebut = $this->getCmd(null, 'filtrationDebut');
                if (!is_object($filtrationDebut)) {
                    $filtrationDebut = new poolCmd();
                }
                $filtrationDebut->setEqLogic_id($this->getId());
                $filtrationDebut->setName('filtrationDebut');
                $filtrationDebut->setType('info');
                $filtrationDebut->setSubType('numeric');
                $filtrationDebut->setLogicalId('filtrationDebut');
                $filtrationDebut->setIsVisible(0);
                $filtrationDebut->setOrder($order++);
                $filtrationDebut->setIsHistorized(0);
                $filtrationDebut->save();
            }

            // filtrationFin
            {
                $filtrationFin = $this->getCmd(null, 'filtrationFin');
                if (!is_object($filtrationFin)) {
                    $filtrationFin = new poolCmd();
                }
                $filtrationFin->setEqLogic_id($this->getId());
                $filtrationFin->setName('filtrationFin');
                $filtrationFin->setType('info');
                $filtrationFin->setSubType('numeric');
                $filtrationFin->setLogicalId('filtrationFin');
                $filtrationFin->setIsVisible(0);
                $filtrationFin->setOrder($order++);
                $filtrationFin->setIsHistorized(0);
                $filtrationFin->save();
            }

            // temperatureMaxi
            {
                $temperatureMaxi = $this->getCmd(null, 'temperatureMaxi');
                if (!is_object($temperatureMaxi)) {
                    $temperatureMaxi = new poolCmd();
                    $temperatureMaxi->setType('info');
                    $temperatureMaxi->setSubType('numeric');
                    $temperatureMaxi->setValue(0);
                }
                $temperatureMaxi->setEqLogic_id($this->getId());
                $temperatureMaxi->setName('temperatureMaxi');
                $temperatureMaxi->setType('info');
                $temperatureMaxi->setSubType('numeric');
                $temperatureMaxi->setLogicalId('temperatureMaxi');
                $temperatureMaxi->setIsVisible(0);
                $temperatureMaxi->setOrder($order++);
                $temperatureMaxi->setIsHistorized(0);
                $temperatureMaxi->save();
            }

            // calculateStatus
            {
                $calculateStatus = $this->getCmd(null, 'calculateStatus');
                if (!is_object($calculateStatus)) {
                    $calculateStatus = new poolCmd();
                    $calculateStatus->setType('info');
                    $calculateStatus->setSubType('numeric');
                    $calculateStatus->setValue(0);
                }
                $calculateStatus->setEqLogic_id($this->getId());
                $calculateStatus->setName('calculateStatus');
                $calculateStatus->setType('info');
                $calculateStatus->setSubType('numeric');
                $calculateStatus->setLogicalId('calculateStatus');
                $calculateStatus->setIsVisible(0);
                $calculateStatus->setOrder($order++);
                $calculateStatus->setIsHistorized(0);
                $calculateStatus->save();
            }

            // arretTotal
            {
                $arretTotal = $this->getCmd(null, 'arretTotal');
                if (!is_object($arretTotal)) {
                    $arretTotal = new poolCmd();
                }
                $arretTotal->setEqLogic_id($this->getId());
                $arretTotal->setName('arretTotal');
                $arretTotal->setType('info');
                $arretTotal->setSubType('numeric');
                $arretTotal->setLogicalId('arretTotal');
                $arretTotal->setIsVisible(0);
                $arretTotal->setOrder($order++);
                $arretTotal->setIsHistorized(0);
                $arretTotal->save();
            }

            // marcheForcee
            {
                $marcheForcee = $this->getCmd(null, 'marcheForcee');
                if (!is_object($marcheForcee)) {
                    $marcheForcee = new poolCmd();
                }
                $marcheForcee->setEqLogic_id($this->getId());
                $marcheForcee->setName('marcheForcee');
                $marcheForcee->setType('info');
                $marcheForcee->setSubType('numeric');
                $marcheForcee->setLogicalId('marcheForcee');
                $marcheForcee->setIsVisible(0);
                $marcheForcee->setOrder($order++);
                $marcheForcee->setIsHistorized(0);
                $marcheForcee->save();
            }

            // hivernageWidgetStatus
            {
                $hivernageWidgetStatus = $this->getCmd(null, 'hivernageWidgetStatus');
                if (!is_object($hivernageWidgetStatus)) {
                    $hivernageWidgetStatus = new poolCmd();
                }
                $hivernageWidgetStatus->setEqLogic_id($this->getId());
                $hivernageWidgetStatus->setName('hivernageWidgetStatus');
                $hivernageWidgetStatus->setType('info');
                $hivernageWidgetStatus->setSubType('numeric');
                $hivernageWidgetStatus->setLogicalId('hivernageWidgetStatus');
                $hivernageWidgetStatus->setIsVisible(0);
                $hivernageWidgetStatus->setOrder($order++);
                $hivernageWidgetStatus->setIsHistorized(0);
                $hivernageWidgetStatus->save();
            }

            // filtrationPauseDebut
            {
                $filtrationPauseDebut = $this->getCmd(null, 'filtrationPauseDebut');
                if (!is_object($filtrationPauseDebut)) {
                    $filtrationPauseDebut = new poolCmd();
                }
                $filtrationPauseDebut->setEqLogic_id($this->getId());
                $filtrationPauseDebut->setName('filtrationPauseDebut');
                $filtrationPauseDebut->setType('info');
                $filtrationPauseDebut->setSubType('numeric');
                $filtrationPauseDebut->setLogicalId('filtrationPauseDebut');
                $filtrationPauseDebut->setIsVisible(0);
                $filtrationPauseDebut->setOrder($order++);
                $filtrationPauseDebut->setIsHistorized(0);
                $filtrationPauseDebut->save();
            }

            // filtrationPauseFin
            {
                $filtrationPauseFin = $this->getCmd(null, 'filtrationPauseFin');
                if (!is_object($filtrationPauseFin)) {
                    $filtrationPauseFin = new poolCmd();
                }
                $filtrationPauseFin->setEqLogic_id($this->getId());
                $filtrationPauseFin->setName('filtrationPauseFin');
                $filtrationPauseFin->setType('info');
                $filtrationPauseFin->setSubType('numeric');
                $filtrationPauseFin->setLogicalId('filtrationPauseFin');
                $filtrationPauseFin->setIsVisible(0);
                $filtrationPauseFin->setOrder($order++);
                $filtrationPauseFin->setIsHistorized(0);
                $filtrationPauseFin->save();
            }
        }

        ///////////////////////////////////////////////////////////////////////////////////////
        ///////////////////////////////////////////////////////////////////////////////////////

        if ($this->getIsEnable() == 1) {
            $asservissements = $this->getConfiguration('asservissement');
            if (count($asservissements) > 0) {
                $listener = listener::byClassAndFunction('pool', 'asservissement', array('pool_id' => intval($this->getId())));
                if (!is_object($listener)) {
                    $listener = new listener();
                }
                $listener->setClass('pool');
                $listener->setFunction('asservissement');
                $listener->setOption(array('pool_id' => intval($this->getId())));
                $listener->emptyEvent();
                foreach ($asservissements as $asservissement) {
                    $listener->addEvent($asservissement['cmd']);
                }
                $listener->save();
            }

        } else {
            $listener = listener::byClassAndFunction('pool', 'asservissement', array('pool_id' => intval($this->getId())));
            if (is_object($listener)) {
                $listener->remove();
            }
        }

        if ($this->getIsEnable() == 1) {
            $arretTotals = $this->getConfiguration('arretTotal');
            if (count($arretTotals) > 0) {
                $listener = listener::byClassAndFunction('pool', 'arretTotal', array('pool_id' => intval($this->getId())));
                if (!is_object($listener)) {
                    $listener = new listener();
                }
                $listener->setClass('pool');
                $listener->setFunction('arretTotal');
                $listener->setOption(array('pool_id' => intval($this->getId())));
                $listener->emptyEvent();
                foreach ($arretTotals as $arretTotal) {
                    $listener->addEvent($arretTotal['cmd']);
                }
                $listener->save();
            }

        } else {
            $listener = listener::byClassAndFunction('pool', 'arretTotal', array('pool_id' => intval($this->getId())));
            if (is_object($listener)) {
                $listener->remove();
            }
        }

        if ($this->getIsEnable() == 1) {
            $marcheForcees = $this->getConfiguration('marcheForcee');
            if (count($marcheForcees) > 0) {
                $listener = listener::byClassAndFunction('pool', 'marcheForcee', array('pool_id' => intval($this->getId())));
                if (!is_object($listener)) {
                    $listener = new listener();
                }
                $listener->setClass('pool');
                $listener->setFunction('marcheForcee');
                $listener->setOption(array('pool_id' => intval($this->getId())));
                $listener->emptyEvent();
                foreach ($marcheForcees as $marcheForcee) {
                    $listener->addEvent($marcheForcee['cmd']);
                }
                $listener->save();
            }

        } else {
            $listener = listener::byClassAndFunction('pool', 'marcheForcee', array('pool_id' => intval($this->getId())));
            if (is_object($listener)) {
                $listener->remove();
            }
        }

        // log::add('pool', 'debug', $this->getHumanName() . 'postSave() end');
    }

    public function preUpdate()
    {

    }

    public function postUpdate()
    {

    }

    public function postRemove()
    {

    }

    public function runtimeByDay($_startDate = null, $_endDate = null)
    {
        // log::add('pool', 'debug', 'runtimeByDay()');
        // log::add('pool', 'debug', '$_startDate:'.$_startDate);
        // log::add('pool', 'debug', '$_endDate:'.$_endDate);

        $actifCmd = $this->getCmd(null, 'filtration');
        if (!is_object($actifCmd)) {
            return array();
        }
        $return = array();
        $prevValue = 0;
        $prevDatetime = 0;
        $day = null;

        foreach ($actifCmd->getHistory($_startDate, $_endDate) as $history) {

            // log::add('pool', 'debug', '$history->getDatetime:'.$history->getDatetime());
            // log::add('pool', 'debug'// , '$history->getValue:'.$history->getValue());

            if (date('Y-m-d', strtotime($history->getDatetime())) != $day && $prevValue == 1 && $day != null) {
                if (strtotime($day . ' 23:59:59') > $prevDatetime) {
                    $return[$day][1] += (strtotime($day . ' 23:59:59') - $prevDatetime) / 3600;
                }
                $prevDatetime = strtotime(date('Y-m-d 00:00:00', strtotime($history->getDatetime())));
            }
            $day = date('Y-m-d', strtotime($history->getDatetime()));
            if (!isset($return[$day])) {
                $return[$day] = array(strtotime($day . ' 00:00:00 UTC') * 1000, 0);
            }
            if ($history->getValue() == 1 && $prevValue == 0) {
                $prevDatetime = strtotime($history->getDatetime());
                $prevValue = 1;
            }
            if ($history->getValue() == 0 && $prevValue == 1) {
                if ($prevDatetime > 0 && strtotime($history->getDatetime()) > $prevDatetime) {
                    $return[$day][1] += (strtotime($history->getDatetime()) - $prevDatetime) / 3600;
                }
                $prevValue = 0;
            }
        }

        // Ajoute le temps restant de la journée courante jusqu'a l'instant present time()
        if ($prevValue == 1) {
            $return[$day][1] += (time() - $prevDatetime) / 3600;
            // log::add('pool', 'debug', 'reste:' . (time() - $prevDatetime) / 3600);
        }

        //log::add('pool', 'debug', '$return:' . $return);
        return $return;
    }

    /*     * **********************Getteur Setteur*************************** */

}

class poolCmd extends cmd
{
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    public function dontRemoveCmd()
    {
        return true;
    }

    public function execute($_options = array())
    {
        $eqLogic = $this->getEqLogic();

        // log::add('pool', 'debug', $this->getHumanName() . 'execute() ' . $this->getLogicalId());

        ///////////////////////////////////////////////////////////////////////////////////////

        if ($this->getLogicalId() == 'temperature') {
            preg_match_all("/#([0-9]*)#/", $eqLogic->getConfiguration('temperature_water'), $matches);
            $date = '';
            foreach ($matches[1] as $cmd_id) {
                if (is_numeric($cmd_id)) {
                    $cmd = cmd::byId($cmd_id);
                    if (is_object($cmd) && $cmd->getType() == 'info') {
                        $cmd->execCmd();
                        if ($date == '' || strtotime($date) < strtotime($cmd->getCollectDate())) {
                            $date = $cmd->getCollectDate();
                        }
                        break;
                    }
                }
            }
            if ($date != '') {
                $this->setCollectDate($date);
            }

            // log::add('pool', 'debug', $this->getHumanName() . ' execute() temperature_water:' . round(jeedom::evaluateExpression($eqLogic->getConfiguration('temperature_water')), 1));

            return round(jeedom::evaluateExpression($eqLogic->getConfiguration('temperature_water')), 1);
        }

        ///////////////////////////////////////////////////////////////////////////////////////

        if ($this->getLogicalId() == 'temperature_outdoor') {
            preg_match_all("/#([0-9]*)#/", $eqLogic->getConfiguration('temperature_outdoor'), $matches);
            $date = '';
            foreach ($matches[1] as $cmd_id) {
                if (is_numeric($cmd_id)) {
                    $cmd = cmd::byId($cmd_id);
                    if (is_object($cmd) && $cmd->getType() == 'info') {
                        $cmd->execCmd();
                        if ($date == '' || strtotime($date) < strtotime($$cmd->getCollectDate())) {
                            $date = $cmd->getCollectDate();
                        }
                        break;
                    }
                }
            }
            if ($date != '') {
                $this->setCollectDate($date);
            }

            // log::add('pool', 'debug', $this->getHumanName() . ' execute() temperature_outdoor:' . round(jeedom::evaluateExpression($eqLogic->getConfiguration('temperature_outdoor')), 1));

            return round(jeedom::evaluateExpression($eqLogic->getConfiguration('temperature_outdoor')), 1);
        }

        ///////////////////////////////////////////////////////////////////////////////////////

        if ($this->getLogicalId() == 'lever_soleil') {
            preg_match_all("/#([0-9]*)#/", $eqLogic->getConfiguration('lever_soleil'), $matches);
            $date = '';
            foreach ($matches[1] as $cmd_id) {
                if (is_numeric($cmd_id)) {
                    $cmd = cmd::byId($cmd_id);
                    if (is_object($cmd) && $cmd->getType() == 'info') {
                        $cmd->execCmd();
                        if ($date == '' || strtotime($date) < strtotime($$cmd->getCollectDate())) {
                            $date = $cmd->getCollectDate();
                        }
                        break;
                    }
                }
            }
            if ($date != '') {
                $this->setCollectDate($date);
            }

            // log::add('pool', 'debug', $this->getHumanName() . ' execute() lever_soleil:' . round(jeedom::evaluateExpression($eqLogic->getConfiguration('lever_soleil')), 1));

            return round(jeedom::evaluateExpression($eqLogic->getConfiguration('lever_soleil')), 1);
        }

        ///////////////////////////////////////////////////////////////////////////////////////

        if ($this->getLogicalId() == 'surpresseurOn') {
            $eqLogic->executeSurpresseurOn();

            return '';
        }

        ///////////////////////////////////////////////////////////////////////////////////////

        if ($this->getLogicalId() == 'filtreSableLavageOn') {
            $eqLogic->executeFiltreSableLavageOn();

            return '';
        }

        ///////////////////////////////////////////////////////////////////////////////////////

        if ($this->getLogicalId() == 'poolStop') {
            $eqLogic->executePoolStop();

            return '';
        }

        ///////////////////////////////////////////////////////////////////////////////////////

        if ($this->getLogicalId() == 'resetCalcul') {
            $eqLogic->executeResetCalcul();

            return '';
        }

        ///////////////////////////////////////////////////////////////////////////////////////

        if ($this->getLogicalId() == 'asservissementActif') {
            $eqLogic->executeAsservissementActif();

            return '';
        }

        ///////////////////////////////////////////////////////////////////////////////////////

        if ($this->getLogicalId() == 'asservissementAuto') {
            $eqLogic->executeAsservissementAuto();

            return '';
        }

        ///////////////////////////////////////////////////////////////////////////////////////

        if ($this->getLogicalId() == 'asservissementInactif') {
            $eqLogic->executeAsservissementInactif();

            return '';
        }

        ///////////////////////////////////////////////////////////////////////////////////////

        if ($this->getLogicalId() == 'saison') {
            $eqLogic->executeSaison();

            return '';
        }

        ///////////////////////////////////////////////////////////////////////////////////////

        if ($this->getLogicalId() == 'hivernage') {
            $eqLogic->executeHivernage();

            return '';
        }
    }

    /*     * **********************Getteur Setteur*************************** */

}

?>
