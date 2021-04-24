<?php
/**
 * Description of addons
 *
 * @author Björn
 */
class addons {
    /**
     * the current user
     * @var user
     */
    var $user;
    /**
     * The global db-connection
     * @var mysqli
     */
    var $db;
    /**
     * the addon's id
     * @var int
     */
    var $id = 0;
    /**
     * The possible status of an addon
     * @var array
     */
    static $status = array('stability' => array('alpha','beta','stable'),'use' => array('unknown','working','broken'));
    function __construct() {
        $this->db = &$GLOBALS['db'];
        $this->user = &$GLOBALS['user'];
    }
    function getPage($addon = '') {
        $page = '';
        if($addon == '') {
            $page .= $this->getOverview();
        } elseif($addon == 'new') {
            $page .= $this->makeNew();
        } else {
            $page .= $this->getAddon($GLOBALS['parser']->getPage(1));
        }
        $view = new view();

        $pageTitle = $addon == '' ? "Addons" : $addon;

        return $view->generateStaticPage($page . $this->makeComments(),$pageTitle);
    }
    function downloadAddon($slug,$version) {
        $res = false;
        if ($version === 'latest') {
            $res = $this->db->query("SELECT version.data,version.id,version.addon FROM `version`
                INNER JOIN addon ON version.addon=addon.id
                WHERE addon.slug='" . $this->db->real_escape_string($slug) . "'
                AND NOT version.disabled
                ORDER BY version.main DESC, version.sub DESC, version.bug DESC
                LIMIT 1");
        } else {
            $res = $this->db->query("SELECT version.data,version.id,version.addon FROM `version`
                INNER JOIN addon ON version.addon=addon.id
                WHERE addon.slug='" . $this->db->real_escape_string($slug) . "'
                AND NOT version.disabled
                AND version.main=" . intval(explode('-',$version)[0]) . "
                AND version.sub=" . intval(explode('-',$version)[1]) . "
                ANd version.bug=" . intval(explode('-',$version)[2]));
        }
        if($res) {
            list($data,$id,$addon) = $res->fetch_row();
            if($data) {
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: public");
                header("Content-Description: File Transfer");
                header("Content-type: application/octet-stream");
                header("Content-Disposition: attachment; filename=\"" . $slug . "." . $version . ".zip\"");
                header("Content-Transfer-Encoding: binary");
                echo $data;
                $this->db->query("INSERT INTO download (user,version,addon,useragent) "
                        . "VALUES (" . intval($this->user->id) . "," . $id . "," . $addon . ",'" . $this->db->real_escape_string($_SERVER["HTTP_USER_AGENT"]) . "')");
                exit;
            }
        }
        header('Location /addons/' . $slug . '/');
        exit;
    }
    function makeDescriptions($addon) {
        $content = '<script src="tabs.js"></script><ul class="tab-menu"><li onclick="activateTab(0,this.parentNode);" class="active">English</li><li onclick="activateTab(1,this.parentNode);">Français</li><li onclick="activateTab(2,this.parentNode);">Deutsch</li></ul><div class="tabs">';
        foreach(array("en","fr","de") AS $lang) {
            $description = array();
            $res = $this->db->query("SELECT description,`date`,display FROM description INNER JOIN `user` ON `user`.id=description.author WHERE active AND lang='$lang' AND addon=" . $this->id);
            if($res) {
                $description = $res->fetch_assoc();
            }
            $content.='<div class="tab" data-name="' . $lang . '"><a href="addons/' . $addon['slug'] . '/edit/' . $lang . '/">Edit</a>'
                    . '<p style="font-size:75%">Please remember to keep info in regards to HOW an addon works, what it SHOULD do in the description and all temporary things(doesn\'t work now etc.) in the comments.</p>'
                    . '<div class="description">' . $description['description']
                    . ($addon['date'] > 0?'<footer>last updated: ' . date('r',$description['date']) . ' | written by: ' . $addon['display'] . '</footer>':'')
                    . '</div></div>';
        }
        return str_replace('data-name="en"','style="display:block;" data-name="en"',$content) . '</div>';
    }
    function getEndorseButton($isEndorsed) {
        if(!$this->user->isActive() || $this->user->banned) {
            return ""; //Can't endorse if not logged in / banned.
        }

        $value = $isEndorsed ? 0 : 1;
        $text = $isEndorsed ? "Unendorse" : "Endorse";
        $class = $isEndorsed ? "" : 'class="secondary"';
        return '<form method="POST" class="endorsement">
                    <button type="submit" value="' . $value . '" name="endorsement" ' . $class . '>
                        <svg class="icon"><use xlink:href="https://'. $GLOBALS['hostname'] .'/feather-sprite.svg#thumbs-up"/></svg>
                        '. $text .'
                    </button>
                </form>';
    }
    function displayAddon(&$addon) {
        $tags = '';
        $res = $this->db->query("SELECT GROUP_CONCAT(name) FROM tag INNER JOIN addon_tag ON tag=aid WHERE addon=" . $this->id);
        if($res) {
            $tagMarkup = '';
            $tagString = $res->fetch_row()[0];
            if(!empty($tagString)) {
                $tags = explode(',', $tagString);                                        
                foreach($tags as $tag) {
                    $tagMarkup .= '<span class="tag">' . $tag . '</span>';
                }   
            }
        }
        $content .= '<div class="tags">' . $tagMarkup . '</div>' . $this->makeDescriptions($addon);
        if (isset($_POST['endorsement']) && $this->user->isActive()) {
            $this->db->query("INSERT INTO endorsement (user, addon, endorsed) VALUES (".$this->user->id.",".$addon['id'].",".(intval($_POST['endorsement'])?1:0).") ON DUPLICATE KEY UPDATE endorsed=".(intval($_POST['endorsement'])?1:0));
        }
        $res = $this->db->query("SELECT endorsed FROM endorsement WHERE user=".$this->user->id." AND addon=".$addon['id']);
        
        $content .= $this->getEndorseButton($res && $res->fetch_assoc()['endorsed']);
            
        $content .= '<table><thead><tr><th>Version</th><th>Status</th><th>Changes</th><th>Uploader</th><th>Downloads</th></tr></thead><tbody>';
        $res = $this->db->query("SELECT main,sub,bug,`status`,`use`,tstamp,`change`,display, COUNT(*) as downloads
FROM version
INNER JOIN user ON user.id=version.author
LEFT JOIN download ON download.version=version.id
WHERE version.addon=".$addon['id']."
GROUP BY version.id
ORDER BY main DESC,sub DESC, bug DESC");
        if($res) {
            while($item = $res->fetch_assoc()) {
                $content .= '<tr>'
                        . '<td><a rel="nofollow" href="addons/' . $addon['slug'] . '/download/' . $item['main'] . '-' . $item['sub'] . '-' . $item['bug'] . '/">' . $item['main'] . '.' . $item['sub'] . '.' . $item['bug'] . '</a></td>'
                        . '<td>' . self::$status['stability'][$item['status']] . '</td>'
                        . '<td style="max-height:3em;overflow-y:scroll;">' . $item['change'] . '</td>'
                        . '<td>' . $item['display'] . '</td>'
                        . '<td style="max-height:3em;overflow-y:scroll;">' . $item['downloads'] . '</td>'
                        . '</tr>';
            }
            $res->free();
        }
        $content .= '</tbody></table>';

        if($this->user->isActive() && !$this->user->banned) {
            $content .= '<a title="Add new Addon" href="/addons/' . $addon['slug'] . '/upload/" class="actionButton" title="Upload new version">
            <svg class="icon"><use xlink:href="https://'. $GLOBALS['hostname'] .'/feather-sprite.svg#upload"/></svg>
                Upload new version
            </a>';
        }
        $content .= '<a rel="nofollow" class="actionButton" href="addons/' . $addon['slug'] . '/download/latest/">
                <svg class="icon"><use xlink:href="https://'. $GLOBALS['hostname'] .'/feather-sprite.svg#download"/></svg>
                Download Latest
            </a>';
        return $content;
    }
    function getAddon($slug) {
        $res = $this->db->query("SELECT addon.name,addon.slug,addon.id
        FROM addon
        WHERE addon.slug LIKE '" . $this->db->real_escape_string($slug) . "'");
        if(!$res) {
            header("Location: https://" . $GLOBALS['hostname'] . "/addons/");
            exit;
        }
        $addon = $res->fetch_assoc();
        $res->free();
        if(!$addon['name']) {
            header("Location: https://" . $GLOBALS['hostname'] . "/addons/");
            exit;
        }
        $this->id = $addon['id'];
        if($GLOBALS['parser']->getPage(2) == 'download') {
            $this->downloadAddon($slug,$GLOBALS['parser']->getPage(3));
        }
        if($GLOBALS['parser']->getPage(2) == 'edit') {
            return $this->editAddon($addon,$GLOBALS['parser']->getPage(3));
        }
        if($GLOBALS['parser']->getPage(2) == 'upload') {
            return $this->uploadFile($addon);
        }
        return $this->displayAddon($addon);
    }
    function editAddon($addon,$lang) {
        if(!$this->user->isActive()) {
            return 'No Access without login';
        }
        if(!$lang || !in_array($lang,array("en","fr","de"))) {
            $lang = "en";
        }
        if(isset($_POST['description'])) {
            $GLOBALS['db']->query("UPDATE description SET active=0 WHERE lang='" . $lang . "' AND addon=" . $this->id);
            $GLOBALS['db']->query("INSERT INTO description (lang,addon,author,date,ip,description) VALUES "
                    . "('" . $lang . "','" . $this->id . "','" . $this->user->id . "','" . time() . "','" . $this->db->real_escape_string($_SERVER['REMOTE_ADDR']) . "','" . $this->db->real_escape_string($_POST['description']) . "')");
            if($_POST['tags']) {
                $_POST['tags'] = trim(preg_replace('/\s*,\s*/',',',preg_replace('/,{2,}/',',',preg_replace('/[^a-zA-Z0-9\'\\- ,]/','',$_POST['tags']))),',');
                $GLOBALS['db']->query("INSERT INTO tag_log (addon,user,ip,tags) VALUES "
                        . "('" . $this->id . "','" . $this->user->id . "','" . $this->db->real_escape_string($_SERVER['REMOTE_ADDR']) . "','" . $this->db->real_escape_string($_POST['tags']) . "')");
                $tags = explode(',',$_POST['tags']);
                $this->db->query("DELETE FROM addon_tag WHERE addon=" . $this->id);
                foreach($tags As $tag) {
                    $tag = trim($tag);
                    if(strlen($tag) > 2) {
                        $this->db->query("INSERT INTO tag (name) VALUES ('" . $this->db->real_escape_string($tag) . "')");
                        $this->db->query("INSERT INTO addon_tag (addon,tag) (SELECT " . $this->id . ",aid FROM tag WHERE name='" . $this->db->real_escape_string($tag) . "')");
                    }
                }
            }
            header('Location: /addons/' . $addon['slug'] . '/',302);
            exit;
        }
        $tags = '';
        $res = $this->db->query("SELECT GROUP_CONCAT(name) FROM tag INNER JOIN addon_tag ON tag=aid WHERE addon=" . $this->id);
        if($res) {
            $tags = $res->fetch_row()[0];
        }
        $tag = array();
        $res = $this->db->query("SELECT name FROM tag");
        while($i = $res->fetch_row()) {
            $tag[] = $i[0];
        }
        $description = '';
        $res = $this->db->query("SELECT description FROM description WHERE active AND lang='$lang' AND addon=" . $this->id);
        if($res) {
            $description = $res->fetch_row()[0];
        }
        $tag = '<ul><li onclick="document.getElementById(\'tags\').value=document.getElementById(\'tags\').value+\',\'+this.innerHTML">' . implode('</li><li onclick="document.getElementById(\'tags\').value=document.getElementById(\'tags\').value+\',\'+this.innerHTML">',$tag) . '</li></ul>';
        return '<form method="post"><fieldset>' .
                '<div>' . $this->makeTextEditor($description,'description') . '<div>'
                . '<div><label for="tags">Tags(comma-seperated)</label><textarea id="tags" name="tags">' . $tags . '</textarea><div>'
                . '<button type="submit" onclick="editor.post()">Save</button><div>'
                . '</fieldset></form>click to add known tag:' . $tag;
    }
    function makeTextEditor($text,$name) {
        return "<textarea id=\"tinyeditor\" style=\"width: 400px; height: 200px\" name='$name'></textarea>
            <script src='/TinyEditor/tiny.editor.packed.js'></script>
            <style>#tinyeditor {border:none; margin:0; padding:0; font:14px 'Courier New',Verdana}
.tinyeditor {border:1px solid #bbb; padding:0 1px 1px; font:12px Verdana,Arial}
.tinyeditor iframe {border:none; overflow-x:hidden}
.tinyeditor-header {height:31px; border-bottom:1px solid #bbb; background:url(TinyEditor/images/header-bg.gif) repeat-x; padding-top:1px}
.tinyeditor-header select {float:left; margin-top:5px}
.tinyeditor-font {margin-left:12px}
.tinyeditor-size {margin:0 3px}
.tinyeditor-style {margin-right:12px}
.tinyeditor-divider {float:left; width:1px; height:30px; background:#ccc}
.tinyeditor-control {float:left; width:34px; height:30px; cursor:pointer; background-image:url(TinyEditor/images/icons.png)}
.tinyeditor-control:hover {background-color:#fff; background-position:30px 0}
.tinyeditor-footer {height:32px; border-top:1px solid #bbb; background:#f5f5f5}
.toggle {float:left; background:url(images/icons.png) -34px 2px no-repeat; padding:9px 13px 0 31px; height:23px; border-right:1px solid #ccc; cursor:pointer; color:#666}
.toggle:hover {background-color:#fff}
.resize {float:right; height:32px; width:32px; background:url(TinyEditor/images/resize.gif) 15px 15px no-repeat; cursor:s-resize}
#editor {cursor:text; margin:10px}</style>
            <script>
            var editor = new TINY.editor.edit('editor', {
                id: 'tinyeditor',
                width: 584,
                height: 175,
                cssclass: 'tinyeditor',
                controlclass: 'tinyeditor-control',
                rowclass: 'tinyeditor-header',
                dividerclass: 'tinyeditor-divider',
                controls: ['bold', 'italic', 'underline', 'strikethrough', '|', 'subscript', 'superscript', '|',
                    'orderedlist', 'unorderedlist', '|', 'outdent', 'indent', '|', 'unformat', '|', 'undo', 'redo', 'n',
                    'size', '|', 'image', 'hr', 'link', 'unlink'],
                footer: true,
                fonts: ['Verdana'],
                xhtml: true,
                content: '" . str_replace(["'","\n","\r"],["\\'",'\\n',''],$text) . "',
                bodyid: 'editor',
                footerclass: 'tinyeditor-footer',
                toggle: {text: 'source', activetext: 'wysiwyg', cssclass: 'toggle'},
                resize: {cssclass: 'resize'}
            });
            </script>";
    }
    function makeComments() {
        if($this->id == 0 || $GLOBALS['parser']->getPage(2)) {
            return '';
        }
        if(isset($_POST['comment']) && $this->user->isActive()) {
            $GLOBALS['db']->query("INSERT INTO comment (user,foreign_id,`table`,tstamp,comment,ip) VALUES "
                    . "('" . $this->user->id . "','" . $this->id . "','addon','" . time() . "','" . $this->db->real_escape_string($_POST['comment']) . "','" . $this->db->real_escape_string($_SERVER['REMOTE_ADDR']) . "')");
        }
        $content = '<div id="comments">';
        if($this->user->isActive()) {
            $content .='<form method="post"><fieldset onclick="this.setAttribute(\'class\',\'active\')"><legend>Your Comment</legend>' .
                    '<div>' . $this->makeTextEditor('','comment') . '</div><div>'
                    . '<button type="submit" onclick="editor.post()">Comment on this Addon</button><div>'
                    . '</fieldset></form>';
        } elseif($this->user->banned) {
            $content.='<strong>Your account is banned. You can not comment.</strong>';
        } else {
            $content.='<strong>You need to be logged in to comment</strong>';
        }
        $res = $this->db->query("SELECT comment.comment,comment.id,comment.tstamp,user.display,user.slug "
                . "FROM comment "
                . "LEFT JOIN user ON user.id=comment.user "
                . "WHERE foreign_id='" . $this->id . "' "
                . "AND `table`='addon' "
                . "ORDER BY tstamp DESC");
        if($res) {
            while($item = $res->fetch_assoc()) {
                $content .= '<div id="comment-' . $item['id'] . '">'
                        . '<div class="comment-user">'
                        . '<strong><a href="/account/' . $item['slug'] . '/">' . $item['display'] . '</a></strong>'
                        . '<p>' . date('r',$item['tstamp']) . '</p>'
                        . '<a href="/addons/' . $GLOBALS['parser']->getPage(1) . '#comment-' . $item['id'] . '">Direct Link</a>'
                        . '</div>'
                        . '<div class="comment-text">' . $item['comment'] . '</div></div>';
            }
        }
        return $content . '</div>';
    }
    function humanTiming($time)
    {

        $time = time() - $time; 
        $time = ($time<1)? 1 : $time;
        $tokens = array (
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
        }

    }
    function getSortingOptions() {
        return '
            <label for="sortBy">Sort by: </label>
            <select id="sortBy">
                <option value="mostToLeastRecentUpdate" selected>Most Recently Updated</option>
                <option value="leastToMostRecentUpdate">Least Recently Updated</option>
                <option value="highToLowDownloads">Most Downloads</option>
                <option value="lowToHighDownloads">Least Downloads</option>
                <option value="highToLowEndorsements">Most Endorsements</option>
                <option value="lowToHighEndorsements">Least Endorsements</option>
                <option value="alphabeticalName">Name A-Z</option>
                <option value="alphabeticalNameReverse">Name Z-A</option>
            </select>';
    }

    function getTagsDropDown() {
        $options = '<option value="0">Any</option>';
        $res = $this->db->query("SELECT tag.*, COUNT(addon_tag.addon) as addons FROM tag INNER JOIN addon_tag ON addon_tag.tag=tag.aid GROUP BY tag.aid ORDER BY name ASC");
        while ($res && $tag = $res->fetch_assoc()) {
            $options .= '<option value="'.$tag['aid'].'"'.(isset($_POST['tag']) && $tag['aid'] == $_POST['tag'] ? ' selected' : '').'>'.$tag['name'].' ('.$tag['addons'].')</tag>';
        }
        return '<div class="searchField tag"><label for="tag">Tagged as</label><select name="tag" id="tag">'.$options.'</select></div>';
    }

    function getEndorsementsAsArray() {
        $data = [];
        $res = $this->db->query("SELECT addon, COUNT(addon) as endorsements FROM `endorsement` WHERE endorsed = 1 GROUP BY addon");
        if($res) {
            while($item = $res->fetch_assoc()) {
                $data[$item['addon']] = $item;
            }
        }
        return $data;
    }

    function getOverview() {
        $content = '';

        $q = "SELECT 
        addon.id,
        addon.name,
        slug,
        curVersion,
        lastUpdate,
        (SELECT COUNT(*) FROM download WHERE download.addon=addon.id) as downloads,
        description.description as description,
        GROUP_CONCAT( DISTINCT tag.name) as tags
    FROM addon
    LEFT JOIN description on addon.id = description.addon
    LEFT JOIN addon_tag on addon.id = addon_tag.addon
    LEFT JOIN tag on addon_tag.tag = tag.aid
    WHERE addon.active
    ".(isset($_POST['tag']) && $_POST['tag']!=0 && is_numeric($_POST['tag']) ? "AND addon.id IN (SELECT addon FROM addon_tag WHERE tag=".$_POST['tag'].")" : "")."
" . (isset($_POST['search']) && strlen($_POST['search']) > 0?"AND lower(addon.name) LIKE '%" . $this->db->real_escape_string(mb_strtolower($_POST['search'])) . "%'":'') . "
GROUP BY addon.id
ORDER BY lastUpdate DESC,name ASC";
        $res = $this->db->query($q);
        if($res) {
            $content .= '<div class="searchWrapper">';
            $content .= '<div class="searchOptions">';
            $content .= $this->getSortingOptions();
            $content .= '</div>';
            $content .= '<div class="searchResults" id="searchResults">';

            if($res->num_rows <= 0) {
                $content .= '<p class="placeholderResultText">No addons match your search terms.</p>';
            }

            $endorsementData = $this->getEndorsementsAsArray();

            while($item = $res->fetch_assoc()) {

                $endorsements = 0;
                if( $endorsementData && $endorsementData[$item['id']]) {
                    $endorsements = $endorsementData[$item['id']]['endorsements'];
                }

                $sortableData = [
                    'name' => $item['name'],
                    'downloads' => $item['downloads'],
                    'endorsements' => $endorsements,
                    'lastUpdate' => $item['lastUpdate']
                ];

                $content .= '<div class="addonListing" data-addon="' . htmlspecialchars(json_encode($sortableData), ENT_QUOTES, 'UTF-8') .'">
                                <div class="info">
                                    <header><h3><a href="/addons/' . $item['slug'] . '/">' . $item['name'] . '</a></h3>';
                                    if(!empty($item['curVersion'])) {
                                        $content .= '<span class="version">v' . $item['curVersion'] . '</span>';
                                    }
                                    if(!empty($item['tags'])) {
                                        $tags = explode(',', $item['tags']);
                                        
                                        foreach($tags as $tag) {
                                            $content .= '<span class="tag">' . $tag . '</span>';
                                        }
                                    }
                                    $content .= "</header>";
                                    $content .= '<p class="addonMetaData">Updated ' . $this->humanTiming($item['lastUpdate']) . ' ago | 
                                    ' . number_format($item['downloads']) . ' downloads | 
                                    ' . number_format($endorsements) . ' endorsements</p>
                                    
                                </div>';                                   
                                    
                                    if(!empty($item['curVersion'])) {
                                        $content .= '<div class="actions"><a rel="nofollow" class="actionButton" href="addons/' . $item['slug'] . '/download/' . str_replace('.', '-', $item['curVersion']) . '/">
                                            <svg class="icon"><use xlink:href="https://'. $GLOBALS['hostname'] .'/feather-sprite.svg#download"/></svg>
                                            Download
                                        </a></div>';
                                    }
                            $content .= '</div>';
            }
            $content .= '</div>';
            $content .= '</div>';
        }
        
        return '<div>
                    <a title="Add new Addon" href="/addons/new/" class="actionButton">
                    <svg class="icon"><use xlink:href="https://'. $GLOBALS['hostname'] .'/feather-sprite.svg#upload"/></svg>
                        Upload an Addon
                    </a>
                    </div>
        <form method="post" class="search">
            ' . $this->getTagsDropDown() . '
            <div class="searchField name">
                <label for="name">Name similar to</label>
                <input type="text" name="search" value="' . $_POST['search'] . '" id="name"/>
            </div>
            <div class="searchField button">
                <button type="submit" class="actionButton" alt="search">
                    <svg class="icon"><use xlink:href="https://'. $GLOBALS['hostname'] .'/feather-sprite.svg#search"/></svg>
                    Search Addons
                </button>
            </div>
        </form>
        '. $content;
    }
    function uploadFile($addon) {
        if(!$this->user->isActive()) {
            return 'No Access without login';
        }
        if(isset($_POST['main']) && isset($_POST['sub']) && isset($_POST['bug']) && isset($_POST['status']) && isset($_FILES['data'])) {
            $this->db->query("INSERT INTO version (main,sub,bug,addon,author,`status`,`change`,ip,tstamp,`data`) VALUES ("
                    . intval($_POST['main']) . ","
                    . intval($_POST['sub']) . ","
                    . intval($_POST['bug']) . ","
                    . $this->id . ","
                    . $this->user->id . ","
                    . intval($_POST['status']) . ","
                    . "'" . $this->db->real_escape_string($_POST['change']) . "',"
                    . "'" . $this->db->real_escape_string($_SERVER['REMOTE_ADDR']) . "',"
                    . time() . ","
                    . "'" . $this->db->real_escape_string(file_get_contents($_FILES['data']['tmp_name'])) . "'"
                    . ")");
            $this->db->query("INSERT INTO addon (id,curVersion,lastUpdate)
                    (SELECT addon,CONCAT(main,'.',sub,'.',bug),tstamp FROM version WHERE addon=" . $this->id . " ORDER BY main DESC,sub DESC,bug DESC LIMIT 1)
                    ON DUPLICATE KEY UPDATE curVersion=VALUES(curVersion),lastUpdate=VALUES(lastUpdate)");
            header('Location: /addons/' . $addon['slug'] . '/',302);
            exit;
        }
        $res = $this->db->query("SELECT CONCAT(main,'.',sub,'.',bug) FROM version WHERE addon=" . $this->id);
        $content = array();
        while($item = $res->fetch_row()) {
            $content[] = $item[0];
        }
        return '<p>Known Versions: ' . implode(', ',$content) . '</p>
            <p><a href="http://semver.org/">Versioning Info</a></p>
<form method="post" enctype="multipart/form-data">
    <div><label for="data">Select Zip-File to upload:</label><input accept="application/zip,.zip" required type="file" name="data" id="data"></div>
    <div><label for="main">Main-Version:</label><input required type="number" name="main" id="main"></div>
    <div><label for="sub">Sub-Version:</label><input required type="number" name="sub" id="sub"></div>
    <div><label for="bug">Bug-version:</label><input required type="number" name="bug" id="bug"></div>
    <div><label for="status">Status:</label><select id="status" name="status"><option value="0">Alpha</option><option value="1">Beta</option><option value="2">Stable</option></select></div>
    <div><label for="change">Changes:</label><textarea name="change" id="change"></textarea></div>
    <input type="submit" value="Add new version" name="submit">
</form>';
    }
    function makeNew() {
        if(!$this->user->isActive()) {
            return 'Sorry, you need to have an active account.';
        }
        if(isset($_POST['name'])) {
            $this->db->query("INSERT INTO addon (name,slug) VALUES ('" . $this->db->real_escape_string($_POST['name']) . "','" . strtolower(preg_replace('/[^A-Za-z0-9_\-]+/','-',$_POST['name'])) . "')");
            header('Location: /addons/' . strtolower(preg_replace('/[^A-Za-z0-9_\-]+/','-',$_POST['name'])) . '/upload/',302);
            exit;
        }
        $content = '<form method="post">';
        $content .= '<fieldset><legend>Basic Data</legend>';
        $content .= '<div><label for="name">Name</label><input type="text" required="required" value="" name="name" id="name"/></div>';
        $content .= '</fieldset><button type="submit">Create Addon</button><div></form></div>';
        return $content;
    }
}
