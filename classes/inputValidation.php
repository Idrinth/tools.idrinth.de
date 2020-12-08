<?php
if(!defined('IDRINTH')) {
    die('No direct calls to this file allowed.');
}

/**
 * Die Basisklasse zur Eingabevalidierung
 **/
 class inputValidation {
     /**
      * Überprüft eine Variable darauf, ob sie eine Zahl im richtigen Bereich ist
      *
      * @param $value float der zu überprüfende Wert
      * @param $min float der kleinste valide Wert
      * @param $max float der größte valide Wert
      *
      * @return bool Valide?
      **/
     public function testInt ($value,$min = NULL,$max = NULL) {
         return $this->testNumber($value,$min,$max,TRUE);
     }

     /**
      * Überprüft eine Variable darauf, ob sie eine Zahl im richtigen Bereich ist
      *
      * @param $value float der zu überprüfende Wert
      * @param $min float der kleinste valide Wert
      * @param $max float der größte valide Wert
      *
      * @return bool Valide?
      **/
     public function testFloat ($value,$min = NULL,$max = NULL) {
         return $this->testNumber($value,$min,$max,FALSE);
     }
     
     /**
      * Überprüft eine Variable darauf, ob sie eine Zahl im richtigen Bereich ist
      *
      * @param $value float der zu überprüfende Wert
      * @param $min float der kleinste valide Wert
      * @param $max float der größte valide Wert
      * @param $isInt bool die Frage, ob die Zahl ganzzahlig sein soll
      *
      * @return bool/number Valide?
      **/
     protected function testNumber($value,$min = NULL,$max = NULL,$isInt = FALSE) {
         //alles mit Ausnahme von . und Zahlen codieren
         $convmap = array(
             0x0,0x2d,0x0,0x2d,
             0x2f,0x2f,0x0,0x2f,
             0x3a,0xffffff,0x0,0xffffff,
             );
         $value = mb_encode_numericentity($value,$convmap,'utf-8');

         if(strpos($value,'&')) {
            return array(FALSE,'no number');
        }
        if($min != NULL ) {
            if($min > $value) {
                return array(FALSE,'To Small');
            }
        }
        if($max != NULL ) {
            if($max < $value) {
                return array(FALSE,'To Large');
            }
        }
        if($isInt) {
             if(ceil($value) != $value) {
                 return array(FALSE,'INT');
             }
         }
         return array($value,'');
     }
     /**
      * Testet alle Werte in $REQUEST auf den angegebenen Wert(entweder alle den Gleichen oder ein Wert pro Key)
      *
      * @param Ein Array mit den zu überprüfenden Keys aus Request als key und dem gewünschten Typ als string
      *
      * @return Ein Array, das unter dem jeweiligen Key das Ergebnis der Überprüfung hat
      */
      public function testInput($typeArray) {
          foreach($typeArray as $key => &$value) {
              try {
                  $value = $this->{'test'.ucfirst(strtolower($value))}($_REQUEST[$key]);
              } catch(Exeption $e) {
                  $value = array(FALSE,'Unknown Validator'.$e);
              }
          }
          return $typeArray;
      }
 }
?>