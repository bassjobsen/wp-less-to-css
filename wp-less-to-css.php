<?php
/*
Plugin Name: WP LESS to CSS
Plugin URI: https://github.com/bassjobsen/wp-less-to-css
Description: This plugin helps you to build and maintain your website with LESS.
Version: 1.0
Author: Bass Jobsen
Author URI: http://bassjobsen.weblogs.fm/
License: GPLv2
*/

/*  Copyright 2013 Bass Jobsen (email : bass@w3masters.nl)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/



if(!class_exists('WP_LESS_to_CSS')) 
{ 
	
class WP_LESS_to_CSS 
{ 

	private	$customlesscode;
        
	public static $instance;
	public $folder,$filename,$folderurl;

/*
* Construct the plugin object 
*/ 
public function __construct() 
{ 
	self::$instance = $this;
	load_plugin_textdomain( 'wpless2css', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
 	// register actions 
	add_action('admin_init', array(&$this, 'admin_init')); 
	add_action('admin_menu', array(&$this, 'add_menu')); 
	
        $upload_dir = wp_upload_dir();
        $this->folder = trailingslashit($upload_dir['basedir']).'wpless2css/';
        $this->folderurl = trailingslashit($upload_dir['baseurl']).'wpless2css/'; 
        $this->filename = 'wpless2css.css';

	
	
	add_filter( 'init', array( $this, 'init' ) );
} 
// END public 



public function wpless2csssavecss()
{
				$plugindir = plugin_dir_path( __FILE__ );
				if(!class_exists('Less_Parser'))
				{
				require $plugindir.'phpless/Less.php';
			
				}
				$parser = new Less_Parser();
				if(!file_exists($rootless=get_stylesheet_directory().'/wpless2css/wpless2css.less'))
				{
					if(!file_exists($rootless=get_template_directory().'/wpless2css/wpless2css.less'))
					{
						if(!file_exists($rootless=str_replace('/wp-content/themes', '', get_theme_root()) .'/wpless2css/wpless2css.less'))
						{
							wp_die('<strong>wpless2css/wpless2css.less</strong> is missing');
						}	
					}
			    }
				$parser->parseFile($rootless , '' );
				
				if($extrafiles = apply_filters('add_extra_less_files',''))
				{
					foreach($extrafiles as $extrafile)
					{
						$parser->parseFile($extrafile);
				    }	
		     	}	
				$parser->parse( apply_filters('add_extra_less_code','') );
				$parser->parse( get_option('customlesscode'));
				$css = $parser->getCss();
			   

				file_put_contents( $this->folder.$this->filename, $css);
}	


/** 
 * Activate the plugin 
**/ 
public static function activate() 
{ 
	// Do nothing 
} 
// END public static function activate 

/** 
 * Deactivate the plugin 
 * 
**/ 
public static function deactivate() 
{ 

$upload_dir = wp_upload_dir();
$folder = trailingslashit($upload_dir['basedir']).'wpless2css/';
$filename = 'wpless2css.css';
unlink($folder.$filename);
rmdir($folder);
} 
// END public static function deactivate 

/** 
 * hook into WP's admin_init action hook 
 * */ 
 
public function admin_init() 
{ 
	// Set up the settings for this plugin 
	
	$this->init_settings(); 
	// Possibly do additional admin_init tasks 
} 
// END public static function activate - See more at: http://www.yaconiello.com/blog/how-to-write-wordpress-plugin/#sthash.mhyfhl3r.JacOJxrL.dpuf

/** * Initialize some custom settings */ 
public function init_settings() 
{ 
	// register the settings for this plugin 
	register_setting('wpless2cssversion-group', 'customlesscode'); 
	register_setting('wpless2cssversion-group', 'wpless2cssversion'); 
} // END public function init_custom_settings()


function load_options() {
		$this->customlesscode = get_option('customlesscode');

	}
	function reset_options() {
		delete_option($this->customlesscode);
		unset($this->customlesscode);
		$this->load_options();
	}
	
	function save_options() {
	
		update_option('customlesscode',$this->customlesscode);
		update_option('wpless2cssversion',time());
		
	}

		

/** * add a menu */ 
public function add_menu() 
{
	 
	 add_options_page('WP LESS to CSS', 'WP LESS to CSS', 'manage_options', 'wp-less-to-css', array(&$this, 'WP_LESS_to_CSS_settings_page'));
} // END public function add_menu() 

/** * Menu Callback */ 
public function WP_LESS_to_CSS_settings_page() 
{ 
	if(!current_user_can('manage_options')) 
	{ 
		wp_die(__('You do not have sufficient permissions to access this page.')); 
	
	} 
// Render the settings template 

include(sprintf("%s/templates/settings.php", dirname(__FILE__))); 

} 
// END public function plugin_settings_page() 

	



function init()
{

		$this->load_options();
		
		
		/* load css from upload dir */
		
		add_action( 'wp_enqueue_scripts', array($this,'wp_less_to_css_styles'));
			
}
		
function wp_less_to_css_styles()
{
		wp_enqueue_style( 'wpless2css', $this->folderurl.'wpless2css.css', array(),get_option('wpless2cssversion',1)  ); 
}


} // END class 

}

if(class_exists('WP_LESS_to_CSS')) 
{ // Installation and uninstallation hooks 
	register_activation_hook(__FILE__, array('WP_LESS_to_CSS', 'activate')); 
	register_deactivation_hook(__FILE__, array('WP_LESS_to_CSS', 'deactivate')); 
	
	$cbe = new WP_LESS_to_CSS();
	// Add a link to the settings page onto the plugin page 
	if(isset($cbe))
	{
		
		 function WP_LESS_to_CSS_settings_link($links) 
		 { 
			 $settings_link = '<a href="options-general.php?page=wp-less-to-css">'.__('Settings','wpless2css').'</a>';
			 array_unshift($links, $settings_link); 
			
			 return $links; 
		 } 	
		 $plugin = plugin_basename(__FILE__); 
		 	
		
		 add_filter("plugin_action_links_$plugin", 'WP_LESS_to_CSS_settings_link'); 
	}
	
}
