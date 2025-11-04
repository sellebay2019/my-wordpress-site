<?php
namespace Hostinger\EasyOnboarding\Rest;

use Hostinger\EasyOnboarding\Admin\Onboarding\PluginManager;
use Hostinger\EasyOnboarding\Dto\WooSetupParameters;
use Hostinger\EasyOnboarding\WooCommerce\SetupHandler;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

/**
 * Class for handling WooCommerce related routes
 */
class WooRoutes {
    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response
     */
    public function get_plugins( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
        $parameters = $request->get_params();

        $locale              = ! empty( $parameters['locale'] ) ? sanitize_text_field( $parameters['locale'] ) : '';
        $available_languages = get_available_languages();

        if ( ! empty( $locale ) && in_array( $locale, $available_languages, true ) ) {
            switch_to_locale( $locale );
        }

        $parameters = $request->get_params();

        $type = ! empty( $parameters['type'] ) ? $this->filter_allowed_types( $parameters['type'] ) : '';

        $errors = array();

        if ( empty( $type ) ) {
            /* translators: %s field name that is missing */
            $errors['type'] = sprintf( __( '%s missing or empty', 'hostinger-easy-onboarding' ), 'type' );
        }

        $locale = get_option( 'woocommerce_default_country', false );

        if ( empty( $locale ) ) {
            $errors['locale'] = __( 'Shop locale is empty, please setup store first', 'hostinger-easy-onboarding' );
        }

        if ( ! empty( $errors ) ) {
            return new \WP_Error(
                'data_invalid',
                __( 'Sorry, there are validation errors.', 'hostinger-easy-onboarding' ),
                array(
                    'status' => \WP_Http::BAD_REQUEST,
                    'errors' => $errors,
                )
            );
        }

        $plugin_manager = new PluginManager();

        $data = array(
            'plugins' => $plugin_manager->get_plugins_by_criteria( $type, $locale ),
            'locale'  => get_option( 'woocommerce_default_country', '' ),
        );

        $response = new \WP_REST_Response( array( 'data' => $data ) );

        $response->set_headers( array( 'Cache-Control' => 'no-cache' ) );

        $response->set_status( \WP_Http::OK );

        return $response;
    }

    /**
     * @param \WP_REST_Request $request
     *
     * @return \WP_Error|\WP_REST_Response
     */
    public function woo_setup( \WP_REST_Request $request ) {
        $parameters = $request->get_params();

        foreach ( $parameters as $key => $value ) {
            $parameters[ $key ] = sanitize_text_field( $value );
        }

        $woo_parameters = WooSetupParameters::from_array( $parameters );
        $setup_handler  = new SetupHandler( $woo_parameters );
        $errors         = $setup_handler->validate();

        if ( ! empty( $errors ) ) {
            return new \WP_Error(
                'data_invalid',
                __( 'Sorry, there are validation errors.', 'hostinger-easy-onboarding' ),
                array(
                    'status' => \WP_Http::BAD_REQUEST,
                    'errors' => $errors,
                )
            );
        }

        $setup_handler->setup();

        $response = new \WP_REST_Response( array() );
        $response->set_headers( array( 'Cache-Control' => 'no-cache' ) );
        $response->set_status( \WP_Http::OK );

        return $response;
    }

    /**
     * @param string $type
     *
     * @return void
     */
    private function filter_allowed_types( string $type ) {
        $allowed_types = array( 'shipping', 'payment' );

        return in_array( $type, $allowed_types, true ) ? $type : '';
    }
}
