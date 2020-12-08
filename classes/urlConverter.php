<?php
if(!defined('IDRINTH')) {
    die('No direct calls to this file allowed.');
}

require_once('inputValidation.php');

/**
 * Konvertierteine alte URL in eine Neue url für den Tankrechner
 */
class urlConverter {
    var $keys = array();
    var $keyTranslate = array();

    /**
     * Filling in the base-values
     */
    function __construct() {
        $this->keys = array('el','l','a','ap','er','sr','cr','i','cm','b','bm','w','pm');
        $this->keyTranslate = array (
            'el' => 'a0l',
            'l' => 'd0l',
            'a' => 'd0a',
            'ap' => 'd0ap',
            'er' => 'd0er',
            'sr' => 'd0sr',
            'cr' => 'd0cr',
            'i' => 'd0i',
            'cm' => 'd0cm',
            'b' => 'd0b',
            'bm' => 'd0bm',
            'w' => 'd0ws',
            'pm' => 'd0pm'
        );
    }

    /**
     * gibt eine Ausgabe aus
     */
    public function converter() {
        $view = new view();
        if(array_key_exists('oldUrl', $_REQUEST) && !empty($_REQUEST['oldUrl'])) {
            $content = $this->buildNewLink();
            $content .=file_get_contents('templates/converterForm.htm');
        } else {
            $content = file_get_contents('templates/converterForm.htm');
            $_REQUEST['oldUrl'] = '';
        }
        $content = str_replace('###URLINPUTFIELD###', $_REQUEST['oldUrl'], $content);
        return $view->generateStaticPage($content, 'url-converter');
    }

    /**
     * Erstellt einen neuen Link aus den Übergabeparametern
     * @return string
     */
    protected function buildNewLink() {
        $string = $_REQUEST['oldUrl'];
        $params = array();
        foreach($this->keys as $value) {
            if(strpos($string,'&'.$value.'=') !== FALSE) {
                $start = strpos($string,'&'.$value.'=')+strlen($value)+2;
                if(strpos($string,'&',$start) === FALSE) {
                    $length = strlen($string)-$start;
                } else {
                    $length = strpos($string,'&',$start)-$start;
                }
                $params[$this->keyTranslate[$value]] = substr($string,$start,$length);
            } elseif(strpos($string,'?'.$value.'=') !== FALSE) {
                $start = strpos($string,'?'.$value.'=')+strlen($value)+2;
                if(strpos($string,'&',$start) === FALSE) {
                    $length = strlen($string)-$start;
                } else {
                    $length = strpos($string,'&',$start)-$start;
                }
                $params[$this->keyTranslate[$value]] = substr($string,$start,$length);
            }
        }

        $content = '<a href="http://'.$_SERVER['HTTP_HOST'].'/tank/?';
        foreach ($params as $key => $value) {
                $content .= $key.'='.$value.'&';
        }
        return substr($content,0,strlen($content)-1).'">to the new calculator</a>';
    }
}
?>
