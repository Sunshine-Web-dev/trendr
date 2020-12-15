<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('BBoss_Global_Search_Forums')):

	/**
	 *
	 * BuddyPress Global Search  - search forums
	 * **************************************
	 *
	 *
	 */
	class BBoss_Global_Search_Forums extends BBoss_Global_Search_Type {
		private $type = 'forums';
		
		/**
		 * Insures that only one instance of Class exists in memory at any
		 * one time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0.0
		 * 
		 * @return object BBoss_Global_Search_Forums
		 */
		public static function instance() {
			// Store the instance locally to avoid private static replication
			static $instance = null;

			// Only run these methods if they haven't been run previously
			if (null === $instance) {
				$instance = new BBoss_Global_Search_Forums();
			}

			// Always return the instance
			return $instance;
		}
		
		/**
		 * A dummy constructor to prevent this class from being loaded more than once.
		 *
		 * @since 1.0.0
		 */
		private function __construct() { /* Do nothing here */
		}
		function sql( $search_term, $only_totalrow_count=false ){
			global $trmdb;
			$query_placeholder = array();
			
			$sql = " SELECT ";
			
			if( $only_totalrow_count ){
				$sql .= " COUNT( DISTINCT id ) ";
			} else {
				$sql .= " DISTINCT id , 'forums' as type, post_title LIKE '%%%s%%' AS relevance, post_date as entry_date  ";
				$query_placeholder[] = $search_term;
			}
			
			$sql .= " FROM 
						{$trmdb->prefix}posts 
					WHERE 
						1=1 
						AND (
								(
										(post_title LIKE '%%%s%%') 
									OR 	(post_content LIKE '%%%s%%')
								)
							) 
						AND post_type IN ( 'forum', 'topic', 'reply' ) 
						AND post_status = 'publish' 
				";
			$query_placeholder[] = $search_term;
			$query_placeholder[] = $search_term;
			return $trmdb->prepare( $sql, $query_placeholder );
		}
		
		protected function generate_html( $template_type='' ){
			$post_ids = array();
			foreach( $this->search_results['items'] as $item_id=>$item_html ){
				$post_ids[] = $item_id;
			}

			//now we have all the posts
			//lets do a trm_query and generate html for all posts
			$qry = new TRM_Query( array( 'post_type' =>array( 'forum', 'topic', 'reply' ), 'post__in'=>$post_ids, 'posts_per_page'=> -1 ) );
			if( $qry->have_posts() ){
				while( $qry->have_posts() ){
					$qry->the_post();

					/**
					 * The following will try to load loop/forum.php, loop/topic.php loop/reply.php(if reply is included).
					 * 
					 */
					$result_item = array(
						'id'	=> get_the_ID(),
						'type'	=> $this->type,
						'title'	=> get_the_title(),
						'html'	=> buddyboss_global_search_buffer_template_part( 'loop/' . get_post_type(), $template_type, false ),
					);

					$this->search_results['items'][get_the_ID()] = $result_item;
				}
			}
			trm_reset_postdata();
		}
		
	}

// End class BBoss_Global_Search_Posts

endif;
?>