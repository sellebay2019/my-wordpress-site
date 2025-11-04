<?php

namespace Hostinger\EasyOnboarding\Admin;

use Hostinger\EasyOnboarding\Admin\Onboarding\Onboarding;
use Hostinger\EasyOnboarding\Helper;

defined( 'ABSPATH' ) || exit;

class Actions {
    public const AI_STEP             = 'ai_step';
    public const STORE_TASKS         = 'store_tasks';
    public const SETUP_STORE         = 'setup_store';
    public const ADD_PRODUCT         = 'add_product';
    public const ADD_PAYMENT         = 'add_payment_method';
    public const ADD_SHIPPING        = 'add_shipping_method';
    public const DOMAIN_IS_CONNECTED = 'connect_domain';

    public const AMAZON_AFFILIATE = 'amazon_affiliate';

    public const GOOGLE_KIT          = 'google_kit';
    public const REACH               = 'hostinger_reach';
    public const ENABLE_AI_DISCOVERY = 'enable_ai_discovery';
    public const ACTIONS_LIST        = array(
        self::DOMAIN_IS_CONNECTED,
    );

    public const STORE_ACTIONS_LIST = array(
        self::SETUP_STORE,
        self::ADD_PRODUCT,
        self::ADD_PAYMENT,
        self::ADD_SHIPPING,
    );

    public static function get_category_action_lists(): array {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . '/wp-admin/includes/plugin.php';
        }

        return array(
            Onboarding::HOSTINGER_EASY_ONBOARDING_WEBSITE_STEP_CATEGORY_ID => self::get_action_list(),
            Onboarding::HOSTINGER_EASY_ONBOARDING_STORE_STEP_CATEGORY_ID   => self::get_store_action_list(),
        );
    }

    public static function get_action_list(): array {
        $list = self::ACTIONS_LIST;

        if ( ! empty( Onboarding::is_first_step_active() ) ) {
            $list[] = self::AI_STEP;
        }

        if ( \is_plugin_active( 'hostinger-affiliate-plugin/hostinger-affiliate-plugin.php' ) ) {
            $list[] = self::AMAZON_AFFILIATE;
        }

        if ( \is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            $list[] = self::STORE_TASKS;
        }

        $list[] = self::GOOGLE_KIT;

        if ( self::is_enable_ai_discovery_step_eligible() ) {
            $list[] = self::ENABLE_AI_DISCOVERY;
        }

        $list[] = self::REACH;

        return $list;
    }

    public static function get_action_list_without_reach(): array {
        $list        = self::get_action_list();
        $reach_index = array_search( self::REACH, $list, true );
        if ( $reach_index !== false ) {
            unset( $list[ $reach_index ] );
        }

        return $list;
    }

    public static function get_store_action_list(): array {
        $helper = new Helper();

        $steps = array(
            self::SETUP_STORE,
            self::ADD_PRODUCT,
            self::ADD_PAYMENT,
        );

        if ( ! $helper->is_selling_digital_products() ) {
            $steps[] = self::ADD_SHIPPING;
        }

        return $steps;
    }

    public static function is_enable_ai_discovery_step_eligible(): bool {
        $free_domains      = '/hostingersite\.com|hostinger\.dev/';
        $is_active         = is_plugin_active( 'hostinger/hostinger.php' );
        $is_hostinger_user = ! empty( $_SERVER['H_PLATFORM'] );
        $is_free_domain    = preg_match( $free_domains, get_site_url() );

        return $is_active && $is_hostinger_user && ! $is_free_domain;
    }
}
