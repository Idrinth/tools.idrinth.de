<?php
if(!defined('IDRINTH')) {
    die('No direct calls to this file allowed.');
}

require_once('inputValidation.php');
require_once('characterFunctions.php');

/**
 * Die Basisklasse zur mit den Werten eines Characters
 **/
class attacker extends characterFunctions{
    // Ausgangswerte
    var $level;
    var $renownRank;
    var $weaponDamagePerSecond = array();
    var $strength;
        var $meleePower;
    var $intelligence;
        var $magicPower;
    var $ballisticSkill;
        var $rangedPower;
    var $weaponskill;
    var $damageBonus;
    var $critChanceBonus;
        var $critDamageBonus;
    var $armorPenetrationBonus;
    var $armorIgnore;
    var $autoAttack;
        var $weaponDelay;
        var $haste;
        var $autoAttackType;
    var $parryStrikeThrough;
    var $avoidanceStrikeThrough;
    var $disruptStrikeThrough;
    var $blockStrikeThrough;

    /**
     * Initialisiert die Werte der Klasse mit Werten aus $_REQUEST, nachdem diese überprüft wurden
     *
     * @param int $attackerNumber Gibt die Erkennungsnummer dieser Klasse in den Übergabewerten an
     **/
    function __construct($attackerNumber = 0) {
        parent::__construct();
        $searchArray = $this->getSearchArray();
        $validator = new inputValidation();
        $this->keys = array (
            'primaryAttributes' => array (
                'a'.$attackerNumber.'s' => 'strength',
                'a'.$attackerNumber.'i' => 'intelligence',
                'a'.$attackerNumber.'bs' => 'ballisticSkill',
                'a'.$attackerNumber.'ws' => 'weaponskill',
            ),
            'secondaryAttributes' => array (
                'a'.$attackerNumber.'mep' => 'meleePower',
                'a'.$attackerNumber.'map' => 'magicPower',
                'a'.$attackerNumber.'rp' => 'rangedPower',
            ),
            'percentages' => array (
                'a'.$attackerNumber.'db' => 'damageBonus',
                'a'.$attackerNumber.'ccb' => 'critChanceBonus',
                'a'.$attackerNumber.'pst' => 'parryStrikeThrough',
                'a'.$attackerNumber.'ast' => 'avoidanceStrikeThrough',
                'a'.$attackerNumber.'dst' => 'disruptStrikeThrough',
                'a'.$attackerNumber.'bst' => 'blockStrikeThrough',
            ),
            'other' => array (
                'a'.$attackerNumber.'l' => 'level',
                'a'.$attackerNumber.'rr' => 'renownRank',
                'a'.$attackerNumber.'wdps0' => 'weaponDamagePerSecond[0]',
                'a'.$attackerNumber.'wdps1' => 'weaponDamagePerSecond[1]',
                'a'.$attackerNumber.'cdb' => 'critDamageBonus',
                'a'.$attackerNumber.'aa' => 'autoAttack',
                'a'.$attackerNumber.'wd' => 'weaponDelay',
                'a'.$attackerNumber.'h' => 'haste',
                'a'.$attackerNumber.'aat' => 'autoAttackType',
            )
        );
        // Getting Values that have been passed by the user
        if(is_array($searchArray) && array_key_exists('a'.$attackerNumber.'l', $searchArray) && !empty($searchArray['a'.$attackerNumber.'l'])) {
            list($this->level,$this->error['a'.$attackerNumber.'l']) = $validator->testInt($searchArray['a'.$attackerNumber.'l'],1,50);
        } else{
            $this->level = 40;
        }
        if(is_array($searchArray) && array_key_exists('a'.$attackerNumber.'rr', $searchArray) && !empty($searchArray['a'.$attackerNumber.'rr'])) {
            list($this->renownRank,$this->error['a'.$attackerNumber.'rr']) = $validator->testInt($searchArray['a'.$attackerNumber.'rr'],0,100);
        } else {
            $this->renownRank = 0;
        }
        if(is_array($searchArray) && array_key_exists('a'.$attackerNumber.'wdps0', $searchArray) && !empty($searchArray['a'.$attackerNumber.'wdps0'])) {
            list($this->weaponDamagePerSecond[0],$this->error['a'.$attackerNumber.'wdps0']) = $validator->testFloat($searchArray['a'.$attackerNumber.'wdps0'],1,150);
        } else {
            $this->weaponDamagePerSecond[0] = 1;
        }
        if(is_array($searchArray) && array_key_exists('a'.$attackerNumber.'wdps1', $searchArray) && !empty($searchArray['a'.$attackerNumber.'wdps1'])) {
            list($this->weaponDamagePerSecond[1],$this->error['a'.$attackerNumber.'wdps1']) = $validator->testFloat($searchArray['a'.$attackerNumber.'wdps1'],0,100);
        } else {
            $this->weaponDamagePerSecond[1] = 0;
        }
        if(is_array($searchArray) && array_key_exists('a'.$attackerNumber.'cdb', $searchArray) && !empty($searchArray['a'.$attackerNumber.'cdb'])) {
            list($this->critDamageBonus,$this->error['a'.$attackerNumber.'cdb']) = $validator->testInt($searchArray['a'.$attackerNumber.'cdb'],0,500);
        } else {
            $this->critDamageBonus = 0;
        }
        if(is_array($searchArray) && array_key_exists('a'.$attackerNumber.'ccb', $searchArray) && !empty($searchArray['a'.$attackerNumber.'ccb'])) {
            list($this->critChanceBonus,$this->error['a'.$attackerNumber.'ccb']) = $validator->testInt($searchArray['a'.$attackerNumber.'ccb'],0,500);
        } else {
            $this->critDamageBonus = 0;
        }
        if(is_array($searchArray) && array_key_exists('a'.$attackerNumber.'aa', $searchArray) && !empty($searchArray['a'.$attackerNumber.'aa'])) {
            list($this->autoAttack,$this->error['a'.$attackerNumber.'aa']) = $validator->testInt($searchArray['a'.$attackerNumber.'aa'],0,1);
        } else {
            $this->autoAttack = 0;
        }
        if(is_array($searchArray) && array_key_exists('a'.$attackerNumber.'aat', $searchArray) && !empty($searchArray['a'.$attackerNumber.'aat'])) {
            list($this->autoAttackType,$this->error['a'.$attackerNumber.'aat']) = $validator->testString($searchArray['a'.$attackerNumber.'aat'],array('m','r','b'));
            switch($this->autoAttackType) {
                case 'ma':
                    $this->autoAttackType = array (0 => 'm');
                break;
                case 'r':
                    $this->autoAttackType = array (0 => 'r');
                break;
                case 'b':
                    $this->autoAttackType = array ('m','r');
                break;
                default:
                    $this->autoAttackType = array ('m');
            }
        } else {
            $this->autoAttackType = array('m');
        }
        if(is_array($searchArray) && array_key_exists('a'.$attackerNumber.'wd', $searchArray) && !empty($searchArray['a'.$attackerNumber.'wd'])) {
            list($this->weaponDelay,$this->error['a'.$attackerNumber.'wd']) = $validator->testFloat($searchArray['a'.$attackerNumber.'wd'],1,5);
        } else {
            $this->weaponDelay = 0;
        }
        if(is_array($searchArray) && array_key_exists('a'.$attackerNumber.'h', $searchArray) && !empty($searchArray['a'.$attackerNumber.'h'])) {
            list($this->haste,$this->error['a'.$attackerNumber.'h']) = $validator->testFloat($searchArray['a'.$attackerNumber.'h'],0,250);
        } else {
            $this->haste = 0;
        }

        foreach($this->keys['primaryAttributes'] as $key => $value) {
            if(is_array($searchArray) && array_key_exists($key, $searchArray) && !empty($searchArray[$key])) {
                list($this->{$value},$this->error[$key]) = $validator->testInt($searchArray[$key],1,2000);
            } else {
                $this->{$value} = $this->getNpcAttribute();
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
    }
}
?>