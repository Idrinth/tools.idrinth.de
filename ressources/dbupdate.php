<?php
/*
if(!isset($_SERVER['DOCUMENT_ROOT'])||!$_SERVER['DOCUMENT_ROOT']||$_SERVER['DOCUMENT_ROOT']=='/') {
  $_SERVER['DOCUMENT_ROOT']=preg_replace('/\/ressources\/?$/','',__DIR__);
}
include_once $_SERVER['DOCUMENT_ROOT'].'/ressources/definitions.php';
function makeAscii($string) {
    $replace = array(
        'ä' => 'ae',
        'á' => 'a',
        'à' => 'a',
        'â' => 'a',
        'é' => 'e',
        'è' => 'e',
        'ê' => 'e',
        'î' => 'i',
        'í' => 'i',
        'ì' => 'i',
        'ö' => 'oe',
        'ó' => 'o',
        'ô' => 'o',
        'ò' => 'o',
        'ü' => 'ue',
        'ú' => 'u',
        'ù' => 'u',
        'û' => 'u',
        '/' => '-',
        '.' => '-',
        '_' => '-',
        '?' => '-',
        '!' => '-',
        ':' => '-',
        '²' => '2',
        '³' => '3',
        'ß' => 'ss',
        '<' => '-',
        '>' => '-',
        '|' => '-',
        ' ' => '-',
        '   ' => '-',
        '\\' => '-',
        '€' => '-euro-',
        '$' => '-dollar-',
        '@' => '-at-',
    );
    $string = mb_strtolower($string);
    foreach($replace AS $search => $replace) {
        $string = str_replace($search,$replace,$string);
    }
    $string = preg_replace('#-+$|^-+|[^a-z0-0\-]#','',$string);
    return preg_replace('#-{2}#','-',$string);
}
function parseElement($id) {
  global $cn2id;
  $file = file_get_contents('http://www.returnofreckoning.com/armory.php?character_id='.$id.'&character_name=');
  $start = strpos($file,'<div id="armory-stats">')+23;
  $file = substr($file,$start,strpos($file,'<div id="amory-stats-bottom">')-$start);
  unset($start);
  $file = preg_replace('#<(img|span)([^>]*|"[^"]*")*>#','',$file);
  $file = str_replace('</span>','',$file);
  $file = preg_replace('#<br\s*\/?>#','',$file);
  $file = preg_replace("#( |\t|\n)[ \t\n]+#",' ',$file);
  $file = str_replace('> <','><',$file);
  $file = trim($file);
  preg_match_all('#<div id="ast-(name|career|level|renown|played)">([A-Za-z 0-9]*)<\/div>#',$file,$matches);
  unset($file);
  $values = array();
  $hasContent = FALSE;
  foreach($matches[1] AS $key => $value) {
    $t = '';
    if($value=='level'||$value=='renown') {
      $t=preg_replace('#[^0-9]#','',$matches[2][$key]);
    } elseif($value=='played') {
        preg_match_all('#([0-9]+)([dhms])#',$matches[2][$key],$tm);
        $t = 0;
        header('ID-'.$id.': '.  serialize($tm));
        $factors=array('d'=>86400,'h'=>3600,'m'=>60,'s'=>1);
        if(is_array($tm[1])&&count($tm[1])>0){
            foreach($tm[1] AS $key => $val) {
                $t = $t+$factors[$tm[2][$key]]*$val;
            }
        }
    } else {
      $t=str_replace('Ruen Priest','Rune Priest',trim($matches[2][$key]));
    }
    if($t) {
      $hasContent=TRUE;
      $values[trim($value)]=$t;
    }
    unset($t);
  }
  if($hasContent) {
    file_put_contents($_SERVER['DOCUMENT_ROOT'].'/log',$values['career']);
    return "UPDATE signature SET played='".(isset($values['played'])?$values['played']:0)."',level='".$values['level']."',renown='".$values['renown']."',name='".$values['name']."',career='".intval($cn2id[$values['career']])."',last_updated='".time()."' WHERE id='".$id."'";
  } else {
    return "DELETE FROM signature WHERE id='".$id."'";
  }
}
$res = $db->query("SELECT id FROM signature WHERE (last_used>".(time()-21600)." AND last_updated<".(time()-1800).") OR career=0");
while($item = $res->fetch_assoc()) {
  header('Signature-'.$item['id'].':'.serialize($item));
  $q = parseElement($item['id']);
  $db->query($q);
}
$res->free_result();
$res = $db->query("SELECT id,display FROM user WHERE slug='' OR ISNULL(slug) OR slug=id ORDER BY id ASC LIMIT 0,".(PHP_SAPI=='cli'?'1':'')."5");
$names = array();
while($item = $res->fetch_assoc()) {
  header('User-'.$item['id'].':'.serialize($item));
    $name = makeAscii($item['display']);
    $dr;
    $counter =-1;
    if(isset($names[$name])) {
        $counter = $names[$name];
    }
    do {
        $counter++;
        $dr = $db->query("SELECT id FROM user WHERE slug='".$name.($counter>0?'-'.dechex($counter):'')."'");
    } while($dr->num_rows>0);
    $names[$name]=$counter;
    $db->query("UPDATE user SET slug='".$name.($counter>0?'-'.dechex($counter):'')."' WHERE id=".$item['id']);
}
unset($names);
$res->free_result();*/