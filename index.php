<?php

ini_set('display_errors',0);
ini_set('error_reporting',-1);
//This File is the bare minimum needed for either of the calculators
define('IDRINTH',FALSE);
$descriptions = array(
    '' => 'A collection of tools for the MMORPG Warhammer Online: Age of Reckoning. Featuring a tanking calculator so far.',
    'tank' => 'A tool to calculate your real stats in bossfights in the MMOPRG Warhammer Online: Age of Reckoning.',
    'url-converter' => 'A tool to convert URIs from the old Warhammer Online tanking calculator to the more powerful, new one.',
    'ability-chain' => 'A tool to calculate the effects of cooldown and casttime increasers and decreasers on your performance in Warhammer Online.',
    'imprint' => 'The imprint of this page, so you know who you are dealing with.',
    'updates' => 'The updates applied to the website\'s tools so far.',
    'feed' => 'A rss-feed showing the latest updates to this collection of tools.',
    'signature' => 'A signature generator for the private Warhammer Online server at http://www.returnofreckoning.com.',
    'addons' => 'A collection of UI-Addons for Warhammer-Online.',
);
if(empty($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = $GLOBALS['hostname'];
}
require_once __DIR__.'/config.php';
require_once __DIR__ . '/classes/urlparser.php';
$GLOBALS['parser'] = new urlparser($_SERVER['REQUEST_URI']);
require_once __DIR__ . '/classes/user.php';
$GLOBALS['user'] = new user();
$calledPage = $GLOBALS['parser']->getPage();
$cPageOrig = $GLOBALS['parser']->endedWithSlash();
if(isset($_REQUEST['submit'])) {
    $_REQUEST['submit'] = NULL;
    unset($_REQUEST['submit']);
}
$canGZip = FALSE;
$status = '200';
$GLOBALS['canonical'] = $_SERVER['HTTP_HOST'] . '/';
$headers = apache_request_headers();
if(array_key_exists('Accept-Encoding',$headers) && strpos($headers['Accept-Encoding'],'gzip') !== FALSE) {
    $canGZip = TRUE;
}
$headers = NULL;
unset($headers);

$page = '';
$ext = $GLOBALS['parser']->extension;
$headers = array();
if($ext === '' || $ext === 'htm' || $ext === 'html' || $ext === 'php' || $ext === 'xml' || $ext === 'xhtml') {
    session_start();
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    if($calledPage === 'tank') {
        if($ext != '' || $cPageOrig != '/') {
            $status = '301';
        }
        require_once('classes/tankCalculator.php');
        require_once('classes/view.php');
        $ext = '/';
        $GLOBALS['canonical'] = str_replace('//','/',$GLOBALS['canonical'] . $calledPage . $ext);
        $calculator = new tankCalculator();
        $page = $calculator->generateView();
        $headers[] = 'Last-Modified: ' . date('r');
        $headers[] = 'Expires: ' . date('r',time() + 10);
    } elseif($calledPage === 'updates') {
        if($ext != '' || $cPageOrig != '/') {
            $status = '301';
        }
        require_once('classes/view.php');
        $ext = '/';
        $view = new view();
        $GLOBALS['canonical'] = str_replace('//','/',$GLOBALS['canonical'] . $calledPage . $ext);
        $page = $view->generateUpdatePage();
        $headers[] = 'Last-Modified: ' . date('r');
        $headers[] = 'Expires: ' . date('r',time() + 10);
    } elseif($calledPage === 'url-converter') {
        if($ext != '' || $cPageOrig != '/') {
            $status = '301';
        }
        require_once('classes/urlConverter.php');
        require_once('classes/view.php');
        $ext = '/';
        $GLOBALS['canonical'] = str_replace('//','/',$GLOBALS['canonical'] . $calledPage . $ext);
        $converter = new urlConverter();
        $page = $converter->converter();
        $headers[] = 'Last-Modified: ' . date('r');
        $headers[] = 'Expires: ' . date('r',time() + 10);
    } elseif($calledPage == 'ajax') {
        if($ext != 'xml') {
            $status = '301';
        }
        $ext = '.xml';
        $GLOBALS['canonical'] = str_replace('//','/',$GLOBALS['canonical'] . $calledPage . $ext);
        require_once('classes/ajax.php');
        $headers[] = 'Content-Type: text/xml; charset=UTF-8';
        $ajax = new ajax();
        $page = $ajax->getXML();
        $headers[] = 'Expires: ' . date('r',time() + 10);
    } elseif($calledPage == 'feed') {
        if($ext != 'xml') {
            $status = '301';
        }
        $ext = '.xml';
        $GLOBALS['canonical'] = str_replace('//','/',$GLOBALS['canonical'] . $calledPage . $ext);
        require_once('classes/view.php');
        $headers[] = 'Content-Type: text/xml; charset=UTF-8';
        $view = new view();
        $page = $view->rss_feed('https://' . $GLOBALS['canonical']);
        $headers[] = 'Expires: ' . date('r',time() + 3600);
    } elseif($calledPage == 'ability-chain') {
        if($ext != '' || $cPageOrig != '/') {
            $status = '301';
        }
        require_once('classes/view.php');
        require_once('classes/abilityChain.php');
        $ext = '/';
        $GLOBALS['canonical'] = str_replace('//','/',$GLOBALS['canonical'] . $calledPage . $ext);
        $abilityChain = new abilityChain();
        $page = $abilityChain->getPage();
        $headers[] = 'Expires: ' . date('r',time() + 10);
    } elseif($calledPage == 'signature') {
        if($ext != '' || $cPageOrig != '/') {
            $status = '301';
        }
        require_once('classes/view.php');
        require_once('classes/imageForm.php');
        $ext = '/';
        $GLOBALS['canonical'] = str_replace('//','/',$GLOBALS['canonical'] . $calledPage . $ext);
        $signature = new imageForm();
        $page = $signature->getPage();
        $headers[] = 'Expires: ' . date('r',time() + 1);
    } elseif($calledPage == 'account') {
        if($ext != '' || $cPageOrig != '/') {
            $status = '301';
        }
        require_once('classes/view.php');
        require_once('classes/account.php');
        $ext = '/';
        $GLOBALS['canonical'] = str_replace('//','/',$GLOBALS['canonical'] . $calledPage . $ext);
        $account = new account();
        $page = $account->getPage($GLOBALS['parser']->getPage(1));
        $headers[] = 'Expires: ' . date('r',time() + 1);
    } elseif($calledPage == 'addons') {
        if($ext != '' || $cPageOrig != '/') {
            $status = '301';
        }
        require_once('classes/view.php');
        require_once('classes/addons.php');
        $ext = '/';
        $GLOBALS['canonical'] = str_replace('//','/',$GLOBALS['canonical'] . $calledPage . $ext);
        $addons = new addons();
        $page = $addons->getPage($GLOBALS['parser']->getPage(1));
        $headers[] = 'Expires: ' . date('r',time() + 1);
    } elseif($calledPage == 'addon-api') {
        if($ext != '' || $cPageOrig != '/') {
            $status = '301';
        }
        require_once('classes/addonApi.php');
        $ext = '/';
        $GLOBALS['canonical'] = str_replace('//','/',$GLOBALS['canonical'] . $calledPage . $ext);
        $addons = new addonApi();
        $page = $addons->getPage($GLOBALS['parser']->getPage(1));
        $headers[] = 'Expires: ' . date('r',time() + 1);
    } elseif($calledPage == 'scenarios') {
        if($ext != '' || $cPageOrig != '/') {
            $status = '301';
        }
ini_set('display_errors',1);
        require_once('classes/Szenarios.php');
        $ext = '/';
        $GLOBALS['canonical'] = str_replace('//','/',$GLOBALS['canonical'] . $calledPage . $ext);
        $addons = new Scenarios();
        $page = $addons->getPage($GLOBALS['parser']->getPage(1));
        $headers[] = 'Expires: ' . date('r',time() + 1);
    } else {
        require_once('classes/view.php');
        if($ext != '' || $cPageOrig != '/') {
            $status = '301';
        }
        if($calledPage !== 'home' && is_file('static/' . $calledPage . '.static.php')) {
            $page = file_get_contents('static/' . $calledPage . '.static.php');
            $headers[] = 'Last-Modified: ' . date('r',filemtime('static/' . $calledPage . '.static.php'));
        } elseif($calledPage === '' || empty($calledPage)) {
            $page = file_get_contents('static/home.static.php');
            $headers[] = 'Last-Modified: ' . date('r',filemtime('static/home.static.php'));
        } else {
            $page = file_get_contents('static/home.static.php');
            $headers[] = 'Last-Modified: ' . date('r',filemtime('static/home.static.php'));
            $calledPage = '';
            $headers[] = 'Expires: ' . date('r');
            $status = '404';
        }
        $ext = '/';
        $GLOBALS['canonical'] = str_replace('//','/',$GLOBALS['canonical'] . $calledPage . $ext);
        $page = substr($page,strpos($page,'/*') + 2,strpos($page,'*/') - strpos($page,'/*') - 2);
        $view = new view();
        $page = $view->generateStaticPage($page,$calledPage);
        $headers[] = 'Expires: ' . date('r',time() + 100000);
    }
    $page = str_replace('###ACCOUNT###',$GLOBALS['user']->loggedIn?'Profile':'Login',$page);
    $page = str_replace('###canonical###','<link rel="canonical" href="https://' . $GLOBALS['parser']->getCanonical($ext) . '" />',$page);
    $page = str_replace('###description###','<meta name="description" content="' . (isset($descriptions[$calledPage])?$descriptions[$calledPage]:'') . '" />',$page);
    if($canGZip) {
        $headers[] = 'Content-Encoding: gzip';
        $page = gzencode($page);
    }
    $headers[] = 'Content-Length: ' . strlen($page);
} elseif($ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif' || $ext == 'ico') {
    if($ext == 'jpeg') {
        $ext = 'jpg';
    }
    if($calledPage == 'signature') {
        include_once 'classes/signature.php';
        $signature = new signature();
        $signature->main();
        exit;
    } elseif(is_file('img/' . $calledPage . '.' . $ext)) {
        $headers[] = 'Last-Modified: ' . date('r',filemtime('img/' . $calledPage . '.' . $ext));
        $headers[] = 'Content-Length: ' . filesize('img/' . $calledPage . '.' . $ext);
        if($ext === 'ico') {
            $headers[] = 'Content-Type: image/vnd.microsoft.icon';
        } else {
            $headers[] = 'Content-Type: image/' . $ext;
        }
        $headers[] = 'Expires: ' . date('r',time() + 3000000);
        $page = file_get_contents('img/' . $calledPage . '.' . $ext);
    } else {
        $headers[] = 'Content-Length: 0';
        $headers[] = 'Content-Type: image/' . $ext;
        $headers[] = 'Expires: ' . date('r');

        $status = '404';
    }
} elseif($ext == 'css') {
    if(is_file('styles/' . $calledPage . '.' . $ext)) {
        $headers[] = 'Last-Modified: ' . date('r',filemtime('styles/' . $calledPage . '.' . $ext));
        $page = file_get_contents('styles/' . $calledPage . '.' . $ext);
        //replace comments
        $page = preg_replace('#/\*[^\*]+\*/#','',$page);
        $page = preg_replace('#[/]{2}[^\n]*\n#','\n',$page);
        //remove unneeded nice formatting
        $page = preg_replace('#[\s]+#',' ',$page);
        //replace unneeded spaces around :
        $page = preg_replace('#[:][ ]#',':',$page);
        $page = preg_replace('#[ ][:]#',':',$page);
        $page = preg_replace('#[;][ ]#',';',$page);
        $page = preg_replace('#[ ][}]#','}',$page);
        $page = preg_replace('#[{][ ]#','{',$page);
        $page = preg_replace('#[ ][{]#','{',$page);
        $page = preg_replace('#[,][ ]#',',',$page);
        //removing unneeded semicolons
        $page = preg_replace('#[;][}]#','}',$page);
        //removing unneeded units at 0
        $page = preg_replace('#[:][0]px#',':0',$page);
        $page = preg_replace('#[ ][0]px#',' 0',$page);
        $page = preg_replace('#[,][0]px#',',0',$page);
        $page = preg_replace('#[:][0]pt#',':0',$page);
        $page = preg_replace('#[ ][0]pt#',' 0',$page);
        $page = preg_replace('#[,][0]pt#',',0',$page);
        $page = preg_replace('#[:][0]\%#',':0',$page);
        $page = preg_replace('#[ ][0]\%#',' 0',$page);
        $page = preg_replace('#[,][0]\%#',',0',$page);
        $page = preg_replace('#[:][0]em#',':0',$page);
        $page = preg_replace('#[ ][0]em#',' 0',$page);
        $page = preg_replace('#[,][0]em#',',0',$page);
        $page = preg_replace('#(@charset "[^"]*") #',"$1\n",$page);
        //removing left overs
        trim($page);
        if($canGZip) {
            $headers[] = 'Content-Encoding: gzip';
            $page = gzencode($page);
        }
        $headers[] = 'Content-Length: ' . filesize('styles/' . $calledPage . '.' . $ext);
        $headers[] = 'Content-Type: text/css; charset=UTF-8';
        $headers[] = 'Expires: ' . date('r',time() + 3000000);
    } else {
        $headers[] = 'Content-Length: 0';
        $headers[] = 'Content-Type: text/' . $ext;
        $headers[] = 'Expires: ' . date('r');

        $status = '404';
    }
} elseif($ext == 'js') {
    if(is_file('scripts/' . $calledPage . '.' . $ext) && !($calledPage == 'piwik' && filemtime('scripts/' . $calledPage . '.' . $ext) < time() - 3000000)) {
        $headers[] = 'Last-Modified: ' . date('r',filemtime('scripts/' . $calledPage . '.' . $ext));
        $page = file_get_contents('scripts/' . $calledPage . '.' . $ext);
        if($canGZip) {
            $headers[] = 'Content-Encoding: gzip';
            if(!is_file('scripts/' . $calledPage . '.gzip.' . $ext) || filemtime('scripts/' . $calledPage . '.gzip.' . $ext) <= filemtime('scripts/' . $calledPage . '.' . $ext)) {
                $page = file_get_contents('scripts/' . $calledPage . '.' . $ext);
                $page = gzencode($page);
                file_put_contents('scripts/' . $calledPage . '.gzip.' . $ext,$page);
            } else {
                $page = file_get_contents('scripts/' . $calledPage . '.gzip.' . $ext);
            }
        } else {
            $page = file_get_contents('scripts/' . $calledPage . '.' . $ext);
        }
        $headers[] = 'Content-Length: ' . strlen($page);
        $headers[] = 'Content-Type:text/javascript;charset=UTF-8';
        $headers[] = 'Expires: ' . date('r',time() + 3000000);
    } elseif($calledPage == 'piwik') {
        $headers[] = 'Last-Modified: ' . date('r',filemtime($_SERVER['DOCUMENT_ROOT'] . '../statistics/piwik.js'));
        $page = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '../statistics/piwik.js');
        file_put_contents('scripts/piwik.js',$page);
        if($canGZip) {
            $headers[] = 'Content-Encoding: gzip';
            $page = gzencode($page);
        }
        $headers[] = 'Content-Length: ' . filesize($_SERVER['DOCUMENT_ROOT'] . '../statistics/piwik.js');
        $headers[] = 'Content-Type:text/javascript;charset=UTF-8';
        $headers[] = 'Expires: ' . date('r',time() + 3000000);
    } else {
        $headers[] = 'Content-Length: 0';
        $headers[] = 'Content-Type:text/javascript;charset=UTF-8';
        $headers[] = 'Expires: ' . date('r');

        $status = '404';
    }
} else {
    $headers[] = 'Content-Length: 0';
    $headers[] = 'Expires: ' . date('r');
    $status = '404';
}
$headers[] = 'Cache-Control: private';
$headers[] = 'Vary: Accept-Encoding';
if($ext == '/' || $ext == 'xml') {
    if($status == '301') {
        $page = '';
        $headers[] = 'Content-Length: 0';
        $headers[] = 'Location: https://' . str_replace('//','/',$_SERVER['HTTP_HOST'] . '/' . $calledPage . ($ext == '/'?'/':'.' . $ext));
    } elseif($status == '404') {
        $headers[] = 'Location: https://' . str_replace('//','/',$_SERVER['HTTP_HOST'] . '/');
    }
}
foreach($headers as $value) {
    header($value,TRUE,$status);
}
echo $page;
