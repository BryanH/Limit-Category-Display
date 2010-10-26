<?php
/*
Plugin Name: Limit Category Display
Plugin URI: http://github.com/BryanH/Limit-Category-Display
Description: Limits the number of categories displayed on all posts on the index page.
Version: 0.912
Author: Bryan Hanks, PMP
Author URI: http://www.chron.com/apps/adbxh0/
License: GPLv3
*/
/*
  Copyright 2010 Houston Chronicle, Inc.

  Limit Category Display is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This software is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
if (!class_exists("LimitCategoryDisplay")) {
	class LimitCategoryDisplay {
		var $meta_sm = 'max_category_display';
		var $defaults = array (
			'max_category_display' => '5'
		); // default values
		function LimitCategoryDisplay() { //constructor
			$this->register_plugin();
			/* Widget settings. */
			$widget_ops = array (
				'classname' => 'limit_category_display',
				'description' => __('Limit number of categories to display for each article on the index page', 'limit_categories')
			);
			add_action('plugins_loaded', array (
				$this,
				'register_plugin'
			));
			add_action('admin_menu', array (
				$this,
				'plugin_options_menu'
			));
			add_filter('the_category', array (
				$this,
				'limit_category_filter' //				'niche_site_limit_the_category_output'
				
			), 10, 2);
			//			add_filter('the_category', 'niche_site_limit_the_category_output', 10, 2);
		}
		// Generic plugin functionality by John Blackbourn
		function register_plugin() {
			$this->plugin = (object) array (
				'dom' => strtolower(get_class($this)),
				'url' => WP_PLUGIN_URL . '/' . basename(dirname(__FILE__)),
				'dir' => WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__))
			);
			$this->settings = get_option($this->plugin->dom);
			if (!$this->settings) {
				add_option($this->plugin->dom, $this->defaults, true, true);
				$this->settings = $this->defaults;
			}
			load_plugin_textdomain($this->plugin->dom, false, $this->plugin->dom);
			add_action('admin_init', array (
				$this,
				'register_setting'
			));
		}
		function register_setting() {
			if ($callback = method_exists($this, 'sanitize'))
				$callback = array (
					$this,
					'sanitize'
				);
			register_setting($this->plugin->dom, $this->plugin->dom, $callback);
		}
		function plugin_options_menu() {
			add_options_page(__('Limit Category Display', 'limit_categories'), __('Limit Category Display', 'limit_categories'), 'manage_options', 'limitcategorysettings', array (
				$this,
				'options'
			));
		}
		/*
		* Retrieves value from datastore. If nothing returned,
		* then it returns the default (or null if no default)
		* (equivalent to $foo = $bar || $default)ted talk motivation
		* Parameters:	$key - datastore key
		*				$default - default value
		* Returns: either datastore's value, or default if former is empty
		*/
		function get_value_or_default($key, $default = null) {
			$the_data = get_option($key);
			if (true == empty ($the_data)) {
				$the_data = $default;
			}
			return $the_data;
		}
		/*
		 * Updates a given option from the form's posted value
		 * PARAMETER: key - key of value
		 * RETURNS: value of key
		 */
		function update_from_post($key) {
			if (true == empty ($key)) {
				wp_die(__('Invalid key passed to trss_update_from_post'));
			}
			update_option($key, $_POST[$key]);
			return $_POST[$key];
		}
		/*
		 * Plugin options
		 */
		function options() {
			if (!current_user_can('manage_options')) {
				wp_die(__('You do not have sufficient permissions to access this page.'));
			}
			$max_cats = 5;
			if (isset ($_POST[$this->meta_sm])) {
				check_admin_referer('limit_cats-admin_settings');
				if (!is_numeric($_POST[$this->meta_sm])) {
					wp_die("Value must be a number");
				}
				$max_cats = $this->update_from_post($this->meta_sm);
			} else {
				$max_cats = get_option($this->meta_sm, $this->defaults[$this->meta_sm]);
			}
?>
<div class="updated"><p><strong><?php _e('settings saved', 'menu-trss' ); ?></strong></p></div>
<?php

			include ("{$this->plugin->dir}/options.php");
		}
		/*
		 * Limit the post's category display by truncating the array to no
		 * bigger than the configured value
		 */
		function limit_category_filter($content, $separator = ', ') {
			if (!is_admin()) {
				$categories = array_slice(get_the_category(), 0, get_option($this->meta_sm, $this->defaults[$this->meta_sm]));
				$contents = array ();
				foreach ($categories as $category) {
					$link_str = '<a href="' . get_category_link($category->term_id) . '">' . $category->cat_name . '</a>';
					array_push($contents, $link_str);
				}
				// Pretty it up
				$content = implode($separator, $contents);
			}
			return $content;
		}
	}
}
if (class_exists("LimitCategoryDisplay")) {
	new LimitCategoryDisplay();
}
?>
