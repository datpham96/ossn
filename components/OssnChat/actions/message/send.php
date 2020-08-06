<?php
/**
 * Open Source Social Network
 *
 * @package   Open Source Social Network
 * @author    Open Social Website Core Team <info@softlab24.com>
 * @copyright (C) SOFTLAB24 LIMITED
 * @license   Open Source Social Network License (OSSN LICENSE)  http://www.opensource-socialnetwork.org/licence
 * @link      https://www.opensource-socialnetwork.org/
 */
define('__OSSN_WEBHOOKS__', ossn_route()->com . 'OssnWebhooks/');
require_once(__OSSN_WEBHOOKS__ . 'classes/OssnHttp.php');
define('__OSSN_WEBHOOKS_BASIC_USER__', 'newtel');
define('__OSSN_WEBHOOKS_BASIC_PASSWORD__', 'newtel123');
define('__OSSN_WEBHOOKS_URL_NOTIFICATION__', 'http://localhost:3007/api/v1/sendNotification');

$message = input('message');
$to = input('to');
$from = ossn_loggedin_user()->guid;

header('Content-Type: application/json');
if ($to && $from && strlen($message)) {
	// send message chat box
	//user
	$user  = new OssnUser;
	$usersSend = $user->searchUsers(array(
		'wheres' => 'u.guid = "'.$from.'"'
	));
	$usersReceive = $user->searchUsers(array(
		'wheres' => 'u.guid = "'.$to.'"'
	));
	
	$params = [];
	if($usersSend) {
		$params['customer_info'] = $usersSend[0];
	}
	if($usersReceive) {
		$params['owner_info'] = $usersReceive[0];
	}

	//webhooks
	$http = new OssnHttp;
	$http->setHeader([
		'Content-Type' => 'application/json',
		'Accept' => 'application/json'
	]);
	$http->setBasicAuth(__OSSN_WEBHOOKS_BASIC_USER__, __OSSN_WEBHOOKS_BASIC_PASSWORD__);

	$url = __OSSN_WEBHOOKS_URL_NOTIFICATION__;
	$http->post($url, ["type" => "sendMessage","data" => $params]);
	
	$send = ossn_chat();
	if ($send->send($from, $to, $message)) {
		
		$vars['message'] = $message;
		$vars['time'] = time();
		echo json_encode(array(
			'type' => 1,
			'message' => ossn_plugin_view('chat/message-item-send', $vars),
		));
		exit;
	}
}
echo json_encode(array('type' => 0));
