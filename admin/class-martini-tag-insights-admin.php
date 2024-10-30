<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      0.1.1
 *
 * @package    martini_tag_insights
 * @subpackage martini_tag_insights/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    martini_tag_insights
 * @subpackage martini_tag_insights/admin
 * @author     Your Name <email@example.com>
 */
class martini_tag_insights_Admin
{
    /**
     * The ID of this plugin.
     *
     * @since    0.1.1
     * @access   private
     * @var      string $martini_tag_insights The ID of this plugin.
     */
    private $martini_tag_insights;

    /**
     * The version of this plugin.
     *
     * @since    0.1.1
     * @access   private
     * @var      string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $martini_tag_insights The name of this plugin.
     * @param string $version The version of this plugin.
     * @since    0.1.1
     */
    public function __construct( $martini_tag_insights, $version )
    {
        $this->martini_tag_insights = $martini_tag_insights;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    0.1.1
     */
    public function enqueue_styles()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in martini_tag_insights_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The martini_tag_insights_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style(
            $this->martini_tag_insights,
            plugin_dir_url( __FILE__ ) . 'css/martini-tag-insights-admin.css',
            [],
            $this->version,
            'all'
        );
        wp_enqueue_style(
            'daterangepicker-style',
            plugin_dir_url( __FILE__ ) . 'css/daterangepicker.css',
            [],
            $this->version
        );
        wp_enqueue_style(
            'toaster-style',
            plugin_dir_url( __FILE__ ) . 'css/toaster.css',
            [],
            $this->version
        );
        wp_enqueue_style(
            'loading-bar',
            plugin_dir_url( __FILE__ ) . 'css/loading-bar.min.css',
            [],
            $this->version
        );

        if ( isset( $_GET['page'] ) && $_GET['page'] === 'martini-insights-menu' ) {
            wp_enqueue_style(
                'datatables-style',
                plugin_dir_url( __FILE__ ) . 'css/datatables.min.css',
                [],
                $this->version
            );
        }

        if ( $this->check_plugin() ) {
            wp_enqueue_style(
                'feedback-style-martini-tag-insights',
                plugin_dir_url( __FILE__ ) . 'css/feedback.css',
                [],
                MARTINI_TAG_INSIGHTS_VERSION
            );
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    0.1.1
     */
    public function enqueue_scripts()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in martini_tag_insights_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The martini_tag_insights_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        if ( !wp_script_is( 'moment', 'enqueued' ) ) {
            wp_enqueue_script( 'moment' );
        }

        wp_enqueue_script(
            'daterangepicker-script',
            plugin_dir_url( __FILE__ ) . 'js/daterangepicker.js',
            [],
            null,
            true
        );

        wp_enqueue_script(
            'toaster-script',
            plugin_dir_url( __FILE__ ) . 'js/toaster.js',
            [],
            null,
            true
        );

        wp_enqueue_script(
            'd3',
            plugin_dir_url( __FILE__ ) . 'js/d3.js',
            [],
            null,
            true
        );

        wp_enqueue_script(
            'cloud',
            plugin_dir_url( __FILE__ ) . 'js/cloud.min.js',
            [ 'jquery', 'd3' ],
            null,
            true
        );

        wp_enqueue_script(
            'loading-bar',
            plugin_dir_url( __FILE__ ) . 'js/loading-bar.min.js',
            [ 'jquery', 'd3' ],
            null,
            true
        );

        wp_enqueue_script(
            $this->martini_tag_insights,
            plugin_dir_url( __FILE__ ) . 'js/martini-tag-insights-admin.js',
            [ 'jquery', 'cloud', 'loading-bar' ],
            $this->version,
            true
        );

        wp_enqueue_script(
            'table-script',
            plugin_dir_url( __FILE__ ) . 'js/martini-tag-insights-table.js',
            [ 'jquery', 'cloud', 'loading-bar' ],
            $this->version,
            true
        );

        if ( isset( $_GET['page'] ) && $_GET['page'] === 'martini-insights-menu' ) {
            wp_enqueue_script(
                'datatables-script',
                plugin_dir_url( __FILE__ ) . 'js/datatables.min.js',
                [ 'jquery' ],
                null,
                true
            );
        }

        if ( $this->check_plugin() ) {
            wp_enqueue_script(
                'feedback-js-martini-tag-insights',
                plugin_dir_url( __FILE__ ) . 'js/martini-tag-insights-feedback.js',
                [ 'jquery' ],
                MARTINI_TAG_INSIGHTS_VERSION,
                true
            );
        }
    }

    public function check_plugin() {
        $screen = get_current_screen();
        return (empty( $screen ) || !in_array( $screen->id, [ 'plugins', 'plugins-network' ], true ) ) ? false : true;
    }
}
