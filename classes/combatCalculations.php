<?php
if(!defined('IDRINTH')) {
    die('No direct calls to this file allowed.');
}

require_once('attacker.php');
require_once('defender.php');

/**
 * Die Basisklasse zur Berechnung von Kampfwerten
 **/
class combatCalculations {
    var $attacker = NULL;
    var $defender = NULL;
    var $combatId = 0;
    static $version = '1.3.0';

    function __construct($attackerId = 0,$defenderId = 0) {
        $this->attacker = new attacker($attackerId);
        $this->defender = new defender($defenderId);
    $this->combatId = 10*$attackerId+$defenderId;
    }

    /**
     * Berechnet die absolute Chance kritisch zu treffen
     *
     * @return array
     **/
    public function critRate() {
        //Initiative
        $value = ((7.5*$this->attacker->effectiveRank()+50)/$this->defender->initiative/10);
        //Modifikatoren
        $value = $value+($this->attacker->critChanceBonus - $this->defender->critChanceReduction)/100;

                return $value;
    }

    /**
     * Berechnet die Abwehrchancen fÃ¼r beliebige Angriffstypen
     *
     * @param array $attackType die Art des Angriffs(als array mit stringwerten m - melee, c -cast, r - ranged)
     *
     * @return array
     **/
    function defenceChance($attackType = NULL) {
        if($attackType == NULL) {
            $attackType = $this->attacker->autoAttackType;
        }
        if($attackType == 'b') {
            $attackType = array('m','r');
        }
        if(!is_array($attackType)) {
            $attackType = array($attackType);
        }
        $defence = array ();
        $counter = 0;
        foreach($attackType as $value) {
            if($value == 'm') {
                //melee
                $attackerStat = $this->attacker->strength;
                $defenderStat = $this->defender->weaponskill;
                $attackerModifier = $this->attacker->parryStrikeThrough/100;
                $defenderModifier = $this->defender->parryModifiers/100;
            } elseif($value == 'r') {
                //ranged
                $attackerStat = $this->attacker->strength;
                $defenderStat = $this->defender->weaponskill;
                $attackerModifier = $this->attacker->parryStrikeThrough/100;
                $defenderModifier = $this->defender->parryModifiers/100;
            } else {
                //cast
                $attackerStat = $this->attacker->strength;
                $defenderStat = $this->defender->weaponskill;
                $attackerModifier = $this->attacker->parryStrikeThrough/100;
                $defenderModifier = $this->defender->parryModifiers/100;
            }

            //Block
            if($this->defender->blockValue > 0) {
                $defence[$counter]['block'] = $this->defender->blockValue/(5*$attackerStat);
                if($defence[$counter]['block'] > 0.5) {
                    $defence[$counter]['block'] = 0.5;
                }
                $defence[$counter]['block'] = $defence[$counter]['block']+($this->defender->blockModifiers-$this->attacker->blockStrikeThrough)/100;
                if($defence[$counter]['block'] < 0) {
                    $defence[$counter]['block'] = 0;
                }
            } else {
                $defence[$counter]['block'] = 0;
            }

            //Zweite Defensive
            $defence[$counter]['secondary'] = ($defenderStat/$attackerStat)*0.075;
            if($defence[$counter]['secondary'] > 0.25) {
                $defence[$counter]['secondary'] = 0.25;
            }
            $defence[$counter]['secondary'] = $defence[$counter]['secondary']+$defenderModifier-$attackerModifier;
            if($defence[$counter]['secondary'] < 0) {
                $defence[$counter]['secondary'] = 0;
            }
            $defence[$counter]['total'] = 1-(1-$defence[$counter]['block'])*(1-$defence[$counter]['secondary']);
        }
        return $defence;
    }
        /**
         * Berechnet den Rüstungsdurchstoß
         *
         * @param float $percentageIgnore die prozentuale Rüstungsminderung, welche unabhängig vom Kampfgeschick ist
         * @return float der rüstungsdurchstoß
         */
        protected function armorPenetration($percentageIgnore = 0) {
            //via weaponskill
            $armorpenetration = $this->attacker->weaponskill*
                    ($this->attacker->level/$this->attacker->effectiveRank())
                    /(7.5*$this->defender->level+50)*0.25;
            if($percentageIgnore < $this->attacker->armorPenetrationBonus) {
                $percentageIgnore = $this->attacker->armorPenetrationBonus;
            }
            $armorpenetration = $armorpenetration + ($percentageIgnore - $this->defender->penetrationReduction)/100;
            if($armorpenetration < 0) {
                $armorpenetration = 0;
            } elseif($armorpenetration > 1) {
                $armorpenetration = 1;
            }
            return $armorpenetration;
        }
        /**
         * Berechnet den Rüstungsschutz vor Rüstungsdurchstoß
         *
         * @param int $absoluteIgnore die absolute Menge an ignorierter Rüstung(z.b. Rüstungsdebuff)
         *
         * @return float the damage-reduction
         */
        protected function defenderMitigation($absoluteIgnore = 0) {
            if($absoluteIgnore < $this->attacker->armorIgnore) {
                $absoluteIgnore = $this->attacker->armorIgnore;
            }
            $armor = ($this->defender->armor - $absoluteIgnore)/($this->attacker->effectiveRank()*44)*0.4;
            return $armor;
        }
        /**
         * Berechnet den Schutz, der vor physischem Schaden besteht
         *
         * @param int $absoluteIgnore die absolute Menge an ignorierter Rüstung(z.b. Rüstungsdebuff)
         * @param float $percentageIgnore die prozentuale Rüstungsminderung, welche unabhängig vom Kampfgeschick ist
         *
         * @return float Der Schutz
         */
        function physicalProtection($absoluteIgnore = 0,$percentageIgnore = 0){
            $mitigation = $this->defenderMitigation($absoluteIgnore)*(1-$this->armorPenetration($percentageIgnore));
            if($mitigation > 0.75) {
                $mitigation = 0.75;
            }
            return $mitigation;
        }
        /**
         * Berechnet die minderung für magischen Schaden
         *
         * @param int $debuff any appliing debuff
         * @param string $resist Die art der Magie
         *
         * @return float der Schutz
         */
        function magicalProtection($resist,$debuff = 0){
            $protection = ($this->defender->{$resist.'Resistance'} - $debuff)/($this->attacker->effectiveRank()*8.4)*0.2;
            if($protection > 0.4) {
                $protection = ($protection-0.4)/3+0.4;
            }
            return $protection;
        }
        /**
         * Berechnet den dps-wert der Automatischen Angriffe
         *
         * @return float der dps-wert
         * @todo write method
         */
        function calculateAADps($attacker,$defender) {
            $dps = $attacker->weapondelay*(
                (
                    ($attacker->strength+$attacker->meleePower)/10
                    +$attacker->weaponDamagePerSecond[0]
                )
                * (1+$attacker->damageBonus)
                * (1-$defender->damageReduction)
                - ($defender->toughness+$defender->fortitude)/10
            );
            if($attacker->weaponDamagePerSecond[1] > 0) {
                //zwei einhandwaffen
                $dps = $dps+0.45*$attacker->weapondelay*(
                    (
                        ($attacker->strength+$attacker->meleePower)/10
                        +$attacker->weaponDamagePerSecond[0]
                    )
                    * (1+$attacker->damageBonus)
                    * (1-$defender->damageReduction)
                    - ($defender->toughness+$defender->fortitude)/10
                );
            }
            //in dps umwandeln
            $dps = $dps/1.5;
            return $dps;
        }
#((<mainhand dps>*<mainhand weaponspeed>)+(<mainhand weaponspeed>*(<offensive stat>+<power stat>)/10))*(1+<damage bonus>)*(1-<damage reduction>)-(<stat coefficient>*(<toughness>+fortitude>)/10) = <mainhand damage>

#((<mainhand weaponspeed>-<offhand weaponspeed>)*<offhand dps>+<offhand dps>*<offhand weaponspeed>*0.9)+(<mainhand weaponspeed>*(<offensive stat>+<power stat>)/10))*(1+<damage bonus>)*(1-<damage reduction>)-(<stat coefficient>*(<toughness>+fortitude>)/10) = <offhand damage>
        /**
         * Berechnet die noch nötige Menge an Rüstung zum Cap
         *
         * @param int $absoluteIgnore die absolute Menge an ignorierter Rüstung(z.b. Rüstungsdebuff)
         * @param float $percentageIgnore die prozentuale Rüstungsminderung, welche unabhängig vom Kampfgeschick ist
         *
         * @return int die aufgerundete Rüstungsmenge
         */
        function armorToCap($absoluteIgnore = 0,$percentageIgnore = 0) {
            return ((0.75*$this->attacker->effectiveRank()*110+$absoluteIgnore)/(1-$this->armorPenetration($percentageIgnore))-$this->defender->armor);
        }
        function initiativeToImmunity() {
            $modifier = ($this->attacker->critChanceBonus - $this->defender->critChanceReduction)/100;
            if($modifier >= 0) {
                return '&#8734;';
            } else {
                $initiative = ceil(7.5*$this->attacker->effectiveRank()+50)/($this->defender->critChanceReduction - $this->attacker->critChanceBonus)*10-$this->defender->initiative;
                if($initiative > 0) {
                    return ceil($initiative);
                } else {
                    return floor($initiative);
                }
            }
        }
}
?>