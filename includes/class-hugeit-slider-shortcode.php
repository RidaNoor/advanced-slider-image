<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class AdvancedSlider_Image_Shortcode {

	/**
	 * AdvancedSlider_Image_Shortcode constructor.
	 */
	public function __construct() {
		add_shortcode( 'advanced_it_slider', array( $this, 'run_shortcode' ) );
		add_action( 'admin_footer', array( $this, 'inline_popup_content' ) );
		add_action('media_buttons_context', array($this, 'add_editor_media_button'));
	}

	public function run_shortcode($attrs) {
		$attrs = shortcode_atts(array('id' => 'no slider'), $attrs);

		$id = (int)$attrs['id'] === absint($attrs['id']) ? absint($attrs['id']) : false;

		if ( ! $id ) {
			return false;
		}

		do_action('AdvancedSlider_Image_before_shortcode', $id);

		return $this->init_frontend($id);
	}

	/**
	 * Add editor media button
	 *
	 * @param $context
	 *
	 * @return string
	 */
	public function add_editor_media_button( $context ) {
		$img = AdvancedSlider_Image_ADMIN_IMAGES_URL . '/post.button.png';

		$container_id = 'AdvancedSlider_Image_media_popup';

		$title = __( 'Select advanced IT Slider to insert into post', 'advanced-slider-image' );

		$button_text = __( 'Add Slider', 'advanced-slider-image' );

		$context .= '<a class="button thickbox" title="' . $title . '" href="#TB_inline?width=700&height=500&inlineId=' . $container_id . '">
		<span class="wp-media-buttons-icon" style="background: url(' . $img . '); background-repeat: no-repeat; background-position: left bottom;"></span>' . $button_text . '</a>';

		return $context;
	}

	public function inline_popup_content() {
		$sliders = AdvancedSlider_Image_Slider::get_all_sliders();
		$slider_data = array();

		foreach ( $sliders as $key => $slider ) {
			$id = $slider->get_id();

			$slider_data[$id] = new stdClass();
			$slider_data[$id]->name = $slider->get_name();
		}

		echo AdvancedSlider_Image_Template_Loader::render(
			AdvancedSlider_Image_ADMIN_TEMPLATES_PATH . DIRECTORY_SEPARATOR . 'add-slider-popup.php',
			array('sliders' => $slider_data)
		);
	}

	private function init_frontend($id) {
		$slider = new AdvancedSlider_Image_Slider($id);

		return AdvancedSlider_Image()->template_loader->load_front_end($slider);
	}
}

new AdvancedSlider_Image_Shortcode();