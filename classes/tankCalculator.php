<?php
if(!defined('IDRINTH')) {
    die('No direct calls to this file allowed.');
}

require_once('combatCalculations.php');
require_once('view.php');

/**
 * Die Basisklasse , welche einen einzelne Rechner definiert
 **/
class tankCalculator {
    // Systemrelevantes
    var $error = array();
    var $keys = array();
        static $version = '2.0.1';
        // Ausgangswerte
    var $combatCalculations = NULL;
        var $defenders = 1;

    /**
     * Initialisiert die Werte der Klasse mit Werten aus $_REQUEST, nachdem diese überprüft wurden
     **/
    function __construct() {
            if(array_key_exists('dN', $_REQUEST) && $_REQUEST['dN'] > 1) {
                $this->combatCalculations = array();
                if(array_key_exists('d0l', $_REQUEST)) {
                    $_REQUEST['d1l'] = $_REQUEST['d0l'];//sicherstellen, dass die geteilten Werte identisch sind
                }
                $this->combatCalculations[0] = new combatCalculations(0,0);
                $this->combatCalculations[1] = new combatCalculations(0,1);
                $this->defenders = 2;
            }else {
                $this->combatCalculations[0] = new combatCalculations(0,0);
            }
            foreach($_REQUEST as $name => $value) {
              $_SESSION[session_id()][$name] = $value;
            }
    }

    /**
     * Holt die gewünschten Daten aus der Klasse combatCalculations und stellt diese mit der Klasse view dar
         *
         * @todo ansicht für zwei Eingaben hinzufügen
     **/
    function generateView() {
            $tankCalculator = array(
                'title' => 'Tanking Calculator',
                'path' => 'tank',
                'version' => array(
                    'Tanking Calculator' => tankCalculator::$version,
                ),
                'head-js' => array(),
                'foot-js' => array('update'),
                'styles' => array()
            );
            if($this->defenders > 1) {
                list($defences[0]) = $this->combatCalculations[0]->defenceChance(array('m'));
                list($defences[1]) = $this->combatCalculations[1]->defenceChance(array('m'));
                $tankCalculator += array(
                    'rows' => array (
                        0 => array(
                            'title' => 'Shared Values',
                            'mode' => 'input',
                            'values' => array (
                                0 => array (
                                    'name' => $this->combatCalculations[0]->attacker->getKey('level'),
                                    'label' => 'Enemy Level',
                                    'value' => $this->combatCalculations[0]->attacker->level,
                                    'type' => 'number',
                                    'shared' => TRUE,
                                ),
                                1 => array (
                                    'name' => $this->combatCalculations[0]->defender->getKey('level'),
                                    'label' => 'Your Level',
                                    'value' => $this->combatCalculations[0]->defender->level,
                                    'type' => 'number',
                                    'shared' => TRUE,
                                ),
                                2 => array (
                                    'name' => 'dN',
                                    'value' => 2,
                                    'label' => 'Use comparison mode?',
                                    'type' => 'check',
                                    'checked' => true,
                                    'script' => 'window.setTimeout(function() {document.forms[\'calculator-form\'].submit();},1);',
                                ),
                                3 => array (
                                    'name' => 'submit',
                                    'label' => '',
                                    'value' => 'calculate manually',
                                    'type' => 'submit',
                                ),
                            )
                        ),
                        1 => array (
                            'title' => 'Your first Statset',
                            'mode' => 'input',
                            'values' => array (
                                0 => array (
                                    'name' => $this->combatCalculations[0]->defender->getKey('armor'),
                                    'label' => 'Your armorvalue',
                                    'value' => $this->combatCalculations[0]->defender->armor,
                                    'type' => 'number',
                                ),
                                1 => array (
                                    'name' => $this->combatCalculations[0]->defender->getKey('penetrationReduction'),
                                    'label' => 'Your armorpenetration reduction',
                                    'value' => $this->combatCalculations[0]->defender->penetrationReduction,
                                    'type' => 'number',
                                    'unit' => '%',
                                ),
                                2 => array (
                                    'name' => $this->combatCalculations[0]->defender->getKey('corporalResistance'),
                                    'label' => 'Your corporal resistance',
                                    'value' => $this->combatCalculations[0]->defender->corporalResistance,
                                    'type' => 'number',
                                ),
                                3 => array (
                                    'name' => $this->combatCalculations[0]->defender->getKey('spiritResistance'),
                                    'label' => 'Your spirit resistance',
                                    'value' => $this->combatCalculations[0]->defender->spiritResistance,
                                    'type' => 'number',
                                ),
                                4 => array (
                                    'name' => $this->combatCalculations[0]->defender->getKey('elementalResistance'),
                                    'label' => 'Your elemental resistance',
                                    'value' => $this->combatCalculations[0]->defender->elementalResistance,
                                    'type' => 'number',
                                ),
                                5 => array (
                                    'name' => $this->combatCalculations[0]->defender->getKey('initiative'),
                                    'label' => 'Your initiative',
                                    'value' => $this->combatCalculations[0]->defender->initiative,
                                    'type' => 'number',
                                ),
                                6 => array (
                                    'name' => $this->combatCalculations[0]->defender->getKey('critChanceReduction'),
                                    'label' => 'Your crit chance reduction modifiers',
                                    'value' => $this->combatCalculations[0]->defender->critChanceReduction,
                                    'type' => 'number',
                                    'unit' => '%',
                                ),
                                7 => array (
                                    'name' => $this->combatCalculations[0]->defender->getKey('blockValue'),
                                    'label' => "Your shield's blockvalue",
                                    'value' => $this->combatCalculations[0]->defender->blockValue,
                                    'type' => 'number',
                                ),
                                8 => array (
                                    'name' => $this->combatCalculations[0]->defender->getKey('blockModifiers'),
                                    'label' => 'Your block chance modifiers modifiers',
                                    'value' => $this->combatCalculations[0]->defender->blockModifiers,
                                    'type' => 'number',
                                    'unit' => '%',
                                ),
                                9 => array (
                                    'name' => $this->combatCalculations[0]->defender->getKey('weaponskill'),
                                    'label' => 'Your weaponskill',
                                    'value' => $this->combatCalculations[0]->defender->weaponskill,
                                    'type' => 'number',
                                ),
                                10 => array (
                                    'name' => $this->combatCalculations[0]->defender->getKey('parryModifiers'),
                                    'label' => 'Your parry chance modifiers',
                                    'value' => $this->combatCalculations[0]->defender->parryModifiers,
                                    'type' => 'number',
                                    'unit' => '%',
                                ),
                            )
                        ),
                        2 => array (
                            'title' => 'Your second Statset',
                            'mode' => 'input',
                            'values' => array (
                                0 => array (
                                    'name' => $this->combatCalculations[1]->defender->getKey('armor'),
                                    'label' => 'Your armorvalue',
                                    'value' => $this->combatCalculations[1]->defender->armor,
                                    'type' => 'number',
                                ),
                                1 => array (
                                    'name' => $this->combatCalculations[1]->defender->getKey('penetrationReduction'),
                                    'label' => 'Your armorpenetration reduction',
                                    'value' => $this->combatCalculations[1]->defender->penetrationReduction,
                                    'type' => 'number',
                                    'unit' => '%',
                                ),
                                2 => array (
                                    'name' => $this->combatCalculations[1]->defender->getKey('corporalResistance'),
                                    'label' => 'Your corporal resistance',
                                    'value' => $this->combatCalculations[1]->defender->corporalResistance,
                                    'type' => 'number',
                                ),
                                3 => array (
                                    'name' => $this->combatCalculations[1]->defender->getKey('spiritResistance'),
                                    'label' => 'Your spirit resistance',
                                    'value' => $this->combatCalculations[1]->defender->spiritResistance,
                                    'type' => 'number',
                                ),
                                4 => array (
                                    'name' => $this->combatCalculations[1]->defender->getKey('elementalResistance'),
                                    'label' => 'Your elemental resistance',
                                    'value' => $this->combatCalculations[1]->defender->elementalResistance,
                                    'type' => 'number',
                                ),
                                5 => array (
                                    'name' => $this->combatCalculations[1]->defender->getKey('initiative'),
                                    'label' => 'Your initiative',
                                    'value' => $this->combatCalculations[1]->defender->initiative,
                                    'type' => 'number',
                                ),
                                6 => array (
                                    'name' => $this->combatCalculations[1]->defender->getKey('critChanceReduction'),
                                    'label' => 'Your crit chance reduction modifiers',
                                    'value' => $this->combatCalculations[1]->defender->critChanceReduction,
                                    'type' => 'number',
                                    'unit' => '%',
                                ),
                                7 => array (
                                    'name' => $this->combatCalculations[1]->defender->getKey('blockValue'),
                                    'label' => "Your shield's blockvalue",
                                    'value' => $this->combatCalculations[1]->defender->blockValue,
                                    'type' => 'number',
                                ),
                                8 => array (
                                    'name' => $this->combatCalculations[1]->defender->getKey('blockModifiers'),
                                    'label' => 'Your block chance modifiers modifiers',
                                    'value' => $this->combatCalculations[1]->defender->blockModifiers,
                                    'type' => 'number',
                                    'unit' => '%',
                                ),
                                9 => array (
                                    'name' => $this->combatCalculations[1]->defender->getKey('weaponskill'),
                                    'label' => 'Your weaponskill',
                                    'value' => $this->combatCalculations[1]->defender->weaponskill,
                                    'type' => 'number',
                                ),
                                10 => array (
                                    'name' => $this->combatCalculations[1]->defender->getKey('parryModifiers'),
                                    'label' => 'Your parry chance modifiers',
                                    'value' => $this->combatCalculations[1]->defender->parryModifiers,
                                    'type' => 'number',
                                    'unit' => '%',
                                )
                            )
                        ),
                        3 => array (
                            'title' => 'Your Results',
                            'mode' => 'output',
                            'values' => array (
                                0 => array (
                                    'name' => array(
                                        'c0pp',
                                        'c1pp'
                                    ),
                                    'label' => 'Physical Protection',
                                    'value' => array(
                                        floor($this->combatCalculations[0]->physicalProtection()*10000)/100,
                                        floor($this->combatCalculations[1]->physicalProtection()*10000)/100
                                    ),
                                    'type' => 'number',
                                    'unit' => '%',
                                ),
                                1 => array (
                                    'name' => array(
                                        'c0mpc',
                                        'c1mpc'
                                    ),
                                    'label' => 'Corporal Protection',
                                    'value' => array(
                                        floor($this->combatCalculations[0]->magicalProtection('corporal')*10000)/100,
                                        floor($this->combatCalculations[1]->magicalProtection('corporal')*10000)/100,
                                    ),
                                    'type' => 'number',
                                    'unit' => '%',
                                ),
                                2 => array (
                                    'name' => array(
                                        'c0mpe',
                                        'c1mpe',
                                    ),
                                    'label' => 'Elemental Protection',
                                    'value' => array(
                                        floor($this->combatCalculations[0]->magicalProtection('elemental')*10000)/100,
                                        floor($this->combatCalculations[1]->magicalProtection('elemental')*10000)/100
                                    ),
                                    'type' => 'number',
                                    'unit' => '%',
                                ),
                                3 => array (
                                    'name' => array(
                                        'c0mps',
                                        'c1mps'
                                    ),
                                    'label' => 'Spirit Protection',
                                    'value' => array(
                                        floor($this->combatCalculations[0]->magicalProtection('spirit')*10000)/100,
                                        floor($this->combatCalculations[1]->magicalProtection('spirit')*10000)/100
                                    ),
                                    'type' => 'number',
                                    'unit' => '%',
                                ),
                                4 => array (
                                    'name' => array(
                                        'c0cr',
                                        'c1cr'
                                    ),
                                    'label' => 'Chance to be crit',
                                    'value' => array (
                                        ceil($this->combatCalculations[0]->critRate()*10000)/100,
                                        ceil($this->combatCalculations[1]->critRate()*10000)/100
                                    ),
                                    'type' => 'number',
                                    'unit' => '%',
                                ),
                                5 => array (
                                    'name' => array(
                                        'c0dcmp',
                                        'c1dcmp'
                                    ),
                                    'label' => 'Block Chance',
                                    'value' => array(
                                        floor($defences[0]['block']*10000)/100,
                                        floor($defences[1]['block']*10000)/100
                                    ),
                                    'type' => 'number',
                                    'unit' => '%',
                                ),
                                6 => array (
                                    'name' => array(
                                        'c0dcms',
                                        'c1dcms'
                                    ),
                                    'label' => 'Parry Chance',
                                    'value' => array(
                                        floor($defences[0]['secondary']*10000)/100,
                                        floor($defences[1]['secondary']*10000)/100
                                    ),
                                    'type' => 'number',
                                    'unit' => '%',
                                ),
                                7 => array (
                                    'name' => array(
                                        'c0dcmt',
                                        'c1dcmt'
                                    ),
                                    'label' => 'Total Avoidance',
                                    'value' => array(
                                        floor($defences[0]['total']*10000)/100,
                                        floor($defences[1]['total']*10000)/100
                                    ),
                                    'type' => 'number',
                                    'unit' => '%',
                                ),
                                8 => array (
                                    'name' => array(
                                        'a0s',
                                        'a1s'
                                    ),
                                    'label' => 'Toughness required',
                                    'value' => array(
                                        $this->combatCalculations[0]->attacker->strength,
                                        $this->combatCalculations[1]->attacker->strength
                                    ),
                                    'type' => 'number',
                                ),
                                9 => array (
                                    'name' => array(
                                        'c0atc',
                                        'c1atc'
                                    ),
                                    'label' => 'Additional armor needed to cap',
                                    'value' => array (
                                        ceil($this->combatCalculations[0]->armorToCap()),
                                        ceil($this->combatCalculations[1]->armorToCap()),
                                    ),
                                    'type' => 'number',
                                ),
                                10 => array (
                                    'name' => array(
                                        'c0iti',
                                        'c1iti'
                                    ),
                                    'label' => 'Initiative needed for Crit-immunity',
                                    'value' => array(
                                        $this->combatCalculations[0]->initiativeToImmunity(),
                                        $this->combatCalculations[1]->initiativeToImmunity()
                                    ),
                                    'type' => 'number',
                                ),
                            )
                        )
                    )
                );
            } else {
                list($defences) = $this->combatCalculations[0]->defenceChance(array('m'));
                $tankCalculator += array(
                    'rows' => array (
                    0 => array (
                        'title' => 'Your Stats',
                        'mode' => 'input',
                        'values' => array (
                            0 => array (
                                'name' => $this->combatCalculations[0]->attacker->getKey('level'),
                                'label' => 'Enemy Level',
                                'value' => $this->combatCalculations[0]->attacker->level,
                                'type' => 'number',
                            ),
                            1 => array (
                                'name' => $this->combatCalculations[0]->defender->getKey('level'),
                                'label' => 'Your Level',
                                'value' => $this->combatCalculations[0]->defender->level,
                                'type' => 'number',
                            ),
                            2 => array (
                                'name' => $this->combatCalculations[0]->defender->getKey('armor'),
                                'label' => 'Your armorvalue',
                                'value' => $this->combatCalculations[0]->defender->armor,
                                'type' => 'number',
                            ),
                            3 => array (
                                'name' => $this->combatCalculations[0]->defender->getKey('penetrationReduction'),
                                'label' => 'Your armorpenetration reduction',
                                'value' => $this->combatCalculations[0]->defender->penetrationReduction,
                                'type' => 'number',
                                'unit' => '%',
                            ),
                            4 => array (
                                'name' => $this->combatCalculations[0]->defender->getKey('corporalResistance'),
                                'label' => 'Your corporal resistance',
                                'value' => $this->combatCalculations[0]->defender->corporalResistance,
                                'type' => 'number',
                            ),
                            5 => array (
                                'name' => $this->combatCalculations[0]->defender->getKey('spiritResistance'),
                                'label' => 'Your spirit resistance',
                                'value' => $this->combatCalculations[0]->defender->spiritResistance,
                                'type' => 'number',
                            ),
                            6 => array (
                                'name' => $this->combatCalculations[0]->defender->getKey('elementalResistance'),
                                'label' => 'Your elemental resistance',
                                'value' => $this->combatCalculations[0]->defender->elementalResistance,
                                'type' => 'number',
                            ),
                            7 => array (
                                'name' => $this->combatCalculations[0]->defender->getKey('initiative'),
                                'label' => 'Your initiative',
                                'value' => $this->combatCalculations[0]->defender->initiative,
                                'type' => 'number',
                            ),
                            8 => array (
                                'name' => $this->combatCalculations[0]->defender->getKey('critChanceReduction'),
                                'label' => 'Your crit chance reduction modifiers',
                                'value' => $this->combatCalculations[0]->defender->critChanceReduction,
                                'type' => 'number',
                                'unit' => '%',
                            ),
                            9 => array (
                                'name' => $this->combatCalculations[0]->defender->getKey('blockValue'),
                                'label' => "Your shield's blockvalue",
                                'value' => $this->combatCalculations[0]->defender->blockValue,
                                'type' => 'number',
                            ),
                            10 => array (
                                'name' => $this->combatCalculations[0]->defender->getKey('blockModifiers'),
                                'label' => 'Your block chance modifiers modifiers',
                                'value' => $this->combatCalculations[0]->defender->blockModifiers,
                                'type' => 'number',
                                'unit' => '%',
                            ),
                            11 => array (
                                'name' => $this->combatCalculations[0]->defender->getKey('weaponskill'),
                                'label' => 'Your weaponskill',
                                'value' => $this->combatCalculations[0]->defender->weaponskill,
                                'type' => 'number',
                            ),
                            12 => array (
                                'name' => $this->combatCalculations[0]->defender->getKey('parryModifiers'),
                                'label' => 'Your parry chance modifiers',
                                'value' => $this->combatCalculations[0]->defender->parryModifiers,
                                'type' => 'number',
                                'unit' => '%',
                            ),
                            13 => array (
                                'name' => 'dN',
                                'value' => 2,
                                'label' => 'Use comparison mode?',
                                'type' => 'check',
                                'checked' => false,
                                'script' => 'window.setTimeout(function() {document.forms[\'calculator-form\'].submit();},1);',
                            ),
                            14 => array (
                                'name' => 'submit',
                                'label' => '',
                                'value' => 'calculate manually',
                                'type' => 'submit',
                            ),
                        )
                    ),
                    1 => array (
                        'title' => 'Your Result',
                        'mode' => 'output',
                        'values' => array (
                            0 => array (
                                'name' => 'c0pp',
                                'label' => 'Physical Protection',
                                'value' => floor($this->combatCalculations[0]->physicalProtection()*10000)/100,
                                'type' => 'number',
                                'unit' => '%',
                            ),
                            1 => array (
                                'name' => 'c0mpc',
                                'label' => 'Corporal Protection',
                                'value' => floor($this->combatCalculations[0]->magicalProtection('corporal')*10000)/100,
                                'type' => 'number',
                                'unit' => '%',
                            ),
                            2 => array (
                                'name' => 'c0mpe',
                                'label' => 'Elemental Protection',
                                'value' => floor($this->combatCalculations[0]->magicalProtection('elemental')*10000)/100,
                                'type' => 'number',
                                'unit' => '%',
                            ),
                            3 => array (
                                'name' => 'c0mps',
                                'label' => 'Spirit Protection',
                                'value' => floor($this->combatCalculations[0]->magicalProtection('spirit')*10000)/100,
                                'type' => 'number',
                                'unit' => '%',
                            ),
                            4 => array (
                                'name' => 'c0cr',
                                'label' => 'Chance to be crit',
                                'value' => ceil($this->combatCalculations[0]->critRate()*10000)/100,
                                'type' => 'number',
                                'unit' => '%',
                            ),
                            5 => array (
                                'name' => 'c0dcmp',
                                'label' => 'Block Chance',
                                'value' => floor($defences['block']*10000)/100,
                                'type' => 'number',
                                'unit' => '%',
                            ),
                            6 => array (
                                'name' => 'c0dcms',
                                'label' => 'Parry Chance',
                                'value' => floor($defences['secondary']*10000)/100,
                                'type' => 'number',
                                'unit' => '%',
                            ),
                            7 => array (
                                'name' => 'c0dcmt',
                                'label' => 'Total Avoidance',
                                'value' => floor($defences['total']*10000)/100,
                                'type' => 'number',
                                'unit' => '%',
                            ),
                            8 => array (
                                'name' => 'a0s',
                                'label' => 'Toughness required',
                                'value' => $this->combatCalculations[0]->attacker->strength,
                                'type' => 'number',
                            ),
                            9 => array (
                                'name' => 'c0atc',
                                'label' => 'Additional armor needed to cap',
                                'value' => ceil($this->combatCalculations[0]->armorToCap()),
                                'type' => 'number',
                            ),
                            10 => array (
                                'name' => 'c0iti',
                                'label' => 'Initiative needed for Crit-immunity',
                                'value' => $this->combatCalculations[0]->initiativeToImmunity(),
                                'type' => 'number',
                            ),
                        )
                    )
                )
            );
        }

        $view = new view();
        return $view->generatePage($tankCalculator);
    }
}
?>