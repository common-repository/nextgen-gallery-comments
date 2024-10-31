<?php
/*
Plugin Name: NextGEN Gallery Comments
Plugin URI: 
Description: This plugin add comments to every NextGEN gallery (admin and frontend)
Version: 0.1.1
Author: Roberto Cantarano
Author URI: http://www.cantarano.com
*/
/*
Copyright 2011 Roberto Cantarano  (email : roberto@cantarano.com)

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

// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('Non puoi accedere direttamente a questa pagina...'); }

// ini_set('display_errors', '1');
// ini_set('error_reporting', E_ALL);

$rcwd_ngg_base_page = 'admin.php?page=nggallery-manage-gallery';

if (!class_exists('rcwdNggComments')){
	class rcwdNggComments{
		
		function init(){
			$active_plugins = get_option('active_plugins', FALSE);
			$this->vars_and_constants();
			$this->functions();				
			if (!in_array($this->depends, $active_plugins)){
				deactivate_plugins(plugin_basename(__FILE__));
				wp_die("Questo plugin necessita l'attivazione di NEXTGEN... che non risulta essere presente.");
				return; 
			}				
/*			if(class_exists('nggGallery')){ TODO: find a way to use it!
			}*/
			register_activation_hook( $this->plugin_name, array(&$this, 'activate') );
			register_deactivation_hook( $this->plugin_name, array(&$this, 'deactivate') );
			add_action( 'plugins_loaded', array(&$this, 'start_plugin') );		
			include_once(dirname(__FILE__).'/comments/comments.php');
		}
	
		function vars_and_constants(){
			global $wpdb;
			define('RCWDNGGCOMMENTS_DIRNAME', plugin_basename( dirname(__FILE__)));
			define('RCWDNGGCOMMENTS_ALBUM_TAB', $wpdb->prefix.'ngg_album');
			define('RCWDNGGCOMMENTS_GALLERY_TAB', $wpdb->prefix.'ngg_gallery');
			define('RCWDNGGCOMMENTS_PATH', trailingslashit( str_replace("\\","/", WP_PLUGIN_DIR . '/' . plugin_basename( dirname(__FILE__) ) ) ));
			define('RCWDNGGCOMMENTS_TEMPLATE', 'comments-nggallery.php');
			define('RCWDNGGCOMMENTS_THEME_TEMPLATE_FOLDER', 'nggallery');
			define('RCWDNGGCOMMENTS_TEMPLATE_PATH', RCWDNGGCOMMENTS_PATH.'/template/'.RCWDNGGCOMMENTS_TEMPLATE);
			$this->plugin_name = plugin_basename(__FILE__);
			$this->depends	   = 'nextgen-gallery/nggallery.php';
		}
	
		function functions(){
			require_once(dirname(__FILE__).'/functions/functions.php');
		}
		
		function activate(){
			global $wpdb;
			if (version_compare(PHP_VERSION, '5.2.0', '<')) { 
				deactivate_plugins(plugin_basename(__FILE__));
				wp_die("Il plugin richiede una versione di PHP pari o maggiore di 5.2.0"); 
				return; 
			} 
			$active_plugins = get_option('active_plugins', FALSE);
			if (!in_array($this->depends, $active_plugins)){
				deactivate_plugins(plugin_basename(__FILE__));
				wp_die("Questo plugin necessita l'attivazione di NEXTGEN... che non risulta essere presente.");
				return; 
			}
			if(!current_user_can('activate_plugins')) return;	
		}
		
		function deactivate(){
		}	

		function start_plugin(){
			load_plugin_textdomain('nggcomments', false, dirname(plugin_basename(__FILE__)).'/lang');
		}							
	}
}
$rcwdNggComments = new rcwdNggComments();
$rcwdNggComments->init();
?>