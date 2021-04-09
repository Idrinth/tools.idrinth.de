<?php
if(!defined('IDRINTH')) {
    die('No direct calls to this file allowed.');
}
require_once('classes/combatCalculations.php');
/**
 * Generiert eine HTML-Ausgabe
 * */
class view {
    var $template = NULL;
    function __construct($template = 'template.htm') {
        $this->template = file_get_contents(str_replace('//','/',$_SERVER['DOCUMENT_ROOT'] . '/templates/' . $template));
    }
    /**
     * Generiert den auszugebenden Content
     *
     * @param array $rows Ein assoziatives Array mit vorgegebener Struktur
     *
     * @return string die HTML-Ausgabe
     * */
    protected function generateColumns($rows) {
        $content = '<div class="wrapper cols-' . (count($rows)) . '"><form id="calculator-form" method="get" action="###baseURL###/###calculator###/">';
        if(isset($_GET['dN'])) {
            $content .= '<input type="hidden" name="dN" value="' . $_GET['dN'] . '">';
        }
        if(isset($_GET['aN'])) {
            $content .= '<input type="hidden" name="aN" value="' . $_GET['aN'] . '">';
        }
        foreach($rows as $counter => $row) {
            $content .= '<div id="col-' . $counter . '" class="floating-col">';
            $content .= '<h2>' . $row['title'] . '</h2>';
            if($row['mode'] == 'input') {
                foreach($row['values'] as $cell) {
                    $content .= '<div class="wrapper">';
                    if(!empty($cell['label'])) {
                        $content .= '<label for="' . $cell['name'] . '">' . $cell['label'] . '</label>';
                    }
                    if($cell['type'] == 'check') {
                        $content .= '<input type="hidden" value="0" name="' . $cell['name'] . '">';
                        $content .= '<input type="checkbox" value="' . (isset($cell['value'])?$cell['value']:1) . '"' . (isset($cell['script'])?' onchange="' . $cell['script'] . '"':'') . ($cell['checked']?' checked="checked"':'') . ' name="' . $cell['name'] . '" id="' . $cell['name'] . '">';
                    } elseif($cell['type'] != 'submit') {
                        $content .= '<input ';
                        if(isset($cell['shared'])) {
                            $content .= 'data-ajax="shared" ';
                        }
                        $content .= 'type="' . $cell['type'] . '" name="' . $cell['name'] . '" id="' . $cell['name'] . '" value="' . $cell['value'] . '"/>';
                    } else {
                        $content .= '<button type="' . $cell['type'] . '" name="' . $cell['name'] . '" id="' . $cell['name'] . '">' . $cell['value'] . '</button>';
                    }
                    if(array_key_exists('unit',$cell)) {
                        $content .= '<div class="unit">' . $cell['unit'] . '</div>';
                    }
                    $content .= '</div>';
                }
            } elseif($row['mode'] == 'output') {
                foreach($row['values'] as $cell) {
                    $content .= '<div class="wrapper output">';
                    $content .= '<p class="label">' . $cell['label'] . ' </p>';
                    $content .= '<div class="comparison">';

                    if(!is_array($cell['name'])) {
                        $cell['name'] = array($cell['name']);
                    }
                    if(!is_array($cell['value'])) {
                        $cell['value'] = array($cell['value']);
                    }

                    for($counter = 0; $counter < count($cell['name']); $counter++) {
                        $content .= '<div>';
                        $content .= '<span id="' . $cell['name'][$counter] . '">' . $cell['value'][$counter] . '</span>';
                        if(array_key_exists('unit',$cell)) {
                            $content .= '<span class="unit">' . $cell['unit'] . '</span>';
                        }

                        if($counter > 0) {
                            $content .= '<div class="compare">';
                            if($cell['value'][0] > $cell['value'][$counter] && $cell['value'][$counter] != '&#8734;') {
                                $content .= '&darr;';
                            } elseif($cell['value'][0] < $cell['value'][$counter] && $cell['value'][0] != '&#8734;') {
                                $content .= '&uarr;';
                            } else {
                                $content .= '=';
                            }
                            $content .= '</div>';
                        }
                        $content .= '</div>';
                    }
                    $content .= '</div>';
                    $content .= '</div>';
                }
            }

            $content .= '</div>';
        }
        $content .= '</form></div>';
        return $content;
    }
    function rss_feed($self) {
        $content = '';
        require_once __DIR__ . '/../ressources/updates.php';
        foreach($updates as $mainVersion => $a1) {
            foreach($a1 as $subVersion => $a2) {
                foreach($a2 as $bugVersion => $version) {
                    $content .= '
            <item>
              <title>Version ' . $mainVersion . '.' . $subVersion . '.' . $bugVersion . '</title>
              <guid>https://' . $GLOBALS['hostname'] . '/updates/#v' . $mainVersion . '-' . $subVersion . '-' . $bugVersion . '</guid>
              <link>https://' . $GLOBALS['hostname'] . '/updates/#v' . $mainVersion . '-' . $subVersion . '-' . $bugVersion . '</link>
              <pubDate>' . date('r',strtotime($this->numberToDate($version['date']) . ' 00:00:00'
                            )) . '</pubDate>
              <description><![CDATA[<h2>Version ' . $mainVersion . '.' . $subVersion . '.' . $bugVersion . '</h2><ul>';
                    foreach($version['descriptions'] as $description) {
                        $content .= '<li>' . $description . '</li>';
                    }
                    $content .='</ul>]]></description>
              <category><![CDATA[' . implode(']]></category><category><![CDATA[',$version['categories']) . ']]></category>
            </item>';
                }
            }
        }
        return '<?xml version="1.0" encoding="utf-8" ?>
 <rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
 <channel>
 <title>Updates RSS-Feed | Idrinth\'s Tools</title>
 <atom:link href="' . $self . '" rel="self" type="application/rss+xml" />
 <link>https://' . $GLOBALS['hostname'] . '</link>
 <description>' . $GLOBALS['descriptions'][''] . '</description>
 <language>en-gb</language>
 <pubDate>' . date('r',filemtime(__DIR__ . '/../ressources/updates.php')) . '</pubDate>
 <image>
 <title>Idrinth</title>
 <url>https://' . $GLOBALS['hostname'] . '/logo.png</url>
 <link>https://' . $GLOBALS['hostname'] . '</link>
 </image>' . $content . '
                 </channel>
                 </rss>';
    }
    /**
     * Generiert die News-Ansicht
     *
     * @return string die HTML-Ausgabe
     */
    protected function generateSidebar() {
        include __DIR__ . '/../ressources/updates.php';
        $content = '<div id="side-column">
              <h2>last Updates</h2>
                            <ul>';
        $count = 0;
        foreach($updates as $mainVersion => $a1) {
            foreach($a1 as $subVersion => $a2) {
                foreach($a2 as $bugVersion => $version) {
                    $content .= '
            <li><a href="/updates/#v' . $mainVersion . '-' . $subVersion . '-' . $bugVersion . '">Version ' . $mainVersion . '.' . $subVersion . '.' . $bugVersion . '</a>(' . implode(',',$version['categories']) . ') ' .
                            $this->numberToDate($version['date']) . '</li>';
                    if($count == 4) {
                        return $content . '
                            </ul>
                        </div>';
                    }
                    $count++;
                }
            }
        }
        return $content . '
                            </ul>
                        </div>';
    }
    /**
     * Fügt Daten in das HTML-Template ein
     *
     * @param $page Die Seitendaten in einem Array
     *
     * @return string der html-output
     * */
    public function generatePage($page) {
        $markers = array();
        $markers['title'] = $page['title'];
        $markers['pagetitle'] = $page['title'] . " - Idrinth's Tools";
        $markers['content'] = $this->generateColumns($page['rows']);
        $markers['baseURL'] = 'https://' . $_SERVER['HTTP_HOST'];
        $markers['calculator'] = $page['path'];
        $markers['version'] = '';
        $page['version']['Calculator Core'] = combatCalculations::$version;
        foreach($page['version'] as $key => $value) {
            $markers['version'] .= '<li>' . $key . ' v' . $value . '</li>';
        }
        $markers['sidebar'] = $this->generateSidebar();

        $markers['head-js'] = '';
        foreach($page['head-js'] as $value) {
            $markers['head-js'] .= '<script type="text/javascript" src="' . $value . '.js"></script>';
        }
        $markers['foot-js'] = '';
        foreach($page['foot-js'] as $value) {
            $markers['foot-js'] .= '<script type="text/javascript" src="' . $value . '.js"></script>';
        }
        $markers['styles'] = '<link href="layout.css" rel="stylesheet" type="text/css" />';

        foreach($markers as $key => $value) {
            $this->template = str_replace('###' . $key . '###',$value,$this->template);
        }
        return $this->template;
    }
    /**
     * Erstellt eine Seite anhand eines Templates für den Content
     *
     * @param string $template das template für den Seiteninhalt
     * @param array $page the data to enter
     * */
    function generateTemplateBasedPage($template,$page) {
        $markers = array();
        $markers['title'] = $page['title'];
        $markers['pagetitle'] = $page['title'] . " - Idrinth's Tools";
        $markers['content'] = $this->generateColumns($page['rows']);
        $markers['baseURL'] = 'https://' . $_SERVER['HTTP_HOST'];
        $markers['calculator'] = $page['path'];
        $markers['version'] = '';
        $page['version']['Calculator Core'] = combatCalculations::$version;
        foreach($page['version'] as $key => $value) {
            $markers['version'] .= '<li>' . $key . ' v' . $value . '</li>';
        }
        $markers['sidebar'] = $this->generateSidebar();
        $markers['head-js'] = '';
        foreach($page['head-js'] as $value) {
            $markers['head-js'] .= '<script type="text/javascript" src="' . $value . '.js"></script>';
        }
        $markers['foot-js'] = '';
        foreach($page['foot-js'] as $value) {
            $markers['foot-js'] .= '<script type="text/javascript" src="' . $value . '.js"></script>';
        }
        $markers['styles'] = '<link href="layout.css" rel="stylesheet" type="text/css" />';
        $markers['content'] = file_get_contents('templates/' . $template);

        foreach($page['content'] as $key => $value) {
            $markers[$key] = $value;
        }

        foreach($markers as $key => $value) {
            $this->template = str_replace('###' . $key . '###',$value,$this->template);
        }
        return $this->template;
    }
    /**
     * Generates a Page from static content
     *
     * @param string $page der feste Seiteninahlt
     * @param string $calledPage der Name der aufgerufenen Seite
     *
     * @return string der html-output
     * */
    function generateStaticPage($page,$calledPage) {
        $markers = array();
        $calledPage = strtoupper(substr($calledPage,0,1)) . substr($calledPage,1);
        $markers['pagetitle'] = $calledPage;
        $markers['title'] = $calledPage;
        if($markers['pagetitle'] === '') {
            $markers['pagetitle'] = "Idrinth's Tools";
            $markers['title'] = "Idrinth's Tools";
        } else {
            $markers['pagetitle'] .= " - Idrinth's Tools";
        }
        $markers['content'] = $page;
        $markers['baseURL'] = 'https://' . $_SERVER['HTTP_HOST'];
        $markers['version'] = '<li>Calculator Core v' . combatCalculations::$version . '</li>';
        $markers['sidebar'] = $this->generateSidebar();
        $markers['head-js'] = '';
        $markers['styles'] = '<link href="layout.css" rel="stylesheet" type="text/css" />';
        $markers['foot-js'] = '';

        foreach($markers as $key => $value) {
            $this->template = str_replace('###' . $key . '###',$value,$this->template);
        }
        return $this->template;
    }
    function generateUpdatePage() {
        include __DIR__ . '/../ressources/updates.php';
        $content = '';
        foreach($updates as $mainVersion => $a1) {
            foreach($a1 as $subVersion => $a2) {
                foreach($a2 as $bugVersion => $version) {
                    $content .= '<h2 id="v' . $mainVersion . '-' . $subVersion . '-' . $bugVersion . '">Version ' . $mainVersion . '.' . $subVersion . '.' . $bugVersion . '</h2><ul>';
                    foreach($version['descriptions'] as $description) {
                        $content .= '<li>' . $description . '</li>';
                    }
                    $content .='</ul>
                       <p><em>' . $this->numberToDate($version['date']) . '</em>';
                }
            }
        }
        $markers = array();
        $markers['title'] = 'Updates';
        $markers['pagetitle'] = "Updates - Idrinth's Tools";
        $markers['content'] = $content;
        $markers['baseURL'] = 'https://' . $_SERVER['HTTP_HOST'];
        $markers['calculator'] = 'updates';
        $markers['version'] .= '<li>Calculator Core v' . combatCalculations::$version . '</li>';
        $markers['sidebar'] = $this->generateSidebar();
        $markers['styles'] = '<link href="layout.css" rel="stylesheet" type="text/css" />';
        $markers['head-js'] = '';
        $markers['foot-js'] = '';

        foreach($markers as $key => $value) {
            $this->template = str_replace('###' . $key . '###',$value,$this->template);
        }
        return $this->template;
    }
    function numberToDate($num) {
        $year = floor($num / 10000);
        $month = floor(($num % 10000) / 100);
        $day = floor($num % 100);
        return $day . '.' . $month . '.' . $year;
    }
}