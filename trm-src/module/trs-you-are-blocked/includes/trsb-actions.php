<?php
if ( !defined( 'TRSB_VERSION' ) ) exit;

/**
 * Action Handler
 * @since 1.0
 * @version 1.0
 */
function trsb_handle_actions() {

	if ( !is_user_logged_in() ) return;


	if ( !isset( $_REQUEST['action'] ) || !isset( $_REQUEST['list'] ) || !isset( $_REQUEST['token'] ) || !isset( $_REQUEST['num'] ) ) return;


	switch ( $_REQUEST['action'] ) {
	case 'unblock' :
			if ( trm_verify_nonce( $_REQUEST['token'], 'unblock-' . $_REQUEST['list'] ) ) {
				$current = trsb_get_blocked_users( (int) $_REQUEST['list'] );
				//
					if(isset($_REQUEST['inv']) && $_REQUEST['inv']=='ajax'){

						if (($key = array_search($_REQUEST['num'], $current)) !== false ) {
							    unset($current[$key]);


							update_user_meta( (int) $_REQUEST['list'], '_block', $current );

							do_action( 'trsb_action_unblock', $current );

						$std = new stdClass();
						$std->res = true;
						$std->lnk = trsb_block_link((int) $_REQUEST['list'],$_REQUEST['num'])."&inv=ajax";
						echo json_encode($std);
						unset($_REQUEST['num']);
						unset($_REQUEST['inv']);
						die();
						}

					} else{

						if (isset( $current[ $_REQUEST['num'] ] ) ) {
							    unset($current[ $_REQUEST['num'] ]);
									unset($_REQUEST['num']);
							update_user_meta( (int) $_REQUEST['list'], '_block', $current );

							do_action( 'trsb_action_unblock', $current );
							//
							// trs_core_add_message( __( 'User successfully unblocked', 'trsblock' ) );
							$std = new stdClass();
							$std->res = true; 
							echo json_encode($std);
							unset($_REQUEST['num']);
							unset($_REQUEST['inv']);
							die();

						}
				}
			}
		break;
		case 'block' :


			if ( trm_verify_nonce( $_REQUEST['token'], 'block-' . $_REQUEST['list'] ) ) {


				$current = trsb_get_blocked_users( (int) $_REQUEST['list'] );


				if ( user_can( (int) $_REQUEST['num'], TRSB_ADMIN_CAP ) ) {

					if(isset($_REQUEST['inv']) && $_REQUEST['inv']=='ajax'){
						unset($_REQUEST['num']);
						unset($_REQUEST['inv']);

						$std = new stdClass();
						$std->res = false;
						echo json_encode($std);

						die();

					} else{
						trs_core_add_message( __( 'You can not block administrators / moderators', 'trsblock' ), 'error' );

					}

				}
				else {


					$current[] = (int) $_REQUEST['num'];
					update_user_meta( (int) $_REQUEST['list'], '_block', $current );

					do_action( 'trsb_action_block', $current );

					if(isset($_REQUEST['inv']) && $_REQUEST['inv']=='ajax'){

							unset($_REQUEST['inv']);

						$std = new stdClass();
						$std->res = true;
						$std->lnk = trsb_unblock_link((int) $_REQUEST['list'],$_REQUEST['num'])."&inv=ajax";
						echo json_encode($std);
							unset($_REQUEST['num']);
						die();

					}else{
						trs_core_add_message( __( 'User successfully blocked', 'trsblock' ) );
					}

				}
			}

		break;
		default :
			do_action( 'trsb_action' );
		break;
	}

	trm_safe_redirect( remove_query_arg( array( 'action', 'list', 'num', 'token' ) ) );
	exit();
}

/**
 * Add Block Button in Members List
 * @since 1.0
 * @version 1.0
 */
function trsb_insert_block_button_loop() {
	if ( !is_user_logged_in() ) return;
	$user_id = trs_loggedin_user_id() ;
	$member_id = trs_get_member_user_id();
	if ( $user_id == $member_id || user_can( $member_id, TRSB_ADMIN_CAP ) ) return;

		$list = trsb_get_blocked_users( $user_id );

		if(!in_array( $member_id, (array) $list)){
		echo '<div class="generic-button block-this-user"><a data-ref ="'.trsb_block_link( $user_id, $member_id ).'&inv=ajax" href="#" class="activity-button block">' . __( 'Block', 'trsblock' ) . '</a></div>';
	}else {
			echo '<div class="generic-button block-this-user"><a data-ref ="'.trsb_unblock_link( $user_id, $member_id ).'&inv=ajax" href="#" class="activity-button unblock">' . __( 'Unblock', 'trsblock' ) . '</a></div>';
		}

}

/**
 * Add Block Button in Loop
 * @since 1.0
 * @version 1.0
 */
 //asamir
function trsb_insert_block_button_profile() {

	if ( !is_user_logged_in() ) return;
	$user_id = trs_loggedin_user_id() ;
	$member_id = trs_displayed_user_id();
	if ( $user_id == $member_id || user_can( $member_id, TRSB_ADMIN_CAP ) ) return;

	$list = trsb_get_blocked_users( $user_id );
	if(!in_array( $member_id, (array) $list))
		echo '<div class="generic-button block-this-user"><a data-ref ='.trsb_block_link( $user_id, $member_id ).' href="#" class="activity-button block">' . __( 'Block', 'trsblock' ) . '</a></div>';
	else {
		echo '<div class="generic-button block-this-user"><a data-ref ='.trsb_unblock_link( $user_id, $member_id ).' href="#" class="activity-button unblock">' . __( 'Unblock', 'trsblock' ) . '</a></div>';
	}
}

?>
