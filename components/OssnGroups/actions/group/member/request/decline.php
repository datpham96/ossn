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
$add = new OssnGroup;
$group = input('group');
$user = input('user');

if (ossn_get_group_by_guid($group)->owner_guid !== ossn_loggedin_user()->guid && !ossn_isAdminLoggedin()) {
    ossn_trigger_message(ossn_print('member:add:error'), 'error');
    redirect(REF);
}
if ($add->deleteMember($user, $group)) {
    //decline join group
    $params = [];

    $current_group = $add->getGroup($group);
    $group_owner   = $current_group->owner_guid;
    $params["group_info"] = $current_group;
    
    //user
	$userClass  = new OssnUser;
	$usersJoinGroup = $userClass->searchUsers(array(
		'wheres' => 'u.guid = "'.$user.'"'
    ));
    $usersOwnerGroup = $userClass->searchUsers(array(
		'wheres' => 'u.guid = "'.$group_owner.'"'
    ));
    
	if($usersJoinGroup) {
		$params['owner_info'] = $usersJoinGroup[0];
    }
    if($usersOwnerGroup) {
		$params['customer_info'] = $usersOwnerGroup[0];
    }

    //webhooks
	$http = new OssnHttp;
	$http->setHeader([
		'Content-Type' => 'application/json',
		'Accept' => 'application/json'
	]);
	$http->setBasicAuth(__OSSN_WEBHOOKS_BASIC_USER__, __OSSN_WEBHOOKS_BASIC_PASSWORD__);

	$url = __OSSN_WEBHOOKS_URL_NOTIFICATION__;
    $http->post($url, ["type" => "declineGroup","data" => $params]);

    ossn_trigger_message(ossn_print('member:request:deleted'), 'success');
    redirect(REF);
} else {
    ossn_trigger_message(ossn_print('member:request:delete:fail'), 'error');
    redirect(REF);
}
