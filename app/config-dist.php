<?php
// HTTP
define('HTTP_SERVER', 'http://web.xxx.test/');

// HTTPS
define('HTTPS_SERVER', HTTP_SERVER);

// DIR
define('DIR_ROOT', __DIR__ . '/');
define('DIR_APPLICATION', DIR_ROOT . '/catalog/');
define('DIR_SYSTEM', DIR_ROOT . 'system/');
define('DIR_IMAGE', DIR_ROOT . 'image/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
// DB
define('DB_DRIVER', 'mpdo');
define('DB_HOSTNAME', 'mysql5');
define('DB_USERNAME', '');
define('DB_PASSWORD', '');
define('DB_DATABASE', '');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');
define('DEBUG', false);