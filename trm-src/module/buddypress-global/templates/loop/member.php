<li class="bboss_search_item bboss_search_item_member">
    <div class="item-portrait">
        <a href="<?php trs_member_permalink(); ?>"><?php trs_member_portrait('type=full&width=70&height=70'); ?></a>
    </div>

    <div class="item">
        <div class="item-title">
            <a href="<?php trs_member_permalink(); ?>"><?php trs_member_name(); ?></a>
        </div>

        <div class="item-meta">
            <span class="activity">
                <?php trs_member_last_active(); ?>
            </span>
        </div>

        <div class="item-desc">
            <p>
                <?php if ( trs_get_member_latest_update() ) : ?>
                    <?php trs_member_latest_update( array( 'view_link' => true ) ); ?>
                <?php endif; ?>
            </p>
        </div>

        <?php do_action( 'trs_directory_members_item' ); ?>

        <?php
         /***
          * If you want to show specific profile fields here you can,
          * but it'll add an extra query for each member in the loop
          * (only one regardless of the number of fields you show):
          *
          * trs_member_profile_data( 'field=the field name' );
          */
        ?>
    </div>

    <div class="action">

        <?php do_action( 'trs_directory_members_actions' ); ?>

    </div>
</li>
