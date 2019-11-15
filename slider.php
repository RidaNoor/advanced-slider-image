<?php

/*
Plugin Name: Adavanced Slider Image
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

final class AdvancedSlider_Image {


	private $slug = 'advanced-slider-image';

	/**
	 * AdvancedSlider_Image slider's table name.
	 *
	 * @var string
	 */

	private $slider_table_name;

	/**
	 * AdvancedSlider_Image slide's table name.
	 *
	 * @var string
	 */
	private $slide_table_name;

	/**
	 * The instance of current class.
	 *
	 * @var AdvancedSlider_Image
	 */
	private static $instance;

	/**
	 * Instance of AdvancedSlider_Image_Template_Loader.
	 *
	 * @var AdvancedSlider_Image_Template_Loader
	 */
	public $template_loader;

	/**
	 * @var AdvancedSlider_Image_Admin
	 */
	public $admin;

    /**
     * @var AdvancedSlider_Image_Tracking
     */
	public $tracking;

	/**
	 * AdvancedSlider_Image constructor.
	 */


	$image = addslashes(file_get_contents($_FILES['image']['tmp_name'])); //SQL Injection defence!
$image_name = addslashes($_FILES['image']['name']);
$sql = "INSERT INTO `product_images` (`id`, `image`, `image_name`) VALUES ('1', '{$image}', '{$image_name}')";
if (!mysql_query($sql)) { // Error handling
    echo "Something went wrong! :("; 
}

	private function __construct() {
		$this->slide_table_name  = $GLOBALS['wpdb']->prefix . 'AdvancedSlider_Image_slide';
		$this->slider_table_name = $GLOBALS['wpdb']->prefix . 'AdvancedSlider_Image_slider';

        require_once "includes/tracking/class-advanced-slider-image-tracking.php";
        $this->tracking = new AdvancedSlider_Image_Tracking();

		$this->define_constants();
		$this->includes();
		$this->init_hooks();

		do_action( 'AdvancedSlider_Image_loaded' );
	}

	/**
	 * Hook into actions and filters.
	 */
	private function init_hooks() {
		register_activation_hook( __FILE__, array( 'AdvancedSlider_Image_Install', 'init' ) );

		add_action( 'init', array( $this, 'init' ), 1, 0 );
		add_action( 'init', array( 'AdvancedSlider_Image_Install', 'init' ) );
		add_action( 'before_AdvancedSlider_Image_init', array( $this, 'before_init' ), 1, 0 );
		add_action( 'widgets_init', array($this, 'register_widgets'));
		add_action('init',array($this,'schedule_tracking'),0);
		add_filter('cron_schedules',array($this,'custom_cron_job_recurrence'));
	}

	public function before_init() {
		if (isset($_GET['page'], $_GET['task']) && 'AdvancedSlider_Image' === $_GET['page'] && 'add' === $_GET['task']) {
			ob_start();
		}
	}

	public function init() {
		do_action('before_AdvancedSlider_Image_init');

        new AdvancedSlider_Image_Deactivation_Feedback();

		$this->template_loader = new AdvancedSlider_Image_Template_Loader();

		AdvancedSlider_Image_Install::init();


		if ( $this->is_request( 'admin' ) ) {
			$this->admin = new AdvancedSlider_Image_Admin();
		} elseif ($this->is_request('frontend')) {
			new AdvancedSlider_Image_Frontend_Scripts();
		}

		do_action('after_AdvancedSlider_Image_init');
	}

	public function register_widgets(){
        register_widget('AdvancedSlider_Image_Widget');
    }

	/**
	 * Defines plugin basic constants.
	 */
	private function define_constants() {
		define('AdvancedSlider_Image_VERSION', $this->get_version());

		define('AdvancedSlider_Image_PLUGIN_URL', untrailingslashit(plugin_dir_url(__FILE__)));
		define('AdvancedSlider_Image_PLUGIN_PATH', untrailingslashit(plugin_dir_path(__FILE__)));

		define('AdvancedSlider_Image_ADMIN_IMAGES_URL', AdvancedSlider_Image_PLUGIN_URL . '/assets/images/admin');
		define('AdvancedSlider_Image_ADMIN_IMAGES_PATH', AdvancedSlider_Image_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'admin');

		define('AdvancedSlider_Image_FRONT_IMAGES_URL', AdvancedSlider_Image_PLUGIN_URL . '/assets/images/front');
		define('AdvancedSlider_Image_FRONT_IMAGES_PATH', AdvancedSlider_Image_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'front');

		define('AdvancedSlider_Image_ADMIN_TEMPLATES_URL', AdvancedSlider_Image_PLUGIN_URL . '/templates/admin');
		define('AdvancedSlider_Image_ADMIN_TEMPLATES_PATH', AdvancedSlider_Image_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin');

		define('AdvancedSlider_Image_FRONT_TEMPLATES_URL', AdvancedSlider_Image_PLUGIN_URL . '/templates/front');
		define('AdvancedSlider_Image_FRONT_TEMPLATES_PATH', AdvancedSlider_Image_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'front');

		define('AdvancedSlider_Image_STYLESHEETS_URL', AdvancedSlider_Image_PLUGIN_URL . '/assets/style');
		define('AdvancedSlider_Image_STYLESHEETS_PATH', AdvancedSlider_Image_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'style');

		define('AdvancedSlider_Image_SCRIPTS_URL', AdvancedSlider_Image_PLUGIN_URL . '/assets/js');
		define('AdvancedSlider_Image_SCRIPTS_PATH', AdvancedSlider_Image_PLUGIN_PATH . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'js');
	}

	/**
	 * Includes plugin related files.
	 */
	private function includes() {
		require_once "includes/admin/class-advanced-slider-image-html-loader.php";

		require_once "includes/interfaces/interface-advanced-slider-image-slider-interface.php";
		require_once "includes/interfaces/interface-advanced-slider-image-slide-interface.php";
		require_once "includes/interfaces/interface-advanced-slider-image-slide-image-interface.php";
		require_once "includes/interfaces/interface-advanced-slider-image-slide-video-interface.php";
		require_once "includes/interfaces/interface-advanced-slider-image-slide-post-interface.php";
		require_once "includes/interfaces/interface-advanced-slider-image-options-interface.php";

		require_once "includes/class-advanced-slider-image-slider.php";

		require_once "includes/class-advanced-slider-image-slide.php";
		require_once "includes/class-advanced-slider-image-slide-image.php";



        require_once "includes/class-advanced-slider-image-migrate.php";
		require_once "includes/class-advanced-slider-image-install.php";
		require_once "includes/class-advanced-slider-image-template-loader.php";
		require_once "includes/class-advanced-slider-image-options.php";
		require_once "includes/class-advanced-slider-image-ajax.php";

        require_once "includes/class-advanced-slider-image-helpers.php";

		if ($this->is_request('admin')) {
			require_once( ABSPATH . '/wp-admin/includes/media.php' );
			require_once( ABSPATH . '/wp-admin/includes/file.php' );
			require_once( ABSPATH . '/wp-admin/includes/image.php' );
			require_once( ABSPATH . '/wp-includes/pluggable.php' );


			require_once "includes/admin/class-advanced-slider-image-general-options.php";
			require_once "includes/admin/class-advanced-slider-image-admin.php";
			require_once "includes/admin/class-advanced-slider-image-admin-assets.php";
			require_once "includes/admin/class-advanced-slider-image-sliders.php";


		}

		require_once "includes/class-advanced-slider-image-widget.php";
		require_once "includes/class-advanced-slider-image-shortcode.php";
		require_once "includes/class-advanced-slider-image-frontend-scripts.php";

		require_once "includes/tracking/class-advanced-slider-image-deactivation-feedback.php";


	}

    public function schedule_tracking()
    {
        if ( ! wp_next_scheduled( 'AdvancedSlider_Image_opt_in_cron' ) ) {
            $this->tracking->track_data();
            wp_schedule_event( current_time( 'timestamp' ), 'advanced-slider-image-weekly', 'AdvancedSlider_Image_opt_in_cron' );
        }
	}

    public function custom_cron_job_recurrence($schedules)
    {
        $schedules['advanced-slider-image-weekly'] = array(
            'display' => __( 'Once per week', 'advanced-slider-image' ),
            'interval' => 604800
        );
        return $schedules;
	}

	/**
	 * @param $type
	 *
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return  ! is_admin() && ! defined( 'DOING_CRON' );
			default :
				return false;
		}
	}

	/**
	 * No cloning.
	 */
	private function __clone() {}

	private function __sleep() {}

	private function __wakeup()	{}

	
}

$GLOBALS['AdvancedSlider_Image'] = AdvancedSlider_Image::get_instance();

/**
 * @return AdvancedSlider_Image
 */
function AdvancedSlider_Image() {
	return $GLOBALS['AdvancedSlider_Image'];
}
