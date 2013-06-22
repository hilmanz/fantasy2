<?php
/**
 * register a user example.
 */
include_once "functions.php";
session_start();
$access_token = $_SESSION['access_token'];
$api_url = "http://localhost:3000";
$data = array(
	'fb_id'=>'123123123',
	'name'=>'Alex Grill',
	'email'=>'test@foo.com',
	'phone'=>'123123123',
	'access_token'=>$access_token
);

print $api_url.'/user/register?access_token='.$access_token."<br/>";
$response = curlPost($api_url."/user/register",$data);
print $response;
?>
