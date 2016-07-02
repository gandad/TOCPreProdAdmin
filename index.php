<?php
    header('Access-Control-Allow-Origin:*');
	header("Content-type: text/html;charset=utf-8");
	define('APP_NAME', 'Index');
	define('APP_PATH', './Index/');
	define('APP_DEBUG', TRUE);
//	define('APP_DEBUG', FALSE);
	define('ABS_PATH', dirname(__FILE__));
	require ('./ThinkPHP/ThinkPHP.php');
//	require ('./oss-php/sdk.class.php');

?>
