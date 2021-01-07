<?php
/**
 * @package Ezpizee
 */
/*
Plugin Name: Ezpizee Connector
Plugin URI: https://www.ezpizee.com/
Description: For accessing Ezpizee Portal right inside WordPress and integrating Ezpizee e-commerce with WordPress.
Version: 0.0.3
Author: Sothea Nim
Author URI: https://github.com/nimsothea
License: GNU General Public License version 2 or later; see LICENSE.txt
Text Domain: ezpizee
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2020-2021 Ezpizee Co., Ltd.
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

if (!defined('EZPIZEE_DS')) {define('EZPIZEE_DS', DIRECTORY_SEPARATOR);}

function ezpizee_get_wp_root()
{
    $base = dirname(__FILE__);
    if (@file_exists(dirname(dirname($base))."/wp-config.php")) {
        $path = dirname(dirname($base));
    }
    else if (@file_exists(dirname(dirname(dirname($base)))."/wp-config.php")) {
        $path = dirname(dirname(dirname($base)));
    }
    else {
        $path = '';
    }
    if (!empty($path))
    {
        $path = str_replace("\\", EZPIZEE_DS, $path);
    }
    return $path;
}

define('EZPIZEE_WP_ROOT_DIR', ezpizee_get_wp_root());
define('EZPIZEE_WP_VERSION', '0.0.1');
define('EZPIZEE_MINIMUM_WP_VERSION', '4.0');
define('EZPIZEE_PLUGIN_DIR', __DIR__);
define('EZPIZEE_PLUGIN_MAIN_FILE', __FILE__);
define('EZPIZEE_PLUGIN_ASSET_DATA', EZPIZEE_PLUGIN_DIR.EZPIZEE_DS.'asset'.EZPIZEE_DS.'data');
define('EZPIZEE_PLUGIN_ASSET_HTML', EZPIZEE_PLUGIN_DIR.EZPIZEE_DS.'asset'.EZPIZEE_DS.'html');
define('EZPIZEE_PLUGIN_ASSET_HBS', EZPIZEE_PLUGIN_DIR.EZPIZEE_DS.'asset'.EZPIZEE_DS.'hbs');
define('EZPIZEE_DELETE_LIMIT', 100000);
define('EZPIZEE_PLUGIN_URL_ROOT', plugins_url('', __FILE__));

include_once __DIR__.EZPIZEE_DS.'ezpzlib'.EZPIZEE_DS.'autoload.php';

\Ezpizee\ContextProcessor\CustomLoader::appendPackage([
    'EzpizeeWordPress' => __DIR__ . EZPIZEE_DS . 'lib' . EZPIZEE_DS . 'src'
], true);

if (isset($_GET['page']) &&
    trim(filter_input(INPUT_GET, 'page', FILTER_SANITIZE_STRING)) === \EzpizeeWordPress\EzpizeeAdmin::ADMIN_PORTAL) {
    if (strpos(filter_input(INPUT_SERVER, 'SCRIPT_FILENAME', FILTER_SANITIZE_STRING),
            \EzpizeeWordPress\EzpizeeAdmin::WP_PAGE_ADMIN) !== false) {
        if (!function_exists( 'wp_create_nonce')) {
            include_once EZPIZEE_WP_ROOT_DIR . EZPIZEE_DS . 'wp-includes' . EZPIZEE_DS . 'pluggable.php';
            include_once EZPIZEE_WP_ROOT_DIR . EZPIZEE_DS . 'wp-includes' . EZPIZEE_DS . 'user.php';
        }
        \EzpizeeWordPress\EzpizeeAdmin::displayPortalPage();
    }
}

register_activation_hook(__FILE__, '\EzpizeeWordPress\MainReactor::pluginActivation');
register_deactivation_hook(__FILE__, '\EzpizeeWordPress\MainReactor::pluginDeactivation');

add_action('widgets_init', '\EzpizeeWordPress\EzpizeeWidget::register');
add_action('init', '\EzpizeeWordPress\MainReactor::init');
add_action('rest_api_init', '\EzpizeeWordPress\EzpizeeRESTAPI::init');

if (is_admin())
{
    add_action('init', '\EzpizeeWordPress\EzpizeeAdmin::init');
    add_action('admin_menu', '\EzpizeeWordPress\EzpizeeAdmin::adminMenu');
}