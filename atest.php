<?php
echo '<style>
#customers {
  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

#customers td, #customers th {
  border: 1px solid #ddd;
  padding: 8px;
}

#customers tr:nth-child(even){background-color: #f2f2f2;}

#customers tr:hover {background-color: #ddd;}

#customers th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #4CAF50;
  color: white;
}
</style>';

// Create connection
$conn = new mysqli("127.0.0.1", "root", "", "public");
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else{

	$sql = "SELECT * FROM trm_users "; 	
	$result = $conn->query($sql);
        if ($result->num_rows > 0) {
	 echo "<table id='customers'><tr><th>ID</th><th>username</th><th>email</th><th>password</th><th>user_registerd</th><th>token</th></tr>";
         // output data of each row
         while($row = $result->fetch_assoc()) {
         echo "<tr><td>".$row["ID"]."</td><td>".$row["user_nicename"]." </td><td>".$row["user_email"]." </td><td>".$row["user_pass"]." </td><td>".$row["user_registerd"]." </td><td>".$row["user_registerd"]." </td></tr>";
         }
         echo "</table>";  

    
    echo  'this is mobileuserpassword encoding<br>';	
    echo md5("a:1:{s:13:j;b:1;}");
   $trm_hasher = new PasswordHash(8, TRUE);
   echo $trm_hasher;

		
	}
}

$conn->close();
 
 
?>
<?php 

include_once($_SERVER['DOCUMENT_ROOT'].'/trm-setup.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/initiate.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/Source-zACHAvU6As28quwr-trendr/trm-db.php');
include '/Source-zACHAvU6As28quwr-trendr/class-phpass.php');

global $userdata;
global $trmdb; 

//get the posted values

$posted_username = $_POST['username'];
$posted_password = $_POST['password'];

$user_name = htmlspecialchars($posted_username,ENT_QUOTES);

$pass_word = trm_hash_password($posted_password);

$pass_md5 = md5($posted_password);

$hash = $user->user_pass;
$trm_hasher = new PasswordHash(8, TRUE);
$check = $trm_hasher->CheckPassword($password, $hash);


$pass = $pass_word;

$userinfo = get_userdatabylogin($user_name);

if ( $pass == $userinfo->user_pass){

    echo "yes";

  } else 

    echo "no<br />:";

echo $pass;
echo '<br />:';
echo $userinfo->user_pass;
echo '<br />:';
echo $userinfo->ID;
echo '<br />:';
echo $userinfo->user_login;
echo '<br />:';
echo $pass_md5;
echo '<br />:';
echo trm_hash_password('mypassword');

?>

