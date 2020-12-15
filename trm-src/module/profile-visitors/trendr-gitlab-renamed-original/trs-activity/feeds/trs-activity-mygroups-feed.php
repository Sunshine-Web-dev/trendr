<?php

/**
 * RSS2 Feed Template for displaying a member's group's activity
 *
 * @package trendr
 * @sutrsackage ActivityFeeds
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
header('Status: 200 OK');
?>
<?php echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	<?php do_action('trs_activity_mygroups_feed'); ?>
>

<channel>
	<title><?php trs_site_name() ?> | <?php echo $trs->displayed_user->fullname; ?> | <?php _e( 'My Groups - Public Activity', 'trendr' ) ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php echo home_url( trs_get_activity_root_slug() . '/#my-groups/' ) ?></link>
	<description><?php echo $trs->displayed_user->fullname; ?> - <?php _e( 'My Groups - Public Activity', 'trendr' ) ?></description>
	<pubDate><?php echo mysql2date('D, d M Y H:i:s O', trs_activity_get_last_updated(), false); ?></pubDate>
	<generator>http://trendr.org/?v=<?php echo TRS_VERSION ?></generator>
	<language><?php echo get_option('rss_language'); ?></language>
	<?php do_action('trs_activity_mygroups_feed_head'); ?>

	<?php
		$groups = groups_get_user_groups( $trs->loggedin_user->id );
		$group_ids = implode( ',', $groups['groups'] );
	?>

	<?php if ( trs_has_activities( 'object=' . $trs->groups->id . '&primary_id=' . $group_ids . '&max=50&display_comments=threaded' ) ) : ?>
		<?php while ( trs_activities() ) : trs_the_activity(); ?>
			<item>
				<guid><?php trs_activity_thread_permalink() ?></guid>
				<title><?php trs_activity_feed_item_title() ?></title>
				<link><?php echo trs_activity_thread_permalink() ?></link>
				<pubDate><?php echo mysql2date('D, d M Y H:i:s O', trs_get_activity_feed_item_date(), false); ?></pubDate>

				<description>
					<![CDATA[
						<?php trs_activity_feed_item_description() ?>

						<?php if ( trs_activity_can_comment() ) : ?>
							<p><?php printf( __( 'Comments: %s', 'trendr' ), trs_activity_get_comment_count() ); ?></p>
						<?php endif; ?>
					]]>
				</description>
				<?php do_action('trs_activity_mygroups_feed_item'); ?>
			</item>
		<?php endwhile; ?>

	<?php endif; ?>
</channel>
</rss>
