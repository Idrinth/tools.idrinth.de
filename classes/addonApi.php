<?php

/**
 * Description of addons
 *
 * @author Björn
 */
class addonApi {
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
      $res = $this->db->query("SELECT description.description,description_fr.description As description_fr,description_de.description AS description_de,addon.name,addon.slug,addon.curVersion AS version,GROUP_CONCAT(tag.name) AS tags
        FROM addon
        LEFT JOIN description ON description.addon=addon.id AND description.active AND description.lang='en'
        LEFT JOIN description AS description_fr ON description_fr.addon=addon.id AND description_fr.active AND description_fr.lang='fr'
        LEFT JOIN description As description_de ON description_de.addon=addon.id AND description_de.active AND description_de.lang='de'
        LEFT JOIN addon_tag ON addon_tag.addon=addon.id
        LEFT JOIN tag ON addon_tag.tag=tag.aid
        GROUP BY addon.id");
        if(!$res) {
            return null;
        }
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