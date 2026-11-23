<?php
// HTTP
define('HTTP_CATALOG', 'http://web.xxx.test/');
define('HTTP_SERVER', HTTP_CATALOG . 'admin/');

// HTTPS
define('HTTPS_CATALOG', HTTP_CATALOG);
define('HTTPS_SERVER', HTTPS_CATALOG . 'admin/');

// DIR
define('DIR_ROOT', dirname(__DIR__) . '/');
define('DIR_APPLICATION', DIR_ROOT . 'admin/');
define('DIR_SYSTEM', DIR_ROOT . 'system/');
define('DIR_IMAGE', DIR_ROOT . 'image/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_CATALOG', DIR_ROOT . 'catalog/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mpdo');
define('DB_HOSTNAME', 'mysql5');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');
define('DB_DATABASE', '');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');
define('DEBUG', false);

// OpenCart API
define('OPENCART_SERVER', 'https://www.opencart.com/');
