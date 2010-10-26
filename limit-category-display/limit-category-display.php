<?php
/*
Plugin Name: Limit Category Display
Plugin URI: http://github.com/BryanH/Limit-Category-Display
Description: Force posts by a user to include one or more specified categories (custom taxonomies) and/or prevent that user from assigning some categories (custom taxonomies) to her posts.
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
		var $meta_sm = 'limit_cat_display';
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
			add_filter('the_content', array (
				& $limit_cats,
				'limit_category_filter'
			));
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
		* (equivalent to $foo = $bar || $default)
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
			$max_cats = '';
			if (isset ($_POST['max_cats'])) {
				check_admin_referer('limit_cats-admin_settings');
				$max_cats = $this->update_from_post('max_cats');
?>
<div class="updated"><p><strong><?php _e('settings saved', 'menu-trss' ); ?></strong></p></div>
<?php

			} else {
				$max_cats = get_option('max_cats', esc_attr($defaults['max_category_display']));
			}
			include ("{$this->plugin->dir}/options.php");
		}

		/*
		 * Limit the post's category display
		 */
		function limit_category_filter($content) {
			if (!is_single(get_the_ID())) {
				$opt_meta_words = get_option(META_KEYWORDS);
				if (true == empty ($opt_meta_words)) { // exception
					Throw new Exception("RSS Truncation values are not set in the 'Settings' menu on the admin screen.");
				}
				$is_syndication_post = (get_post_meta(get_the_ID(), $opt_meta_words, true)) ? true : false;
				if ($is_syndication_post) {
					$opt_max_words = get_option(META_LENGTH);
					if (true == empty ($opt_max_words)) { // exception
						Throw new Exception("RSS Truncation values are not set in the 'Settings' menu on the admin screen.");
					}
					$content = x_words_from_post($opt_max_words, $content, true) . ' <a href="' . get_page_link(get_the_ID()) . '">More <span>&raquo;</span></a>';
				} else { /* leave it as is */
				}
			}
			return $content;
		}
	}
}
if (class_exists("LimitCategoryDisplay")) {
	new LimitCategoryDisplay();
}
?>
