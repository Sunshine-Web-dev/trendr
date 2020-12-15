<li class="bboss_search_item bboss_search_item_group">
    <div class="item-portrait">
        <a href="<?php trs_group_permalink(); ?>"><?php trs_group_portrait( 'type=full&width=70&height=70' ); ?></a>
    </div>

    <div class="item">
        <div class="item-title"><a href="<?php trs_group_permalink(); ?>"><?php trs_group_name(); ?></a></div>
        <div class="item-meta"><span class="activity"><?php printf( __( 'active %s', 'trendr-global-search' ), trs_get_group_last_active() ); ?></span></div>

        <div class="item-desc"><?php trs_group_description_excerpt(); ?></div>

        <?php do_action( 'trs_directory_groups_item' ); ?>

    </div>

    <div class="action">

        <?php do_action( 'trs_directory_groups_actions' ); ?>

        <div class="meta">

            <?php trs_group_type(); ?> / <?php trs_group_member_count(); ?>

        </div>

    </div>
</li>