<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('BBoss_Global_Search_CPT')):

	/**
	 *
	 * BuddyPress Global Search  - search posts
	 * **************************************
	 *
	 *
	 */
	class BBoss_Global_Search_CPT extends BBoss_Global_Search_Type {
		private $cpt_name;
		private $search_type;
		
		/**
		 * A real constructor. Since we do want multiple copies of this class.
		 * The idea is to have one object for each searchable custom post type.
		 *
		 * @since 1.0.0
		 */
		public function __construct( $cpt_name, $search_type ) {
			$this->cpt_name = $cpt_name;
			$this->search_type =$search_type;
		}
		
		public function sql( $search_term, $only_totalrow_count=false ){
			
			global $trmdb;
			$query_placeholder = array(); 
			
			$sql = " SELECT ";
			
			if( $only_totalrow_count ){
				$sql .= " COUNT( DISTINCT id ) ";
			} else {
				$sql .= " DISTINCT id , %s as type, post_title LIKE '%%%s%%' AS relevance, post_date as entry_date  ";
				$query_placeholder[] = $this->search_type;
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
						AND post_type = %s 
						AND post_status = 'publish' 
				";
			$query_placeholder[] = $search_term;
			$query_placeholder[] = $search_term;
			$query_placeholder[] = $this->cpt_name;
			return $trmdb->prepare( $sql, $query_placeholder );
		}
		
		protected function generate_html( $template_type='' ){
			$post_ids = array();
			foreach( $this->search_results['items'] as $item_id=>$item_html ){
				$post_ids[] = $item_id;
			}

			//now we have all the posts
			//lets do a trm_query and generate html for all posts
			$qry = new TRM_Query( array( 'post_type' => $this->cpt_name, 'post__in'=>$post_ids, 'posts_per_page'=> -1 ) );
			if( $qry->have_posts() ){
				while( $qry->have_posts() ){
					$qry->the_post();	
					$result = array(
						'id'	=> get_the_ID(),
						'type'	=> $this->search_type,
						'title'	=> get_the_title(),
						'html'	=> buddyboss_global_search_buffer_template_part( 'loop/post', $template_type, false ),
					);

					$this->search_results['items'][get_the_ID()] = $result;
				}
			}
			trm_reset_postdata();
		}
		
	}

// End class BBoss_Global_Search_CPT

endif;
?>