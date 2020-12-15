<?php
/**
Plugin Name: TRM-PJAX
Plugin URI: http://wordpress.org/extend/plugins/trm-pjax/
Description: Makes Wordpress use the PJAX (PushState + AJAX) technique for loading content
Version: 0.0.4.1
Author: Peter Elmered
Author URI: http://elmered.com
Text Domain: pe_trm_pjax
License: http://www.gnu.org/licenses/gpl.html GNU General Public License
*/
/*
  Copyright 2013 Peter Elmered (email: peter@elmered.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/




define('TRM_DEBUG', true);
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED );
ini_set("display_errors", 1);





if(!class_exists('Phpconsole'))
    require_once('libs/phpconsole/install.php');

//phpconsole('Hello world', 'peter');


define('TRM_PJAX_PLUGIN_URL', plugins_url().'/trm-pjax');
define('TRM_PJAX_PLUGIN_PATH', dirname(__FILE__).DIRECTORY_SEPARATOR);

require_once(TRM_PJAX_PLUGIN_PATH.'inc/define.php');

/**
 * Make sure this plugin is laoded first!
 */
add_action("activated_plugin", "load_this_plugin_first");
function load_this_plugin_first() {
	$active_plugins = get_option('active_plugins');
	$this_plugin_key = array_search(TRM_PJAX_PLUGIN_PATH, $active_plugins);
	if ($this_plugin_key) { // if it's 0 it's the first plugin already, no need to continue
		array_splice($active_plugins, $this_plugin_key, 1);
		array_unshift($active_plugins, TRM_PJAX_PLUGIN_PATH);
		update_option('active_plugins', $active_plugins);
	}
}


/**
 * Returns instance of singleton class
 *
 * @param string $class
 * @return object
 */
function trm_pjax_get_instance($class) {
    static $instances = array();
    
    if (!isset($instances[$class])) {
        $filepath =  TRM_PJAX_PLUGIN_INCLUDE_PATH.str_replace('_', '-', $class).'.php';
        if(file_exists($filepath))
        {
            require_once($filepath);
        }
        $classname = 'TRM_PJAX_'.$class;
        
        $instances[$class] = new $classname();
    }

    return $instances[$class];   // Don't return reference
}


/*
function trm_pjax_init() 
{
	
  $TRM_PJAX = new TRM_PJAX();
    
}
add_action( 'plugins_loaded', 'trm_pjax_init' );
*/


global $trm_pjax_options;


if(!function_exists( 'is_pjax_request' ))
{
        function is_pjax_request()
    {
        if(defined('IS_PJAX' && IS_PJAX))
        {
            return TRUE;
        }
        else if(defined('IS_PJAX' && !IS_PJAX))
        {
            return FALSE;
        }
        else if ( (array_key_exists('HTTP_X_PJAX', $_SERVER) && $_SERVER['HTTP_X_PJAX']) || (array_key_exists('X_PJAX', $_SERVER) && $_SERVER['X_PJAX']) )
        {
            define('IS_PJAX', TRUE);
            return TRUE;
        }
        else
        {
            define('IS_PJAX', FALSE);
            return FALSE;
        }
    }
}



if(!function_exists( 'trm_pjax_check_request' ))
{
    function trm_pjax_check_request( $check )
    {
        $define_key = 'IS_'.$check;
            
        if(defined($define_key) && constant($define_key))
        {
            return TRUE;
        }
        if(defined($define_key) && !constant($define_key))
        {
            return FALSE;
        }
        else if ( (array_key_exists('HTTP_'.$check, $_SERVER) && $_SERVER['HTTP_'.$check]) || (array_key_exists($check, $_SERVER) && $_SERVER[$check]) )
        {
            define($define_key, TRUE);
            return TRUE;
        }
        else
        {
            define($define_key, FALSE);
            return FALSE;
        }
    }
}


if(!function_exists( 'get_pjax_header' ))
{
    function get_pjax_header()
    {  
        if ( is_pjax_request() )
        {
            //Return TRUE to skip execution of trm_header
            return TRUE;
        }
        else
        {
            //Return FALSE to execute trm_header
            return FALSE;
        }
    }
}
if(!function_exists( 'get_pjax_footer' ))
{
    function get_pjax_footer()
    {  
        if ( is_pjax_request() )
        {
            do_action('get_pjax_footer');
    
            //Return TRUE to skip execution of trm_footer
            return TRUE;
        }
        else
        {
            //Return FALSE to execute trm_footer
            return FALSE;
        }
    }
}
if(!function_exists( 'get_pjax_sidebar' ))
{
    function get_pjax_sidebar()
    {  
        if ( is_pjax_request() )
        {
            do_action('trm_pjax_sidebar');
            
            //Return TRUE to skip execution of sidebar
            return TRUE;
        }
        else
        {
            //Return FALSE to execute sidebar
            return FALSE;
        }
    }
}

if(!function_exists( 'trm_pjax_header' ))
{
    add_action('trm_pjax_header', 'trm_pjax_header', 10, 3);
    
    function trm_pjax_header($trm, $pjax, $cacheHit)
    {
        if(  $pjax->_config[TRM_PJAX_CONFIG_PREFIX.'show-extended-notice'] && current_user_can('edit_plugins') || $pjax->_config['debug_mode'] )
        {
            header('PJAX-loaded-resource: '.$pjax->page_cache['key']);
        }
        
?>
<title><?php
	/*
	 * Print the <title> tag based on what is being viewed.
	 */
	//global $page, $paged;

       $title = apply_filters('trm_pjax_title', '');
       
       echo $title;
?></title>


<?php
    }
    
}

add_filter('trm_pjax_title', 'trm_pjax_title');

function trm_pjax_title($title) {

    $title = '';
    
    $title = trm_title( '|', FALSE, 'right' );

    // Add the blog name.
    $title .= get_bloginfo( 'name' );

    // Add the blog description for the home/front page.
    $site_description = get_bloginfo( 'description', 'display' );
    if ( $site_description && ( is_home() || is_front_page() ) )
        $title .= " | $site_description";
    
    return $title;
}
    



/* 
 * Instantiate the class
 */


$trm_pjax = trm_pjax_get_instance('TRM_PJAX');

$trm_pjax->run();


/*
if (class_exists('TRM_PJAX')) {
  $TRM_PJAX = new TRM_PJAX();
}
else
{
//    trigger_error('ERROR: TRM-PJAX Class not found: '.__FILE__ . ' (' .__LINE__ . ')', E_USER_WARNING );
}
*/

