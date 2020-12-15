<?php 
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if (!class_exists('BBoss_Global_Search_Helper')):

	/**
	 *
	 * BuddyPress Global Search Plugin Main Controller
	 * **************************************
	 *
	 *
	 */
	class BBoss_Global_Search_Helper {
		/**
		 * The variable to hold the helper class objects for each type of searches.
		 * E.g 
		 * [
		 *	'posts' => an object of BBoss_Global_Search_Posts,
		 *  'groups' => an object of BBoss_Global_Search_Groups
		 * ]
		 * @var array 
		 * @since 1.0.0
		 */
		private $search_helpers = array();
		
		/**
		 * The variable to hold arguments used for search.
		 * It will be used by other methods later on.
		 * @var array 
		 */
		private $search_args = array();
	
		/**
		 * The variable to hold search results.
		 * The results will be grouped into different types(e.g: posts, members, etc..)
		 * 
		 * The structure of array after being populated should be:
		 *		'posts'		=> [
		 *			'total_match_count'	=> 34,
		 *			'items'				=> 
		 *		],
		 *		'members'	=>
		 * @var array
		 */
		private $search_results = array();
		
		/**
		 * Insures that only one instance of Class exists in memory at any
		 * one time. Also prevents needing to define globals all over the place.
		 *
		 * @since 1.0.0
		 *
		 * @see buddyboss_global_search()
		 *
		 * @return object BuddyBoss_Global_Search_Plugin
		 */
		public static function instance() {
			// Store the instance locally to avoid private static replication
			static $instance = null;

			// Only run these methods if they haven't been run previously
			if (null === $instance) {
				$instance = new BBoss_Global_Search_Helper();
				
				//create instances of helpers and associate them with types
				add_action( 'init',						array( $instance, 'load_search_helpers' ), 80 );
				
				add_action( 'trm_ajax_bboss_global_search_ajax',			array( $instance, 'ajax_search' ) );
				add_action( 'trm_ajax_nopriv_bboss_global_search_ajax',	array( $instance, 'ajax_search' ) );
			}

			// Always return the instance
			return $instance;
		}

		/* Magic Methods
		 * ===================================================================
		 */

		/**
		 * A dummy constructor to prevent this class from being loaded more than once.
		 *
		 * @since 1.0.0
		 */
		private function __construct() { /* Do nothing here */
		}

		/**
		 * A dummy magic method to prevent this class from being cloned.
		 *
		 * @since 1.0.0
		 */
		public function __clone() {
			_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'trendr-global-search'), '1.7');
		}

		/**
		 * A dummy magic method to prevent this class being unserialized.
		 *
		 * @since 1.0.0
		 */
		public function __wakeup() {
			_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'trendr-global-search'), '1.7');
		}
		
		/**
		 * 
		 */
		public function load_search_helpers(){

			$searchable_types = buddyboss_global_search()->option('items-to-search');

			if( !empty( $searchable_types ) ){
				//load the helper type parent class
				require_once( BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_DIR . 'includes/search-types/class.BBoss_Global_Search_Type.php' );
				
				//load and associate helpers one by one
				if( in_array( 'posts', $searchable_types ) ){
					require_once( BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_DIR . 'includes/search-types/class.BBoss_Global_Search_Posts.php' );
					$this->search_helpers['posts'] = BBoss_Global_Search_Posts::instance();
				}
				
				if( in_array( 'groups', $searchable_types ) ){
					require_once( BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_DIR . 'includes/search-types/class.BBoss_Global_Search_Groups.php' );
					$this->search_helpers['groups'] = BBoss_Global_Search_Groups::instance();
				}
				
				if( in_array( 'members', $searchable_types ) ){
					require_once( BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_DIR . 'includes/search-types/class.BBoss_Global_Search_Members.php' );
					$this->search_helpers['members'] = BBoss_Global_Search_Members::instance();
				}
				
				if( in_array( 'forums', $searchable_types ) ){
					require_once( BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_DIR . 'includes/search-types/class.BBoss_Global_Search_Forums.php' );
					$this->search_helpers['forums'] = BBoss_Global_Search_Forums::instance();
				}
				
				if( in_array( 'activity', $searchable_types ) ){
					require_once( BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_DIR . 'includes/search-types/class.BBoss_Global_Search_Activities.php' );
					$this->search_helpers['activity'] = BBoss_Global_Search_Activities::instance();
				}
				
				if( in_array( 'messages', $searchable_types ) ){
					require_once( BUDDYBOSS_GLOBAL_SEARCH_PLUGIN_DIR . 'includes/search-types/class.BBoss_Global_Search_Messages.php' );
					$this->search_helpers['messages'] = BBoss_Global_Search_Messages::instance();
				}
				
				/**
				 * Hook to load helper classes for additional search types.
				 */
				$additional_search_helpers = apply_filters( 'bboss_global_search_additional_search_helpers', array() );
				if( !empty( $additional_search_helpers ) ){
					foreach( $additional_search_helpers as $search_type=>$helper_object ){
						/**
						 * All helper classes must inherit from BBoss_Global_Search_Type
						 */
						if( !isset( $this->search_helpers[$search_type] ) && is_a( $helper_object, 'BBoss_Global_Search_Type' ) ){
							$this->search_helpers[$search_type] = $helper_object;
						}
					}
				}
			}
		}
		
		public function ajax_search(){
			check_ajax_referer( 'bboss_global_search_ajax', 'nonce' );
			$this->load_search_helpers();
			if($_POST["view"] == "content") {
				
				$_GET["s"] = $_POST["s"];
				if(!empty($_POST["subset"])) {
					$_GET["subset"] = $_POST["subset"];
				}
				
				if(!empty($_POST["list"])) {
					$_GET["list"] = $_POST["list"];
				}
				
				$content = "";
				
				buddyboss_global_search()->search->prepare_search_page();

				if($_POST['type'] == 'load_more')
				{
					$content = buddyboss_global_search_buffer_template_part( 'result_ajax', '', false );	
				}
				else
				{
					$content = buddyboss_global_search_buffer_template_part( 'results-page-content', '', false );	
				}
				
				$list = $_POST['list'];
				echo json_encode(array('content'=>$content,'list'=>$list+1));
				
				die();
			}
			
			$args = array(
				'search_term'	=> $_REQUEST['search_term'],
				//How many results should be displyed in autosuggest?
				//@todo: give a settings field for this value
				'per_page'		=> 5,
				'count_total'	=> false,
				'template_type'	=> 'ajax',
			);
			
			$this->do_search( $args );
			
			$search_results = array();
			if( isset( $this->search_results['all']['items'] ) && !empty( $this->search_results['all']['items'] ) ){
				/* ++++++++++++++++++++++++++++++++++
				group items of same type together
				++++++++++++++++++++++++++++++++++ */
				$types = array();
				foreach( $this->search_results['all']['items'] as $item_id=>$item ){
					$type = $item['type'];
					if( empty( $types ) || !in_array( $type, $types ) ){
						$types[] = $type;
					}
				}
				
				$new_items = array();
				foreach( $types as $type ){
					$first_html_changed = false;
					foreach( $this->search_results['all']['items'] as $item_id=>$item ){
						if( $item['type']!= $type )
							continue;
						
						//add group/type title in first one
						/*
						if( !$first_html_changed ){
							//this filter can be used to change display of 'posts' to 'Blog Posts' etc..
							$label = apply_filters( 'bboss_global_search_label_search_type', $type );
							
							//$item['html'] = "<div class='results-group results-group-{$type}'><span class='results-group-title'>{$label}</span></div>" . $item['html'];
							$first_html_changed = true;
						}

						*/						
                        
						$new_items[$item_id] = $item;
					}
				}
				
				$this->search_results['all']['items'] = $new_items;
				
				/* _______________________________ */
				$type_mem = "";
				foreach( $this->search_results['all']['items'] as $item_id=>$item ){
					$new_row = array( 'value'=>$item['html'] );
					$type_label = apply_filters( 'bboss_global_search_label_search_type', ucfirst($item['type']) );
					$new_row['type'] = $item['type'];
					$new_row['type_label'] = "";
					$new_row['value'] = $item['html'];
					if( isset( $item['title'] ) ){
						$new_row['label'] = $item['title'];
					}
					
					if($type_mem != $new_row['type']) {
						$type_mem = $new_row['type'];
						$cat_row = $new_row;
						$cat_row["type"] = $item['type'];
						$cat_row['type_label'] = $type_label;
						$cat_row["value"] = $type_label;
						$search_results[] = $cat_row;
					}
					
					$search_results[] = $new_row;
				}
				
				$url = $this->search_page_search_url();
				
				$all_results_row = array(
					"value" => "<div class='bboss_ajax_search_item allresults'><a href='" . esc_url( $url ) . "'>" . sprintf( __( "View all results for '%s'", "trendr-global-search" ), $_REQUEST['search_term'] ) . "</a></div>",
					"type"	=> 'view_all_type',
					"type_label"	=> ''
				);
				$search_results[] = $all_results_row;
			} else {
				//@todo give a settings screen for this field
				$search_results[] = array(
					'value' => '<div class="bboss_ajax_search_item noresult">' . sprintf( __( "Nothing found for '%s'", "trendr-global-search" ), $_REQUEST['search_term'] ) . '</div>',
					'label'	=> $_REQUEST['search_term']
				);
			}
			
			die( json_encode( $search_results ) );
		}
		
		/**
		 * Perform search and generate search results
		 * @param mixed $args
		 * @since 1.0.0
		 */
		public function do_search( $args='' ){
			global $trmdb;
			$defaults = array(
				//the search term
				'search_term'		=> '',
				//Restrict search results to only this subset. eg: posts, members, groups, etc.
				//See Setting > what to search?
				'search_subset'		=> 'all',//
				//What all to search for. e.g: members.
				//See Setting > what to search?
				//The options passed here must be a subset of all options available on Setting > what to search, nothing extra can be passed here.
				//
				//This is different from search_subset.
				//If search_subset is 'all', then search is performed for all searchable items.
				//If search_subset is 'members' then only total match count for other searchable_items is calculated( so that it can be displayed in tabs)
				//members(23) | posts(201) | groups(2) and so on.
				'searchable_items'	=> buddyboss_global_search()->option('items-to-search'),
				//how many search results to display per page
				'per_page'			=> 10,
				//current page
				'current_page'		=> 1,
				//should we calculate total match count for all different types?
				//it should be set to false while calling this function for ajax search
				'count_total'		=> true,
				//template type to load for each item
				//search results will be styled differently(minimal) while in ajax search
				//options ''|'minimal' 
				'template_type'		=> '',
			);
			
			$args = trm_parse_args( $args, $defaults );
			

			$this->search_args = $args;//save it for using in other methods
			
			//bail out if nothing to search for
			if( !$args['search_term'] )
				return;
			
			if( 'all' == $args['search_subset'] ){
				
				/**
				 * 1. Generate a 'UNION' sql query for all searchable items with only ID, RANK, TYPE(posts|members|..) as columns, order by RANK DESC.
				 * 3. Generate html for each of them
				 */
				/* an example UNION query :- 
				-----------------------------------------------------
				(
					SELECT 
					trm_posts.id , 'posts' as type, trm_posts.post_title LIKE '%ho%' AS relevance, trm_posts.post_date as entry_date 
					FROM 
						trm_posts 
					WHERE 
						1=1 
						AND (
								(
										(trm_posts.post_title LIKE '%ho%') 
									OR 	(trm_posts.post_content LIKE '%ho%')
								)
							) 
						AND trm_posts.post_type IN ('post', 'page', 'attachment') 
						AND (
							trm_posts.post_status = 'publish' 
							OR trm_posts.post_author = 1 
							AND trm_posts.post_status = 'private'
						) 
				)
				UNION
				(
					SELECT 
						DISTINCT g.id, 'groups' as type, g.name LIKE '%ho%' AS relevance, gm2.meta_value as entry_date 
					FROM 
						trm_trs_groups_groupmeta gm1, trm_trs_groups_groupmeta gm2, trm_trs_groups g 
					WHERE 
						1=1 
						AND g.id = gm1.group_id 
						AND g.id = gm2.group_id 
						AND gm2.meta_key = 'last_activity' 
						AND gm1.meta_key = 'total_member_count' 
						AND ( g.name LIKE '%ho%' OR g.description LIKE '%ho%' )
				)

				ORDER BY 
					relevance DESC, entry_date DESC LIMIT 0, 10
				----------------------------------------------------
				*/
				
				$sql_queries = array();
				foreach( $args['searchable_items'] as $search_type ){
					if( !isset($this->search_helpers[$search_type]))
						continue;
					
					/**
					 * the following variable will be an object of current search type helper class
					 * e.g: an object of BBoss_Global_Search_Groups or BBoss_Global_Search_Posts etc.
					 * so we can safely call the public methods defined in those classes.
					 * This also means that all such classes must have a common set of methods.
					 */
					$obj = $this->search_helpers[$search_type];
					$sql_queries[] = "( " . $obj->union_sql( $args['search_term'] ) . " ) ";
				}
				
				if( empty( $sql_queries ) ){
					//thigs will get messy if program reaches here!!
					return;
				}
				
				$pre_search_query = implode( ' UNION ', $sql_queries) . " ORDER BY relevance DESC, entry_date DESC ";
				
				if( $args['per_page']> 0 ){
					$offset = ( $args['current_page'] * $args['per_page'] ) - $args['per_page'];
					$pre_search_query .= " LIMIT {$offset}, {$args['per_page']} ";
				
				}
				
				$results = $trmdb->get_results( $pre_search_query );
				/* $results will have a structure like below */
				/*
				id | type | relevance | entry_date
				45 | groups | 1 | 2014-10-28 17:05:18
				40 | posts | 1 | 2014-10-26 13:52:06
				4 | groups | 0 | 2014-10-21 15:15:36
				*/
				if( !empty( $results ) ){
					$this->search_results['all'] = array( 'total_match_count' => 0, 'items' => array(), 'items_title'=> array() );
					//segregate items of a type together and pass it to corresponsing search handler, so that an aggregate query can be done
					//e.g one single wordpress loop can be done for all posts
					
					foreach( $results as $item ){
						$obj = $this->search_helpers[$item->type];
						$obj->add_search_item( $item->id );
					}
					
					//now get html for each item
					foreach( $results as $item ){
					
						$obj = $this->search_helpers[$item->type];
						
						$result = array(
							'id'	=> $item->id,
							'type'	=> $item->type,
							'html'	=> $obj->get_html( $item->id, $args['template_type'] ),
							'title'	=> $obj->get_title( $item->id )
						);
						
						$this->search_results['all']['items'][$item->id] = $result;
					}
					//now we've html saved for search results
					
					if( !empty( $this->search_results['all']['items'] ) && $args['template_type']!='ajax' ){
						/* ++++++++++++++++++++++++++++++++++
						group items of same type together
						++++++++++++++++++++++++++++++++++ */
						//create another copy, of items, this time, items of same type grouped together
						$ordered_items_group = array();
						foreach( $this->search_results['all']['items'] as $item_id=>$item ){
							$type = $item['type'];
							if( !isset( $ordered_items_group[$type] ) ){
								$ordered_items_group[$type] = array();
							}

							$ordered_items_group[$type][$item_id] = $item;
						}
						
						foreach( $ordered_items_group as $type=>&$items ){
							//now prepend html (opening tags) to first item of each type
							$first_item = reset($items);
							$start_html = "<div class='results-group results-group-{$type} {$type}'>";
							if($_POST['type'] != 'load_more')
							{
								$start_html .= "<h2 class='results-group-title'><span>" . apply_filters( 'bboss_global_search_label_search_type', $type ) . "</span></h2>";
							}
										
								$start_html	.=	"<ul id='{$type}-stream' class='item-list {$type}-list'>";
									
							$group_start_html = apply_filters( "bboss_global_search_results_group_start_html", $start_html, $type );
							
							$first_item['html'] = $group_start_html . $first_item['html'];
							$items[$first_item['id']] = $first_item;
							
							//and append html (closing tags) to last item of each type
							$last_item = end($items);
							$end_html = "</ul></div>";
									
							$group_end_html = apply_filters( "bboss_global_search_results_group_end_html", $end_html, $type );
							
							$last_item['html'] = $last_item['html'] . $group_end_html;
							$items[$last_item['id']] = $last_item;
						}
						
						//replace orginal items with this new, grouped set of items
						$this->search_results['all']['items'] = array();
						foreach( $ordered_items_group as $type=>$grouped_items ){
							foreach( $grouped_items as $item_id=>$item ){
								$this->search_results['all']['items'][$item_id] = $item;
							}
						}
						/* ________________________________ */
					}
				}
			} else {
				//if subset not in searchable items, bail out.
				if( !in_array( $args['search_subset'], $args['searchable_items'] ) )
					return;
				
				if( !isset($this->search_helpers[$args['search_subset']]))
					return;
				
				/**
				 * 1. Search top top 20( $args['per_page'] ) item( posts|members|..)
				 * 2. Generate html for each of them
				 */
				
				$obj = $this->search_helpers[$args['search_subset']];
				$pre_search_query = $obj->union_sql( $args['search_term'] ) . " ORDER BY relevance DESC, entry_date DESC ";
				
				if( $args['per_page']> 0 ){
					$offset = ( $args['current_page'] * $args['per_page'] ) - $args['per_page'];
					$pre_search_query .= " LIMIT {$offset}, {$args['per_page']} ";
				}
				
				$results = $trmdb->get_results( $pre_search_query );
				
				/* $results will have a structure like below */
				/*
				id | type | relevance | entry_date
				45 | groups | 1 | 2014-10-28 17:05:18
				40 | posts | 1 | 2014-10-26 13:52:06
				4 | groups | 0 | 2014-10-21 15:15:36
				*/
				if( !empty( $results ) ){
					$obj = $this->search_helpers[$args['search_subset']];
					$this->search_results[$args['search_subset']] = array( 'total_match_count' => 0, 'items' => array() );
					//segregate items of a type together and pass it to corresponsing search handler, so that an aggregate query can be done
					//e.g one single wordpress loop can be done for all posts
					foreach( $results as $item ){
						$obj->add_search_item( $item->id );
					}
					
					//now get html for each item
					foreach( $results as $item ){
						$html = $obj->get_html( $item->id, $args['template_type'] );
						
						$result = array(
							'id'	=> $item->id,
							'type'	=> $args['search_subset'],
							'html'	=> $obj->get_html( $item->id, $args['template_type'] ),
							'title'	=> $obj->get_title( $item->id ),
						);
						
						$this->search_results[$args['search_subset']]['items'][$item->id] = $result;
					}
					
					//now prepend html (opening tags) to first item of each type
					$first_item = reset($this->search_results[$args['search_subset']]['items']);
					$start_html = "<div class='results-group results-group-{$args['search_subset']}'>"
							.	"<ul id='{$args['search_subset']}-stream' class='item-list {$args['search_subset']}-list'>";

					$group_start_html = apply_filters( "bboss_global_search_results_group_start_html", $start_html, $args['search_subset'] );

					$first_item['html'] = $group_start_html . $first_item['html'];
					$this->search_results[$args['search_subset']]['items'][$first_item['id']] = $first_item;

					//and append html (closing tags) to last item of each type
					$last_item = end($this->search_results[$args['search_subset']]['items']);
					$end_html = "</ul></div>";

					$group_end_html = apply_filters( "bboss_global_search_results_group_end_html", $end_html, $args['search_subset'] );

					$last_item['html'] = $last_item['html'] . $group_end_html;
					$this->search_results[$args['search_subset']]['items'][$last_item['id']] = $last_item;
				}
			}
			
			//html for search results is generated.
			//now, lets calculate the total number of search results, for all different types
			if( $args['count_total'] ){
				$all_items_count = 0;
				foreach( $args['searchable_items'] as $search_type ){
					if( !isset($this->search_helpers[$search_type]))
						continue;

					$obj = $this->search_helpers[$search_type];
					$total_match_count = $obj->get_total_match_count( $this->search_args['search_term'] );
					$this->search_results[$search_type]['total_match_count'] = $total_match_count;
					
					$all_items_count += $total_match_count;
				}
				
				$this->search_results['all']['total_match_count'] = $all_items_count;
			}
		}

		/**
		 * setup everything before starting to display content for search page.
		 */
		public function prepare_search_page(){
			$args = array();
			if( isset( $_GET['subset'] ) && !empty( $_GET['subset'] ) ){
				$args['search_subset'] = $_GET['subset'];
			}
			
			if( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ){
				$args['search_term'] = $_GET['s'];
			}
			
			if( isset( $_GET['list'] ) && !empty( $_GET['list'] ) ){
				$current_page = (int)$_GET['list'];
				if( $current_page > 0 ){
					$args['current_page'] = $current_page;
				}
			}
			
			$args = apply_filters( 'bboss_global_search_search_page_args', $args );
			$this->do_search( $args );
		}
				
		/**
		 * Returns the url of the page which is selected to display search results.
		 * @since 1.0.0
		 * @return string url of the serach results page
		 */
		public function search_page_url($value=""){
			$url = home_url( '/' );
			
			if(!empty($value)){
				$url = add_query_arg( 's',urlencode($value), $url );
			}
			
			return $url;
		}
		
		/**
		 * function to return full search url, added with search terms and other filters
		 */
		private function search_page_search_url(){
			$base_url = $this->search_page_url();
			$full_url = add_query_arg( 's', urlencode( $this->search_args['search_term'] ), $base_url );
			//for now we only have one filter in url
			return $full_url;
		}
		
		public function print_tabs(){
			$search_url = $this->search_page_search_url();
			
			//first print the 'all results' tab
			$class = 'all'==$this->search_args['search_subset'] ? 'active current' : '';
			//this filter can be used to change display of 'all' to 'Everything' etc..
			$label = apply_filters( 'bboss_global_search_label_search_type', 'all' );

			if( $this->search_args['count_total'] ){
				$label .= "<span class='count'>" . $this->search_results['all']['total_match_count'] . "</span>";
			}
			
			$tab_url = $search_url;
			echo "<li class='{$class}'><a href='" . esc_url($tab_url) . "'>{$label}</a></li>";
			
			//then other tabs
			foreach( $this->search_args['searchable_items'] as $item ){
				$class = $item==$this->search_args['search_subset'] ? 'active current' : '';
				//this filter can be used to change display of 'posts' to 'Blog Posts' etc..
				$label = apply_filters( 'bboss_global_search_label_search_type', $item );
				
				if(empty($this->search_results[$item]['total_match_count'])) {
					continue; //skip tab
				}
				
				if( $this->search_args['count_total'] ){
					$label .= "<span class='count'>" . (int)$this->search_results[$item]['total_match_count'] . "</span>";
				}
				
				$tab_url = add_query_arg( 'subset', $item, $search_url );
				echo "<li class='{$class} {$item}' data-item='{$item}'><a href='" . esc_url($tab_url) . "'>{$label}</a></li>";
			}
		}
		
		public function print_results(){
			$current_tab = $this->search_args['search_subset'];
			if( isset( $this->search_results[$current_tab]['items'] ) && !empty( $this->search_results[$current_tab]['items'] ) ){
				foreach( $this->search_results[$current_tab]['items'] as $item_id=>$item ){
					echo $item['html'];
				}
				
				if( function_exists( 'emi_generate_paging_param' ) ){
					$page_slug = untrailingslashit( str_replace( home_url(), '', $this->search_page_url() ) );
					emi_generate_paging_param( 
						$this->search_results[$current_tab]['total_match_count'], 
						$this->search_args['per_page'], 
						$this->search_args['current_page'], 
						$page_slug
					);
				}
			} else {
				buddyboss_global_search_buffer_template_part( 'no-results', $current_tab );
			}
		}
		
		public function get_search_term(){
			return isset( $this->search_args['search_term'] ) ? $this->search_args['search_term'] : '';
		}
	}

// End class BBoss_Global_Search_Helper

endif;
?>