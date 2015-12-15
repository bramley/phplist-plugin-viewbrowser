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
define('XORmask', '6f409c5681427eeaaaaa495797642e4b');

$GLOBALS['systemroot'] = '/home/duncan/Development/GitHub/phplist3/public_html/lists';
$GLOBALS['public_scheme'] = 'http';
$GLOBALS['pageroot'] = '/lists';
$GLOBALS['website'] = 'mysite.com';
$GLOBALS['domain'] = 'mysite.com';
$GLOBALS['strUnsubscribe'] = 'unsubscribe';
$GLOBALS['strThisLink'] = 'this link';
$GLOBALS['strForward'] = 'Forward to a friend';
$GLOBALS['PoweredByText'] = 'Powered by phplist';
$GLOBALS['PoweredByImage'] = 'mysite.com';

include '/home/duncan/Development/GitHub/phplist-plugin-viewbrowser/plugins/ViewBrowserPlugin.php';
$pi = new ViewBrowserPlugin();
$pi->activate();

$GLOBALS['plugins'] = [
    'ViewBrowserPlugin' => $pi,
];
include '/home/duncan/Development/GitHub/phplist-plugin-common/plugins/CommonPlugin/Autoloader.php';

class phplistPlugin
{
    public function __construct()
    {
    }

    public function activate()
    {
    }

    public function setFinalDestinationEmail()
    {
    }
}

function s($text)
{
    if (func_num_args() > 1) {
        $args = func_get_args();
        array_shift($args);
        $text = vsprintf($text, $args);
    }
    return $text;
}

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
        case 'viewbrowser_attributes':
            return '';
            break;
        case 'viewbrowser_plugins':
            return "ContentAreas\nconditionalPlaceholderPlugin\nRssFeedPlugin\nViewBrowserPlugin";
            break;
        case 'html_email_style':
            return '<style></style>';
            break;
        case 'subscribeurl':
            return '';
            break;
        case 'unsubscribeurl':
            return '';
            break;
        case 'preferencesurl':
            return '';
            break;
        case 'forwardurl':
            return '';
            break;
        case 'confirmationurl':
            return '';
            break;
        case 'blacklisturl':
            return '';
            break;
        default:
            throw new Exception("config $key missing");
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

function parseLogoPlaceholders($content) {
    ## replace Logo placeholders
    preg_match_all('/\[LOGO\:?(\d+)?\]/', $content, $logoInstances);
    foreach ($logoInstances[0] as $index => $logoInstance) {
        $size = sprintf('%d', $logoInstances[1][$index]);
        if (!empty($size)) {
            $logoSize = $size;
        } else {
            $logoSize = '500';
        }
        //~ createCachedLogoImage($logoSize);
        $content = str_replace($logoInstance, 'ORGANISATIONLOGO'.$logoSize.'.png', $content);
    }
    return $content;
}


