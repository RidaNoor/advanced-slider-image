<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class AdvancedSlider_Image_Helpers {

	public static function has_background( &$has_background ) {

		if ($has_background) {
			$has_background = false;

			return true;
		}

		$has_background = true;

		return false;
	}

	
	public static function echo_on_match( $value, $true_value, $echo_text, $strict_match = false ) {
		$condition = $strict_match ? $value === $true_value : $value == $true_value;

		if ($condition) {
			echo $echo_text;
		}
	}

	
	public static function youtube_or_vimeo( $url ) {
		if (preg_match('/^(https?\:\/\/)?(www\.youtube\.com|youtu\.?be)\/.+$/', $url)) {
			return 'youtube';
		} elseif (preg_match('/https:\/\/vimeo.com\/\d{8,12}(?=\b|\/)/', $url)) {
			return 'vimeo';
		} else {
			return false;
		}
	}
}