<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://martini.technology
 * @since             0.1.3
 * @package           martini_tag_insights
 *
 * @wordpress-plugin
 * Plugin Name:       Martini Tag Insights
 * Plugin URI:        https://martini.technology
 * Description:       Use AI to automatically generate post and page tags, then analyse your tags to see what subject matter drives traffic.
 * Version:           0.1.3
 * Author:            Martini Technology
 * Author URI:        https://martini.technology
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       martini-tag-insights
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
//    die;
}

/**
 * Currently plugin version.
 * Start at version 0.1.1 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
require_once plugin_dir_path( __FILE__ ) . 'constant.php';

require_once plugin_dir_path( __FILE__ ) . 'lib/aws-autoloader.php';
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-martini-tag-insights-activator.php
 */
function martini_tag_insights_activate()
{
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-martini-tag-insights-activator.php';
    Martini_Tag_Insights_Activator::activate();
}
register_activation_hook( __FILE__, 'martini_tag_insights_activate' );

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-martini-tag-insights-deactivator.php
 */
function martini_tag_insights_deactivate()
{
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-martini-tag-insights-deactivator.php';
    Martini_Tag_Insights_Deactivator::deactivate();
}
register_deactivation_hook( __FILE__, 'martini_tag_insights_deactivate' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-martini-tag-insights.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1.1
 */
function martini_tag_insights_run()
{
    $plugin = new Martini_Tag_Insights();
    $plugin->run();
}
martini_tag_insights_run();

//connection function.php
require_once plugin_dir_path( __FILE__ ) . 'includes/martini-tag-insights-functions.php';
