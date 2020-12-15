<?php
/**
 * These functions can be replaced via plugins. If plugins do not redefine these
 * functions, then these will be used instead.
 *
 * @package Trnder
 */

	require_once('initiate.php');

	require 'vendor/autoload.php';
	use Ender\YunPianSms\SMS\YunPianSms;
	use Ender\YunPianSms\SMS\YunPianUser;

	global $trs, $trmdb;
	if ($_REQUEST['action'] == 'check-phone-number') {
		$mobile         = $_REQUEST['phone_number'];
		$phone_number = $mobile;
		$user = $trmdb->get_row( $trmdb->prepare( "SELECT ID FROM {$trmdb->users} WHERE user_status = 0 AND phone_number = %s LIMIT 1", $phone_number ) );
		if (!empty($user)) {
			exit(json_encode(array("id"=>"trs_signup_phone_errors","error"=>"This phone number is already registered. Please choose another one!")));
		}
	    $area           = $_REQUEST["area"];
	    $number         = rand(1000,9999);
	    $content= '【极云加速】您的验证码是'. $number .'';

	    if (86 !== intval($area)) {
	        $mobile = '+' . $area . $mobile;
	        $content = '【GEEYUN】Your verification code is ' . $number;
	    }
	    $key = 'd2a16c74ece271518d23fea6f395f224';
	    $trmdb->query( $trmdb->prepare( "DELETE FROM trm_users WHERE user_status = 5 AND phone_number = %s", $phone_number ));
	    $yunpianSms = new YunPianSms($key);
	    try{
			$response = $yunpianSms->sendMsg($mobile,$content);
	    }
	    catch(Exception $e){
			exit(json_encode(array("id"=>"trs_signup_phone_errors","error"=>"Please try again, I'm sorry but I have an error")));
	    }
		$insert = "('$phone_number', '$number', 5)";
		if($response["data"]["msg"] == "OK"){
			$trmdb->query("INSERT INTO $trmdb->users (phone_number, verify_code, user_status) VALUES ".$insert);
			exit(json_encode(array("id"=>"OK","error"=>"Please enter a your inviteCode")));
		}
		else{
			$trmdb->query( $trmdb->prepare( "DELETE FROM trm_users WHERE phone_number = %s", $phone_number ));
		}
		exit(json_encode(array("id"=>"trs_signup_phone_errors","error"=>"Sorry but your phone number is invalid")));
	}
	else {
		$mobile         = $_REQUEST['phone_number'];
		$phone_number = $mobile;
		$user = $trmdb->get_row( $trmdb->prepare( "SELECT user_login FROM {$trmdb->users} WHERE user_status = 0 AND phone_number = %s LIMIT 1", $phone_number ) );
		if (!empty($user)) {
			exit(json_encode(array("id"=>"trs_signup_phone_errors","error"=>"This phone number is already registered by ".$user->user_login.". Please choose another one!")));
		}
		$cookie = $_COOKIE[LOGGED_IN_COOKIE];
		$cookie_elements = explode('|', $cookie);
		if ( count($cookie_elements) != 3 )
			exit(json_encode(array("id"=>"trs_signup_phone_errors","error"=>"You have to login!")));

		list($username, $expiration, $hmac) = $cookie_elements;
		
	    $area           = $_REQUEST["area"];
	    $number         = rand(1000,9999);
	    $content= '【极云加速】您的验证码是'. $number .'';

	    if (86 !== intval($area)) {
	        $mobile = '+' . $area . $mobile;
	        $content = '【GEEYUN】Your verification code is ' . $number;
	    }
	    $key = 'd2a16c74ece271518d23fea6f395f224';
	    $trmdb->query( $trmdb->prepare( "DELETE FROM trm_users WHERE user_status = 5 AND phone_number = %s", $phone_number ));
	    $yunpianSms = new YunPianSms($key);
	    try{
			$response = $yunpianSms->sendMsg($mobile,$content);
	    }
	    catch(Exception $e){
			exit(json_encode(array("id"=>"trs_signup_phone_errors","error"=>"Please try again, I'm sorry but I have an error")));
	    }
		if($response["data"]["msg"] == "OK"){
			$trmdb->query( "UPDATE {$trmdb->users} SET verify_code = '{$number}' WHERE user_login = '{$username}'" );
			exit(json_encode(array("id"=>"OK","error"=>"Please enter a your inviteCode")));
		}
		exit(json_encode(array("id"=>"trs_signup_phone_errors","error"=>"Sorry but your phone number is invalid")));
	
	}

?>
