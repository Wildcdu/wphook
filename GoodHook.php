<?php
/*
Plugin Name: Good Hook
Plugin URI: http://clubwp.ru
Description: Лучшые хуки для оптимизации работы WordPress
Version: 1.0
Author: Garri
Author URI: http://clubwp.ru
*/

/*  Copyright 2016  Garri  (email : info {at} clubwp.ru)

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
//Define the version of the plugin. used to check for updates and other
define('GOODHOOK_VERSION', '1.0');

/**
 * Define the basic name, shortname, path, currency code , .....
 */

define('GOODHOOK_NAME', 'goodhook');
if (!defined('GOODHOOK_PATH')) {
    define('GOODHOOK_PATH', dirname(__FILE__));
}
define('GOODHOOK_URL', plugins_url($path = '/' . GOODHOOK_NAME));

/**
 * Add/Load translation
 */
add_action('plugins_loaded', 'GOODHOOK_localization');
function GOODHOOK_localization() {
    load_plugin_textdomain(GOODHOOK_NAME, false, dirname( plugin_basename( __FILE__ ) ) . '/lang/');
}
require_once GOODHOOK_PATH . '/gh-option.php';

