<?php
/**
 * calculates ability chains
 *
 * This file contains a content class, that allows players to calculate a chain
 * of abilities for the game WAR
 *
 * PHP versions 5
 *
 * @category   Calculator
 * @package    Idrinth
 * @subpackage IdrinthTools
 * @author     Björn Büttner <webmaster@idrinth.de>
 * @copyright  2013-2113 Björn Büttner
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link       https://idrinth.de
 * @since      1.8.0
 */

/**
 * calculates ability chains
 *
 * @category   Calculator
 * @package    Idrinth
 * @subpackage IdrinthTools
 * @author     Björn Büttner <webmaster@idrinth.de>
 * @copyright  2013-2113 Björn Büttner
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @link       https://idrinth.de
 * @since      1.8.0
 */
class abilityChain {
    var $abilities = array();
    function manageSession() {
        $id = session_id();
        foreach($_SESSION[$id]['ability-chain'] as $key => $value) {
          $temp[$key] = $value;
        }
        foreach($_REQUEST as $key => $value) {
          if(strlen($value)>0) {
            if($key=='max') {
              $temp[$key] = 2*$value;
            } else {
              $temp[$key] = $value;
            }
          }
        }
        $_SESSION[$id]['ability-chain'] = $temp;
        unset($_REQUEST);
  }
  public function __construct() {
    set_time_limit(90);
    $id = session_id();
    $this->manageSession();
    if($_SESSION[$id]['ability-chain']['max']<60) {
      $_SESSION[$id]['ability-chain']['max']==60;
    } elseif($_SESSION[$id]['ability-chain']['max']>360) {
      $_SESSION[$id]['ability-chain']['max']=360;
    }
    if($_SESSION[$id]['ability-chain']['ma']>12) {
      $_SESSION[$id]['ability-chain']['ma']=12;
    }
    if(isset($_SESSION[$id]['ability-chain']['ma'])&& is_numeric($_SESSION[$id]['ability-chain']['ma']) && $_SESSION[$id]['ability-chain']['ma']>0 && $_SESSION[$id]['ability-chain']['ma']=ceil($_SESSION[$id]['ability-chain']['ma'])) {
      for($counter=0;$counter < $_SESSION[$id]['ability-chain']['ma'];$counter++) {
        if(isset($_SESSION[$id]['ability-chain']['a'.$counter.'n'])) {
          $this->abilities[$counter]['name'] = strip_tags($_SESSION[$id]['ability-chain']['a'.$counter.'n']);
          if(
            isset($_SESSION[$id]['ability-chain']['a'.$counter.'d']) &&
            is_numeric($_SESSION[$id]['ability-chain']['a'.$counter.'d']) &&
            $_SESSION[$id]['ability-chain']['a'.$counter.'d'] >0 &&
            $_SESSION[$id]['ability-chain']['a'.$counter.'d'] < 15000 &&
            $_SESSION[$id]['ability-chain']['a'.$counter.'d'] = ceil($_SESSION[$id]['ability-chain']['a'.$counter.'d'])
          ) {
            $this->abilities[$counter]['damage']=$_SESSION[$id]['ability-chain']['a'.$counter.'d'];
          }
          if(
            isset($_SESSION[$id]['ability-chain']['a'.$counter.'cd']) &&
            is_numeric($_SESSION[$id]['ability-chain']['a'.$counter.'cd']) &&
            $_SESSION[$id]['ability-chain']['a'.$counter.'cd'] >0 &&
            $_SESSION[$id]['ability-chain']['a'.$counter.'cd'] < 300
          ) {
            $this->abilities[$counter]['cooldown']=$_SESSION[$id]['ability-chain']['a'.$counter.'cd'];
            if($this->abilities[$counter]['cooldown']<1.5) {
              $this->abilities[$counter]['cooldown']=1.5;
            }
          }
          if(
            isset($_SESSION[$id]['ability-chain']['a'.$counter.'du']) &&
            is_numeric($_SESSION[$id]['ability-chain']['a'.$counter.'du']) &&
            $_SESSION[$id]['ability-chain']['a'.$counter.'du'] >0 &&
            $_SESSION[$id]['ability-chain']['a'.$counter.'du'] < 300
          ) {
            $this->abilities[$counter]['duration']=$_SESSION[$id]['ability-chain']['a'.$counter.'du'];
            if($this->abilities[$counter]['duration']>1.4) {
              $this->abilities[$counter]['cooldown']=$this->abilities[$counter]['cooldown']-$this->abilities[$counter]['duration'];
              if($this->abilities[$counter]['cooldown']<0) {
                $this->abilities[$counter]['cooldown']=0;
              }
            }
          }
        }
      }
    }
  }
  function getPage() {
    $id = session_id();
    $abilities = array(
        'damageTotal' => 0,
        'timeTotal' => 0,
        'times' => '',
    );
    $t = 0;
    $cooldowns=array();
    $unused=array();
    foreach($this->abilities as $key => $v) {
      $unused[$key]=$key;
    }
    $lastStep=0;
    if(count($unused)>0) {
      while((count($cooldowns)>0||$t<20||count($unused)>0)&&$t<$_SESSION[$id]['ability-chain']['max'] && memory_get_usage(true)<7500000) {
        $found = FALSE;
        $first_key = 0;
        $first_value = array();
        foreach($this->abilities as $key => $value) {
          if(!isset($cooldowns[$key])&&!$found) {
            $first_key = $key;
            $found = TRUE;
          }
        }
        if($this->abilities[$first_key]['cooldown']>0) {
          $cooldowns[$first_key]=($this->abilities[$first_key]['cooldown']+$this->abilities[$first_key]['duration'])*2;
        }
        if(isset($unused[$first_key])) {
          unset($unused[$first_key]);
        }
        $step=3;
        if($this->abilities[$first_key]['duration']>1.5) {
          $step=$this->abilities[$first_key]['duration']*2;
        }
        $remove = array();
        foreach($cooldowns as $key => &$cooldown) {
          $cooldown = $cooldown-$step;
          if($cooldown<=0) {
            $remove[]=$key;
          }
        }
        foreach($remove as $value) {
          unset($cooldowns[$value]);
        }
        unset($remove);
        $abilities['damageTotal'] = $abilities['damageTotal']+$this->abilities[$first_key]['damage'];
        $abilities['timeTotal'] = $abilities['timeTotal']+$step;
        $abilities['times'].= '<tr><td>'.($t/2).'s</td><td>'.$this->abilities[$first_key]['name'].'<div class="onHover"><strong>'.$this->abilities[$first_key]['name'].'</strong><br />'.str_replace(',','<br />',trim(
                ($this->abilities[$first_key]['damage']?'DMG:'.$this->abilities[$first_key]['damage'].',':'')
                .($this->abilities[$first_key]['cooldown']?'CD:'.$this->abilities[$first_key]['cooldown'].'s,':'')
                .($this->abilities[$first_key]['duration']?'CT:'.$this->abilities[$first_key]['duration'].'s':''),','))
                .'</div></td></tr>';
        $t=$t+$step;
        $lastStep=$step;
        unset($step);
        unset($first_key);
      }
    }
    $abilities['timeTotal'] = $abilities['timeTotal']-$lastStep;
    unset($unused);
    $form = '<h2>Abilities</h2><form method="post">';
    foreach($this->abilities as $c => $value) {
        $form .= '<fieldset><legend>Ability '.($c+1).'</legend>'
                . '<div class="wrapper"><label>Name</label><input type="text" name="a'.$c.'n" value="'.$_SESSION[$id]['ability-chain']['a'.$c.'n'].'"></div>'
                . '<div class="wrapper"><label>Damage</label><input min="0" max="15000" type="number" name="a'.$c.'d" value="'.$_SESSION[$id]['ability-chain']['a'.$c.'d'].'"></div>'
                . '<div class="wrapper"><label>Cast time(CT)</label><input min="0" step="0.5" max="300" type="number" name="a'.$c.'du" value="'.$_SESSION[$id]['ability-chain']['a'.$c.'du'].'"></div>'
                . '<div class="wrapper"><label>Cooldown(CD)</label><input min="0" step="0.5" max="300" type="number" name="a'.$c.'cd" value="'.$_SESSION[$id]['ability-chain']['a'.$c.'cd'].'"></div>'
                . '</fieldset>';
    }
    $ac = count($this->abilities);
    unset($this->abilities);
    $page = '<p>No abilities selected yet</p><div><div>';
    if(strlen($abilities['times'])>0) {
      $page = '<div class="wrapper cols-2"><div class="floating-col"><h2>Ability chain</h2>'
              . '<p>Damage: '.$abilities['damageTotal'].'<br />'
              . 'Time: '.($abilities['timeTotal']/2).'s (Next: '.(($abilities['timeTotal']+$lastStep)/2).'s)<br />'
              . 'DPS: '.(floor(200*$abilities['damageTotal']/($abilities['timeTotal']+$lastStep))/100).'</p>'
              . '<table style="width:100%;text-align:right;"><tr><th>Time</th><th>Name</th></tr>'.$abilities['times'];
      $page .= '</table></div><div class="floating-col">';
      unset($abilities);
      unset($lastStep);
    }
    $form .= '<fieldset><legend>Ability '.($ac+1).'</legend>'
                . '<div class="wrapper"><label>Name</label><input type="text" name="a'.$ac.'n" value=""></div>'
                . '<div class="wrapper"><label>Damage</label><input title="0-15000" min="0" max="15000" type="number" name="a'.$ac.'d" value="0"></div>'
                . '<div class="wrapper"><label>Cast time(CT)</label><input min="0" step="0.5" max="300" title="0-300s" type="number" name="a'.$ac.'du" value="0"></div>'
                . '<div class="wrapper"><label>Cooldown(CD)</label><input min="0" step="0.5" max="300" title="0-300s" type="number" name="a'.$ac.'cd" value="0"></div>'
                . '</fieldset>'
            . '<fieldset><legend>General</legend>'
            . '<input type="hidden" name="ma" value="'.($ac+1).'">'
            . '<div class="wrapper"><label>Seconds to show</label><input title="15-180s" type="number" min="15" max="180" name="max" value="'.(intval($_SESSION[$id]['ability-chain']['max'])/2).'"></div>'
            . '<input type="submit" value="calculate"></div></div>';
    $page .= $form;
    unset($form);
    $view  = new view();
    return $view->generateStaticPage($page,'Ability Chains');
  }
}
?>