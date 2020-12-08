<?php
if(!defined('IDRINTH')) {
    die('No direct calls to this file allowed.');
}
error_reporting(-1);
ini_set("display_errors","1");
require_once('combatCalculations.php');

/**
 * Die Klasse, welche die Berechnung von Werten vornimmt
 **/
class ajax{
  var $combatCalcResults = array();
  protected function getComparison($name,$id) {
    if($id==0) {
      return 'none';
    }
    if($this->combatCalcResults[$id][$name]<$this->combatCalcResults[0][$name]) {
      return '-1';
    } elseif($this->combatCalcResults[$id][$name]>$this->combatCalcResults[0][$name]) {
      return '1';
    } else {
      return '0';
    }
  }
  protected function getCombatResults($name,$id,$value) {
    $this->combatCalcResults[$id][$name]=$value;
    return '<result compare="'.$this->getComparison($name,$id).'" name="c'.$id.$name.'">'.$value.'</result>';
  }
  public function getXML() {
    $id = session_id();
    $xml = '<?xml version="1.0" encoding="utf-8" standalone="yes"?>'."\n".'<values>'."\n";
    $valChanged = array(0 => array('pos' => '','num' => 0,'val'=>''));
    $shared = FALSE;
    unset($_REQUEST['asXML']);
    if(isset($_REQUEST['shared'])&&$_REQUEST['shared']==1) {
        $shared = true;
    }
    unset($_REQUEST['shared']);
    //moving changed values in
    foreach($_REQUEST as $name => $value) {
      $valChanged[0] = array(
          'pos' => substr($name,0,1),
          'num' => substr($name,1,1),
          'val' => substr($name,2),
          );
    };
    if($shared) {
        $valChanged[1] = $valChanged[0];
        $valChanged[1]['num'] = 1;
        $valChanged[0]['num'] = 0;
        $_REQUEST[$valChanged[0]['pos'].'1'.$valChanged[0]['val']] = $_REQUEST[$valChanged[0]['pos'].'0'.$valChanged[0]['val']];
        $_REQUEST[$valChanged[0]['pos'].'1'.$valChanged[0]['val']] = $_REQUEST[$valChanged[0]['pos'].'0'.$valChanged[0]['val']];
    }
    $combatCalc = array(
        new combatCalculations(0,0),
        new combatCalculations(1,0),
        new combatCalculations(0,1),
        new combatCalculations(1,1)
    );
    foreach ($valChanged as $changed) {
        if($changed['pos'] == 'a') {
            //attacker value changed
          foreach ($combatCalc as $key => $cc) {
            switch($changed['val']) {
              case 'l':
              default:
                //Level or none -> return all
                $xml .= $this->getCombatResults('mpe', $cc->combatId, floor($cc->magicalProtection('elemental')*10000)/100);
                $xml .= $this->getCombatResults('mps', $cc->combatId, floor($cc->magicalProtection('spirit')*10000)/100);
                $xml .= $this->getCombatResults('mpc', $cc->combatId, floor($cc->magicalProtection('corporal')*10000)/100);
                $xml .= $this->getCombatResults('cr', $cc->combatId, floor($cc->critRate()*10000)/100);
                $xml .= $this->getCombatResults('iti', $cc->combatId, $cc->initiativeToImmunity());
                $xml .= $this->getCombatResults('pp', $cc->combatId, floor($cc->physicalProtection()*10000)/100);
                $support = $cc->armorToCap();
                $xml .= $this->getCombatResults('atc', $cc->combatId, $support>=0?ceil($support):floor($support));
                $support = $cc->defenceChance('m');
                $xml .= $this->getCombatResults('dcms', $cc->combatId, (floor($support[0]['secondary']*10000)/100));
                $xml .= $this->getCombatResults('dcmt', $cc->combatId, (floor($support[0]['total']*10000)/100));
                $xml .= $this->getCombatResults('dcmp', $cc->combatId, (floor($support[0]['block']*10000)/100));
                $xml .= '<result name="a'.$changed['num'].'s">'.$cc->attacker->strength.'</result>';
            }
          }
        } else {
            //defender value changed
          foreach ($combatCalc as $key => $cc) {
            switch($changed['val']) {
              case 'pm':
              case 'ws':
              case 'bm':
              case 'bv':
                //parry and weaponskill; block and blockvalue
                $support = $cc->defenceChance('m');
                $xml .= $this->getCombatResults('dcms', $cc->combatId, (floor($support[0]['secondary']*10000)/100));
                $xml .= $this->getCombatResults('dcmt', $cc->combatId, (floor($support[0]['total']*10000)/100));
                $xml .= $this->getCombatResults('dcmp', $cc->combatId, (floor($support[0]['block']*10000)/100));
                break;
              case 'pr':
              case 'a':
                //armor and armor penetration
                $xml .= $this->getCombatResults('pp', $cc->combatId, floor($cc->physicalProtection()*10000)/100);
                $support = $cc->armorToCap();
                $xml .= $this->getCombatResults('atc', $cc->combatId, $support>=0?ceil($support):floor($support));
                break;
              case 'i':
              case 'ccr':
                //initiative and crit chance reduction
                $xml .= $this->getCombatResults('cr', $cc->combatId, floor($cc->critRate()*10000)/100);
                $xml .= $this->getCombatResults('iti', $cc->combatId, $cc->initiativeToImmunity());
                break;
              case 'er':
                //elemental resist
                $xml .= $this->getCombatResults('mpe', $cc->combatId, floor($cc->magicalProtection('elemental')*10000)/100);
                break;
              case 'sr':
                //spirit resist
                $xml .= $this->getCombatResults('mps', $cc->combatId, floor($cc->magicalProtection('spirit')*10000)/100);
                break;
              case 'cr':
                //corporeal resist
                $xml .= $this->getCombatResults('mpc', $cc->combatId, floor($cc->magicalProtection('corporal')*10000)/100);
                break;
              case 'l':
              default:
                //Level or none -> return all
                $xml .= $this->getCombatResults('mpe', $cc->combatId, floor($cc->magicalProtection('elemental')*10000)/100);
                $xml .= $this->getCombatResults('mps', $cc->combatId, floor($cc->magicalProtection('spirit')*10000)/100);
                $xml .= $this->getCombatResults('mpc', $cc->combatId, floor($cc->magicalProtection('corporal')*10000)/100);
                $xml .= $this->getCombatResults('cr', $cc->combatId, floor($cc->critRate()*10000)/100);
                $xml .= $this->getCombatResults('iti', $cc->combatId, $cc->initiativeToImmunity());
                $xml .= $this->getCombatResults('pp', $cc->combatId, floor($cc->physicalProtection()*10000)/100);
                $support = $cc->armorToCap();
                $xml .= $this->getCombatResults('atc', $cc->combatId, $support>=0?ceil($support):floor($support));
                $support = $cc->defenceChance('m');
                $xml .= $this->getCombatResults('dcms', $cc->combatId, (floor($support[0]['secondary']*10000)/100));
                $xml .= $this->getCombatResults('dcmt', $cc->combatId, (floor($support[0]['total']*10000)/100));
                $xml .= $this->getCombatResults('dcmp', $cc->combatId, (floor($support[0]['block']*10000)/100));
                break;
            }
          }
        }
    }
    return $xml."\n</values>";
  }
}
?>