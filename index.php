<?php
/**
 *
 *
 * @version 2.1
 *
 * @year 2016 - 2018
 *
 * @author Philipp Franck
 *
 * @desc
 * This is a very simple PHP script to create simple JSOn Web API for anything in PHP
 *
 * How to use
 *
 * * make a folder with php-default-api
 * * create your api as obejct wich inherits from server.class.php and put it in this folder
 * * create a settings file in thiw folder.
 *
 *
 *
 */



/**
 * go
 *
 */
try {

	// enabling CORS (would be a shameful webservice without)
	if (isset($_SERVER['HTTP_ORIGIN'])) {
		header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Max-Age: 86400');    // cache for 1 day
	}

	// Access-Control headers are received during OPTIONS request
	if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
			header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
		}
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
			header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
		}
		exit(0);
	}
	
	// settings
	$errorReporting = true;
	$includePath = (!isset($includePath)) ? dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . '/' : $includePath;
	if (file_exists($includePath . '/' . "settings.php")) {
		include_once($includePath . '/' . "settings.php");
	} else {
		throw new Exception("No settings File! ($includePath)");
	}
    $plainOutput = !isset($plainOutput) ? false: $plainOutput;

	// set up error reporting
	if ($errorReporting) {
		error_reporting(E_ALL);
		ini_set('display_errors', 'on');
	}  else {
		ini_set ("display_errors", "0");
		error_reporting(false);
	}

	// register shutdown function
	register_shutdown_function(function()  {
		$error = error_get_last();
		//check if it's a core/fatal error, otherwise it's a normal shutdown
		if ($error !== NULL && in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING))) {
			$return = array(
				'success'	=> false,
				'message'	=> "500 / Internal Server Error" . ": {$error['message']} in line {$error['line']} of {$error['file']}"
			);
	
			http_response_code(200);
			header('Content-Type: application/json');
			echo json_encode($return);
		}
	});

	// get logger
	require_once('logger.class.php');
	$logger = new logger($debugmode);
	
	// low budget security check
	$ip	= $_SERVER['REMOTE_ADDR'];
	if (!in_array($ip, $allowedIps) and count($allowedIps)) {
		throw new Exception("Not allowed, Mr. $ip!");
	}

	// post body
    $_ANGULAR_POST = array();
	if (!isset($_SERVER["CONTENT_TYPE"]) or ($_SERVER["CONTENT_TYPE"] == "application/json")) {
        $_ANGULAR_POST = json_decode(file_get_contents("php://input"));
    } else {
        $_POST_BODY = file_get_contents("php://input");
    }

	// combine sets
	$post = array();
	foreach ($allowedSets as $set) {
		if ($set == 'ANGULAR_POST') {
			$post = array_merge($post, (array) $_ANGULAR_POST);
		}
		if ($set == 'POST') {
			$post = array_merge($post, (array) $_POST);
		}
		if ($set == 'GET') {
			$post = array_merge($post, (array) $_GET);
		}
		
	}

	// task
	if (!isset($post['task'])) {
		throw new Exception('No task defined');
	}
	$task = $post['task'];
	$data = isset($post['data']) ? $post['data'] : $post;
    $data = isset($_POST_BODY) ? $_POST_BODY : $data;

	// go
	$logger->log('get server ' . $serverclass);
	require_once("server.class.php");
	require_once($includePath . "{$serverclass}.class.php");

	$settings['debug'] = $debugmode;
	$server = new $serverclass($data, $logger, $settings);

	$server->start();
	$server->call($task);
	$server->finish();

	$return = $server->return;

} catch (Exception $a) {
	ob_clean();
	if (isset($server)) {
		$server->finish();
	}

	$return = array(
		'success'	=> false,
		'message'	=> $a->getMessage(),
		'warnings'	=> isset($logger) ? $logger->warnings : 'no logger'
	);
	if (!isset($debugmode) or $debugmode) {
		$return['debug'] = isset($logger) ? $logger->log : 'no logger';
	}

	$returnCode = isset($server) && (substr($server->returnCode,0,1) != "2") ? $server->returnCode : 500;
    http_response_code($returnCode);
	header('Content-Type: application/json');
	echo json_encode($return);
	die();
}


// return  success
$logger->log('OK');
$return['task'] = $task;
$return['success'] = true;
$return['warnings'] = $logger->warnings;
if ($debugmode) {
	$return['debug'] = $logger->log;
}
http_response_code($server->returnCode);
header('Content-Type: application/json');
echo json_encode($plainOutput ? $server->return : $return);
?>