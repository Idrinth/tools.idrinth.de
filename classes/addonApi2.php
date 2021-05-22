<?php

/**
 * Description of addons
 *
 * @author Björn
 */
class addonApi2{
    /**
     * The global db-connection
     * @var mysqli
     */
    var $db;
    /**
     * the addon's id
     * @var int
     */
    var $id=0;
    /**
     * The possible status of an addon
     * @var array
     */
    static $status = array('stability'=>array('alpha','beta','stable'),'use'=>array('unknown','working','broken'));

    function __construct() {
        $this->db = &$GLOBALS['db'];
    }

    function getPage($addon = '') {
      if ($addon) {
          $res  = $this->db->query ("SELECT description.description,description_fr.description As description_fr,description_de.description AS description_de, GROUP_CONCAT(CONCAT(version.main, '.', version.sub, '.', version.bug)) AS versions
              FROM addon
        LEFT JOIN description ON description.addon=addon.id AND description.active AND description.lang='en'
        LEFT JOIN description AS description_fr ON description_fr.addon=addon.id AND description_fr.active AND description_fr.lang='fr'
        LEFT JOIN description As description_de ON description_de.addon=addon.id AND description_de.active AND description_de.lang='de'
        LEFT JOIN version ON version.addon = addon.id
        WHERE addon.slug='".$this->db->escape_string($addon)."'");
          $data = $res->fetch_assoc();
          $data['versions'] = explode(",", $data['versions']);
          return json_encode($data);
      }
      $res = $this->db->query("SELECT addon.name,addon.slug,addon.curVersion AS version,GROUP_CONCAT(tag.name) AS tags,
        (SELECT COUNT(*) FROM download WHERE download.addon=addon.id) as downloads,
        (SELECT COUNT(*) FROM endorsement WHERE endorsement.addon=addon.id) as endorsements
        FROM addon
        LEFT JOIN addon_tag ON addon_tag.addon=addon.id
        LEFT JOIN tag ON addon_tag.tag=tag.aid
        GROUP BY addon.id");
        $data=array();
        while($addon = $res->fetch_assoc()) {
            if($addon['tags']) {
                $addon['tags']=explode(',',$addon['tags']);
            } else {
                $addon['tags']=array('Not Tagged');
            }
            $data[]=$addon;
        }
        return json_encode($data);
    }
}