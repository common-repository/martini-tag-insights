<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://example.com
 * @since      0.1.1
 *
 * @package    martini_tag_insights
 * @subpackage martini_tag_insights/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.1.1
 * @package    martini_tag_insights
 * @subpackage martini_tag_insights/includes
 * @author     Your Name <email@example.com>
 */
class Martini_Tag_Insights
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    0.1.1
     * @access   protected
     * @var      martini_tag_insights_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    0.1.1
     * @access   protected
     * @var      string $martini_tag_insights The string used to uniquely identify this plugin.
     */
    protected $martini_tag_insights;

    /**
     * The current version of the plugin.
     *
     * @since    0.1.1
     * @access   protected
     * @var      string $version The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    0.1.1
     */
    public function __construct()
    {
        $this->martini_tag_insights = 'martini-tag-insights';
        $this->version = '0.1.3';

        if ( defined( 'MARTINI_TAG_INSIGHTS_VERSION' ) ) {
            $this->version = MARTINI_TAG_INSIGHTS_VERSION;
        }

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - martini_tag_insights_Loader. Orchestrates the hooks of the plugin.
     * - martini_tag_insights_i18n. Defines internationalization functionality.
     * - martini_tag_insights_Admin. Defines all hooks for the admin area.
     * - martini_tag_insights_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    0.1.1
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-martini-tag-insights-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-martini-tag-insights-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-martini-tag-insights-admin.php';

        $this->loader = new martini_tag_insights_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the martini_tag_insights_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    0.1.1
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new martini_tag_insights_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    0.1.1
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new martini_tag_insights_Admin( $this->get_martini_tag_insights(), $this->get_version() );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
    }


    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    0.1.1
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @return    string    The name of the plugin.
     * @since     0.1.1
     */
    public function get_martini_tag_insights()
    {
        return $this->martini_tag_insights;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    martini_tag_insights_Loader    Orchestrates the hooks of the plugin.
     * @since     0.1.1
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     0.1.1
     */
    public function get_version()
    {
        return $this->version;
    }
}
