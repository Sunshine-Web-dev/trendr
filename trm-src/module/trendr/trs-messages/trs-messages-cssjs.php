<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function messages_add_autocomplete_js() {
	global $trs;

	// Include the autocomplete JS for composing a message.
	if ( trs_is_messages_component() && trs_is_current_action( 'compose' ) ) {
		add_action( 'trm_head', 'messages_autocomplete_init_jsblock' );

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			trm_enqueue_script( 'trs-jquery-autocomplete', TRS_PLUGIN_URL . '/trs-messages/js/autocomplete/jquery.autocomplete.dev.js', array( 'jquery' ), '20110723' );
			trm_enqueue_script( 'trs-jquery-autocomplete-fb', TRS_PLUGIN_URL . '/trs-messages/js/autocomplete/jquery.autocompletefb.dev.js', array(), '20110723' );
			trm_enqueue_script( 'trs-jquery-bgiframe', TRS_PLUGIN_URL . '/trs-messages/js/autocomplete/jquery.bgiframe.dev.js', array(), '20110723' );
			trm_enqueue_script( 'trs-jquery-dimensions', TRS_PLUGIN_URL . '/trs-messages/js/autocomplete/jquery.dimensions.dev.js', array(), '20110723' );

		} else {
			trm_enqueue_script( 'trs-jquery-autocomplete', TRS_PLUGIN_URL . '/trs-messages/js/autocomplete/jquery.autocomplete.js', array( 'jquery' ), '20110723' );
			trm_enqueue_script( 'trs-jquery-autocomplete-fb', TRS_PLUGIN_URL . '/trs-messages/js/autocomplete/jquery.autocompletefb.js', array(), '20110723' );
			trm_enqueue_script( 'trs-jquery-bgiframe', TRS_PLUGIN_URL . '/trs-messages/js/autocomplete/jquery.bgiframe.js', array(), '20110723' );
			trm_enqueue_script( 'trs-jquery-dimensions', TRS_PLUGIN_URL . '/trs-messages/js/autocomplete/jquery.dimensions.js', array(), '20110723' );
		}
	}
}
add_action( 'trs_actions', 'messages_add_autocomplete_js' );

function messages_add_autocomplete_css() {
	global $trs;

	if ( trs_is_messages_component() && trs_is_current_action( 'compose' ) ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG )
			trm_enqueue_style( 'trs-messages-autocomplete', TRS_PLUGIN_URL . '/trs-messages/css/autocomplete/jquery.autocompletefb.dev.css', array(), '20110723' );
		else
			trm_enqueue_style( 'trs-messages-autocomplete', TRS_PLUGIN_URL . '/trs-messages/css/autocomplete/jquery.autocompletefb.css', array(), '20110723' );

		trm_print_styles();
	}
}
add_action( 'trm_head', 'messages_add_autocomplete_css' );

function messages_autocomplete_init_jsblock() {
?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			var acfb =
			jQuery("ul.first").autoCompletefb({urlLookup:'<?php echo site_url( 'initiate.php' ) ?>'});

			jQuery('#send_message_form').submit( function() {
				var users = document.getElementById('send-to-usernames').className;
				document.getElementById('send-to-usernames').value = String(users);
			});
		});
	</script>
<?php
}