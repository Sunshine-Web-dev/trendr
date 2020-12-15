<?php
header("Content-Type: application/json; charset=UTF-8");
$data = json_decode(file_get_contents('php://input'), true);
$username=$data['username'];
$password=md5($data['password']);
$token=$data['token'];
print_r("esx");exit;
// Create connection
$conn = new mysqli("mysql.hostinger.com.ar", "u483737242_root", "brian17", "trendr");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else{
	 $sql = "UPDATE trm_users SET token='". $token ."' WHERE user_login ='".$username."' AND user_pass = '". $password."'"; 	
	$result = $conn->query($sql);
	echo json_encode(array('status'=>1, 'data'=>'ok'));
}

$conn->close();
 
 
?>
