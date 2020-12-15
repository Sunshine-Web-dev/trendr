<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Config
 *
 * @author Peter Elmered
 */
class TRM_PJAX_Config
{
    private $_config = NULL;
    
    public function __construct()
    {
        $this->_config = $this->get();
    }
    
    function load()
    {
        //Load plugins options
        $this->_config = get_option(TRM_PJAX_OPTIONS_KEY, FALSE);
        
        //If not set, add the option
        if(!$this->_config)
        {
            add_option( TRM_PJAX_OPTIONS_KEY, array(), '', 'yes' );
        }
    }
    
    function get()
    {
        if( empty($this->_config))
        {
            $this->load();
        }
        
        return $this->_config;
    }
    
    function get_defaults()
    {
        return array(
            TRM_PJAX_CONFIG_PREFIX.'menu-selector' => 'body a',
            TRM_PJAX_CONFIG_PREFIX.'content-selector' => '#main',
            TRM_PJAX_CONFIG_PREFIX.'menu-active-class' => 'current_page_item current_menu_item',
            TRM_PJAX_CONFIG_PREFIX.'show-toggle' => 1,
            TRM_PJAX_CONFIG_PREFIX.'load-timeout' => 4000, 
            TRM_PJAX_CONFIG_PREFIX.'show-notice' => 1, 
            TRM_PJAX_CONFIG_PREFIX.'show-extended-notice' => 0,
            TRM_PJAX_CONFIG_PREFIX.'notice-sticky' => 1,
            TRM_PJAX_CONFIG_PREFIX.'page-cache' => 0, 
            TRM_PJAX_CONFIG_PREFIX.'browser-page-cache' => 0, 
            TRM_PJAX_CONFIG_PREFIX.'page-cache-lifetime' => 600,
            TRM_PJAX_CONFIG_PREFIX.'page-cache-exceptions' => 'trm-admin',
            TRM_PJAX_CONFIG_PREFIX.'strip-cookies' => 0,
            TRM_PJAX_CONFIG_PREFIX.'page-cache-prefetch' => 0,
            TRM_PJAX_CONFIG_PREFIX.'page-cache-prefetch-interval' => 300,
            TRM_PJAX_CONFIG_PREFIX.'page-cache-prefetch-pages-per-interval' => 20,
            //TRM_PJAX_CONFIG_PREFIX.'page-cache-prefetch-sitemap-url' => '',
            TRM_PJAX_CONFIG_PREFIX.'page-cache-prefetch-sitemap-refresh-interval' => 6000
        );
    }
    
    
    /**
 * Add admin settingspage
 */
    function admin_pages() {
        add_options_page(  'TRM PJAX', 'TRM-PJAX', 'manage_options','pe-trm-pjax', array($this, 'configuration_page') ); 
    }
    
/**
 * Prints the content of the configuration page
 */

    function configuration_page() 
    {
        
	if ( !current_user_can( 'manage_options' ) )  {
		trm_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
        
        $available_options = array(
            //Clear cache
            TRM_PJAX_CONFIG_PREFIX.'clear-cache',
            //Enable plugin
            TRM_PJAX_CONFIG_PREFIX.'enable',
            //Basic settings / Selectors
            TRM_PJAX_CONFIG_PREFIX.'menu-selector', 
            TRM_PJAX_CONFIG_PREFIX.'content-selector',
            TRM_PJAX_CONFIG_PREFIX.'menu-active-class',
            TRM_PJAX_CONFIG_PREFIX.'show-toggle', 
            //Advanced settings
            //General
            TRM_PJAX_CONFIG_PREFIX.'load-timeout',
            //Notices
            TRM_PJAX_CONFIG_PREFIX.'show-notice', 
            TRM_PJAX_CONFIG_PREFIX.'show-extended-notice',
            TRM_PJAX_CONFIG_PREFIX.'notice-timeout',
            TRM_PJAX_CONFIG_PREFIX.'notice-sticky',
            //Page Cache
            TRM_PJAX_CONFIG_PREFIX.'page-cache',
            TRM_PJAX_CONFIG_PREFIX.'browser-page-cache',
            TRM_PJAX_CONFIG_PREFIX.'page-cache-lifetime',
            TRM_PJAX_CONFIG_PREFIX.'page-cache-exceptions',
            //Strip cookies
            TRM_PJAX_CONFIG_PREFIX.'strip-cookies',
            TRM_PJAX_CONFIG_PREFIX.'strip-cookies-list',
            //Page Cache Prefetch
            TRM_PJAX_CONFIG_PREFIX.'page-cache-prefetch',
            TRM_PJAX_CONFIG_PREFIX.'page-cache-prefetch-interval',
            TRM_PJAX_CONFIG_PREFIX.'page-cache-prefetch-pages-per-interval',
            //Prefetch
            TRM_PJAX_CONFIG_PREFIX.'page-cache-prefetch-sitemap-url',
            TRM_PJAX_CONFIG_PREFIX.'page-cache-prefetch-sitemap-refresh-interval', 
            TRM_PJAX_CONFIG_PREFIX.'page-cache-prefetch-sitemap-refresh-on-publish',
            //Fading
            TRM_PJAX_CONFIG_PREFIX.'content-fade',
            TRM_PJAX_CONFIG_PREFIX.'content-fade-timeout-in',
            TRM_PJAX_CONFIG_PREFIX.'content-fade-timeout-out',
        );
        
        
        $trm_pjax_options = $this->get();
        
        
        
        if( isset($_POST[TRM_PJAX_CONFIG_PREFIX.'clear-cache']))
        {
            $page_cache = trm_pjax_get_instance('PageCache');
            $page_cache->init( $this->_config );
            $page_cache->clearCache();
            
            echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Cache cleared!</strong></p></div>';
        }
        else
        {
            $plugin_option_array = array();

            foreach ( $available_options AS $o )
            {
                if( !empty($_POST[$o]) || is_numeric($_POST[$o]) )
                {
                    $plugin_option_array[$o] = $_POST[$o];
                }
            }

            if( !empty($plugin_option_array) )
            {
                update_option( TRM_PJAX_OPTIONS_KEY, $plugin_option_array ); 

                echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Settings saved!</strong></p></div>';
                
                $trm_pjax_options = array_merge($trm_pjax_options, $plugin_option_array);
            }
            
        }
        
        //Load default settings
        if( isset($_POST[TRM_PJAX_CONFIG_PREFIX.'load-default-settings']))
        {
            $trm_pjax_options = array_merge($trm_pjax_options, $this->get_defaults());
            
            update_option( TRM_PJAX_OPTIONS_KEY, $trm_pjax_options ); 
            
            echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>Deafault settings loaded!</strong></p></div>';
        }
        
        
        //print_r($trm_pjax_options);
        
        include TRM_PJAX_PLUGIN_PATH.'views/configuration.php';
    }
    
}
