<?php
define('PHPLISTINIT', 1);
define("PLUGIN_ROOTDIR","/home/duncan/www/upload");
define("PLUGIN_ROOTDIRS",
   "/home/duncan/Development/GitHub/phplist-plugin-common/plugins"
. ";/home/duncan/Development/GitHub/phplist-plugin-viewbrowser/plugins"
);
define('EMAILTEXTCREDITS', true);
define('ALWAYS_ADD_USERTRACK', true);
define('CLICKTRACK', true);
define('XORmask', 0xdeadbeef);

$GLOBALS['systemroot'] = '/home/duncan/Development/GitHub/phplist3/public_html/lists';
$GLOBALS['public_scheme'] = 'http';
$GLOBALS['pageroot'] = '/lists';
$GLOBALS['plugins'] = [];
//~ include '/home/duncan/Development/PHP/phplist/config.php';
include '/home/duncan/Development/GitHub/phplist-plugin-common/plugins/CommonPlugin/Autoloader.php';

class phplistPlugin
{
    public function __construct()
    {
    }

    public function activate()
    {
    }
}

function s() {};

function cleanUrl($p)
{
    return $p;
};

function getConfig($key) {
    switch ($key) {
        case 'version':
            return '3.2.1';
            break;
        case 'website':
            return 'mysite.com';
            break;
        case 'viewbrowser_link':
            return 'View in your browser';
            break;
        default:
    }
};
function parsePlaceHolders($content, $array = array())
{
  foreach ($array as $key => $val) {
    $array[strtoupper($key)] = $val;
    $array[htmlentities(strtoupper($key),ENT_QUOTES,'UTF-8')] = $val;
    $array[str_ireplace(' ','&nbsp;',strtoupper($key))] = $val;
  }

  foreach ($array as $key => $val) {
    #  print '<br/>'.$key.' '.$val.'<hr/>'.htmlspecialchars($content).'<hr/>';
      if (stripos($content,'['.$key.']') !== false) {
        $content = str_ireplace('['.$key.']',$val,$content);
      } 
      if (preg_match('/\['.$key.'%%([^\]]+)\]/i',$content,$regs)) { ## @@todo, check for quoting */ etc
    #    var_dump($regs);
        if (!empty($val)) {
          $content = str_ireplace($regs[0],$val,$content);
        } else {
          $content = str_ireplace($regs[0],$regs[1],$content);
        }
      }
  }
  return $content;
}

function addHTMLFooter($message,$footer) {
  if (preg_match('#</body>#i',$message)) {
    $message = preg_replace('#</body>#i',$footer.'</body>',$message);
  } else {
    $message .= $footer;
  }
  return $message;
}

