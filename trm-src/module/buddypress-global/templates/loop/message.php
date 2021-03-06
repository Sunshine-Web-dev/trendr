<?php global $current_message; ?>
<li class="bboss_search_item bboss_search_item_message">
	<p class="message_participants">
		<?php 
		_e( 'Conversation between', 'trendr-global-search' );
		$participants = array();
		foreach( $current_message->recepients as $recepient_id ){
			if( $recepient_id==get_current_user_id() )
				continue;
			
			$participants[] = trs_core_get_userlink( $recepient_id );
		}
		
		echo ' ' . implode( ', ', $participants ) . ' ' . __( 'and you.', 'trendr-global-search' );
		?>
		
		<span class='view_thread_link'>
			<a href='<?php echo esc_url( trailingslashit( trs_loggedin_user_domain() ) ) . 'messages/view/' . $current_message->thread_id . '/';?>'>
				<?php _e( 'View Conversation', 'trendr-global-search' );?>
			</a>
		</span>
	</p>
	
	<div class="conversation">
        <div class="item-portrait">
            <a href="<?php echo trs_core_get_userlink( $current_message->sender_id, true, true );?>">
                <?php echo trs_core_fetch_portrait( array( 'item_id'=>$current_message->sender_id, 'width'=> 50, 'height'=> 50 ) );?>
            </a>
        </div>

        <div class="item">
            <div class="item-title">
                <a href="<?php echo esc_url( trailingslashit( trs_loggedin_user_domain() ) ) . 'messages/view/' . $current_message->thread_id . '/';?>">
                    <?php echo stripslashes( $current_message->subject ); ?>
                </a>
            </div>
            <div class="item-desc">
                <?php 
                    $content = trm_strip_all_tags($current_message->message);
                    $trimmed_content = trm_trim_words( $content, 20, '...' ); 
                    echo $trimmed_content; 
                ?>
            </div>
        </div>
	</div>
	
</li>