<?php
/**
 * Open Source Social Network
 *
 * @package   (softlab24.com).ossn
 * @author    OSSN Core Team <info@softlab24.com>
 * @copyright (C) SOFTLAB24 LIMITED
 * @license   Open Source Social Network License (OSSN LICENSE)  http://www.opensource-socialnetwork.org/licence
 * @link      https://www.opensource-socialnetwork.org/
 */
define('__OSSN_WEBHOOKS__', ossn_route()->com . 'OssnWebhooks/');
require_once(__OSSN_WEBHOOKS__ . 'classes/OssnHttp.php');
define('__OSSN_WEBHOOKS_BASIC_USER__', 'newtel');
define('__OSSN_WEBHOOKS_BASIC_PASSWORD__', 'newtel123');
define('__OSSN_WEBHOOKS_URL_NOTIFICATION__', 'http://localhost:3007/api/v1/sendNotification');

if (ossn_is_xhr()) {
    header('Content-Type: application/json');
}
if (ossn_add_friend(ossn_loggedin_user()->guid, input('user'))) {
    if (!ossn_is_xhr()) {
        // invite friend
        //user
        $user  = new OssnUser;
        $usersSendInvite = $user->searchUsers(array(
            'wheres' => 'u.guid = "'.ossn_loggedin_user()->guid.'"'
        ));
        $usersReceiveInvite = $user->searchUsers(array(
            'wheres' => 'u.guid = "'.input('user').'"'
        ));
        
        $params = [];
        if($usersSendInvite) {
            $params['customer_info'] = $usersSendInvite[0];
        }
        if($usersReceiveInvite) {
            $params['owner_info'] = $usersReceiveInvite[0];
        }

        //webhooks
        $http = new OssnHttp;
        $http->setHeader([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ]);
        $http->setBasicAuth(__OSSN_WEBHOOKS_BASIC_USER__, __OSSN_WEBHOOKS_BASIC_PASSWORD__);

        $url = __OSSN_WEBHOOKS_URL_NOTIFICATION__;
        $http->post($url, ["type" => "inviteFriend","data" => $params]);
        
        ossn_trigger_message(ossn_print('ossn:friend:request:submitted'));
        redirect(REF);
    }
    if (ossn_is_xhr()) {
        // accept friend
        //user
        $user  = new OssnUser;
        $usersSendInvite = $user->searchUsers(array(
            'wheres' => 'u.guid = "'.ossn_loggedin_user()->guid.'"'
        ));
        $usersReceiveInvite = $user->searchUsers(array(
            'wheres' => 'u.guid = "'.input('user').'"'
        ));
        
        $params = [];
        if($usersSendInvite) {
            $params['customer_info'] = $usersSendInvite[0];
        }
        if($usersReceiveInvite) {
            $params['owner_info'] = $usersReceiveInvite[0];
        }

        //webhooks
        $http = new OssnHttp;
        $http->setHeader([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ]);
        $http->setBasicAuth(__OSSN_WEBHOOKS_BASIC_USER__, __OSSN_WEBHOOKS_BASIC_PASSWORD__);

        $url = __OSSN_WEBHOOKS_URL_NOTIFICATION__;
        $http->post($url, ["type" => "acceptFriend","data" => $params]);
        echo json_encode(array(
                'type' => 1,
                'text' => ossn_print('ossn:notification:are:friends'),
            ));
    }
} else {
    if (!ossn_is_xhr()) {
        ossn_trigger_message(ossn_print('ossn:add:friend:error'));
        redirect(REF);
    }
    if (ossn_is_xhr()) {
        echo json_encode(array(
                'type' => 1,
                'text' => ossn_print('ossn:add:friend:error'),
            ));
    }
}
