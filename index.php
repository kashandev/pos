<?php
ini_set('session.save_path',realpath(dirname($_SERVER['DOCUMENT_ROOT']) . '/../session'));
ini_set('session.gc_maxlifetime',60*60*24);
ini_set('session.gc_probability',1);
ini_set('session.gc_divisor',1);
ini_set('date.timezone', 'Asia/Karachi');

// Version
define('VERSION', '1.0.1');

// Configuration
require_once('config.php');

// Install 
if (!defined('DIR_APPLICATION')) {
    header('Location: ../install/index.php');
    exit;
}

// Startup
require_once(DIR_SYSTEM . 'startup.php');

// Application Classes
require_once(DIR_SYSTEM . 'library/user.php');
//require_once(DIR_SYSTEM . 'library/currency.php');
//require_once(DIR_SYSTEM . 'library/weight.php');
//require_once(DIR_SYSTEM . 'library/length.php');
// Registry
$registry = new Registry();

// Loader
$loader = new Loader($registry);
$registry->set('load', $loader);

// Config
$config = new Config();
$registry->set('config', $config);
$config->load('cfg');

// Database
$db = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db', $db);

$db2 = new DB(DB_DRIVER, DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_DATABASE);
$registry->set('db2', $db2);

//// Settings
//$query = $db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0'");
//foreach ($query->rows as $setting) {
//	$config->set($setting['key'], $setting['value']);
//}
// Url
$url = new Url(HTTP_SERVER, HTTPS_SERVER);
$registry->set('url', $url);

// Log 
$log = new Log(CONFIG_ERROR_FILE_NAME);
$registry->set('log', $log);

function error_handler($errno, $errstr, $errfile, $errline) {
    global $log, $config;

    switch ($errno) {
        case E_NOTICE:
        case E_USER_NOTICE:
            $error = 'Notice';
            break;
        case E_WARNING:
        case E_USER_WARNING:
            $error = 'Warning';
            break;
        case E_ERROR:
        case E_USER_ERROR:
            $error = 'Fatal Error';
            break;
        default:
            $error = 'Unknown';
            break;
    }

    if (CONFIG_DISPLAY_ERROR) {
        d('<b>' . $error . '</b>: ' . $errstr . ' in <b>' . $errfile . '</b> on line <b>' . $errline . '</b>');
    }

    if (CONFIG_LOG_ERROR) {
        $log->write('PHP ' . $error . ':  ' . $errstr . ' in ' . $errfile . ' on line ' . $errline);
    }

    return true;
}

// Error Handler
set_error_handler('error_handler');

// Request
$request = new Request();
$registry->set('request', $request);

// Response
$response = new Response();
$response->addHeader('Content-Type: text/html; charset=utf-8');
$registry->set('response', $response);

// Cache
$cache = new Cache();
$registry->set('cache', $cache);

// Session
$session = new Session( CONFIG_APPLICATION_CODE );
$registry->set('session', $session);

// Language
$languages = array();

$model_language = $loader->model('common/language');
$rows = $model_language->getRows();


foreach ($rows as $result) {
    $languages[$result['code']] = $result;
    $languagesAvailable[] = $result['code'];
}

if(!(isset($session->data['language_code']) && in_array($session->data['language_code'], $languagesAvailable))) {
    $session->data['language_code'] = getBrowserLanguage($request->server['HTTP_ACCEPT_LANGUAGE'], $languagesAvailable, "en");
}

$session->data['language'] = $languages[$session->data['language_code']];

// Language
$language = new Language($session->data['language']['directory']);
$language->load($session->data['language']['filename']);
$registry->set('language', $language);

// Document
$document = new Document();
$registry->set('document', $document);

// User
$registry->set('user', new User($registry));

// Front Controller
$controller = new Front($registry);

// Import CSV
//$registry->set('importCSV',  new ImportCSV($registry));

// Login
$controller->addPreAction(new Action('common/home/login'));

// Permission
$controller->addPreAction(new Action('common/home/permission'));

// Router
if (isset($request->get['route'])) {
    $action = new Action($request->get['route']);
} else {
    $action = new Action('common/home');
}

// Dispatch
$controller->dispatch($action, new Action('error/not_found'));

// Output
$response->output();
?>