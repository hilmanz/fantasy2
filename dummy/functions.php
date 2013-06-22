<?php
// Parameters:
// $text = The text that you want to encrypt.
// $key = The key you're using to encrypt.
// $alg = The algorithm.
// $crypt = 1 if you want to crypt, or 0 if you want to decrypt.

function cryptare($text, $key, $alg, $crypt)
{
    $encrypted_data="";
    switch($alg)
    {
		case "aes-128":
			$td = mcrypt_module_open('aes-128', '', 'ecb', '');
		break;
        case "3des":
            $td = mcrypt_module_open('tripledes', '', 'ecb', '');
            break;
        case "cast-128":
            $td = mcrypt_module_open('cast-128', '', 'ecb', '');
            break;   
        case "gost":
            $td = mcrypt_module_open('gost', '', 'ecb', '');
            break;   
        case "rijndael-128":
            $td = mcrypt_module_open('rijndael-128', '', 'ecb', '');
            break;       
        case "twofish":
            $td = mcrypt_module_open('twofish', '', 'ecb', '');
            break;   
        case "arcfour":
            $td = mcrypt_module_open('arcfour', '', 'ecb', '');
            break;
        case "cast-256":
            $td = mcrypt_module_open('cast-256', '', 'ecb', '');
            break;   
        case "loki97":
            $td = mcrypt_module_open('loki97', '', 'ecb', '');
            break;       
        case "rijndael-192":
            $td = mcrypt_module_open('rijndael-192', '', 'ecb', '');
            break;
        case "saferplus":
            $td = mcrypt_module_open('saferplus', '', 'ecb', '');
            break;
        case "wake":
            $td = mcrypt_module_open('wake', '', 'ecb', '');
            break;
        case "blowfish-compat":
            $td = mcrypt_module_open('blowfish-compat', '', 'ecb', '');
            break;
        case "des":
            $td = mcrypt_module_open('des', '', 'ecb', '');
            break;
        case "rijndael-256":
            $td = mcrypt_module_open('rijndael-256', '', 'ecb', '');
            break;
        case "xtea":
            $td = mcrypt_module_open('xtea', '', 'ecb', '');
            break;
        case "enigma":
            $td = mcrypt_module_open('enigma', '', 'ecb', '');
            break;
        case "rc2":
            $td = mcrypt_module_open('rc2', '', 'ecb', '');
            break;   
        default:
            $td = mcrypt_module_open('blowfish', '', 'ecb', '');
            break;                                           
    }
   
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
    $key = substr($key, 0, mcrypt_enc_get_key_size($td));
    @mcrypt_generic_init($td, $key, $iv);
   
    if($crypt)
    {
        $encrypted_data = @mcrypt_generic($td, $text);
    }
    else
    {
        $encrypted_data = @mdecrypt_generic($td, $text);
    }
   
    mcrypt_generic_deinit($td);
    mcrypt_module_close($td);
   
    return $encrypted_data;
} 
function convertBase64($str){
	$str = str_replace("=",".",$str);
	$str = str_replace("+","-",$str);
	$str = str_replace("/","_",$str);
	return $str;
}
function realBase64($str){
	$str = str_replace(".","=",$str);
	$str = str_replace("-","+",$str);
	$str = str_replace("_","/",$str);
	return $str;
}
function encrypt_data($str,$key){
	$hash = cryptare($str,$key,'rijndael-128',1);
	$str = convertBase64(base64_encode($hash));
	return $str;
}
function decrypt_data($str,$key){
	$secret = base64_decode(realBase64($str));
	$str = cryptare($secret,$key,'rijndael-128',0);
	return trim($str);
}



function curlGet($url,$params,$cookie_file='',$timeout=15){
	if(count($params) > 0){
		$url .= "?".http_build_query($params);
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	if($cookie_file!=''){
		curl_setopt($ch,CURLOPT_COOKIEJAR, $cookie_file);
		curl_setopt($ch,CURLOPT_COOKIEFILE, $cookie_file); 
	}
	$response = curl_exec ($ch);
	$info = curl_getinfo($ch);
	curl_close ($ch);
	return $response;
}
function curlPost($url,$params,$cookie_file='',$timeout=15){
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_TIMEOUT,$timeout);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	
	if($cookie_file!=''){
		curl_setopt($ch,CURLOPT_COOKIEJAR, $cookie_file);
		curl_setopt($ch,CURLOPT_COOKIEFILE, $cookie_file); 
	}
	$response = curl_exec ($ch);
	$info = curl_getinfo($ch);
	curl_close ($ch);
	return $response;
}
function encrypt($text, $pw, $base64 = true) {
    $method = 'aes-128-cbc';
    $iv_len = openssl_cipher_iv_length($method);
    //$iv = openssl_random_pseudo_bytes($iv_len);

    $pw = substr(md5($pw),0,32);

    $cipher =  openssl_encrypt ($text ,$method ,$pw ,!$base64 ,$iv );

    if($base64) {
            $pw = base64_encode($pw);
            $iv = base64_encode($iv);
        }

    return array(
        'iv' => $iv,
        'pw' => $pw,
        'text' => $text,
        'cipher' => $cipher
    );
}
function pr($o){
	print "<pre>";
	print_r($o);
	print "</pre>";
}
?>
