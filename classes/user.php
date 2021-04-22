<?php
/**
 * Description of class
 *
 * @author Björn "Idrinth" Büttner <eldrim@gmx.de>
 */
class user {
  var $id=0;
  var $display='';
  var $slug='';
  var $email='';
  var $banned=0;
  var $points=0;
  var $admin=FALSE;
  var $loggedIn=FALSE;
  var $loggin='';
  var $hasPic=FALSE;
  public function __construct() {
    $user = array();
    if(isset($_POST['pass']) && $_POST['user']) {
      $res = $GLOBALS['db']->query("SELECT * FROM user WHERE MD5(CONCAT(id,'" . $GLOBALS['db']->real_escape_string($_POST['pass']) . "'))=pass AND time_optIn>0 AND login='" . $GLOBALS['db']->real_escape_string($_POST['user']) . "'");
      if($res) {
        $user = $res->fetch_assoc();
        if($user['id']) {
          setcookie('iadb',$user['login'] . '|' . sha1($user['display'] . $user['pass']),time() + 2592000,'/', $GLOBALS['hostname']);
        } else {
          $user = array();
        }
      }
    } elseif(isset($_COOKIE['iadb'])&&$_COOKIE['iadb']) {
      $res = $GLOBALS['db']->query("SELECT * FROM user WHERE CONCAT(login,'|',SHA1(CONCAT(display,pass)))='" . $GLOBALS['db']->real_escape_string($_COOKIE['iadb']) . "'");
      if($res) {
        $user = $res->fetch_assoc();
        if($user&&$user['id']) {
          setcookie('iadb',$user['login'] . '|' . sha1($user['display'] . $user['pass']),time() + 2592000,'/', $GLOBALS['hostname']);
        }
      }
    }
    if(is_array($user)&&isset($user['id'])&&$user['id']) {
      $this->loggedIn=TRUE;
      $this->banned=$user['banned'];
      $this->id=$user['id'];
      $this->display=$user['display'];
      $this->email=$user['email'];
      $this->points=$user['points'];
      $this->slug=$user['slug'];
      $this->login=$user['login'];
      $this->admin=($user['admin']==1);
      $this->hasPic=is_file($_SERVER['DOCUMENT_ROOT'].'/profile/'.$this->slug.'.png');
    }
  }
  function getProfilePic() {
    if($this->hasPic) {
      return '/img/profile/'.$this->slug.'.png';
    }
    return '/img/profile/default.png';
  }
  function isActive() {
    return $this->loggedIn&&!$this->banned;
  }
  function mayEdit() {
    return ($this->admin||$this->points>=0)&&$this->isActive();
  }
  function mayUpload() {
    return ($this->admin||$this->points>=25)&&$this->isActive();
  }
}