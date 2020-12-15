<?php
/**
 * Description of trm-pjax
 *
 * @author Peter Elmered
 */
 
//var_dump(get_transient('pjax_page_cache_page-na_pagename-fasiliteter'));


require_once 'Util.php';

define('TRM_DEBUG', true);
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED );
ini_set("display_errors", 1);


class TRM_PJAX_TRM_PJAX
{

    var $is_pjax; //Is current request PJAX?
    var $_config = array();
    var $plugin_url;
    var $plugin_path;
    var $page_cache = '';
    
    
    function run()
    {
        //Set plugin url
        $this->plugin_url = plugins_url().'/trm-pjax';
        //Set plugin path
        $this->plugin_path = plugin_dir_path(dirname(__FILE__));
        
        $config = trm_pjax_get_instance('Config');
        $this->_config = $config->get();
        
        $this->_config['debug_mode'] = TRUE;
        
        if( is_admin() )
        {
            //Add admin settings menu item
            add_action('admin_menu', array(&$config, 'admin_pages') );
            
            //Configuration page styles
            trm_enqueue_style( 'trm-pjax-admin', $this->plugin_url . '/css/trm-pjax-admin.css' );
        }
        
        if( $this->_config[TRM_PJAX_CONFIG_PREFIX.'enable'] != 1 )
        {
            return;
        }
        
        $this->is_pjax = is_pjax_request();
        
        global $trm_pjax_options;
        $trm_pjax_options = $this->_config;

        
    //    add_action('plugins_loaded', array($this, 'test'), 1);    // lower priority - allow packages to load

        
        if( $this->is_pjax )
        {
            
            if( $this->_config[TRM_PJAX_CONFIG_PREFIX.'page-cache'] == 1 )
            {
                add_action('send_headers', array(&$this, 'send_headers'), 2, 999 );  
            
                $this->page_cache = &trm_pjax_get_instance('PageCache');
                $this->page_cache->init( $this->_config );
                
                
                
                //add_action('send_headers', array(&$this, 'send_headers'), 1, 999 );  
            }
            else if($this->_config[TRM_PJAX_CONFIG_PREFIX.'show-extended-notice'] == 1)
            {
                header( 'PJAX-Page-Cache: DISABLED');
            }
            
        //phpconsole(array('action'=> 'Initialize', 'start' => $start + $i ), 'peter');
            //$this->send_headers($trm);
            //$this->pjax_render($trm);
            
            //Include and render page template
            add_action('trm', array(&$this, 'pjax_render') );
            //add_action('template_redirect', array(&$this, 'pjax_render') );
            
            //we only want partial content -> Stop execution
            //die();
        }
        else
        {
            add_action('get_header', array(&$this, 'pjax_load') );
         }
        /*
        global $trm_filter;
        print_r($trm_filter['get_header']);
        */
        //add_action('get_header', array(&$this, 'pjax_render') );
       
        //Add style and scripts to header
        //add_action('trm_head', array(&$this, 'enqueue_assets') );
        
        if( $this->_config[TRM_PJAX_CONFIG_PREFIX.'page-cache-prefetch'] == 1)// && $_SERVER['REQUEST_URI'] == '/' )
        {
            $this->page_cache = &trm_pjax_get_instance('PageCachePrefetch');
            $this->page_cache->init( $this->_config );

            //add_action('send_headers', array(&$this, 'send_headers'), 1, 999 );  
        }


        
    // Register hooks that are fired when the plugin is activated, deactivated, and uninstalled, respectively.
        register_activation_hook( __FILE__, array(&$this, 'activate_plugin') );
        register_deactivation_hook( __FILE__, array(&$this, 'deactivate_plugin') );
        register_uninstall_hook( __FILE__, array(&$this, 'uninstall_plugin' ) );
        
    }
    
    
    function activate_plugin()
    {
        
    }
    function deactivate_plugin()
    {
        
    }
    function uninstall_plugin()
    {
        
    }
    

    

    
    function send_headers( $trm, $pg )
    {
        if(!$pg)
        {
            $pg = $this->page_cache;
            //$pg = &trm_pjax_get_instance('PageCache');
        }
        
        if( $pg->status !== 'SKIP')
        {
            //Attemt to cache the HTML in the browser cache
            //But do not serve cached pages to prefetch
            if( !trm_pjax_check_request('HTTP_X_TRM_PJAX_PREFETCH')  && $this->_config[TRM_PJAX_CONFIG_PREFIX.'browser-page-cache'] == 1 )
            {
                $seconds_to_cache = 650;
                $ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
                header("Expires: $ts");
                header("Pragma: cache");
                header("Cache-Control: max-age=$seconds_to_cache");
            }
            
            //echo $_SERVER['HTTP_COOKIE'];
            // Unset cookies
            if ( $this->_config[TRM_PJAX_CONFIG_PREFIX.'strip-cookies'] == 1 && isset($_SERVER['HTTP_COOKIE'])) {
                $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
                foreach($cookies as $cookie) {
                    $parts = explode('=', $cookie);
                    $name = trim($parts[0]);
                    setcookie($name, '', time()-1000);
                    setcookie($name, '', time()-1000, '/');
                }
                //header_remove('Cookie');
            }
        }
        

        if(  $this->_config[TRM_PJAX_CONFIG_PREFIX.'show-extended-notice'] == 1 && current_user_can('edit_plugins')) //|| $this->_config['debug_mode'] )
        {
            header('PJAX-loaded-resource: '.$pg->key );    
            
            if(!isset($pg->status ))
            {
                $pg->status == 'MISS';
            }
            header( 'PJAX-Page-Cache: '.$pg->status );
            /*
            if( $pg->status == 'HIT' )
            {
                header('PJAX-Page-Cache: HIT');
            }
            else
            {
                header('PJAX-Page-Cache: MISS');
            }
            */
        }
        
    }
    

    //PJAX
    function pjax_load()
    {
        trm_enqueue_script('trm-pjax', $this->plugin_url . '/js/jquery-pjax/jquery.pjax.js', array('jquery'));
        //trm_enqueue_script('theme-pjax', $this->plugin_url . '/js/trm-pjax.js', array('jquery', 'pjax'));
        //trm_enqueue_script('theme-pjax', $this->plugin_url . '/trm-pjax.js.php', array('jquery', 'pjax'));
        
        add_action( 'trm_head', array(&$this, 'generate_js') );
        
        if( $this->_config[TRM_PJAX_CONFIG_PREFIX.'show-notice'] == 1  && current_user_can('edit_plugins')|| $this->_config['debug_mode'] )
        {
            trm_enqueue_script('jquery-notice', $this->plugin_url . '/js/jquery.notice.js', array('jquery'));
            
            trm_enqueue_style( 'trm-pjax', $this->plugin_url . '/css/trm-pjax.css' );
        }
        
        if( $this->_config[TRM_PJAX_CONFIG_PREFIX.'show-toggle'] == 1 && current_user_can('edit_plugins') || $this->_config['debug_mode'] )
        {
            trm_enqueue_style( 'trm-pjax', $this->plugin_url . '/css/trm-pjax.css' );
            
            add_action('trm_footer', array(&$this, 'add_toggle_html') );
        }
    }
    
    
    function add_toggle_html()
    {
        include $this->plugin_path.'views/toggle.php';
    }
    
    function generate_js()
    {
        $trm_pjax_options = $this->_config;
        include $this->plugin_path.'inc/TRM-PJAX.js.php';
    }

    function pjax_render( $trm )
    {
        $this->page_cache = &trm_pjax_get_instance('PageCache');
        
        if( $this->_config[TRM_PJAX_CONFIG_PREFIX.'page-cache'] == 1 )
        {
            ob_start();  
        }

        //Include the original TRM tamplate loader. This will output the right page template/content
        include ABSPATH . TRMINC.DIRECTORY_SEPARATOR.'template-loader.php';        

        if( $this->_config[TRM_PJAX_CONFIG_PREFIX.'page-cache'] == 1 )
        {
            $page_content = ob_get_clean();
            //echo $page_content;
            //trm_cache_set( 'pjax_post_'.$post_id, $page_content,  'pjax_page_cache', 300 );
            
            $this->page_cache->set($page_content);
            
            echo $page_content;
            die();
        }
        
        return '';
    }
        


}

