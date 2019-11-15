<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class AdvancedSlider_Image_Install
{

    /**
     * If plugin tables are created will be true, and default rows will be inserted, tables exist nothing will happen.
     *
     * @var bool
     */
    public static function init()
    {
        global $wpdb;

        if (AdvancedSlider_Image()->get_version() !== get_option('AdvancedSlider_Image_version')) {

            $AdvancedSlider_Image_slider_exists = $wpdb->get_row("SELECT * FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "' AND table_name = '" . $wpdb->prefix . "AdvancedSlider_Image_slider' LIMIT 1;", ARRAY_A);
            $AdvancedSlider_Image_slide_exists = $wpdb->get_row("SELECT * FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "' AND table_name = '" . $wpdb->prefix . "AdvancedSlider_Image_slide' LIMIT 1;", ARRAY_A);

            $new_tables_does_not_exist = empty($AdvancedSlider_Image_slider_exists) || empty($AdvancedSlider_Image_slide_exists);

            if ($new_tables_does_not_exist) {
                self::install();
            }

            update_option('AdvancedSlider_Image_version', AdvancedSlider_Image()->get_version());
        }

        if (!self::isset_table_column($wpdb->prefix . "AdvancedSlider_Image_slider", "itemscount")) {
            $wpdb->query("ALTER TABLE `" . $wpdb->prefix . "AdvancedSlider_Image_slider` ADD `itemscount` INT(2) NOT NULL DEFAULT 5 , ADD `view` enum('none','carousel1') NOT NULL DEFAULT 'none' AFTER `itemscount`");
        }

        if (!self::isset_table_column($wpdb->prefix . "AdvancedSlider_Image_slider", "lightbox")) {
            $wpdb->query("ALTER TABLE `" . $wpdb->prefix . "AdvancedSlider_Image_slider` ADD `lightbox` int(1) UNSIGNED DEFAULT 0, ADD `slide_effect` enum('effect_1','effect_2','effect_3','effect_4','effect_5','effect_6','effect_7','effect_8','effect_9','effect_10') NOT NULL DEFAULT 'effect_1', ADD `open_close_effect` enum('none','unfold','unfold_r','blowup','blowup_r','roadrunner','roadrunner_r','runner','runner_r','rotate','rotate_r') NOT NULL DEFAULT 'none', ADD `arrows_style` enum('arrows_1','arrows_2','arrows_3','arrows_4','arrows_5','arrows_6') NOT NULL DEFAULT 'arrows_1' AFTER `random`");
        }


        if (!self::isset_table_column($wpdb->prefix . "AdvancedSlider_Image_slider", "thumbmargin")) {
            $wpdb->query("ALTER TABLE `" . $wpdb->prefix . "AdvancedSlider_Image_slider` CHANGE `view` `view` ENUM('none','carousel1','thumb_view') NOT NULL DEFAULT 'none'");
            $wpdb->query("ALTER TABLE `" . $wpdb->prefix . "AdvancedSlider_Image_slider` ADD `controls` int(1) UNSIGNED DEFAULT 1, ADD `fullscreen` int(1) UNSIGNED DEFAULT 1, ADD `vertical` int(1) UNSIGNED DEFAULT 1, ADD `thumbposition` int(1) UNSIGNED DEFAULT 0, ADD `thumbcontrols` int(1) UNSIGNED DEFAULT 0, ADD `dragdrop` int(1) UNSIGNED DEFAULT 0, ADD `swipe` int(1) UNSIGNED DEFAULT 1, ADD `thumbdragdrop` int(1) UNSIGNED DEFAULT 0, ADD `thumbswipe` int(1) UNSIGNED DEFAULT 0, ADD `titleonoff` int(1) UNSIGNED DEFAULT 1, ADD `desconoff` int(1) UNSIGNED DEFAULT 1,  ADD `titlesymbollimit` int(3) UNSIGNED DEFAULT '20',  ADD `descsymbollimit` int(3) UNSIGNED DEFAULT '70', ADD `pager` int(1) UNSIGNED DEFAULT 1, ADD `mode` enum('slide','fade') NOT NULL DEFAULT 'slide', ADD `vthumbwidth` int(3) UNSIGNED NOT NULL DEFAULT '100', ADD `hthumbheight` int(3) UNSIGNED NOT NULL DEFAULT '80', ADD `thumbitem` int(3) UNSIGNED NOT NULL DEFAULT '10', ADD `thumbmargin` int(2) UNSIGNED NOT NULL DEFAULT '5' AFTER `view`");
        }
    }

    /**
     * Check if column exists in specific table
     *
     * @param string $table_name
     * @param string $column_name
     * @return bool
     */

    private static function isset_table_column($table_name, $column_name)
    {
        global $wpdb;
        $columns = $wpdb->get_results("SHOW COLUMNS FROM  " . $table_name, ARRAY_A);
        foreach ($columns as $column) {
            if ($column['Field'] == $column_name) {
                return true;
            }
        }

        return false;
    }

    private static function install()
    {
        self::create_tables();

        global $wpdb;

        $old_tables_exists = $wpdb->get_var('SHOW TABLES LIKE "' . $wpdb->prefix . 'advanced_itslider_sliders"') && $wpdb->get_var('SHOW TABLES LIKE "' . $wpdb->prefix . 'advanced_itslider_images"');

        if ($old_tables_exists) {
            AdvancedSlider_Image_Migrate::migrate();

            $wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'advanced_itslider_sliders RENAME ' . $wpdb->prefix . 'advanced_itslider_sliders_backup');
            $wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'advanced_itslider_images RENAME ' . $wpdb->prefix . 'advanced_itslider_images_backup');
        } else {
            try {
                self::insert_default_rows();
            } catch (Exception $e) {

            }
        }

        $old_options_table_exists = $wpdb->get_var('SHOW TABLES LIKE "' . $wpdb->prefix . 'advanced_itslider_params"');

        if ($old_options_table_exists) {
            AdvancedSlider_Image_Migrate::migrate_options();

            $wpdb->query('ALTER TABLE ' . $wpdb->prefix . 'advanced_itslider_params RENAME ' . $wpdb->prefix . 'advanced_itslider_params_backup');
        } else {
            self::set_default_options();
        }
    }

    /**
     * Create tables.
     */
    private static function create_tables()
    {
        global $wpdb;

        $collate = '';

        if ($wpdb->has_cap('collation')) {
            $collate = $wpdb->get_charset_collate();
        }

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "AdvancedSlider_Image_slider(
				id int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				name varchar(128) NOT NULL DEFAULT 'My New Slider',
				width int(4) UNSIGNED NOT NULL DEFAULT '600',
				height int(4) UNSIGNED NOT NULL DEFAULT '375',
				effect enum('none','cube_h','cube_v','fade','slice_h','slice_v','slide_h','slide_v','scale_out','scale_in','block_scale','kaleidoscope','fan','blind_h','blind_v','random') NOT NULL DEFAULT 'none',
				pause_time int(5) UNSIGNED NOT NULL DEFAULT '4000',
				change_speed int(5) UNSIGNED NOT NULL DEFAULT '1000',
				position enum('left','right','center') NOT NULL DEFAULT 'center',
				show_loading_icon int(1) UNSIGNED DEFAULT '0',
				navigate_by enum('dot','thumbnail','none') NOT NULL DEFAULT 'none',
				pause_on_hover int(1) UNSIGNED NOT NULL DEFAULT '1',
				video_autoplay int(1) UNSIGNED DEFAULT '0',
				random int(1) UNSIGNED DEFAULT '0',
				lightbox int(1) UNSIGNED DEFAULT '0',
				slide_effect enum('effect_1','effect_2','effect_3','effect_4','effect_5','effect_6','effect_7','effect_8','effect_9','effect_10') NOT NULL DEFAULT 'effect_1',
				open_close_effect enum('none','unfold','unfold_r','blowup','blowup_r','roadrunner','roadrunner_r','runner','runner_r','rotate','rotate_r') NOT NULL DEFAULT 'none',
				arrows_style enum('arrows_1','arrows_2','arrows_3','arrows_4','arrows_5','arrows_6') NOT NULL DEFAULT 'arrows_1',
				itemscount int(2) UNSIGNED NOT NULL DEFAULT '5',
				view enum('none','carousel1', 'thumb_view') NOT NULL DEFAULT 'none',		  
                controls int(1) UNSIGNED DEFAULT 1,
                fullscreen int(1) UNSIGNED DEFAULT 1,
                vertical int(1) UNSIGNED DEFAULT 0, 
                thumbposition int(1) UNSIGNED DEFAULT 0,
                thumbcontrols int(1) UNSIGNED DEFAULT 0,
                dragdrop int(1) UNSIGNED DEFAULT 0, 
                swipe int(1) UNSIGNED DEFAULT 1, 
                thumbdragdrop int(1) UNSIGNED DEFAULT 0, 
                thumbswipe int(1) UNSIGNED DEFAULT 0, 
                titleonoff int(1) UNSIGNED DEFAULT 1, 
                desconoff int(1) UNSIGNED DEFAULT 1, 
                titlesymbollimit int(3) UNSIGNED NOT NULL DEFAULT '20',
                descsymbollimit int(3) UNSIGNED NOT NULL DEFAULT '70', 
                pager int(1) UNSIGNED DEFAULT 1,
                mode enum('slide','fade') NOT NULL DEFAULT 'slide',
                vthumbwidth int(3) UNSIGNED NOT NULL DEFAULT '100',
                hthumbheight int(3) UNSIGNED NOT NULL DEFAULT '80',
                thumbitem int(3) UNSIGNED NOT NULL DEFAULT '10',
                thumbmargin int(2) UNSIGNED NOT NULL DEFAULT '5',
				PRIMARY KEY (id)
			) {$collate}"
        );

        $wpdb->query(
            "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "AdvancedSlider_Image_slide (
				`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				`slider_id` int(11) unsigned NOT NULL,
				`title` varchar(512) DEFAULT NULL,
				`description` varchar(2048) DEFAULT NULL,
				`url` varchar(2048) DEFAULT NULL,
				`attachment_id` bigint(20) unsigned DEFAULT NULL,
				`in_new_tab` int(1) unsigned NOT NULL DEFAULT '1',
				`type` enum('image','video','post') NOT NULL,
				`order` int(5) unsigned NOT NULL,
				`post_term_id` bigint(20) unsigned DEFAULT NULL,
				`post_show_title` int(1) unsigned DEFAULT NULL,
				`post_show_description` int(1) unsigned DEFAULT NULL,
				`post_go_to_post` int(1) unsigned DEFAULT NULL,
				`post_max_post_count` int(4) unsigned DEFAULT NULL,
				`video_quality` int(5) unsigned DEFAULT NULL,
				`video_volume` int(3) unsigned DEFAULT NULL,
				`video_show_controls` int(1) unsigned DEFAULT NULL,
				`video_show_info` int(1) unsigned DEFAULT NULL,
				`video_control_color` int(8) unsigned DEFAULT NULL,
				`draft` int(1) unsigned DEFAULT NULL,
				
				PRIMARY KEY (`id`)
			) {$collate}"
        );
    }

    private static function set_default_options()
    {
        if (AdvancedSlider_Image_Options::get_crop_image() === false) {
            AdvancedSlider_Image_Options::set_crop_image('stretch', __('Slider crop image', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_title_color() === false) {
            AdvancedSlider_Image_Options::set_title_color('000000', __('Slider title color', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_title_font_size() === false) {
            AdvancedSlider_Image_Options::set_title_font_size(13, __('Slider title font size', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_description_color() === false) {
            AdvancedSlider_Image_Options::set_description_color('ffffff', __('Slider description color', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_description_font_size() === false) {
            AdvancedSlider_Image_Options::set_description_font_size(13, __('Slider description font size', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_title_position() === false) {
            AdvancedSlider_Image_Options::set_title_position(33, __('Slider title position', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_description_position() === false) {
            AdvancedSlider_Image_Options::set_description_position(31, __('Slider description position', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_title_border_size() === false) {
            AdvancedSlider_Image_Options::set_title_border_size(0, __('Slider Title border size', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_title_border_color() === false) {
            AdvancedSlider_Image_Options::set_title_border_color('ffffff', __('Slider title border color', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_title_border_radius() === false) {
            AdvancedSlider_Image_Options::set_title_border_radius(4, __('Slider title border radius', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_description_border_size() === false) {
            AdvancedSlider_Image_Options::set_description_border_size(0, __('Slider description border size', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_description_border_color() === false) {
            AdvancedSlider_Image_Options::set_description_border_color('ffffff', __('Slider description border color', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_description_border_radius() === false) {
            AdvancedSlider_Image_Options::set_description_border_radius(0, __('Slider description border radius', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_slideshow_border_size() === false) {
            AdvancedSlider_Image_Options::set_slideshow_border_size(0, __('Slider border size', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_slideshow_border_color() === false) {
            AdvancedSlider_Image_Options::set_slideshow_border_color('ffffff', __('Slider border color', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_slideshow_border_radius() === false) {
            AdvancedSlider_Image_Options::set_slideshow_border_radius(0, __('Slider border radius', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_navigation_type() === false) {
            AdvancedSlider_Image_Options::set_navigation_type(1, __('Slider navigation type', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_navigation_position() === false) {
            AdvancedSlider_Image_Options::set_navigation_position('top', __('Slider navigation position', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_title_background_color() === false) {
            AdvancedSlider_Image_Options::set_title_background_color('ffffff', __('Slider title background color', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_description_background_color() === false) {
            AdvancedSlider_Image_Options::set_description_background_color('000000', __('Slider description background color', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_slider_background_color() === false) {
            AdvancedSlider_Image_Options::set_slider_background_color('ffffff', __('Slider slider background color', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_slider_background_color_transparency() === false) {
            AdvancedSlider_Image_Options::set_slider_background_color_transparency(100, __('Slider slider background color transparency', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_active_dot_color() === false) {
            AdvancedSlider_Image_Options::set_active_dot_color('ffffff', __('Slider active dot color', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_dots_color() === false) {
            AdvancedSlider_Image_Options::set_dots_color('000000', __('Slider dots color', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_loading_icon_type() === false) {
            AdvancedSlider_Image_Options::set_loading_icon_type(1, __('Slider Loading Image', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_description_width() === false) {
            AdvancedSlider_Image_Options::set_description_width(70, __('Slider description width', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_description_height() === false) {
            AdvancedSlider_Image_Options::set_description_height(50, __('Slider description height', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_description_background_transparency() === false) {
            AdvancedSlider_Image_Options::set_description_background_transparency(70, __('Slider description background transparency', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_description_text_align() === false) {
            AdvancedSlider_Image_Options::set_description_text_align('justify', __('Description text-align', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_title_width() === false) {
            AdvancedSlider_Image_Options::set_title_width(30, __('Slider title width', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_title_height() === false) {
            AdvancedSlider_Image_Options::set_title_height(50, __('Slider title height', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_title_background_transparency() === false) {
            AdvancedSlider_Image_Options::set_title_background_transparency(70, __('Slider title background transparency', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_title_text_align() === false) {
            AdvancedSlider_Image_Options::set_title_text_align('right', __('Title text-align', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_title_has_margin() === false) {
            AdvancedSlider_Image_Options::set_title_has_margin(1, __('Title has margin', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_description_has_margin() === false) {
            AdvancedSlider_Image_Options::set_description_has_margin(1, __('Description has margin', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_show_arrows() === false) {
            AdvancedSlider_Image_Options::set_show_arrows(1, __('Slider show left right arrows', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_thumb_count_slides() === false) {
            AdvancedSlider_Image_Options::set_thumb_count_slides(3, __('Count of Thumbs Slides', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_thumb_background_color() === false) {
            AdvancedSlider_Image_Options::set_thumb_background_color('ffffff', __('Thumbnail Background Color', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_thumb_passive_color() === false) {
            AdvancedSlider_Image_Options::set_thumb_passive_color('ffffff', __('Passive Thumbnail Color', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_thumb_passive_color_transparency() === false) {
            AdvancedSlider_Image_Options::set_thumb_passive_color_transparency(50, __('Passive Thumbnail Color Transparency', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_thumb_height() === false) {
            AdvancedSlider_Image_Options::set_thumb_height(100, __('Slider Thumb Height', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_share_buttons() === false) {
            AdvancedSlider_Image_Options::set_share_buttons(1, __('Share buttons', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_share_buttons_facebook() === false) {
            AdvancedSlider_Image_Options::set_share_buttons_facebook(1, __('Facebook', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_share_buttons_twitter() === false) {
            AdvancedSlider_Image_Options::set_share_buttons_twitter(1, __('Twitter', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_share_buttons_gp() === false) {
            AdvancedSlider_Image_Options::set_share_buttons_gp(1, __('Google Plus', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_share_buttons_pinterest() === false) {
            AdvancedSlider_Image_Options::set_share_buttons_pinterest(1, __('Pinterest', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_share_buttons_linkedin() === false) {
            AdvancedSlider_Image_Options::set_share_buttons_linkedin(1, __('Linkedin', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_share_buttons_tumblr() === false) {
            AdvancedSlider_Image_Options::set_share_buttons_tumblr(1, __('Tumblr', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_share_buttons_style() === false) {
            AdvancedSlider_Image_Options::set_share_buttons_style('stretch', __('Share buttons style', 'advanced-slider-image'));
        }

        if (AdvancedSlider_Image_Options::get_share_buttons_hover_style() === false) {
            AdvancedSlider_Image_Options::set_share_buttons_hover_style('stretch', __('Share buttons hover style', 'advanced-slider-image'));
        }
    }

    private static function insert_default_rows()
    {
        /**
         * @var AdvancedSlider_Image_Slide_Image $slide1
         * @var AdvancedSlider_Image_Slide_Image $slide2
         * @var AdvancedSlider_Image_Slide_Image $slide3
         */

        $wp_upload_dir = wp_upload_dir();

        if (!is_dir($wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'advanced-slider-image')) {
            mkdir($wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'advanced-slider-image');
        }

        copy(AdvancedSlider_Image_FRONT_IMAGES_PATH . DIRECTORY_SEPARATOR . 'slides' . DIRECTORY_SEPARATOR . 'slide1.jpg', $wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'advanced-slider-image' . DIRECTORY_SEPARATOR . 'slide1.jpg');
        copy(AdvancedSlider_Image_FRONT_IMAGES_PATH . DIRECTORY_SEPARATOR . 'slides' . DIRECTORY_SEPARATOR . 'slide2.jpg', $wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'advanced-slider-image' . DIRECTORY_SEPARATOR . 'slide2.jpg');
        copy(AdvancedSlider_Image_FRONT_IMAGES_PATH . DIRECTORY_SEPARATOR . 'slides' . DIRECTORY_SEPARATOR . 'slide3.jpg', $wp_upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'advanced-slider-image' . DIRECTORY_SEPARATOR . 'slide3.jpg');

        $attachment_id_1 = wp_insert_attachment(array('post_title' => __('advanced-IT First Slide.', 'advanced-slider-image'), 'post_content' => '', 'post_status' => 'publish', 'post_mime_type' => 'jpg'), $wp_upload_dir['basedir'] . '/advanced-slider-image/slide1.jpg');
        $attachment_id_2 = wp_insert_attachment(array('post_title' => __('advanced-IT Second Slide.', 'advanced-slider-image'), 'post_content' => '', 'post_status' => 'publish', 'post_mime_type' => 'jpg'), $wp_upload_dir['basedir'] . '/advanced-slider-image/slide2.jpg');
        $attachment_id_3 = wp_insert_attachment(array('post_title' => __('advanced-IT Third Slide.', 'advanced-slider-image'), 'post_content' => '', 'post_status' => 'publish', 'post_mime_type' => 'jpg'), $wp_upload_dir['basedir'] . '/advanced-slider-image/slide3.jpg');

        $slide1 = AdvancedSlider_Image_Slide::get_slide('image');
        $slide1
            ->set_title('')
            ->set_description('')
            ->set_url('https://advanced-it.com/')
            ->set_attachment_id($attachment_id_1)
            ->set_order(0);

        $slide2 = AdvancedSlider_Image_Slide::get_slide('image');
        $slide2
            ->set_title(__('Simple Usage', 'advanced-slider-image'))
            ->set_description('')
            ->set_url('https://advanced-it.com/')
            ->set_attachment_id($attachment_id_2)
            ->set_order(1);

        $slide3 = AdvancedSlider_Image_Slide::get_slide('image');
        $slide3
            ->set_title(__('advanced-IT Slider', 'advanced-slider-image'))
            ->set_description(__('The slider allows having unlimited amount of images with their titles and descriptions. The slider uses autogenerated shortcodes making it easier for the users to add it to the custom location.', 'advanced-slider-image'))
            ->set_url('https://advanced-it.com/')
            ->set_attachment_id($attachment_id_3)
            ->set_order(2);

        $slider = new AdvancedSlider_Image_Slider();

        $slider
            ->set_name(__('My First Slider', 'advanced-slider-image'))
            ->set_width(600)
            ->set_height(375)
            ->set_itemscount(5)
            ->set_effect('random')
            ->set_pause_time(4000)
            ->set_change_speed(1000)
            ->set_position('left')
            ->set_show_loading_icon(true)
            ->set_navigate_by('dot')
            ->set_pause_on_hover(true)
            ->set_random(false)
            ->set_lightbox(false)
            ->set_slide_effect('effect_1')
            ->set_open_close_effect('none')
            ->set_arrows_style('arrows_1')
            ->set_controls(true)
            ->set_fullscreen(true)
            ->set_vertical(true)
            ->set_thumbposition(true)
            ->set_thumbcontrols(true)
            ->set_dragdrop(true)
            ->set_swipe(true)
            ->set_thumbdragdrop(true)
            ->set_thumbswipe(true)
            ->set_titleonoff(true)
            ->set_desconoff(true)
            ->set_titlesymbollimit(20)
            ->set_descsymbollimit(70)
            ->set_pager(true)
            ->set_mode('slide')
            ->set_vthumbwidth(100)
            ->set_hthumbheight(80)
            ->set_thumbitem(10)
            ->set_thumbmargin(5);

        $slider->add_slide($slide1);
        $slider->add_slide($slide2);
        $slider->add_slide($slide3);

        if (!$slider->save()) {
            throw new Exception(__('Problem occurred while installation. Please connect to our support team.', 'advanced-slider-image'));
        }
    }
}
