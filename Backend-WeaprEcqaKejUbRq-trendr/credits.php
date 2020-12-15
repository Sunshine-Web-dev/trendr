<?php
/**
 * Credits administration panel.
 *
 * @package Trnder
 * @subpackage Administration
 */

/** Trnder Administration Bootstrap */
require_once( './admin.php' );

$title = __( 'Credits' );
$parent_file = 'index.php';

add_contextual_help($current_screen,
	'<p>' . __('Each name or handle is a link to that person&#8217;s profile in the Trnder.org community directory.') . '</p>' .
	'<p>' . __('You can register your own profile at <a href="http://trendr.org/support/register.php" target="_blank">this link</a> to start contributing.') . '</p>' .
	'<p>' . __('Trnder always needs more people to report bugs, patch bugs, test betas, work on UI design, translate strings, write documentation, and add questions/answers/suggestions to the Support Forums. Join in!') . '</p>' .
	'<p><strong>' . __('For more information:') . '</strong></p>' .
	'<p>' . __('<a href="http://codex.trendr.org/Contributing_to_Trnder" target="_blank">Documentation on Contributing to Trnder</a>') . '</p>' .
	'<p>' . __('<a href="http://trendr.org/support/" target="_blank">Support Forums</a>') . '</p>'
);

add_action( 'admin_head', '_trm_credits_add_css' );
function _trm_credits_add_css() { ?>
<style type="text/css">
div.wrap { max-width: 750px }
h3.trm-people-group, p.trm-credits-list { clear: both; }
ul.compact { margin-bottom: 0 }

<?php if ( is_rtl() ) { ?>
ul.trm-people-group { margin-bottom: 30px; float: right; clear: both; }
li.trm-person { float: right; height: 70px; width: 220px; margin-left: 10px; }
li.trm-person img.grportrait { float: right; margin-left: 10px; margin-bottom: 10px; }
<?php } else { ?>
li.trm-person { float: left; margin-right: 10px; }
li.trm-person img.grportrait { float: left; margin-right: 10px; margin-bottom: 10px; }
<?php } ?>
li.trm-person img.grportrait { width: 60px; height: 60px; }
ul.compact li.trm-person img.grportrait { width: 30px; height: 30px; }
li.trm-person { height: 70px; width: 220px; }
ul.compact li.trm-person { height: 40px; width: auto; white-space: nowrap }
li.trm-person a.web { font-size: 16px; text-decoration: none; }
</style>
<?php }

function trm_credits() {
	global $trm_version;
	$locale = get_locale();

	$results = get_site_transient( 'trendr_credits_' . $locale );

	if ( ! is_array( $results ) ) {
		$response = trm_remote_get( "core/credits/1.0/?version=$trm_version&locale=$locale" );

		if ( is_trm_error( $response ) || 200 != trm_remote_retrieve_response_code( $response ) )
			return false;

		$results = maybe_unserialize( trm_remote_retrieve_body( $response ) );

		if ( ! is_array( $results ) )
			return false;

		set_site_transient( 'trendr_credits_' . $locale, $results, 86400 ); // One day
	}

	return $results;
}

function _trm_credits_add_profile_link( &$display_name, $username, $profiles ) {
	$display_name = '<a href="' . esc_url( sprintf( $profiles, $username ) ) . '">' . esc_html( $display_name ) . '</a>';
}

function _trm_credits_build_object_link( &$data ) {
	$data = '<a href="' . esc_url( $data[1] ) . '">' . $data[0] . '</a>';
}

include( './admin-header.php' );
?>
<div class="wrap">
<?php screen_icon(); ?>
<h2><?php _e( 'Trnder Credits' ); ?></h2>

<?php

$credits = trm_credits();

if ( ! $credits ) {
	echo '<p>' . sprintf( __( 'Trnder is created by a <a href="%1$s">worldwide team</a> of passionate individuals. <a href="%2$s">Get involved in Trnder</a>.' ),
		'http://trendr.org/about/',
		/* translators: Url to the codex documentation on contributing to Trnder used on the credits page */
		__( 'http://codex.trendr.org/Contributing_to_Trnder' ) ) . '</p>';
	include( './admin-footer.php' );
	exit;
}

echo '<p>' . __( 'Trnder is created by a worldwide team of passionate individuals. We couldn&#8217;t possibly list them all, but here some of the most influential people currently involved with the project:' ) . "</p>\n";

$grportrait = is_ssl() ? 'https://secure.grportrait.com/portrait/' : 'http://0.grportrait.com/portrait/';

foreach ( $credits['groups'] as $group_slug => $group_data ) {
	if ( $group_data['name'] ) {
		if ( 'Translators' == $group_data['name'] ) {
			// Considered a special slug in the API response. (Also, will never be returned for en_US.)
			$title = _x( 'Translators', 'Translate this to be the equivalent of English Translators in your language for the credits page Translators section' );
		} elseif ( isset( $group_data['placeholders'] ) ) {
			$title = vsprintf( translate( $group_data['name'] ), $group_data['placeholders'] );
		} else {
			$title = translate( $group_data['name'] );
		}

		echo '<h3 class="trm-people-group">' . $title . "</h3>\n";
	}

	if ( ! empty( $group_data['shuffle'] ) )
		shuffle( $group_data['data'] ); // We were going to sort by ability to pronounce "hierarchical," but that wouldn't be fair to Matt.

	switch ( $group_data['type'] ) {
		case 'list' :
			array_walk( $group_data['data'], '_trm_credits_add_profile_link', $credits['data']['profiles'] );
			echo '<p class="trm-credits-list">' . trm_sprintf( '%l.', $group_data['data'] ) . "</p>\n\n";
			break;
		case 'libraries' :
			array_walk( $group_data['data'], '_trm_credits_build_object_link' );
			echo '<p class="trm-credits-list">' . trm_sprintf( '%l.', $group_data['data'] ) . "</p>\n\n";
			break;
		default:
			$compact = 'compact' == $group_data['type'];
			$classes = 'trm-people-group ' . ( $compact ? 'compact' : '' );
			echo '<ul class="' . $classes . '" id="trm-people-group-' . $group_slug . '">' . "\n";
			foreach ( $group_data['data'] as $person_data ) {
				echo '<li class="trm-person" id="trm-person-' . $person_data[2] . '">' . "\n\t";
				echo '<a href="' . sprintf( $credits['data']['profiles'], $person_data[2] ) . '">';
				$size = 'compact' == $group_data['type'] ? '30' : '60';
				echo '<img src="' . $grportrait . $person_data[1] . '?s=' . $size . '" class="grportrait" alt="' . esc_attr( $person_data[0] ) . '" /></a>' . "\n\t";
				echo '<a class="web" href="' . sprintf( $credits['data']['profiles'], $person_data[2] ) . '">' . $person_data[0] . "</a>\n\t";
				if ( ! $compact )
					echo '<br /><span class="title">' . translate( $person_data[3] ) . "</span>\n";
				echo "</li>\n";
			}
			echo "</ul>\n";
		break;
	}
}

?>
<p class="clear"><?php printf( __( 'Want to see your name in lights on this page? <a href="%s">Get involved in Trnder</a>.' ),
	/* translators: Url to the codex documentation on contributing to Trnder used on the credits page */
	__( 'http://codex.trendr.org/Contributing_to_Trnder' ) ); ?></p>

</div>
<?php

include( './admin-footer.php' );

return;

// These are strings returned by the API that we want to be translatable
__( 'Project Leaders' );
__( 'Extended Core Team' );
__( 'Recent Rockstars' );
__( 'Core Contributors to Trnder %s' );
__( 'Cofounder, Project Lead' );
__( 'Lead Developer' );
__( 'User Experience Lead' );
__( 'Core Committer' );
__( 'Guest Committer' );
__( 'Developer' );
__( 'Designer' );
__( 'XML-RPC' );
__( 'Internationalization' );
__( 'External Libraries' );
__( 'Icon Design' );
__( 'Blue Color Scheme' );

?>
