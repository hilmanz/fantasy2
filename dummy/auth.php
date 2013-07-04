<?php
/**
 * register a user example.
 */
include_once "functions.php";
session_start();
$api_url = "http://198.199.110.39:3002";
$api_key = "1234567890";
$salt = md5("hello world");

//step 1 - ask for a challenge code
$ckfile = tempnam ("/tmp", "CURLCOOKIE");
$response = json_decode(curlPost($api_url.'/auth',array(
						'api_key'=>$api_key
					),$ckfile)
			,true);

$challenge_code = $response['challenge_code'];
$request_code = sha1($api_key.'|'.$challenge_code.'|'.$salt);

$response = json_decode(curlPost($api_url.'/auth',array(
	'api_key'=>$api_key,
	'request_code'=>$request_code
),$ckfile),true);



print_r($response);


$_SESSION['access_token'] = $response['access_token'];

//$response = curlGet($api_url.'/test?access_token='.$response['access_token'],$ckfile);
//print $response;

unlink($ckfile);
?>
