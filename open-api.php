<?php
/*
OpenAPI for NexusPHP
*/
define('TOKEN_TTL', 86400);
echo getenv('REMOTE_ADDR');
$info['version'] = 'cmct-20130226';
$info['client_type'] = 'cmct-openapi-client-v1';
$info['role'] = 'cmct-openapi-server';

require "include/bittorrent.php";
dbconn();

header('Content-Type: application/x-json');

/* get method name for RPC */
$method = openapi_method();

/* get parameters for RPC */
$param = openapi_param();

/* build response */
$response = array();

empty($method) && $method = 'handshake';
/* method below are opened to public */

switch ($method)
{
	case 'handshake';
		$response['success'] = 1;
		$response = array_merge($response, $info);
		break;
	case 'register';
		break;
	case 'get_token';
		$query = sql_query("SELECT id, passhash, secret, enabled, status FROM users WHERE username = " . sqlesc($param['username']));
		$row = mysql_fetch_array($query);
		if (!$row || $row['status'] == 'pending' || $row["passhash"] != md5($row["secret"] . $param['password'] . $row["secret"]) || $row["enabled"] == "no")
		{
			$response['success'] = 0;
			if (!$row)
			{
				$error = 'user-not-found';
			}
			else if ($row['status'] == 'pending')
			{
				$error = 'user-is-pending';
			}
			else if ($row["enabled"] == "no")
			{
				$error = 'user-is-disabled';
			}
			else
			{
				$error = 'incorrect-password';
			}
			$response['error'] = $error;
		}
		else
		{
			$response['success'] = 1;
			$response['token'] = create_token($row['id']);
		}
		break;
	default:
		if (!auth_token($param['token']))
		{
			/* cannot authenticate a valid identity */
			$response['success'] = 0;
			$response['error'] = 'invalid-token';
		}
		else
		{
			/* methods below requires a active user identity */
			if ($method == 'my_info')
			{
				$response['success'] = 1;
				$response['user_name'] = $CURUSER['username'];
				$response['user_class'] = $CURUSER['class'];
				$response['seedbonus'] = $CURUSER['seedbonus'];
				$response['uploaded'] = $CURUSER['uploaded'];
				$response['downloaded'] = $CURUSER['downloaded'];
			}
			if ($method == 'drop_token')
			{
				sql_query("DELETE FROM openapi_token WHERE token = " . sqlesc($CURTOKEN));
				$response['success'] = 1;
			}
		}
}


/* print json encoded response */
echo json_encode($response);

function create_token($uid)
{
	$token = substr(md5('cmct-token-part1-' . time() . '-' . $row['secret']), 0, 20) . substr(md5('cmct-token-part2-' . mt_rand(0, 2147483647) . '-' . $row['secret'] . '-' . $row['username']), 0, 20);
	sql_query("INSERT INTO openapi_token (token, last_activity, uid) VALUES ('{$token}', '" . time() . "', '" . $uid . "')");
	return $token;
}

function auth_token($token)
{
	global $CURUSER, $CURTOKEN;
	if (empty($token))
	{
		return FALSE;
	}
	$query = sql_query("SELECT * FROM openapi_token WHERE token = " . sqlesc($token));
	$row = mysql_fetch_array($query);
	if (!$row)
	{
		return FALSE;
	}
	if (time() - $row['last_activity'] > TOKEN_TTL)
	{
		sql_query("DELETE FROM openapi_token WHERE token = " . sqlesc($token));
		return FALSE;
	}
	sql_query("UPDATE openapi_token SET last_activity = '" . time() . "' WHERE token = " . sqlesc($token));
	$query = sql_query("SELECT * FROM users WHERE id = " . sqlesc($row['uid']) . " AND enabled = 'yes' AND status = 'confirmed' LIMIT 1");
	$row = mysql_fetch_array($query);
	if (!$row)
	{
		return FALSE;
	}
	$CURUSER = $row;
	return TRUE;
}

function openapi_method()
{
	return $_GET['method'];
}

function openapi_param()
{
	return $_POST;
}