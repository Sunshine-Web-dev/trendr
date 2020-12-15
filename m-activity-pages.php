<?php
require_once("trm-setup.php");
if(empty($_POST["page"])) {
  $page=0;
}
else {
$page= $_POST["page"];
}

$page_number = filter_var($page, FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH);

//throw HTTP error if page number is not valid
if(!is_numeric($page_number)){
	if($page=0) {}
	else {
    //header('HTTP/1.1 500 Invalid page number!');
	//echo "error invalid page number";
    exit();
	}
}

$bd = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
// Check connection
if (!$bd) {
    die("Connection failed: " . mysqli_connect_error());
}

function strpos_arr($haystack, $needle) {
    if(!is_array($needle)) $needle = array($needle);
    foreach($needle as $what) {
        if(($pos = strpos($haystack, $what))!==false) return $pos;
    }
    return false;
}
$spm = array("png","jpg","png","webp","jpeg");

$page = $page*10;

$queryactivity = mysqli_query($bd,"SELECT id,content FROM `trm_trs_activity` WHERE type='photo_post' ORDER BY id DESC LIMIT ".$page.",10;");
if(mysqli_num_rows($queryactivity)<1){}
else{
echo "<table>";
$counter = $page+1;
while($rowactivity1=mysqli_fetch_array($queryactivity))
{

//echo "from: ".$rowactivity1['content'].'<br><br>';
	$i_name = str_replace("[med_images]", "", $rowactivity1['content']);
	$i_name = str_replace("\n", "", $i_name);

if (strpos_arr($i_name, $spm)) {
	$finalimage = substr($i_name, 0, strpos($i_name, "[/med_images]"));
	//echo "<br>".$i_name;
	//echo getcwd();
if(file_exists('trm-src/uploads/med/'.$finalimage)) {
echo '
<tr><td>'.$counter.'</td><td>
<img src="/trm-src/uploads/med/'.$finalimage.'" alt="" style="width:100%;">
</td></tr>
';
}
else {
echo "please recheck image presents in trm-src/uploads/med/ folder";
}

} else {
 echo "invalid image format";
}//invalid image
$counter++;
}//while
echo "</table>";
}//if activity mysql has rows
?>