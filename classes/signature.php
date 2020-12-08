<?php
error_reporting(0);
ini_set('display_errors', 0);

$img = imagecreatetruecolor(1, 1);
        header('Content-type:image/gif');
imagegif($img);exit;

include $_SERVER['DOCUMENT_ROOT'] . '/ressources/GIFEncoder.class.php';
include_once $_SERVER['DOCUMENT_ROOT'] . '/ressources/dbupdate.php';

class signature {

    var $data = array();
    var $db;
    var $old = FALSE;
    var $classes = array();
    var $ids = array();

    function __construct() {
        global $db, $classes;
        $this->db = &$db;
        $this->classes = $classes;
    }

    function main() {
        $this->ids = $_GET['i'] ? $_GET['i'] : $_GET['id'];
        if ($this->ids != $_GET['i']) {
            $this->old = TRUE;
        }
        $this->getIDs();
        $this->getData();
        $frames = array('duration' => array(), 'file' => array());
        $this->buildFrames($frames);
        $this->showImage($frames);
        $this->destroyFrames($frames);
    }

    function imagecopymerge_alpha(&$dst_im, &$src_im, $dst_x, $dst_y, $src_x, $src_y, $src_w, $src_h, $pct) {
        // creating a cut resource
        $cut = imagecreatetruecolor($src_w, $src_h);

        // copying relevant section from background to the cut resource
        imagecopy($cut, $dst_im, 0, 0, $dst_x, $dst_y, $src_w, $src_h);

        // copying relevant section from watermark to the cut resource
        imagecopy($cut, $src_im, 0, 0, $src_x, $src_y, $src_w, $src_h);

        // insert cut resource to destination image
        imagecopymerge($dst_im, $cut, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
    }

    function getIDs() {
        $id = &$this->ids;
        if (!is_array($id)) {
            $this->old = TRUE;
            $a=array();
            $a[] = intval(trim($id, '" '));
            $id = $a;
            unset($a);
        }
        $tid = array();
        if ($id && is_array($id)) {
            $a = count($id);
            if ($a == 1) {
                $tid = array(
                    0 => intval($id[0]),
                    1 => intval($id[0]),
                    2 => intval($id[0]),
                    3 => intval($id[0]),
                    4 => intval($id[0]),
                    5 => intval($id[0]),
                );
            } elseif ($a == 2) {
                $tid = array(
                    0 => intval($id[0]),
                    1 => intval($id[1]),
                    2 => intval($id[0]),
                    3 => intval($id[1]),
                    4 => intval($id[0]),
                    5 => intval($id[1]),
                );
            } elseif ($a == 3) {
                $tid = array(
                    0 => intval($id[0]),
                    1 => intval($id[1]),
                    2 => intval($id[2]),
                    3 => intval($id[0]),
                    4 => intval($id[1]),
                    5 => intval($id[2]),
                );
            } elseif ($a == 4) {
                $tid = array(
                    0 => intval($id[0]),
                    1 => intval($id[1]),
                    2 => intval($id[2]),
                    3 => intval($id[3]),
                    4 => intval($id[0]),
                    5 => intval($id[1]),
                );
            } elseif ($a == 5) {
                $tid = array(
                    0 => intval($id[0]),
                    1 => intval($id[1]),
                    2 => intval($id[2]),
                    3 => intval($id[3]),
                    4 => intval($id[4]),
                    5 => intval($id[0]),
                );
            } else {
                $tid = array(
                    0 => intval($id[0]),
                    1 => intval($id[1]),
                    2 => intval($id[2]),
                    3 => intval($id[3]),
                    4 => intval($id[4]),
                    5 => intval($id[5]),
                );
            }
        }
        $this->ids = $tid;
    }

    function getData() {
        $this->data = array();
        $datas = array();
        foreach ($this->ids AS $key => $id) {
            if (!isset($datas[$id])) {
                $res = $this->db->query("SELECT * FROM signature WHERE id='" . $id . "'");
                if ($res) {
                    $datas[$id] = $res->fetch_assoc();
                    $datas[$id]['last_updated'] = $datas[$id]['last_updated'] == 0 ? 'never' : date('c', $datas[$id]['last_updated']);
                }
                if (!$datas[$id]['name']) {
                    $this->db->query("INSERT INTO signature (id,last_used,last_updated) VALUES ('" . intval($id) . "','" . time() . "','0')");
                    $datas[$id]['name'] = 'Unknown Character';
                    $datas[$id]['level'] = 1;
                    $datas[$id]['renown'] = 0;
                    $datas[$id]['career'] = 0;
                    $datas[$id]['played'] = 0;
                    $datas[$id]['last_updated'] = 'never';
                }
            }
            $this->data[$key] = $datas[$id];
        }
        unset($datas);
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/log', serialize($this->data));
    }
    function cleanPath($string) {
        return str_replace('//','/',$string);
    }
    function buildFrames(&$frames) {
        global $fonts;
        $key = md5($_SERVER['REMOTE_ADRESS'] . time() . rand(0, 199));
        $_GET['f'] = $this->cleanPath($_SERVER['DOCUMENT_ROOT'] . '/ressources/fonts/' . $fonts[intval($_GET['f'])]);
        if ($dh = scandir($_SERVER['DOCUMENT_ROOT'] . '/ressources/frames/')) {
            sort($dh);
            $races = array();
            $classes = array();
            $count = 0;
            foreach ($dh AS $dat) {
                if ($dat != "." && $dat != "..") {
                    $t = $this->makeSingleFrame($dat,$races, $classes, $this->data[$count], $_GET['f'], $_GET['p']);
                    imagegif($t, $this->cleanPath($_SERVER['DOCUMENT_ROOT'] . '/temp/' . $key . '.' . $dat));
                    unset($t);
                    $frames['file'][] = $this->cleanPath($_SERVER['DOCUMENT_ROOT'] . '/temp/' . $key . '.' . $dat);
                    $frames['duration'] [] = 175;
                    $count++;
                }
            }
        }
    }
    function time($num) {
        if($num==0) {
            return 'Not yet';
        }
        $timestring = '';
        if($num>=604800) {
            $timestring.=floor($num/604800).'weeks ';
            $num=$num%604800;
        }
        if($num>=86400) {
            $timestring.=floor($num/86400).'days ';
            $num=$num%86400;
        }
        if($num>=3600) {
            $timestring.=floor($num/3600).'hours ';
            $num=$num%3600;
        }
        if($num>=60) {
            $timestring.=floor($num/60).'min ';
            $num=$num%60;
        }
        if($num>0) {
            $timestring.=$num.'sec ';
        }
        return trim($timestring);
    }
    function makeSingleFrame($file,&$races, &$classes,&$data, $font = 0, $percent = 0) {
        if (!isset($classes[$data['career']])) {
            $classes[$data['career']] = imagecreatefrompng($this->cleanPath($_SERVER['DOCUMENT_ROOT'] . '/ressources/icons/' . str_replace(' ', '_', $this->classes[$data['career']][0]) . '.png'));
            imagecolorallocatealpha($classes[$data['career']], 0, 0, 0, 127);
        }
        if (!isset($races[$data['career']])) {
            $races[$data['career']] = imagecreatefrompng($this->cleanPath($_SERVER['DOCUMENT_ROOT'] . '/ressources/icons/' . str_replace(' ', '_', $this->classes[$data['career']][1]) . '.png'));
            imagecolorallocatealpha($races[$data['career'][1]], 0, 0, 0, 127);
        }
        $t = imagecreatefromgif($_SERVER['DOCUMENT_ROOT'] . '/ressources/frames/' . $file);
        imagealphablending($t, true);
        imagecolorallocatealpha($t, 0, 0, 0, 127);
        $white = imagecolorallocate($t, 255, 255, 255);
        imagefttext($t, 25, 0, 360, 100, $white, $font, $data['name']);
        imagefttext($t, 20, 0, 360, 130, $white, $font, 'Rank ' . $data['level']);
        imagefttext($t, 20, 0, 480, 130, $white, $font, 'Renown ' . $data['renown']);
        imagefttext($t, 15, 0, 360, 160, $white, $font, $this->classes[$data['career']][0] . ', ' . $this->classes[$data['career']][1]);
        imagefttext($t, 12, 0, 360, 180, $white, $font, 'Played: '.$this->time($data['played']));
        imagefttext($t, 6, 0, 10, 188, $white, $_SERVER['DOCUMENT_ROOT'] . '/ressources/fonts/OldNewspaperTypes.ttf', 'Picture by Dana');
        imagefttext($t, 6, 0, 10, 196, $white, $_SERVER['DOCUMENT_ROOT'] . '/ressources/fonts/OldNewspaperTypes.ttf', 'Generator by Idrinth');
        if ($this->old) {
            imagefttext($t, 10, 0, 10, 15, $white, $_SERVER['DOCUMENT_ROOT'] . '/ressources/fonts/OldNewspaperTypes.ttf', 'Your Link is not correct, please update');
        }
        $this->imagecopymerge_alpha($t, $races[$data['career']], 300, 75, 0, 0, 32, 32, 100);
        $this->imagecopymerge_alpha($t, $classes[$data['career']], 300, 125, 0, 0, 32, 32, 100);
        if ($percent) {
            $width = 700 * (20 - intval($percent) % 11) / 20;
            $height = 200 * (20 - intval($percent) % 11) / 20;
            $t2 = imagecreate($width, $height);
            imagecolorallocatealpha($t2, 0, 0, 0, 127);
            imagecopyresized($t2, $t, 0, 0, 0, 0, $width, $height, 700, 200);
            $t = $t2;
            unset($t2);
            unset($width);
            unset($height);
        }
        return $t;
    }

    function showImage(&$frames) {
        global $canGZip;
        header('Content-type:image/gif');
        $gif = new GIFEncoder(
                $frames['file'], $frames['duration'], 0, 0, 0, 0, 0, "url"
        );
        $im = $gif->GetAnimation();
        if($canGZip){
            header('Content-Encoding: gzip');
            $im=gzencode($im);
        }
        echo $im;
    }

    function destroyFrames(&$frames) {
        foreach ($frames as $value) {
            unlink($value);
        }
        $this->db->query("UPDATE signature SET last_used='" . time() . "',called=called+1 WHERE id IN ('" . implode("','",$this->ids) . "')");
    }

}
