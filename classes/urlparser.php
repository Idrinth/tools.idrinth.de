<?php
/**
 * Description of urlparser
 *
 * @author Björn
 */
class urlparser {
    var $parts = array();
    var $extension = '';
    var $host;
    var $endedWithSlash = FALSE;
    function __construct($parts) {
        if(empty($_SERVER['HTTP_HOST'])) {
            $_SERVER['HTTP_HOST'] = $GLOBALS['hostname'];
        }
        $this->host = &$_SERVER['HTTP_HOST'];
        if(strpos($parts,'?') !== FALSE) {
            $parts = substr($parts,0,strpos($parts,'?'));//removing the query part
        }
        if(strpos($parts,'.') !== FALSE) {
            $this->extension = substr($parts,strpos($parts,'.') + 1);
            $parts = substr($parts,0,strpos($parts,'.'));//removing the extension
        }
        if($parts[strlen($parts) - 1] == '/') {
            $this->endedWithSlash = TRUE;
        }
        $parts = trim(mb_strtolower($parts),'/');//removing slashes
        $this->parts = explode('/',$parts);
        header('P: ' . json_encode($this->parts));
    }
    function endedWithSlash() {
        return $this->endedWithSlash?'/':'';
    }
    /**
     * return the page-segment of the related level
     * @param int $level
     * @return string
     */
    function getPage($level = 0) {
        if(!isset($this->parts[$level])) {
            return '';
        }
        return $this->parts[$level] . '';
    }
    /**
     * returns the canonical URL for the given page
     * @param string $ext
     * @return string
     */
    function getCanonical($ext = '') {
        return $this->host . '/' . implode('/',$this->parts) . (strlen($ext) > 0 && $ext != '/'?'.' . $ext:'/');
    }
}