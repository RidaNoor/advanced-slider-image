<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AdvancedSlider_Image_Ajax {

	/**
	 * AdvancedSlider_Image_Ajax constructor.
	 */
	public function __construct() {
		add_action('wp_ajax_AdvancedSlider_Image_get_slide_html', array($this, 'get_slide_html'));
		add_action('wp_ajax_AdvancedSlider_Image_delete_slider', array($this, 'delete_slider'));
		add_action('wp_ajax_AdvancedSlider_Image_duplicate_slider', array($this, 'duplicate_slider'));
		add_action('wp_ajax_AdvancedSlider_Image_save_slider', array($this, 'save_slider'));
		add_action('wp_ajax_AdvancedSlider_Image_get_add_video_popup', array($this, 'get_add_video_popup'));
		add_action('wp_ajax_AdvancedSlider_Image_get_post_data_by_category', array($this, 'get_posts_by_category'));
	}

	public function delete_slider() {
		$id = $_POST['id'];
		$nonce = $_POST['nonce'];

		if ( wp_verify_nonce( $nonce, 'delete_slider_' . $id ) ) {

			$success = AdvancedSlider_Image_Slider::delete($id);
			$response['success'] = $success;

			if ($success) {
				$response['message'] = __('Slider has been deleted.', 'advanced-slider-image');
			}

			echo json_encode($response);
			wp_die();
		}

		echo json_encode(array(
			'success' => false,
			'message' => __('Invalid nonce.', 'advanced-slider-image')
		));
		wp_die();
	}


	public function get_slide_html() {
		$slider_id = $_POST['slider_id'];
		$type = $_POST['type'];
		$result = array();

		switch ($type) {
			case 'image' :
				$attachments = isset($_POST['attachments']) ? $_POST['attachments'] : array();

				foreach ( $attachments as $index => $attachment ) {
					$slide = AdvancedSlider_Image_Slide::get_slide('image');
					$slide
						->set_slider_id($slider_id)
						->set_attachment_id($attachment['id'])
						->set_order($index)
						->set_is_draft(1);

					$slide_id = $slide->save();

					$result[$index]['id'] = $slide_id;
					$result[$index]['html'] = AdvancedSlider_Image_Html_Loader::get_slide_html($slide, array('attachment' => $attachment));
					$result[$index]['success'] = $result[$index]['html'] !== false;
				}

				echo json_encode($result);
				die;
			default:
				echo json_encode(array('success' => 0));
				die;
		}
	}

	public function save_slider() {
		$slider_id = $_POST['slider_id'];
		$nonce = $_POST['nonce'];

		if (!wp_verify_nonce($nonce, 'save_slider_' . $slider_id)) {
			die(__('Security check failure.', 'advanced-slider-image'));
		}

		$slider = $_POST['slider'];
		$slides = !empty($_POST['slides']) ? $_POST['slides'] : array();

		do_action('AdvancedSlider_Image_save_slider', $slider_id, $slider, $slides);

		$result = NULL;

		if (isset($GLOBALS['AdvancedSlider_Image_save_result'])) {
			$result = $GLOBALS['AdvancedSlider_Image_save_result'];
			unset($GLOBALS['AdvancedSlider_Image_save_result']);
		}

		if ($result !== NULL) {
			echo json_encode($result);
			wp_die();
		}

		echo json_encode(array('success' => 0));
		wp_die();
	}

	public function get_add_video_popup() {
		echo json_encode(array(
			'html' => AdvancedSlider_Image_Html_Loader::get_add_video_popup(),
			'success' => 1,
			'context' => array('a' => 'b')
		));

		wp_die();
	}

	public function get_posts_by_category() {
		$id = absint($_GET['id']);
		$posts = get_posts(array('category' => $id));
		$response = array();

		foreach ( $posts as $key => $post ) {
			$response[$key] = AdvancedSlider_Image_Html_Loader::get_post_slide_popup_row(
				$post->ID,
				get_the_post_thumbnail_url($post->ID),
				$post->post_title,
				$post->post_excerpt,
				get_the_permalink($post->ID)
			);
		}

		echo json_encode($response);
		wp_die();
	}
}

new AdvancedSlider_Image_Ajax();