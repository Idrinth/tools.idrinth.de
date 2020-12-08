<?php
/**
 * Description of imageForm
 *
 * @author BJ
 */
class imageForm {
  function getPage() {
    $view  = new view();
    return $view->generateStaticPage('<p>The graphic used was originally created by Dana.</p><p>I hereby like to thank her as well as all of the server\'s developers, mods and players for keeping WAR alive.<p><p>Sadly this service no longer works.</p>','Signature Generator');
    $db=&$GLOBALS['db'];
      include_once $_SERVER['DOCUMENT_ROOT'].'/ressources/dbupdate.php';
    if(isset($_POST['i'])) {
      $_POST['i']=  explode(',',  str_replace(';',',',$_POST['i']));
      foreach ($_POST['i'] AS $id) {
        #var_dump($id);
       # var_dump($db);
      $GLOBALS['db']->query("INSERT INTO signature (id,last_used,last_updated) VALUES ('".intval($id)."','".time()."','0')");
      }
    } else {
        $_POST['i'][0]=0;
    }
    $link = 'http://'.$_SERVER['HTTP_HOST'].'/signature.gif?'.
            'f='.intval($_POST['f']);
    foreach ($_POST['i'] AS $id) {
            $link .= '&amp;i[]='.intval($id);
    }
    $link .= '&amp;p='.intval($_POST['p']);
    $content .= '<p>The graphic used was originally created by Dana.</p><p>I hereby like to thank her as well as all of the server\'s developers, mods and players for keeping WAR alive.<a href="'.$link.'" target="_blank"><img src="'.$link.
            '" title="Signature" alt="Signature"/></a>
              <form method="post"><fieldset><legend>Your Character\'s Data</legend><div class="wrapper"><label for="i"><img width="16" src="help.png" alt="Help"/>ID<span class="onHover">You can find your ID in the URL of the armory. Up to 6 IDs can be entered divided by &quot;,&quot;. My ID is 236 for example and the URL of my armory-page is http://www.returnofreckoning.com/armory.php?character_id=236&character_name=Idrinth<br />For a longer explanation see <a href="https://idrinth.de/en/war-signature-generator/" target="_blank">my blog</a>.</span></label><input type="text" value="'.implode(',',$_POST['i']).'" name="i" id="i"/></div>
      <div class="wrapper"><label for="f">Font</label><select name="f" id="f">';
        foreach($fonts AS $key => $name) {
          $content.= '<option value="'.$key.($key==$_POST['f']?'" selected="selected':'').'">'.trim(substr($name,0,strpos($name,'.')),'-_ ').'</option>';
        }
      $content.='</select></div>'
              . '<div class="wrapper"><label for="p">Size</label><select name="p" id="p">';
      $counter=0;
      $basewidth=700;
      $baseheight=200;
      while($counter<11) {
        $content.= '<option value="'.$counter.($counter==$_POST['p']?'" selected="selected':'').'">'.($basewidth*(20-$counter)/20).'x'.($baseheight*(20-$counter)/20).'</option>';
        $counter++;
      }
      $content.= '</select></div></fieldset><fieldset>
      <div class="wrapper"><input type="submit" value="Make Signature"/></div></fieldset>
    </form>';
      if(count($_POST['i'])==1) {
    $content.='<h4>Display in HTML</h4><textarea readonly>'.
            htmlspecialchars('<a href="http://www.returnofreckoning.com/armory.php?character_name=&character_id='.intval($_POST['i'][0]).'"><img src="'.str_replace('&amp;','&',$link).'"/></a>').
            '</textarea>';
    $content.='<h4>Display in bbCode</h4><textarea readonly>'.
            htmlspecialchars('[url=http://www.returnofreckoning.com/armory.php?character_name=&character_id='.intval($_POST['i'][0]).'][img]'.str_replace('[','%5B',str_replace(']','%5D',str_replace('&amp;','&',$link))).'[/img][/url]').
            '</textarea>';
      } else {
    $content.='<h4>Display in HTML</h4><textarea readonly>'.
            htmlspecialchars('<a href="http://www.returnofreckoning.com/"><img src="'.str_replace('&amp;','&',$link).'"/></a>').
            '</textarea>';
    $content.='<h4>Display in bbCode</h4><textarea readonly>'.
            htmlspecialchars('[url=http://www.returnofreckoning.com/][img]'.str_replace('[','%5B',str_replace(']','%5D',str_replace('&amp;','&',$link))).'[/img][/url]').
            '</textarea>';
      }
    $view  = new view();
    return $view->generateStaticPage($content,'Signature Generator');
  }
}