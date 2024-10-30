<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://example.com
 * @since      0.1.1
 *
 * @package    martini_tag_insights
 * @subpackage martini_tag_insights/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      0.1.1
 * @package    martini_tag_insights
 * @subpackage martini_tag_insights/includes
 * @author     Your Name <email@example.com>
 */
class Martini_Tag_Insights_i18n
{
    /**
     * Load the plugin text domain for translation.
     *
     * @since    0.1.1
     */
    public function load_plugin_textdomain()
    {
        load_plugin_textdomain(
            'martini-tag-insights',
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );
    }
}
