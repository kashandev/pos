<?php
// HTTP
define('HTTP_SERVER', 'http://' . $_SERVER['HTTP_HOST'] . '/ifg/');
define('HTTP_BASE', 'http://' . $_SERVER['HTTP_HOST'] . '/ifg/assets/');
define('HTTP_IMAGE', 'http://' . $_SERVER['HTTP_HOST'] . '/ifg/image/');
define('HTTP_EVENT_FILE', 'http://' . $_SERVER['HTTP_HOST'] . '/ifg/event_file/');

// HTTPS
define('HTTPS_SERVER', 'http://' . $_SERVER['HTTP_HOST'] . '/ifg/');
define('HTTPS_BASE', 'http://' . $_SERVER['HTTP_HOST'] . '/ifg/assets/');
define('HTTPS_IMAGE', 'http://' . $_SERVER['HTTP_HOST'] . '/ifg/image/');
define('HTTPS_EVENT_FILE', 'http://' . $_SERVER['HTTP_HOST'] . '/ifg/event_file/');

// DIR
define('DIR_ROOT', getcwd() . "/");
define('DIR_APPLICATION', DIR_ROOT . '/admin/');
define('DIR_PLUGINS', DIR_ROOT . 'assets/plugins/');
define('DIR_SYSTEM', DIR_ROOT . 'system/');
define('DIR_DATABASE', DIR_SYSTEM .  'database/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_IMAGE', DIR_ROOT . 'image/');
define('DIR_CACHE', DIR_SYSTEM . 'cache/');
define('DIR_UPLOAD', DIR_ROOT . 'upload/');
define('DIR_LOGS', DIR_SYSTEM . 'logs/');
define('DIR_CATALOG', DIR_ROOT . 'catalog/');
define('FPDF_FONTPATH',DIR_ROOT . 'fpdf_font/');
define('DIR_EVENT_FILE', DIR_ROOT . 'event_file/');

// DB
define('DB_DRIVER', 'dbmysqli');
define('DB_HOSTNAME', '127.0.0.1');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_DATABASE', 'ifg_master');
define('DB_PREFIX', '');

// Config
define('CONFIG_DISPLAY_ERROR', 0);
define('CONFIG_LOG_ERROR', 1);
define('CONFIG_ERROR_FILE_NAME', 'error_'.date('Ymd').'.log');

// Config
define('CONFIG_APPLICATION_CODE', 'if');
define('CONFIG_APPLICATION_NAME', 'IFG');

// Date
define('PICKER_DATE', 'DD-MM-YYYY');
define('PICKER_DATE_TIME', 'DD-MM-YYYY HH:mm:ss');
define('PICKER_TIME', 'HH:mm');
define('STD_DATE', 'd-m-Y');
define('MYSQL_DATE', 'Y-m-d');
define('STD_DATETIME', 'd-m-Y H:i:s');
define('MYSQL_DATETIME', 'Y-m-d H:i:s');
?>