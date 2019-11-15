<?php

class AdvancedSlider_Image_Frontend_Scripts {

	public function __construct() {
		add_action('AdvancedSlider_Image_before_shortcode', array($this, 'enqueue_scripts'));
		add_action('AdvancedSlider_Image_before_shortcode', array($this, 'enqueue_styles'));
		add_action('AdvancedSlider_Image_before_shortcode', array($this, 'localize_script'));
		add_action('AdvancedSlider_Image_before_shortcode', array(get_class(), 'localize_single_slider_params'));
	}

	public function enqueue_scripts() {
		wp_enqueue_script('AdvancedSlider_Image_frontend_froogaloop', AdvancedSlider_Image_SCRIPTS_URL . '/froogaloop2.min.js', array('jquery'), false, true);
		wp_enqueue_script('AdvancedSlider_Image_frontend_main', AdvancedSlider_Image_SCRIPTS_URL . '/main.js', array('jquery'), false, true);
		wp_enqueue_script('AdvancedSlider_Image_frontend_lightbox', AdvancedSlider_Image_SCRIPTS_URL . '/slightbox.js', array('jquery'), false, true);
	}

	public function localize_script() {
		$slider_options = array(
			'crop_image' => AdvancedSlider_Image_Options::get_crop_image(),
			'slider_background_color' => AdvancedSlider_Image_Options::get_slider_background_color(),
			'slideshow_border_size' => AdvancedSlider_Image_Options::get_slideshow_border_size(),
			'slideshow_border_color' => AdvancedSlider_Image_Options::get_slideshow_border_color(),
			'slideshow_border_radius' => AdvancedSlider_Image_Options::get_slideshow_border_radius(),
			'loading_icon_type' => AdvancedSlider_Image_Options::get_loading_icon_type(),
			'title_width' => AdvancedSlider_Image_Options::get_title_width(),
			'title_has_margin' => AdvancedSlider_Image_Options::get_title_has_margin(),
			'title_font_size' => AdvancedSlider_Image_Options::get_title_font_size(),
			'title_color' => AdvancedSlider_Image_Options::get_title_color(),
			'title_text_align' => AdvancedSlider_Image_Options::get_title_text_align(),
			'title_background_transparency' => AdvancedSlider_Image_Options::get_title_background_transparency(),
			'title_background_color' => AdvancedSlider_Image_Options::get_title_background_color(),
			'title_border_size' => AdvancedSlider_Image_Options::get_title_border_size(),
			'title_border_color' => AdvancedSlider_Image_Options::get_title_border_color(),
			'title_border_radius' => AdvancedSlider_Image_Options::get_title_border_radius(),
			'title_position' => AdvancedSlider_Image_Options::get_title_position(),
			'description_width' => AdvancedSlider_Image_Options::get_description_width(),
			'description_has_margin' => AdvancedSlider_Image_Options::get_description_has_margin(),
			'description_font_size' => AdvancedSlider_Image_Options::get_description_font_size(),
			'description_color' => AdvancedSlider_Image_Options::get_description_color(),
			'description_text_align' => AdvancedSlider_Image_Options::get_description_text_align(),
			'description_background_transparency' => AdvancedSlider_Image_Options::get_description_background_transparency(),
			'description_background_color' => AdvancedSlider_Image_Options::get_description_background_color(),
			'description_border_size' => AdvancedSlider_Image_Options::get_description_border_size(),
			'description_border_color' => AdvancedSlider_Image_Options::get_description_border_color(),
			'description_border_radius' => AdvancedSlider_Image_Options::get_description_border_radius(),
			'description_position' => AdvancedSlider_Image_Options::get_description_position(),
			'navigation_position' => AdvancedSlider_Image_Options::get_navigation_position(),
			'dots_color' => AdvancedSlider_Image_Options::get_dots_color(),
			'active_dot_color' => AdvancedSlider_Image_Options::get_active_dot_color(),
			'show_arrows' => AdvancedSlider_Image_Options::get_show_arrows(),
			'thumb_count_slides' => AdvancedSlider_Image_Options::get_thumb_count_slides(),
			'thumb_height' => AdvancedSlider_Image_Options::get_thumb_height(),
			'thumb_background_color' => AdvancedSlider_Image_Options::get_thumb_background_color(),
			'thumb_passive_color' => AdvancedSlider_Image_Options::get_thumb_passive_color(),
			'thumb_passive_color_transparency' => AdvancedSlider_Image_Options::get_thumb_passive_color_transparency(),
			'navigation_type' => AdvancedSlider_Image_Options::get_navigation_type(),
			'share_buttons' => AdvancedSlider_Image_Options::get_share_buttons(),
			'share_buttons_facebook' => AdvancedSlider_Image_Options::get_share_buttons_facebook(),
			'share_buttons_twitter' => AdvancedSlider_Image_Options::get_share_buttons_twitter(),
			'share_buttons_gp' => AdvancedSlider_Image_Options::get_share_buttons_gp(),
			'share_buttons_pinterest' => AdvancedSlider_Image_Options::get_share_buttons_pinterest(),
			'share_buttons_linkedin' => AdvancedSlider_Image_Options::get_share_buttons_linkedin(),
			'share_buttons_tumblr' => AdvancedSlider_Image_Options::get_share_buttons_tumblr(),
			'share_buttons_style' => AdvancedSlider_Image_Options::get_share_buttons_style(),
			'share_buttons_hover_style' => AdvancedSlider_Image_Options::get_share_buttons_hover_style(),
		);
		wp_localize_script('AdvancedSlider_Image_frontend_main', 'advanced-slider-imageUrl', AdvancedSlider_Image_FRONT_IMAGES_URL);
		wp_localize_script('AdvancedSlider_Image_frontend_main', 'advanced-slider-imageObj', $slider_options);
	}

	public static function localize_single_slider_params( $id ) {
		try {
			$slider = new AdvancedSlider_Image_Slider($id);
		} catch (Exception $e) {

		}

		if (isset($slider) && $slider instanceof AdvancedSlider_Image_Slider) {
			wp_localize_script('AdvancedSlider_Image_frontend_main', 'singleSlider_' . $slider->get_id(), array(
				'width' => $slider->get_width(),
				'height' => $slider->get_height(),
				'itemscount' => $slider->get_itemscount(),
				'view' => $slider->get_view(),
				'pause_on_hover' => $slider->get_pause_on_hover(),
				'navigate_by' => $slider->get_navigate_by(),
				'pause_time' => $slider->get_pause_time(),
				'change_speed' => $slider->get_change_speed(),
				'effect' => $slider->get_effect(),
				'slide_effect' => $slider->get_slide_effect(),
				'open_close_effect' => $slider->get_open_close_effect(),
				'arrows_style' => $slider->get_arrows_style(),
                'controls' => $slider->get_controls(),
                'fullscreen' => $slider->get_fullscreen(),
                'vertical' => $slider->get_vertical(),
                'thumbposition' => $slider->get_thumbposition(),
                'thumbcontrols' => $slider->get_thumbcontrols(),
                'dragdrop' => $slider->get_dragdrop(),
                'swipe' => $slider->get_swipe(),
                'thumbdragdrop' => $slider->get_thumbdragdrop(),
                'thumbswipe' => $slider->get_thumbswipe(),
                'titleonoff' => $slider->get_titleonoff(),
                'desconoff' => $slider->get_desconoff(),
                'titlesymbollimit' => $slider->get_titlesymbollimit(),
                'descsymbollimit' => $slider->get_descsymbollimit(),
                'pager' => $slider->get_pager(),
                'mode' => $slider->get_mode(),
                'vthumbwidth' => $slider->get_vthumbwidth(),
                'hthumbheight' => $slider->get_hthumbheight(),
                'thumbitem' => $slider->get_thumbitem(),
                'thumbmargin' => $slider->get_thumbmargin()
			));
		}
	}

	public function enqueue_styles() {
		wp_enqueue_style('AdvancedSlider_Image_frontend_font_awesome', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
	}
}
