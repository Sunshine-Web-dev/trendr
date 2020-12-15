<?php
/**
 * Trnder Administration Template Footer
 *
 * @package Trnder
 * @subpackage Administration
 */

// don't load directly
if ( !defined('ABSPATH') )
	die('-1');
?>

<div class="clear"></div></div><!-- trmbody-content -->
<div class="clear"></div></div><!-- trmbody -->
<div class="clear"></div></div><!-- trmcontent -->

<div id="footer">
<?php do_action( 'in_admin_footer' ); ?>
<p id="footer-left" class="alignleft"><?php


?></p>
<div class="clear"></div>
</div>
<?php
do_action('admin_footer', '');
do_action('admin_print_footer_scripts');
do_action("admin_footer-" . $GLOBALS['hook_suffix']);


?>

<div class="clear"></div></div><!-- trmwrap -->
<script type="text/javascript">if(typeof trmOnload=='function')trmOnload();</script>
</body>
	<?php echo get_num_queries(); ?> queries in <?php timer_stop(1,3); ?> seconds,,, 
<?php echo memory_get_usage(); ?> Bytes Used
</html>
