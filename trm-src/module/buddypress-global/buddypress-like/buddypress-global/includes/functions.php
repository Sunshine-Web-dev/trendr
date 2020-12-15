<?php
// Exit if accessed directly
if (!defined('ABSPATH'))
	exit;

//add shortcode support
add_shortcode( 'BBOSS_AJAX_SEARCH_FORM', 'buddyboss_global_search_ajax_search_form_shortcode' );
function buddyboss_global_search_ajax_search_form_shortcode( $atts ){
	return buddyboss_global_search_buffer_template_part( 'ajax-search-form', '', false );
}

/**
 * Returns a trimmed activity content string.
 * Must be used while inside activity loop
 */
function buddyboss_global_search_activity_intro( $character_limit=50 ){
	$content = '';
	if ( trs_activity_has_content() ){
		$content = trs_get_activity_content_body();
		
		if( $content ){
			$content = trm_strip_all_tags( $content, true );
			
			$shortened_content = substr( $content, 0, $character_limit);
			if( strlen($content)> $character_limit )
				$shortened_content .= '...';
			
			$content = $shortened_content;
		}
	}
	
	return apply_filters( 'buddyboss_global_search_activity_intro', $content );
}

/**
 * Returns a trimmed reply content string.
 * Works for replies as well as topics.
 * Must be used while inside the loop
 */
function buddyboss_global_search_reply_intro( $character_limit=50 ){
	$content = '';
	
	switch( get_post_type( get_the_ID() ) ){
		case 'topic':
			$reply_content = btrs_get_topic_content(get_the_ID());
			break;
		case 'reply':
			$reply_content = btrs_get_reply_content(get_the_ID());
			break;
		default:
			$reply_content = get_the_content();
			break;
	}
	
	if( $reply_content ){
		$content = trm_strip_all_tags( $reply_content, true );
		$search_term = buddyboss_global_search()->search->get_search_term();

		$search_term_position = strpos( $content, $search_term );
		if( $search_term_position !== false ){
			$shortened_content = '...' . substr( $content, $search_term_position, $character_limit );
			//highlight search keyword
			
			$shortened_content = str_replace( $search_term, "<strong>" . $search_term . "</strong>", $shortened_content );
		} else {
			$shortened_content = substr( $content, 0, $character_limit );
		}
		
		if( strlen($content)> $character_limit )
			$shortened_content .= '...';

		$content = $shortened_content;
	}
	
	return apply_filters( 'buddyboss_global_search_reply_intro', $content );
}

if (!function_exists('emi_generate_paging_param')):

	/**
	 * Prints pagination links for given parameters with pagination value as a querystring parameter.
	 * Instead of printing http://yourdomain.com/../../page/2/, it prints http://yourdomain.com/../../?list=2
	 *
	 * If your theme uses twitter bootstrap styles, define a constant :
	 * define('BOOTSTRAP_ACTIVE', true)
	 * and this function will generate the bootstrap-pagination-compliant html.
	 *
	 * @param int $total_items total number of items(grand total)
	 * @param int $items_per_page number of items displayed per page
	 * @param int $curr_paged current page number, 1 based index
	 * @param string $slug part of url to be appended after the home_url and before the '/?ke1=value1&...' part. withour any starting or trailing '/'
	 * @param string $hashlink Optional, the '#' link to be appended ot url, optional
	 * @param int $links_on_each_side Optional, how many links to be displayed on each side of current active page. Default 2.
	 * @param string $param_key the name of the queystring paramter for page value. Default 'list'
	 *
	 * @return void
	 */
	function emi_generate_paging_param($total_items, $items_per_page, $curr_paged, $slug, $links_on_each_side = 2, $hashlink = "", $param_key = "list") {
		$use_bootstrap = false;
		if (defined('BOOTSTRAP_ACTIVE')) {
			$use_bootstrap = true;
		}

		$s = $links_on_each_side; //no of tabs to show for previos/next paged links
		if ($curr_paged == 0) {
			$curr_paged = 1;
		}
		/* $elements : an array of arrays; each child array will have following structure
		  $child[0] = text of the link
		  $child[1] = page no of target page
		  $child[2] = link type :: link|current|nolink
		 */
		$elements = array();
		$no_of_pages = ceil($total_items / $items_per_page);
		//prev lik
		if ($curr_paged > 1) {
			$elements[] = array('&larr;', $curr_paged - 1, 'link');
		}
		//generating $s(2) links before the current one
		if ($curr_paged > 1) {
			$rev_array = array(); //paged in reverse order
			$i = $curr_paged - 1;
			$counter = 0;
			while ($counter < $s && $i > 0) {
				$rev_array[] = $i;
				$i--;
				$counter++;
			}
			$arr = array_reverse($rev_array);
			if ($counter == $s) {
				$elements[] = array(' ... ', '', 'nolink');
			}
			foreach ($arr as $el) {
				$elements[] = array($el, $el, 'link');
			}
			unset($rev_array);
			unset($arr);
			unset($i);
			unset($counter);
		}

		//generating $s+1(3) links after the current one (includes current)
		if ($curr_paged <= $no_of_pages) {
			$i = $curr_paged;
			$counter = 0;
			while ($counter < $s + 1 && $i <= $no_of_pages) {
				if ($i == $curr_paged) {
					$elements[] = array($i, $i, 'current');
				} else {
					$elements[] = array($i, $i, 'link');
				}
				$counter++;
				$i++;
			}
			if ($counter == $s + 1) {
				$elements[] = array(' ... ', '', 'nolink');
			}
			unset($i);
			unset($counter);
		}

		//next link
		if ($curr_paged < $no_of_pages) {
			$elements[] = array('&rarr;', $curr_paged + 1, 'link');
		}
		/* enough php, lets echo some html */
		if (isset($elements) && count($elements) > 1 && $_POST['type'] != 'load_more') {
			?>
			<div class="pagination navigation" style="text-align: center;">
				<input type="hidden" value="<?php echo $curr_paged+1;?>" class="cur_page"/>
				<input type="hidden" value="<?php echo $no_of_pages;?>" class="no_of_pages"/>
				<a class="load_more" style="cursor:pointer;">Load More</a>
			</div>
			<?php
		}
	}
	
endif;