		</div> <!-- #container -->

		<?php do_action( 'trs_after_container' ) ?>
		<?php do_action( 'trs_before_footer' ) ?>

		<div id="footer">
<p><?php printf( __( ' <a href="/about">About</a> . <a href="/feedback">Feedback</a> . <a href="/terms">Terms & Privacy</a> .  &copy; trendr 2018' ), get_bloginfo( 'name' ) ); ?></p>
			<?php do_action( 'trs_footer' ) ?>
		</div><!-- #footer -->

		<?php do_action( 'trs_after_footer' ) ?>

  <?php echo get_num_queries(); ?> queries in <?php timer_stop(1,3); ?> seconds,,, 
<?php echo memory_get_usage(); ?> Bytes Used

		<script type="text/javascript">


  $('div.dropdown').each(function() {
    var $dropdown = $(this);

    $("a.dropdown-link", $dropdown).click(function(e) {
      e.preventDefault();
      $div = $("div.confirm", $dropdown);
      $div.toggle();
      $("div.confirm").not($div).hide();
      return false;
    });

});

  $('html').click(function(){
    $("div.confirm").hide();      				

  });
     

	</script>
	</body>
</html>		<?php trm_footer(); ?>
  <?php echo get_num_queries(); ?> queries in <?php timer_stop(1,3); ?> seconds,,, 
<?php echo memory_get_usage(); ?> Bytes Used
