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
$send = new OssnMessages;
$message = input('message');

if(trim(ossn_restore_new_lines($message)) == ''){
	echo 0;
	exit;
}
$to = input('to');
if ($message_id = $send->send(ossn_loggedin_user()->guid, $to, $message)) {
	// send message chat view
	//user
	$user  = new OssnUser;
	$usersSend = $user->searchUsers(array(
		'wheres' => 'u.guid = "'.ossn_loggedin_user()->guid.'"'
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


	$user = ossn_user_by_guid(ossn_loggedin_user()->guid);
	$message = ossn_restore_new_lines($message);
	$params['message_id'] = $message_id;
	$params['user'] = $user;
	$params['message'] = $message;
	echo ossn_plugin_view('messages/templates/message-send', $params);
} else {
	echo 0;
}
//messages only at some points #470
// don't mess with system ajax requests
exit;
