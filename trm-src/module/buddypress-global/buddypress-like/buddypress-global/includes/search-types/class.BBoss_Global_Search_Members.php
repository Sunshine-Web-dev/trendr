<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('BBoss_Global_Search_Members')):

	/**
	 *
	 * BuddyPress Global Search  - search members
	 * **************************************
	 *
	 *
	 */
	class BBoss_Global_Search_Members extends BBoss_Global_Search_Type {
		private $type = 'members';
		
		/**
		 * Insures that only one instance of Class exists in memory at any
		 * one time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0.0
		 *
		 * @return object BBoss_Global_Search_Members
		 */
		public static function instance() {
			// Store the instance locally to avoid private static replication
			static $instance = null;

			// Only run these methods if they haven't been run previously
			if (null === $instance) {
				$instance = new BBoss_Global_Search_Members();
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
		
		public function sql( $search_term, $only_totalrow_count=false ){
			/* an example UNION query :- 
			----------------------------------------------------
			SELECT 
				DISTINCT u.id, 'members' as type, u.user_nicename LIKE '%ce%' AS relevance, a.date_recorded as entry_date 
			FROM 
				trm_users u JOIN trm_trs_activity a ON a.user_id=u.id  
			WHERE 
				u.id IN ( SELECT user_id FROM trm_trs_xprofile_data WHERE value LIKE '%ce%' OR value LIKE '%ce%' ) 
				OR u.id IN ( 
					SELECT ID FROM trm_users 
					WHERE ( 
						user_login LIKE '%ce%' 
						OR user_nicename LIKE '%ce%' 
					) 
				) 
				AND a.component = 'members' 
				AND a.type = 'last_activity' 
			GROUP BY u.id 
			----------------------------------------------------
			*/
			global $trmdb, $trs;
			$query_placeholder = array(); 
			
			$sql = " SELECT ";
			
			if( $only_totalrow_count ){
				$sql .= " COUNT( DISTINCT u.id ) ";
			} else {
				$sql .= " DISTINCT u.id, 'members' as type, u.user_nicename LIKE '%%%s%%' AS relevance, a.date_recorded as entry_date ";
				$query_placeholder[] = $search_term;
			}
						
			$sql .= " FROM 
						{$trmdb->users} u JOIN {$trs->activity->table_name} a ON a.user_id=u.id 
					WHERE 
						1=1 
						AND ( u.id IN ( SELECT user_id FROM {$trs->profile->table_name_data} WHERE value LIKE '%%%s%%' ) 
							OR u.id IN ( 
								SELECT ID FROM {$trmdb->users}  
								WHERE ( 
									user_login LIKE '%%%s%%'  
									OR user_nicename LIKE '%%%s%%' 
								) 
							) 
						) 
						AND a.component = 'members' 
						AND a.type = 'last_activity' 
				";
			if( !$only_totalrow_count ){
				$sql .= " GROUP BY u.id ";
			}
			
			$query_placeholder[] = $search_term;
			$query_placeholder[] = $search_term;
			$query_placeholder[] = $search_term;
			return $trmdb->prepare( $sql, $query_placeholder );
		}
		
		protected function generate_html( $template_type='' ){
			$group_ids = array();
			foreach( $this->search_results['items'] as $item_id => $item ){
				$group_ids[] = $item_id;
			}

			//now we have all the posts
			//lets do a groups loop
			if( trs_has_members( array( 'include'=>$group_ids, 'per_page'=>count($group_ids) ) ) ){
				while ( trs_members() ){
					trs_the_member();

					$result_item = array(
						'id'	=> trs_get_member_user_id(),
						'type'	=> $this->type,
						'title'	=> trs_get_member_name(),
						'html'	=> buddyboss_global_search_buffer_template_part( 'loop/member', $template_type, false ),
					);

					$this->search_results['items'][trs_get_member_user_id()] = $result_item;
				}
			}
		}
		
	}

// End class BBoss_Global_Search_Members

endif;
?>