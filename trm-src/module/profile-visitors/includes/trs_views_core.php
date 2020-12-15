<?php

add_action("trs_profile_views","trs_profile_visitors_count");


function trs_profile_visitors_count(){
	global $trs, $trmdb;
		$user_id = trs_displayed_user_id();
		$viewer_id = trs_loggedin_user_id();
		$table_name = $trmdb->prefix . "trs_profile_visitors";
		$makecount=false;
		$vdate=date("Y-m-d h:i:s");
	//echo  "<div>".trs_displayed_user_id()." - ". trs_loggedin_user_id()."</div>";
	
	if($user_id!=0  and $user_id!= $viewer_id and $viewer_id!=0){
			
					if(isset($_SESSION['profile_visitors'])){
							if(!in_array(trs_displayed_user_id(), $_SESSION['profile_visitors'])){
									$makecount=true;
									$_SESSION['profile_visitors'][]=$user_id;
							}
					}else{
							$makecount=true;
							$_SESSION['profile_visitors']=array();
							$_SESSION['profile_visitors'][]=$user_id;
					}
					
					$sql="select count(id) from $table_name where userid=$user_id and viewerid=$viewer_id";
					$view_count = $trmdb->get_var( $sql);
					$view_count=$view_count?$view_count:0;
					
					if($makecount){
							if($view_count<1){
								
								$sqli="insert into $table_name values(NULL, $user_id, $viewer_id, '$vdate', 1)";
								$trmdb->query( $sqli);
								$view_count=1;
							}else{
								$view_count++;
								$sql="update $table_name set  vdate='$vdate', vviews=vviews+1 where userid=$user_id and viewerid=$viewer_id";
								$trmdb->query($sql);
							}
					}


		}
	
	$sql="select sum(vviews) from $table_name where userid=$user_id";
	$totalviews = $trmdb->get_var( $sql);
	$totalviews = $totalviews?$totalviews:0;
	$totalfinal = $totalviews;
	
	$totalfinal = str_replace(',', '', $totalfinal);
	
	if ( $totalfinal >= 1 ) {
		$totalfinal = number_format($totalfinal / 1.0) . 'k';
	} else if ( $totalfinal >= 1000 ) {
		$totalfinal = number_format($totalfinal / 1000, 1) . 'K';
	} else if ( $totalfinal >= 1000000 ) {		
		$totalfinal = number_format($totalfinal / 1000000, 1) . 'M';
	} else if ( $totalfinal >= 1000000000 ) {
		$totalfinal = number_format($totalfinal / 1000000000, 1) . 'B';
	}
	
 	echo "<div  style=\"clear:both\"><div class=\"total\"><strong>Profile views:</strong> 
			<span style=\"color:#fff\"><strong>$totalfinal</strong></span></div>";

		//echo "<div style=\"clear:both\"> </div>";	
	
	
}



?>