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

$add = new OssnGroup;
$group = input('group');
if (empty($group)) {
    ossn_trigger_message(ossn_print('member:add:error'), 'error');
    redirect(REF);
}
if ($add->sendRequest(ossn_loggedin_user()->guid, $group)) {
    //join group
    $params = [];

    $current_group = $add->getGroup($group);
    $group_owner   = $current_group->owner_guid;
    $params["group_info"] = $current_group;
    
    //user
	$user  = new OssnUser;
	$usersJoinGroup = $user->searchUsers(array(
		'wheres' => 'u.guid = "'.ossn_loggedin_user()->guid.'"'
	));
	$usersOwnerGroup = $user->searchUsers(array(
		'wheres' => 'u.guid = "'.$group_owner.'"'
    ));
    
    
	if($usersJoinGroup) {
		$params['customer_info'] = $usersJoinGroup[0];
    }
	if($usersOwnerGroup) {
		$params['owner_info'] = $usersOwnerGroup[0];
    }

    //webhooks
	$http = new OssnHttp;
	$http->setHeader([
		'Content-Type' => 'application/json',
		'Accept' => 'application/json'
	]);
	$http->setBasicAuth(__OSSN_WEBHOOKS_BASIC_USER__, __OSSN_WEBHOOKS_BASIC_PASSWORD__);

	$url = __OSSN_WEBHOOKS_URL_NOTIFICATION__;
    $http->post($url, ["type" => "joinGroup","data" => $params]);
    
    ossn_trigger_message(ossn_print('memebership:sent'), 'success');
    redirect("group/{$group}");
} else {
    ossn_trigger_message(ossn_print('memebership:sent:fail'), 'error');
    redirect(REF);
}