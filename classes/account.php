<?php
if(!defined('IDRINTH') || IDRINTH !== FALSE) {
    die('No direct access.');
}
/**
 * Description of account
 *
 * @author Björn
 */
class account {
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
    function __construct() {
        $this->db = &$GLOBALS['db'];
        $this->user = &$GLOBALS['user'];
    }
    function getPage($type = '') {
        $page = '';
        if($type == 'login') {
            $page .= $this->getLogin();
        } elseif($type == 'register') {
            $page .= $this->getRegister();
        } elseif($type == 'logout') {
            $page .= $this->getLogout();
        } elseif($type == 'reset') {
            $page .= $this->getReset();
        } else {
            $type = 'account';
            $page .= $this->getProfile($GLOBALS['parser']->getPage(1));
        }
        $view = new view();
        return $view->generateStaticPage($page,$type);
    }
    function getReset() {
        if($GLOBALS['parser']->getPage(2) && $GLOBALS['parser']->getPage(3)) {
            $res = $this->db->query("SELECT `user`.id,aid
FROM `user`
LEFT JOIN `password` ON `password`.`user`=`user`.id
WHERE `password`.`hash`='" . $this->db->real_escape_string($GLOBALS['parser']->getPage(3)) . "'
AND MD5(CONCAT(`password`.aid,`user`.email))='" . $this->db->real_escape_string($GLOBALS['parser']->getPage(2)) . "'
AND `password`.created>" . (time() - 1860) . "
AND NOT `password`.used");
            if(!$res) {
                return '<p>The url is not known or no longer valid.</p>';
            }
            list($user,$pw) = $res->fetch_row();
            if(!$user) {
                return '<p>The url is not known or no longer valid.</p>';
            }
            if(isset($_POST['pw1']) && isset($_POST['pw2']) && $_POST['pw1'] == $_POST['pw2'] && strlen($_POST['pw1']) > 3) {
                $this->db->query("UPDATE `user` SET pass='" . md5($user . $_POST['pw1']) . "' WHERE id=" . $user);
                $this->db->query("UPDATE `password` SET used=1 WHERE aid=" . $pw);
                $this->forceLogin($user);
                header('Location: /account/',true,303);
                return;
            }
            return '<form method="post">'
                    . '<fieldset><legend>Your Password</legend>'
                    . '<div class="wrapper">'
                    . '<label for="user">Password</label>'
                    . '<input type="password" name="pw1" id="pw1" value=""/>'
                    . '</div>'
                    . '<div class="wrapper">'
                    . '<label for="pw">Password</label>'
                    . '<input type="password" name="pw2" id="pw2" value=""/>'
                    . '</div></fieldset><fieldset>'
                    . '<div class="wrapper">'
                    . '<button type="submit">Change&amp;Login</button>'
                    . '</div></fieldset>'
                    . '</form>';
        }
        if(count($_POST) > 0) {
            $res = $this->db->query("SELECT MAX(created),email,`user`.id
FROM `user`
LEFT JOIN `password` ON `password`.`user`=`user`.id
WHERE `user`.email='" . $this->db->real_escape_string($_POST['email']) . "'");
            if($res && $user = $res->fetch_assoc()) {
                if($user['email'] && $user['created'] < time() - 1800) {
                    $code = md5(time() . json_encode($user) . mt_rand() . microtime() . 'PasswordReset');
                    $this->db->query("Insert Into `password` (`user`,`hash`,`created`) VALUES('" . $user['id'] . "','$code','" . time() . "')");
                    $this->sendMail($user['email'],'<p>Someone asked for a password reset at ' . $GLOBALS['hostname'] . '.</p>'
                            . '<p>If that wasn\'t you, please ignore this mail nothing will happen.</p>'
                            . '<p>Otherwise click <a href="https://' . $GLOBALS['hostname'] . '/account/reset/' . md5($this->db->insert_id . $user['email']) . '/' . $code . '/">Reset Password</a>.</p>','Password Reset');
                }
            }
            return '<p>If that email is know and belongs to an activated account, we send a password retrieval mail to it.</p>';
        }
        return '<form method="post">'
                . '<fieldset><legend>Your Data</legend>'
                . '<div class="wrapper">'
                . '<label for="user">eMail</label>'
                . '<input type="email" name="email" id="email" value=""/>'
                . '</div></fieldset><fieldset>'
                . '<div class="wrapper">'
                . '<button type="submit">Send Mail</button>'
                . '</div></fieldset></form>';
    }
    function getLogout() {
        setcookie('iadb','',time() - 3600,'/',$GLOBALS['hostname']);
        return '<p>You have been logged out.</p>';
    }
    function getLogin() {
        if($this->user->loggedIn) {
            return 'You are logged in already.';
        }
        return (count($_POST) > 0?'<p>The combination of Login and Password could\'t be found in the database.</p>':'') . '<form method="post">'
                . '<fieldset><legend>Your Data</legend>'
                . '<div class="wrapper">'
                . '<label for="user">Login</label>'
                . '<input type="password" name="user" id="user" value=""/>'
                . '</div>'
                . '<div class="wrapper">'
                . '<label for="pw">Password</label>'
                . '<input type="password" name="pass" id="pw" value=""/>'
                . '</div></fieldset><fieldset>'
                . '<div class="wrapper">'
                . '<button type="submit">Login</button>'
                . '</div></fieldset>'
                . '</form>'
                . '<p>Not registered yet? Just <a href="###baseURL###/account/register/">go here</a> and do it.</p>'
                . '<p>Forgot your password? Reset it <a href="###baseURL###/account/reset/">go here</a>.</p>';
    }
    function makeUser() {
        $this->db->query("INSERT INTO user (display,login,email,ip_register,time_register) "
                . "VALUES("
                . "'" . $this->db->real_escape_string($_POST['display']) . "',"
                . "'" . $this->db->real_escape_string($_POST['login']) . "',"
                . "'" . $this->db->real_escape_string($_POST['email']) . "',"
                . "'" . $this->db->real_escape_string($_SERVER['REMOTE_ADDR']) . "',"
                . "'" . time() . "')");
        if($this->db->errno > 0) {
            return FALSE;
        }
        $id = $this->db->insert_id;
        $this->db->query("UPDATE user SET pass='" . md5($id . $_POST['password'][0]) . "' WHERE id=" . $id);
        $text = '<h1>Welcome to <a href="' . $GLOBALS['hostname'] . '">Idrinth\'s Tools</a></h1>' .
                '<p>Someone registered using your eMail. Please click the appropriate link below to choose what should happen to that account.</p>' .
                '<ul><li><a href="https://' . $GLOBALS['hostname'] . '/account/register/activate/' . md5($_POST['email'] . $_POST['login'] . $_SERVER['REMOTE_ADDR']) . dechex($id) . '/">Activate Account</a></li>' .
                '<li><a href="https://' . $GLOBALS['hostname'] . '/account/register/deactivate/' . md5($_POST['email'] . $_POST['login'] . $_SERVER['REMOTE_ADDR']) . dechex($id) . '/">Deactivate and Bann eMail</a></li></ul>' .
                '<hr />' .
                '<p>Idrinth\'s Tools is a website dedicated to help players of Warhammer Online: Age of Reckoning.</p>' .
                '<p>It is created and operated by Björn "Idrinth" Büttner, further information is avaible in the <a href="https://' . $GLOBALS['hostname'] . '/imprint/">website\'s imprint</a></p>';
        return $this->sendMail($_POST['email'],$text,'Registration at Idrinth\'s Tools');
    }
    function sendMail($mail,$text,$topic) {
        return false;
        if(filter_var($mail,FILTER_VALIDATE_EMAIL) === FALSE) {
            return FALSE;
        }
        return mail($mail,$topic,'<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
   <title>' . $topic . '</title></head><body>' . $text . '</body></html>',"From: Idrinth's Tools (idrinth) <tools@idrinth.de>\r\n" .
                "X-Mailer: PHP\r\n" .
                "Reply-To: Idrinth's Tickets (idrinth) <ticket@idrinth.de>\r\n" .
                "MIME-Version: 1.0\r\n" .
                "Content-Type: text/html; charset=utf-8\r\n" .
                "Content-Transfer-Encoding: 8bit\r\n");
    }
    function userChoice($hash,$type = 0) {
        $id = hexdec(substr($hash,32));
        $hash = substr($hash,0,32);
        if($id == 0 || preg_match('#[^a-z0-9]#',$hash)) {
            return false;
        }
        $res = $this->db->query("SELECT id FROM user "
                . "WHERE MD5(CONCAT(email,login,ip_register))='" . $hash . "' "
                . "AND banned=0 "
                . "AND (ip_optIn='' OR ISNULL(ip_optIn)) "
                . "AND time_optIn='0' "
                . "AND id=" . $id . " "
                . "AND time_register>=" . (time() - 90000));
        if($res->num_rows == 1) {
            if($type == 0 && $this->db->query("UPDATE user SET ip_optIn='" . $_SERVER['REMOTE_ADDR'] . "',time_optIn='" . time() . "' WHERE id=" . $id)) {
                $this->forceLogin($id);
                return TRUE;
            } elseif($type == 1 && $this->db->query("UPDATE user SET banned=1,ip_optIn='" . $_SERVER['REMOTE_ADDR'] . "',time_optIn='" . time() . "' WHERE id=" . $id)) {
                return TRUE;
            }
        }
        return FALSE;
    }
    function forceLogin($id) {
        $res = $this->db->query("SELECT login,display,pass FROM user WHERE id=" . $id);
        $user = $res->fetch_assoc();
        $res->free();
        $_COOKIE['iadb'] = $user['login'] . '|' . sha1($user['display'] . $user['pass']);
        $GLOBALS['user'] = new user();
    }
    function getRegister() {
        if($this->user->loggedIn) {
            return '<p>You are registered and logged in already.</p>';
        }
        if($GLOBALS['parser']->getPage(2) == 'activate' && $GLOBALS['parser']->getPage(3)) {
            if($this->userChoice($GLOBALS['parser']->getPage(3))) {
                return '<p>Your account has been activated and you were logged in.</p>';
            } else {
                return '<p>Account is either to old, or was already activated or banned.</p>';
            }
        } elseif($GLOBALS['parser']->getPage(2) == 'deactivate' && $GLOBALS['parser']->getPage(2)) {
            if($this->userChoice($GLOBALS['parser']->getPage(3),1)) {
                return '<p>Your eMail was banned. Sorry to bother you.</p>';
            } else {
                return '<p>Account is either to old, or was already activated or banned.</p>';
            }
        }
        if(count($_POST) > 0 && $_POST['login'] && $_POST['email'] && $_POST['display'] && $_POST['password'][1] && $_POST['password'][1] = $_POST['password'][0] && $_POST['password'][0]) {
            if($this->makeUser()) {
                return '<p>An eMail was send to the adress given. You\'ll need to activate your account with the link given within.</p><br /><br />'
                . '<p>If you don\'t get an email within half an hour, please notify me on discord or the ror-forum, then I\'ll manually activate the account.</p>';
            }
        }
        return '<form method="post">'
                . '<fieldset><legend>Your Data</legend>'
                . '<div class="wrapper">'
                . '<label for="display">Display Name</label>'
                . '<input type="text" name="display" id="display" value=""/>'
                . '</div>'
                . '<div class="wrapper">'
                . '<label for="email">eMail</label>'
                . '<input type="email" name="email" id="email" value=""/>'
                . '</div>'
                . '</fieldset>'
                . '<fieldset><legend>Your Login</legend>'
                . '<div class="wrapper">'
                . '<label for="user">Login</label>'
                . '<input type="text" name="login" id="user" value=""/>'
                . '</div>'
                . '<div class="wrapper">'
                . '<label for="pw-1">Password</label>'
                . '<input type="password" name="password[]" id="pw-1" value=""/>'
                . '</div>'
                . '<div class="wrapper">'
                . '<label for="pw-2">Password</label>'
                . '<input type="password" name="password[]" id="pw-2" value=""/>'
                . '</div>'
                . '</fieldset>'
                . '<fieldset>'
                . '<div class="wrapper">'
                . '<button type="submit">Register</button>'
                . '</div></fieldset>'
                . '</form><p>An account is required for commenting, editing descriptions and uploading files. for legal reasons the site stores IP-adresses when posting the mentioned content.</p>';
    }
    function getProfile($name) {
        if(!$this->user->loggedIn) {
            header('Location: /account/login/');
            exit;
        }
        $res;
        $own = FALSE;
        if($name == '') {
            $own = TRUE;
            $res = $this->db->query("SELECT slug,login,display,admin,points,banned,time_register FROM user WHERE id='" . $GLOBALS['user']->id . "'");
        } else if($name == $GLOBALS['user']->slug) {
            header('Location: /account/');
            exit;
        } else {
            $res = $this->db->query("SELECT slug,display,admin,points,banned,time_register FROM user WHERE slug LIKE '" . $this->db->real_escape_string($name) . "'");
        }
        $user = $res->fetch_assoc();
        if(!$user) {
            header('Location: /account/');
            exit;
        }
        $res->free();
        $content = '';
        if($own) {
            if(isset($_POST['pw-o']) && isset($_POST['pw-o']) && $_POST['pw-o'] && $_POST['pw-o']) {
                $this->db->query("UPDATE user "
                        . "SET pass='" . md5($this->user->id . $_POST['pw-n']) . "' "
                        . "WHERE id='" . $this->user->id . "' "
                        . "AND pass='" . md5($this->user->id . $_POST['pw-0']) . "'");
                if($this->db->affected_rows == 1) {
                    setcookie('iadb',$this->user->login . '|' . sha1($this->user->display . md5($this->user->id . $_POST['pw-n'])),time() + 2592000,'/',$GLOBALS['hostname']);
                    $content .= '<p>Your password was changed.</p>';
                }
            }
            if($_FILES['pic']['size'] > 0 && !$_FILES['pic']['error']) {
                $im = FALSE;
                $sizes = getimagesize($_FILES['pic']['tmp_name']);
                if($sizes['mime'] == 'image/jpeg') {
                    $im = imagecreatefromjpeg($_FILES['pic']['tmp_name']);
                } elseif($sizes['mime'] == 'image/png') {
                    $im = imagecreatefrompng($_FILES['pic']['tmp_name']);
                } elseif($sizes['mime'] == 'image/gif') {
                    $im = imagecreatefromgif($_FILES['pic']['tmp_name']);
                }
                if($im) {
                    $im2 = imagecreatetruecolor(100,100);
                    imagecopyresized($im2,$im,0,0,0,0,100,100,$sizes[0],$sizes[1]);
                    imagepng($im2,$_SERVER['DOCUMENT_ROOT'] . '/img/profile/' . $this->user->slug . '.png');
                    unset($im2);
                }
                unset($sizes);
                unset($im);
            }
        }
        $content .= '<img class="left" title="' . $user['display'] . '" alt="' . $user['display'] . '" src="' .
                (is_file($_SERVER['DOCUMENT_ROOT'] . '/img/profile/' . $user['slug'] . '.png')?'/img/profile/' . $user['slug'] . '.png':'/img/profile/default.png') . '"/>';
        if($own) {
            $content.='<form method="post" enctype="multipart/form-data">';
            $content.='<div class="formRow"><label for="pic">New Picture</label><input type="file" name="pic" id="pic"/></div>';
            $content.='<button type="submit">
                            <svg class="icon"><use xlink:href="https://'. $GLOBALS['hostname'] .'/feather-sprite.svg#image"/></svg>
                            Change Picture
                        </button>';
            $content.='</form>';
            $content.='<form method="post">';
        }
        $content .= '<table class="account-table right"><tbody>';
        $content .= '<tr><th>Display Name</th><td>' . $user['display'] . '</td></tr>';
        $content .= ($user['admin']?'<tr style="color:#00ff00"><th>Admin</th><td>Yes</td></tr>':'');
        $content .= ($user['banned']?'<tr style="color:#ff0000"><th>Banned</th><td>Yes</td></tr>':'');
        $content .= '<tr><th>Current Points</th><td>' . number_format($user['points'],0,'.',',') . '</td></tr>';
        $content .= '<tr><th>Registered</th><td>' . date('Y-m-d H:i',$user['time_register']) . '</td></tr>';
        if($own) {
            $content .= '<tr>
                            <th>Password</th>
                            <td>
                                <div class="formRow">
                                    <label for="pw-n">New</label>
                                    <input type="password" name="pw-n" id="pw-n">
                                </div>
                                <div class="formRow">
                                    <label for="pw-o">Old</label>
                                    <input type="password" name="pw-o" id="pw-o">
                                </div>
                                <button type="submit">
                                    <svg class="icon"><use xlink:href="https://'. $GLOBALS['hostname'] .'/feather-sprite.svg#lock"/></svg>
                                    Change Password
                                </button>
                            </td>
                        </tr>';
        }
        return $content . '</tbody></table>' . ($own?'</form>':'');
    }
}
