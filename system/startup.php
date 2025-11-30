<?php

// Error Reporting
error_reporting(E_ALL | E_STRICT);
//error_reporting(E_ERROR | E_USER_ERROR);
// Check Version
if (version_compare(phpversion(), '5.1.0', '<') == TRUE) {
    exit('PHP5.1+ Required');
}

// Register Globals
if (ini_get('register_globals')) {
    ini_set('session.use_cookies', 'On');
    ini_set('session.use_trans_sid', 'Off');

    session_set_cookie_params(0, '/');
    session_start();

    $globals = array($_REQUEST, $_SESSION, $_SERVER, $_FILES);

    foreach ($globals as $global) {
        foreach (array_keys($global) as $key) {
            unset($$key);
        }
    }
}

// Magic Quotes Fix
if (ini_get('magic_quotes_gpc')) {

    function clean($data) {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[clean($key)] = clean($value);
            }
        } else {
            $data = stripslashes($data);
        }

        return $data;
    }

    $_GET = clean($_GET);
    $_POST = clean($_POST);
    $_REQUEST = clean($_REQUEST);
    $_COOKIE = clean($_COOKIE);
}

// Windows IIS Compatibility  
if (!isset($_SERVER['DOCUMENT_ROOT'])) {
    if (isset($_SERVER['SCRIPT_FILENAME'])) {
        $_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], 0, 0 - strlen($_SERVER['PHP_SELF'])));
    }
}

if (!isset($_SERVER['DOCUMENT_ROOT'])) {
    if (isset($_SERVER['PATH_TRANSLATED'])) {
        $_SERVER['DOCUMENT_ROOT'] = str_replace('\\', '/', substr(str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']), 0, 0 - strlen($_SERVER['PHP_SELF'])));
    }
}

if (!isset($_SERVER['REQUEST_URI'])) {
    $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);

    if (isset($_SERVER['QUERY_STRING'])) {
        $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
    }
}

// Helper
require_once(DIR_SYSTEM . 'helper/json.php');
require_once(DIR_SYSTEM . 'helper/utf8.php');
require_once(DIR_SYSTEM . 'helper/utility.php');

// Engine
require_once(DIR_SYSTEM . 'engine/action.php');
require_once(DIR_SYSTEM . 'engine/controller.php');
require_once(DIR_SYSTEM . 'engine/hcontroller.php');
require_once(DIR_SYSTEM . 'engine/front.php');
require_once(DIR_SYSTEM . 'engine/loader.php');
require_once(DIR_SYSTEM . 'engine/model.php');
require_once(DIR_SYSTEM . 'engine/hmodel.php');
require_once(DIR_SYSTEM . 'engine/registry.php');

// Common
require_once(DIR_SYSTEM . 'library/config.php');
require_once(DIR_SYSTEM . 'library/db.php');
require_once(DIR_SYSTEM . 'library/url.php');
require_once(DIR_SYSTEM . 'library/log.php');
require_once(DIR_SYSTEM . 'library/request.php');
require_once(DIR_SYSTEM . 'library/response.php');
require_once(DIR_SYSTEM . 'library/cache.php');
require_once(DIR_SYSTEM . 'library/session.php');
require_once(DIR_SYSTEM . 'library/language.php');
require_once(DIR_SYSTEM . 'library/document.php');
require_once(DIR_SYSTEM . 'library/image.php');
require_once(DIR_SYSTEM . 'library/tcpdf/tcpdf.php');
require_once(DIR_SYSTEM . 'library/PHPExcel.php');
require_once(DIR_SYSTEM . 'library/phpmailer.php');

//for debuging
function d($mParam, $bExit = 0, $bVarDump = 0) {

    echo '<hr><pre>';
    ob_start();
    print get_back_trace("\n");
    if (!$bVarDump) {
        print_r($mParam);
    } else {
        var_dump($mParam);
    }
    $sStr = htmlspecialchars(ob_get_contents());
    ob_clean();
    echo $sStr;
    echo '</pre><hr>';
    if ($bExit)
        exit;
}

function dStr($mParam, $bVarDump = 0) {

    $str = '<hr><pre>';
    ob_start();
    print get_back_trace("\n");
    if (!$bVarDump) {
        print_r($mParam);
    } else {
        var_dump($mParam);
    }
    $sStr = htmlspecialchars(ob_get_contents());
    ob_clean();
    $str .= $sStr;
    $str .= '</pre><hr>';

    return $str;
}

function get_back_trace($NL = "\n") {
    $dbgTrace = debug_backtrace();
    $dbgMsg = "Trace @ ".date('YmdHis')." [";
    foreach ($dbgTrace as $dbgIndex => $dbgInfo) {
        if ($dbgIndex > 0 && isset($dbgInfo['file'])) {
            $dbgMsg .= "\t at $dbgIndex  " . $dbgInfo['file'] . " (line {$dbgInfo['line']}) -> {$dbgInfo['function']}(" . count($dbgInfo['args']) . ")$NL";
        }
    }
    $dbgMsg .= "]" . $NL;
    return $dbgMsg;
}

function getGUID(){
    if (function_exists('com_create_guid')){
        return com_create_guid();
    }else{
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            .chr(125);// "}"
        return $uuid;
    }
}
?>