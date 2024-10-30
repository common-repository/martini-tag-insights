<?php
global $wpdb;

// VERSION PLUGINS
define( 'MARTINI_TAG_INSIGHTS_VERSION', '0.1.3' );

// API URL
define( 'MARTINI_TAG_INSIGHTS_API_URL', 'https://app.martini.technology/api/' );

// API DEV URL
define( 'MARTINI_TAG_INSIGHTS_DEV_API_URL', 'https://dev.odysseus-it.com/api/' );

// NAME DB
define( 'MARTINI_TAG_INSIGHTS_DB_TABLE_NAME', $wpdb->prefix . 'martini_tag_insights' );

// PLUGIN DIRECTORY
define( 'MARTINI_TAG_INSIGHTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

define( 'MARTINI_TAG_INSIGHTS_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );

define(
    'MARTINI_TAG_INSIGHTS_LINK_FOR_SERVICE',
    'https://app.martini.technology/?utm_source=wp-plugin&utm_medium=referral&utm_campaign=register-website&utm_content=get-api-key-button'
);