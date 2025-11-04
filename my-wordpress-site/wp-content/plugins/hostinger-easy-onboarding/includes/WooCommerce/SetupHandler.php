<?php
namespace Hostinger\EasyOnboarding\WooCommerce;

use Hostinger\EasyOnboarding\Admin\Onboarding\Onboarding;
use Hostinger\EasyOnboarding\Dto\WooSetupParameters;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class SetupHandler {
    private WooSetupParameters $woo_setup_parameters;

    public function __construct( WooSetupParameters $woo_setup_parameters ) {
        $this->woo_setup_parameters = $woo_setup_parameters;
    }

    public function validate(): array {
        $fields = array(
            'store_name',
            'industry',
            'store_location',
            'business_email',
        );

        $errors = array();

        foreach ( $fields as $field ) {
            $formatted_field = str_replace( '_', ' ', $field );
            $getter          = 'get_' . $field;

            if ( ! method_exists( $this->woo_setup_parameters, $getter ) ) {
                /* translators: %s missing field slug */
                $errors[ $field ] = sprintf( __( '%s missing or empty', 'hostinger-easy-onboarding' ), $formatted_field );
                continue;
            }

            $value = $this->woo_setup_parameters->$getter();

            $field_is_valid = ! empty( $value );

            if ( ! $field_is_valid ) {
                /* translators: %s missing field slug */
                $errors[ $field ] = sprintf( __( '%s missing or empty', 'hostinger-easy-onboarding' ), $formatted_field );
            }
        }

        $locale_info = include WC()->plugin_path() . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'locale-info.php';

        $store_location = $this->woo_setup_parameters->get_formatted_store_location();

        if ( empty( $locale_info[ $store_location ] ) ) {
            $errors['store_location'] = __( 'Store location locale not found', 'hostinger-easy-onboarding' );
        }

        return $errors;
    }

    public function setup(): bool {
        $store_location = $this->woo_setup_parameters->get_formatted_store_location();
        $locale_info    = include WC()->plugin_path() . DIRECTORY_SEPARATOR . 'i18n' . DIRECTORY_SEPARATOR . 'locale-info.php';
        if ( empty( $locale_info[ $store_location ] ) ) {
            return false;
        }

        $store_locale = $locale_info[ $store_location ];

        // Default WooCommerce values.
        update_option( 'woocommerce_default_country', $this->woo_setup_parameters->get_store_location(), true );
        update_option( 'woocommerce_allowed_countries', 'all', true );
        update_option( 'woocommerce_all_except_countries', array(), true );
        update_option( 'woocommerce_specific_allowed_countries', array(), true );
        update_option( 'woocommerce_specific_ship_to_countries', array(), true );
        update_option( 'woocommerce_default_customer_address', 'base', true );
        update_option( 'woocommerce_calc_taxes', 'no', true );
        update_option( 'woocommerce_enable_coupons', 'yes', true );
        update_option( 'woocommerce_calc_discounts_sequentially', 'no', true );
        update_option( 'woocommerce_currency', $store_locale['currency_code'], true );
        update_option( 'woocommerce_currency_pos', $store_locale['currency_pos'], true );
        update_option( 'woocommerce_price_thousand_sep', $store_locale['thousand_sep'], true );
        update_option( 'woocommerce_price_decimal_sep', $store_locale['decimal_sep'], true );
        update_option( 'woocommerce_price_num_decimals', $store_locale['num_decimals'], true );
        update_option( 'woocommerce_weight_unit', $store_locale['weight_unit'], true );
        update_option( 'woocommerce_dimension_unit', $store_locale['dimension_unit'], true );

        $onboarding_profile                            = array();
        $onboarding_profile['is_store_country_set']    = true;
        $onboarding_profile['industry']                = array( $this->woo_setup_parameters->get_industry() );
        $onboarding_profile['is_agree_marketing']      = $this->woo_setup_parameters->get_is_agree_marketing();
        $onboarding_profile['store_email']             = $this->woo_setup_parameters->get_business_email();
        $onboarding_profile['completed']               = true;
        $onboarding_profile['is_plugins_page_skipped'] = true;

        update_option( 'woocommerce_onboarding_profile', $onboarding_profile, true );

        $onboarding = new Onboarding();
        $onboarding->init();

        $onboarding->complete_step( Onboarding::HOSTINGER_EASY_ONBOARDING_STORE_STEP_CATEGORY_ID, 'setup_store' );

        if ( has_action( 'litespeed_purge_all' ) ) {
            do_action( 'litespeed_purge_all' );
        }

        return true;
    }
}
