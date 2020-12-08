<?php
if(!defined('IDRINTH')) {
    die('No direct calls to this file allowed.');
}

/**
 * Die Basisklasse mit Methoden für Charactere
 **/
abstract class characterFunctions {
    // Systemrelevantes
    var $error = array();
    var $keys = array();

    /**
     * Initialisiert die Werte der Klasse
     **/
    function __construct() {
        $this->keys = array ();
        $this->error = array ();
    }
    /**
     * builds the array out of REQUEST and SESSION,so that values can be searched
     */
    function getSearchArray() {
        $id = session_id();
        $searchArray = array();
        if(isset($_SESSION[$id])) {
            $searchArray = $_SESSION[$id];
        }
        foreach ($_REQUEST as $key => $value) {
            $searchArray[$key] = $value;
        }
        $_SESSION[$id] = $searchArray;
        return $searchArray;
    }

    /**
     * Gibt den Key zurück, den das Attribut hat
         *
         * @param $search string der Name des zu suchenden Attributs
         *
         * @return string der Key zum gewünschten Attribut
     **/
       public function getKey($search) {
            foreach($this->keys as $key => $value) {
                if($value == $search) {
                    return $key;
                } elseif(is_array($value)) {
                    foreach($value as $key2 => $value2) {
                        if($value2 == $search) {
                            return $key2;
                        }
                    }
                }
            }
    }

    /**
     * Berechnet den effektiven Rang des Charakters
     **/
    public function effectiveRank() {
        if(!property_exists($this,'renownRank') || $this->renownRank < 80) {
            return $this->level;
        } else {
            return ($this->level + floor(($this->renownRank - 80)/4));
        }
    }

        /**
     * Berechnet den Wert eines NPC-Attributs in Abhängigkeit vom Level
     **/
    protected function getNpcAttribute() {
        $attribute = floor(7.5*$this->level+50);
        return $attribute;
    }
}
?>