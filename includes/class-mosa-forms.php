<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://mortenmosa.com
 * @since      1.0.0
 *
 * @package    Mosa_Forms
 * @subpackage Mosa_Forms/includes
 */

use WordPlate\Acf\Location;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Mosa_Forms
 * @subpackage Mosa_Forms/includes
 * @author     Morten Sassi <dev@mortenmosa.com>
 */
class Mosa_Forms
{

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Mosa_Forms_Loader $loader Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string $plugin_name The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
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
     * @since    1.0.0
     */
    public function __construct()
    {
        if (defined('MOSA_FORMS_VERSION')) {
            $this->version = MOSA_FORMS_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'mosa-forms';

        $this->load_dependencies();
        $this->set_locale();
        $this->register_form_cpt();
        $this->define_admin_hooks();
        $this->define_public_hooks();

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Mosa_Forms_Loader. Orchestrates the hooks of the plugin.
     * - Mosa_Forms_i18n. Defines internationalization functionality.
     * - Mosa_Forms_Admin. Defines all hooks for the admin area.
     * - Mosa_Forms_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {

        /**
         * Composer deps
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php';

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-mosa-forms-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-mosa-forms-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-mosa-forms-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-mosa-forms-public.php';

        $this->loader = new Mosa_Forms_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the Mosa_Forms_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new Mosa_Forms_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

    }

    /**
     * Register CPT and fields
     */
    private function register_form_cpt()
    {
        add_action('init', function () {
            register_extended_post_type('mosa_form', [
                'supports' => ['title'],
                'show_in_rest' => true
            ], [
                'singular' => __('Form', 'mosa'),
                'plural' => __('Forms', 'mosa'),
            ]);

            /*register_extended_post_type('mosa_form_signup', [
                'supports' => ['title'],
            ], [
                'singular' => __('Signup', 'mosa'),
                'plural' => __('Signups', 'mosa'),
            ]);*/
        });

        add_action('acf/init', function () {
			$fields = require plugin_dir_path( __FILE__ ) . 'class-mosa-forms-fields.php';
			
			register_extended_field_group([
				'title' => __('Form', 'mosa'),
				'style' => 'default',
				'show_in_rest' => true,
				'id' => $this->plugin_name . 'form-selection',
				'key' => $this->plugin_name . 'form-selection',
				'fields' => $fields['form'],
				'location' => [
					Location::if('page_template', 'tpl.mosa-forms.php')
				],
			]);
			
            register_extended_field_group([
                'title' => __('Form Settings', 'mosa'),
                'style' => 'default',
                'show_in_rest' => true,
                'id' => $this->plugin_name . 'form-fields',
                'key' => $this->plugin_name . 'form-fields',
                'fields' => $fields['settings'],
                'location' => [
                    Location::if('post_type', 'mosa_form')
                ],
            ]);
        });

        add_shortcode('mosa_form', [$this, 'mosa_form_shortcode']);
    }

    /**
     * @return WP_Error
     */
    private function mosa_form_error_handler()
    {
        return new WP_Error('no_id', __('Please pass a form ID', 'mosa'));
    }

    /**
     * @param $parameters
     * @return false|string
     *
     * Create vue wrapper
     */
    public function mosa_form_shortcode($parameters)
    {
        $error_handler = $this->mosa_form_error_handler();

        if (!$parameters || !$parameters['id']) {
            echo $error_handler->get_error_message();
            return false;
        }

        $app_id = $this->get_plugin_name() . '-app-' . $parameters['id'];

        wp_enqueue_script($this->plugin_name);
		
		$postObject = get_post($parameters['id']);
		
		if ($postObject) {
			$date = new DateTime(get_the_modified_date('Y-m-d H:i:s', $parameters['id']));
			$fields = get_fields($parameters['id']);
			
			foreach ($fields['steps'] as $step) {
				if ($step['header']['image']) {
					echo '<div style="background-image: url('. $step['header']['image']['url'] .')"></div>';
				}
			}
			
			$data = [
				'modified' => $date->format(DateTime::ATOM),
				'acf' => $fields
			];
			echo '<script>var mosaFormsData'. $parameters['id'] .'='. json_encode($data) .';</script>';
		}

        return '<div id="' . $app_id . '" data-form-id="' . $parameters['id'] . '"></div>';
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {

        $plugin_admin = new Mosa_Forms_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');

    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks()
    {

        $plugin_public = new Mosa_Forms_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
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
     * @since     1.0.0
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @return    Mosa_Forms_Loader    Orchestrates the hooks of the plugin.
     * @since     1.0.0
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @return    string    The version number of the plugin.
     * @since     1.0.0
     */
    public function get_version()
    {
        return $this->version;
    }

}
