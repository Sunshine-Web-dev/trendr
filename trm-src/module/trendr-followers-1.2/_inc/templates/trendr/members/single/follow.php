
			<?php do_action( 'trs_before_member_' . trs_current_action() . '_content' ); ?>

			<?php // this is important! do not remove the classes in this DIV as AJAX relies on it! ?>
			<div id="members-dir-list" class="dir-list members follow <?php echo trs_current_action(); ?>">
				<?php trs_get_template_part( 'members/members-loop' ) ?>
			</div>

			<?php do_action( 'trs_after_member_' . trs_current_action() . '_content' ); ?>
