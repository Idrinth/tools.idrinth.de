<?php
if(!defined('IDRINTH')) {
    die('No direct calls to this file allowed.');
} elseif(IDRINTH) {
    die('dreckiger Cheater ;P !');
}

require_once('inputValidation.php');
require_once('characterFunctions.php');

/**
 * Die Basisklasse zur mit den Werten eines Characters
 **/
class defender extends characterFunctions{
    // Ausgangswerte
    var $level;
    var $armor;
        var $penetrationReduction;
    var $spiritResistance;
    var $elementalResistance;
    var $corporalResistance;
    var $initiative;
        var $critChanceReduction;
            var $critDamageReductions;
        var $avoidanceModifiers;
    var $blockValue;
        var $blockModifiers;
    var $weaponskill;
        var $parryModifiers;
    var $willpower;
        var $disruptModifiers;
    var $toughness;
        var $fortitude;
    /**
     * Initialisiert die Werte der Klasse mit Werten aus $_REQUEST, nachdem diese überprüft wurden
     *
     * @param int $defenderNumber Gibt die Erkennungsnummer dieser Klasse in den Übergabewerten an
     **/
    function __construct($defenderNumber = 0) {
        parent::__construct();
        $searchArray = $this->getSearchArray();
        $validator = new inputValidation();
        $this->keys = array (
            'primaryAttributes' => array (
                'd'.$defenderNumber.'i' => 'initiative',
                'd'.$defenderNumber.'wp' => 'willpower',
                'd'.$defenderNumber.'ws' => 'weaponskill',
                'd'.$defenderNumber.'t' => 'toughness',
            ),
            'secondaryAttributes' => array (
                'd'.$defenderNumber.'bv' => 'blockValue',
                'd'.$defenderNumber.'sr' => 'spiritResistance',
                'd'.$defenderNumber.'er' => 'elementalResistance',
                'd'.$defenderNumber.'cr' => 'corporalResistance',
                'd'.$defenderNumber.'f' => 'fortitude',
            ),
            'percentages' => array (
                'd'.$defenderNumber.'pr' => 'penetrationReduction',
                'd'.$defenderNumber.'ccr' => 'critChanceReduction',
                'd'.$defenderNumber.'cdr' => 'critDamageReductions',
                'd'.$defenderNumber.'am' => 'avoidanceModifiers',
                'd'.$defenderNumber.'bm' => 'blockModifiers',
                'd'.$defenderNumber.'pm' => 'parryModifiers',
                'd'.$defenderNumber.'dm' => 'disruptModifiers',
            ),
            'other' => array (
                'd'.$defenderNumber.'l' => 'level',
                'd'.$defenderNumber.'a' => 'armor',
            )
        );
        // Getting Values that have been passed by the user
        foreach($this->keys['primaryAttributes'] as $key => $value) {
            if(is_array($searchArray) && array_key_exists($key, $searchArray) && !empty($searchArray[$key])) {
                list($this->{$value},$this->error[$key]) = $validator->testInt($searchArray[$key],1,2000);
            } else {
                $this->{$value} = 1;
            }
        }
        foreach($this->keys['secondaryAttributes'] as $key => $value) {
            if(is_array($searchArray) && array_key_exists($key, $searchArray) && !empty($searchArray[$key])) {
                list($this->{$value},$this->error[$key]) = $validator->testInt($searchArray[$key],0,1000);
            } else {
                $this->{$value} = 0;
            }
        }
        foreach($this->keys['percentages'] as $key => $value) {
            if(is_array($searchArray) && array_key_exists($key, $searchArray) && !empty($searchArray[$key])) {
                list($this->{$value},$this->error[$key]) = $validator->testFloat($searchArray[$key],0,100);
            }  else {
                $this->{$value} = 0;
            }
        }

        if(is_array($searchArray) && array_key_exists('d'.$defenderNumber.'l', $searchArray) && !empty($searchArray['d'.$defenderNumber.'l'])) {
            list($this->level,$this->error['d'.$defenderNumber.'l']) = $validator->testFloat($searchArray['d'.$defenderNumber.'l'],1,50);
        } else{
            $this->level = 40;
        }
        if(is_array($searchArray) && array_key_exists('d'.$defenderNumber.'a', $searchArray) && !empty($searchArray['d'.$defenderNumber.'a'])) {
            list($this->armor,$this->error['d'.$defenderNumber.'a']) = $validator->testFloat($searchArray['d'.$defenderNumber.'a'],1,7500);
        } else {
            $this->armor = 0;
        }
    }
}
?>