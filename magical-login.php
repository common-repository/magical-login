<?php
/*
Plugin Name:     Magical Login
Version:         1.0.0
Description:     Customize Your WordPress Login Form Magically
Author:          Umbrella Plan Team
Plugin URI:      https://plugin.umbrella-plan.com/magical_login/
License:         GPL2
License URI:     https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:     eex_up_
Domain Path:     /languages

"Magical Login" is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

"Magical Login" is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with "Magical Login". If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/


// include globals
include_once(plugin_dir_path(__FILE__) . '/globals.php');

// Show/Hide ACF field group menu item
add_filter('acf/settings/show_admin', eex_up_ml_() . 'acf_show_to_admin');
function eex_up_magical_login_acf_show_to_admin()
{
    return false;
}

// register acctivation, deactivation, and uninstall hooks for this plugin
register_activation_hook(__FILE__, eex_up_ml_() . 'activation');
register_deactivation_hook(__FILE__, eex_up_ml_() . 'deactivation');
register_uninstall_hook(__FILE__, eex_up_ml_() . 'uninstall');

// do the acctivation, deactivation, and uninstall actions
function eex_up_magical_login_activation()
{
    // Create Custom Post Types if any
    if (function_exists(eex_up_ml_() . 'create_init_posts')) {
        call_user_func(eex_up_ml_() . 'create_init_posts');
    }
    // register options if any
    if (function_exists(eex_up_ml_() . '_register_setting')) {
        call_user_func(eex_up_ml_() . '_register_setting');
    }
}
function eex_up_magical_login_deactivation()
{
    // Delete Custom Post Types if any
    if (function_exists(eex_up_ml_() . 'delete_all_posts')) {
        call_user_func(eex_up_ml_() . 'delete_all_posts', call_user_func(eex_up_ml_() . 'get_all_cpts'));
    }
}
function eex_up_magical_login_uninstall()
{
    // Delete Custom Post Types if any
    if (function_exists(eex_up_ml_() . 'delete_all_posts')) {
        call_user_func(eex_up_ml_() . 'delete_all_posts', call_user_func(eex_up_ml_() . 'get_all_cpts'));
    }
    // unregister options if any
    if (function_exists(eex_up_ml_() . '_unregister_setting')) {
        call_user_func(eex_up_ml_() . '_unregister_setting');
    }
}

// add tabs and options for the plugin menu
include_once(plugin_dir_path(__FILE__) . '/admin/menu.php');

// add the admin menus and sub-menus
add_action('admin_menu', eex_up_ml_() . 'admin_menu_page_config');
function eex_up_magical_login_admin_menu_page_config()
{
    add_submenu_page('options-general.php', '', eex_up_ml_name(), 'manage_options', 'options-general.php/' . eex_up_ml_(), eex_up_ml_() . 'admin_menu_html');
}

// SCC, JS for admin menus
add_action('admin_enqueue_scripts', eex_up_ml_() .  'load_admin_files');
function eex_up_magical_login_load_admin_files()
{
    wp_enqueue_style(eex_up_ml_() . 'admin_style', plugins_url() . '/magical-login/inc/magical-login-admin-style.css', false, '1.0.1');
    wp_enqueue_script(eex_up_ml_() . 'admin_script', plugins_url() . '/magical-login/inc/magical-login-admin-script.js', false, '1.0.2', true);
}

// include custom shortcodes
add_action('init', eex_up_ml_() . 'load_front_files');
function eex_up_magical_login_load_front_files()
{
    include_once(plugin_dir_path(__FILE__) . '/shortcode.php');
}


add_action('init', eex_up_ml_() . '_wp_login');
// redirect the wp-login page

function eex_up_magical_login__wp_login()
{
    $active = get_option(eex_up_prefix() . 'extra_wp_login');
    $postIDs = eex_up_magical_login_get_post_ids_having('eex_up_login_form');
    if (is_string($active) and strlen($active) and is_array($postIDs) and !empty($postIDs)) {
        global $pagenow;
        if ('wp-login.php' == $pagenow && !is_user_logged_in()) {
            wp_redirect(get_site_url());
            exit();
        }
    }
}

// include plugin character
add_action('init', eex_up_ml_() . 'load_characters');
function eex_up_magical_login_load_characters()
{
    // Chnage WP admin defaults to introduce our plugin character.
    include_once(plugin_dir_path(__FILE__) . '/character.php');
}
