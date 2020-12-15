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
	global $trmdb;
	if ($_REQUEST['action'] == 'check-password') {
		$mobile         = $_REQUEST['phone_number'];
	    $area           = $_REQUEST["area"];
	    $number         = rand(1000,9999);
	    $content= '【极云加速】您的验证码是'. $number .'';

	    if (86 !== intval($area)) {
	        $mobile = '+' . $area . $mobile;
	        $content = '【GEEYUN】Your verification code is ' . $number;
	    }
	    $key = 'd2a16c74ece271518d23fea6f395f224';
	    $yunpianSms = new YunPianSms($key);

		$user = $trmdb->get_row( $trmdb->prepare( "SELECT * FROM {$trmdb->users} WHERE user_status = 0 AND phone_number = %s LIMIT 1", $_REQUEST['phone_number'] ) );
		if (empty($user)) {
			exit(json_encode(array("id"=>"login_error","error"=>"Sorry but your phone number is not registered")));
		}
		$response = $yunpianSms->sendMsg($mobile,$content);
		if($response["data"]["msg"] == "OK"){
			if (!empty($user)) {
				$trmdb->query( "UPDATE {$trmdb->users} SET verify_code = {$number} WHERE phone_number = {$_REQUEST['phone_number']}");
				exit(json_encode(array("id"=>"OK","error"=>"")));
			}
			exit(json_encode(array("id"=>"login_error","error"=>"Please try again, I'm sorry but I have an error")));
		}
		else{
			exit(json_encode(array("id"=>"login_error","error"=>"Sorry but your phone number is invalid")));
		}
	}
	else {
		$user = $trmdb->get_row( $trmdb->prepare( "SELECT * FROM {$trmdb->users} WHERE phone_number = {$_REQUEST['number']} AND verify_code = %s LIMIT 1", $_REQUEST['verify_code'] ) );
		$password = trm_hash_password($_REQUEST['password']);
		if( !empty($user) ){
			$trmdb->query( "UPDATE {$trmdb->users} SET verify_code = '', user_pass = '{$password}' WHERE phone_number = {$_REQUEST['number']}" );
			exit(json_encode(array("id"=>"OK","error"=>"")));
		}
		exit(json_encode(array("id"=>"login_error","error"=>"Sorry but your inviteCode is invalid")));
	}
?>
