<?php
namespace WPUF\UserDirectory\Admin;

/**
 * Settings class
 */
class Settings {
    /**
     * Constructor for the settings class
     */
    public function __construct() {
        add_filter( 'wpuf_settings_sections', [ $this, 'plugin_sections' ] );
        add_filter( 'wpuf_settings_fields', [ $this, 'plugin_options' ] );
    }

    /**
     * Admin settings section
     *
     * @param array $sections
     *
     * @return array
     */
    public function plugin_sections( $sections ) {
        $sections[] = array(
            'id'    => 'user_directory',
            'title' => __( 'User Directory', 'wpuf-pro' ),
            'icon'  => 'dashicons-list-view',
        );
        return $sections;
    }

    /**
     * Settings options
     *
     * @param array $settings
     *
     * @return array
     */
    public function plugin_options( $settings ) {
        $sizes = [
            '32'  => '32 x 32',
            '48'  => '48 x 48',
            '80'  => '80 x 80',
            '128' => '128 x 128',
            '160' => '160 x 160',
            '192' => '192 x 192',
            '256' => '256 x 256',
        ];

        $settings['user_directory'] = [
            [
                'name'    => 'pro_img_size',
                'label'   => __( 'Profile Gallery Image Size ', 'wpuf-userlisting' ),
                'desc'    => __( 'Set the image size of picture gallery in frontend', 'wpuf-userlisting' ),
                'type'    => 'select',
                'options' => wpuf_get_image_sizes(),
            ],
            [
                'name'    => 'avatar_size',
                'label'   => __( 'Avatar Size ', 'wpuf-userlisting' ),
                'desc'    => __( 'Set the image size of profile picture in frontend', 'wpuf-userlisting' ),
                'type'    => 'select',
                'options' => $sizes,
            ],
            [
                'name'    => 'profile_header_template',
                'label'   => __( 'Profile Header Template', 'wpuf-pro' ),
                'type'    => 'radio',
                'default' => 'layout',
                'options' => [
                    'layout'  => '<img class="profile-header" src="' . WPUF_UD_ASSET_URI . '/images/profile-header-template-1.jpg' . '" />',
                    'layout1' => '<img class="profile-header" src="' . WPUF_UD_ASSET_URI . '/images/profile-header-template-2.jpg' . '" />',
                    'layout2' => '<img class="profile-header" src="' . WPUF_UD_ASSET_URI . '/images/profile-header-template-3.jpg' . '" />',
                ],
            ],
            [
                'name'    => 'user_listing_template',
                'label'   => __( 'User Listing Template', 'wpuf-pro' ),
                'type'    => 'radio',
                'default' => 'list',
                'options' => [
                    'list'  => '<img class="user-listing" src="' . WPUF_UD_ASSET_URI . '/images/user-listing-template-1.jpg' . '" />',
                    'list1' => '<img class="user-listing" src="' . WPUF_UD_ASSET_URI . '/images/user-listing-template-2.jpg' . '" />',
                    'list2' => '<img class="user-listing" src="' . WPUF_UD_ASSET_URI . '/images/user-listing-template-3.jpg' . '" />',
                    'list3' => '<img class="user-listing" src="' . WPUF_UD_ASSET_URI . '/images/user-listing-template-4.jpg' . '" />',
                    'list4' => '<img class="user-listing" src="' . WPUF_UD_ASSET_URI . '/images/user-listing-template-5.jpg' . '" />',
                    'list5' => '<img class="user-listing" src="' . WPUF_UD_ASSET_URI . '/images/user-listing-template-6.jpg' . '" />',
                ],
            ],
        ];

        return $settings;
    }
}

