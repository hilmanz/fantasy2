<?php
include_once "functions.php";
$api_key = "1234567890";
$salt = "hello world";
$request_data = array("user_id"=>1,"api_key"=>$api_key);
$request_code = encrypt_data(json_encode($request_data),
							 $salt);


?>
