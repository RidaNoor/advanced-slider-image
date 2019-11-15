<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AdvancedSlider_Image_Template_Loader {
	public function load_front_end(AdvancedSlider_Image_Slider $slider) {
		$slider_id = $slider->get_id();
		$show_loading_icon = $slider->get_show_loading_icon();
		$loading_icon_type = AdvancedSlider_Image_Options::get_loading_icon_type();
		$slides = $slider->get_slides();

		if ($slider->get_random()) {
			shuffle($slides);
		}

		return self::render(
			AdvancedSlider_Image_FRONT_TEMPLATES_PATH . DIRECTORY_SEPARATOR . 'slider.php',
			array(
				'slider' => $slider,
				'slider_id' => $slider_id,
				'show_loading_icon' => $show_loading_icon,
				'loading_icon_type' => $loading_icon_type,
				'slides' => $slides
			),
			AdvancedSlider_Image_FRONT_TEMPLATES_PATH . DIRECTORY_SEPARATOR . 'style'. DIRECTORY_SEPARATOR. 'style.css.php'
		);
	}

	public static function render($html_path, $params = array(), $css_path='') {
		ob_start();
		ob_implicit_flush(false);

		extract($params, EXTR_OVERWRITE);

		require $html_path;
		if ( $css_path ) {
			require $css_path;
		}

		return ob_get_clean();
	}
}