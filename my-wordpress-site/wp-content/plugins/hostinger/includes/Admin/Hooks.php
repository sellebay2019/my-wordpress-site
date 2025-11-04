<?php

namespace Hostinger\Admin;

use Hostinger\Helper;
use Hostinger\WpHelper\Utils;

defined( 'ABSPATH' ) || exit;

class Hooks {
    /**
     * @var Helper
     */
    private Helper $helper;

    /**
     * @var Utils
     */
    private Utils $utils;

    public function __construct( $utils ) {
        $this->helper = new Helper();
        $this->utils  = $utils ?? new Utils();
        add_action( 'admin_footer', array( $this, 'rate_plugin' ) );
        add_filter( 'wp_kses_allowed_html', array( $this, 'custom_kses_allowed_html' ), 10, 1 );
    }

    /**
     * @return void
     */
    public function rate_plugin(): void {
        $admin_path = parse_url( admin_url(), PHP_URL_PATH );

        if ( ! $this->utils->isThisPage( $admin_path . 'admin.php?page=' . Menu::MENU_SLUG ) ) {
            return;
        }

        require_once HOSTINGER_ABSPATH . 'includes/Admin/Views/Partials/RateUs.php';
    }



    public function custom_kses_allowed_html( $allowed ) {
        $allowed['svg']  = array(
            'xmlns'   => true,
            'width'   => true,
            'height'  => true,
            'viewBox' => true,
            'fill'    => true,
            'style'   => true,
            'class'   => true,
        );
        $allowed['path'] = array(
            'd'    => true,
            'fill' => true,
        );

        return $allowed;
    }
}
