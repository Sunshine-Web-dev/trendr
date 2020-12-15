<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/* Apply trendr defined filters */
add_filter( 'trs_get_message_notice_subject', 'trm_filter_kses', 1 );
add_filter( 'trs_get_message_notice_text', 'trm_filter_kses', 1 );
add_filter( 'trs_get_message_thread_subject', 'trm_filter_kses', 1 );
add_filter( 'trs_get_message_thread_excerpt', 'trm_filter_kses', 1 );
add_filter( 'trs_get_messages_subject_value', 'trm_filter_kses', 1 );
add_filter( 'trs_get_messages_content_value', 'trm_filter_kses', 1 );
add_filter( 'trs_get_the_thread_message_content', 'trm_filter_kses', 1 );

add_filter( 'messages_message_content_before_save', 'trm_filter_kses', 1 );
add_filter( 'messages_message_subject_before_save', 'trm_filter_kses', 1 );
add_filter( 'messages_notice_message_before_save', 'trm_filter_kses', 1 );
add_filter( 'messages_notice_subject_before_save', 'trm_filter_kses', 1 );

add_filter( 'trs_get_the_thread_message_content', 'trm_filter_kses', 1 );
add_filter( 'trs_get_the_thread_subject', 'trm_filter_kses', 1 );

add_filter( 'messages_message_content_before_save', 'force_balance_tags' );
add_filter( 'messages_message_subject_before_save', 'force_balance_tags' );
add_filter( 'messages_notice_message_before_save', 'force_balance_tags' );
add_filter( 'messages_notice_subject_before_save', 'force_balance_tags' );

add_filter( 'trs_get_message_notice_subject', 'trmtexturize' );
add_filter( 'trs_get_message_notice_text', 'trmtexturize' );
add_filter( 'trs_get_message_thread_subject', 'trmtexturize' );
add_filter( 'trs_get_message_thread_excerpt', 'trmtexturize' );
add_filter( 'trs_get_the_thread_message_content', 'trmtexturize' );

add_filter( 'trs_get_message_notice_subject', 'convert_smilies', 2 );
add_filter( 'trs_get_message_notice_text', 'convert_smilies', 2 );
add_filter( 'trs_get_message_thread_subject', 'convert_smilies', 2 );
add_filter( 'trs_get_message_thread_excerpt', 'convert_smilies', 2 );
add_filter( 'trs_get_the_thread_message_content', 'convert_smilies', 2 );

add_filter( 'trs_get_message_notice_subject', 'convert_chars' );
add_filter( 'trs_get_message_notice_text', 'convert_chars' );
add_filter( 'trs_get_message_thread_subject', 'convert_chars' );
add_filter( 'trs_get_message_thread_excerpt', 'convert_chars' );
add_filter( 'trs_get_the_thread_message_content', 'convert_chars' );

add_filter( 'trs_get_message_notice_text', 'make_clickable', 9 );
add_filter( 'trs_get_message_thread_excerpt', 'make_clickable', 9 );
add_filter( 'trs_get_the_thread_message_content', 'make_clickable', 9 );

add_filter( 'trs_get_message_notice_text', 'trmautop' );
add_filter( 'trs_get_the_thread_message_content', 'trmautop' );

add_filter( 'trs_get_message_notice_subject', 'stripslashes_deep' );
add_filter( 'trs_get_message_notice_text', 'stripslashes_deep' );
add_filter( 'trs_get_message_thread_subject', 'stripslashes_deep' );
add_filter( 'trs_get_message_thread_excerpt', 'stripslashes_deep' );
add_filter( 'trs_get_messages_subject_value', 'stripslashes_deep' );
add_filter( 'trs_get_messages_content_value', 'stripslashes_deep' );
add_filter( 'trs_get_the_thread_message_content', 'stripslashes_deep' );

add_filter( 'trs_get_the_thread_message_content', 'stripslashes_deep' );
add_filter( 'trs_get_the_thread_subject', 'stripslashes_deep' );

?>