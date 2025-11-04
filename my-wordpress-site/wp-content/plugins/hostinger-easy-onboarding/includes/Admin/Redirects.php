<?php

namespace Hostinger\EasyOnboarding\Admin;

use Hostinger\EasyOnboarding\Settings;

defined( 'ABSPATH' ) || exit;

class Redirects {

    private string $platform;
    public const PLATFORM_HPANEL  = 'hpanel';
    public const BUILDER_TYPE     = 'prebuilt';
    public const HOMEPAGE_DISPLAY = 'page';

    public function __construct() {
        if ( ! Settings::get_setting( 'first_login_at' ) ) {
            Settings::update_setting( 'first_login_at', gmdate( 'Y-m-d H:i:s' ) );
        }

        if ( isset( $_GET['platform'] ) ) {
            $this->platform = sanitize_text_field( $_GET['platform'] );

            if ( $this->platform === self::PLATFORM_HPANEL ) {
                $this->login_redirect();
            }
        }
    }

    private function login_redirect(): void {
        $is_prebuilt_website = get_option( 'hostinger_builder_type', '' ) === self::BUILDER_TYPE;
        $is_woocommerce_page = in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true );
        $homepage_id         = get_option( 'show_on_front' ) === self::HOMEPAGE_DISPLAY ? get_option( 'page_on_front' ) : null;
        $is_gutenberg_page   = $homepage_id ? has_blocks( get_post( $homepage_id )->post_content ) : false;

        add_action(
            'init',
            function () use ( $is_prebuilt_website, $is_woocommerce_page, $homepage_id, $is_gutenberg_page ) {
                if ( $is_prebuilt_website && ! $is_woocommerce_page && $homepage_id && $is_gutenberg_page ) {
                    // Redirect to the Gutenberg editor for the homepage.
                    $redirect_url = get_edit_post_link( $homepage_id, '' );
                } else {
                    $redirect_url = admin_url( 'admin.php?page=hostinger' );
                }

                wp_safe_redirect( $redirect_url );
                exit;
            }
        );
    }
}
