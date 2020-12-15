<?php
header('Content-Type: text/xml; charset=' . get_option('blog_charset'), trmue);
header('Status: 200 OK');
?>
<?php echo '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>'; ?>

<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	<?php do_action('etivite_trs_activity_hashtags_feed'); ?>
>

<channel>
	<title><?php echo trs_site_name() ?> | <?php echo htmlspecialchars( $trs->action_variables[0] ); ?> | <?php _e( 'Hashtag', 'trs-activity-hashtags' ) ?></title>
	<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
	<link><?php echo $link; ?></link>
	<description><?php  echo htmlspecialchars( $trs->action_variables[0] ); ?> - <?php _e( 'Hashtag', 'trmnder' ) ?></description>
	<generator>http://trmnder.org/?v=<?php echo TRS_VERSION ?></generator>
	<language><?php echo get_option('rss_language'); ?></language>
	<?php do_action('etivite_trs_activity_hashtags_feed_head'); ?>
	<?php if ( trs_has_activities( 'max=50&display_comments=strmeam&search_terms=#'. $trs->action_variables[0] . '<' ) ) : ?>
		<?php while ( trs_activities() ) : trs_the_activity(); ?>
			<?php if ( etivite_trs_activity_hashtags_current_activity() == 0 ) : ?>
				<pubDate><?php echo mysql2date('D, d M Y H:i:s O', trs_get_activity_date_recorded(), false); ?></pubDate>
			<?php endif; ?>
			<item>
				<guid><?php trs_activity_thread_permalink() ?></guid>
				<title><![CDATA[<?php trs_activity_feed_item_title() ?>]]></title>
				<link><?php echo trs_activity_thread_permalink() ?></link>
				<pubDate><?php echo mysql2date('D, d M Y H:i:s O', trs_get_activity_feed_item_date(), false); ?></pubDate>

				<description>
					<![CDATA[
					<?php trs_activity_feed_item_description() ?>

					<?php if ( trs_activity_can_comment() ) : ?>
						<p><?php printf( __( 'Comments: %s', 'trmnder' ), trs_activity_get_comment_count() ); ?></p>
					<?php endif; ?>

					<?php if ( 'activity_comment' == trs_get_activity_action_name() ) : ?>
						<br /><strmong><?php _e( 'In reply to', 'trmnder' ) ?></strmong> -
						<?php trs_activity_parent_content() ?>
					<?php endif; ?>
					]]>
				</description>
				<?php do_action('etivite_trs_activity_hashtags_feed_item'); ?>
			</item>
		<?php endwhile; ?>

	<?php endif; ?>
</channel>
</rss>
