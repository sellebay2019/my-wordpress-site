<?php

namespace Hostinger\EasyOnboarding\Rest;

use Hostinger\WpHelper\Utils as Helper;
use Hostinger\WpHelper\Requests\Client;
use Hostinger\EasyOnboarding\Config;
use WP_REST_Request;
use WP_REST_Response;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class OnboardingRoutes {
    private $helper;
    private $proxy_client;

    /**
     * @param Client $client
     * @param Helper $helper
     */
    public function __construct( Client $client, Helper $helper ) {
        $this->helper       = $helper;
        $this->proxy_client = new Client(
            HOSTINGER_EASY_ONBOARDING_WP_PROXY_URI,
            array(
                Config::TOKEN_HEADER  => $this->helper->getApiToken(),
                Config::DOMAIN_HEADER => $this->helper->getHostInfo(),
            )
        );
    }

    public function get_suggested_plugins( WP_REST_Request $request ): WP_REST_Response {
        $software_id = $this->get_software_id_from_request( $request );

        if ( empty( $software_id ) ) {
            return $this->create_error_response( 'Software ID is required' );
        }

        $endpoint = "/api/v1/installations/{$software_id}/plugins/suggested";

        return $this->make_api_request( $endpoint );
    }

    public function get_available_plugins( WP_REST_Request $request ): WP_REST_Response {
        $software_id = $this->get_software_id_from_request( $request );

        if ( empty( $software_id ) ) {
            return $this->create_error_response( 'Software ID is required' );
        }

        $parameters = $request->get_params();
        $search     = ! empty( $parameters['search'] ) ? sanitize_text_field( $parameters['search'] ) : '';

        if ( empty( $search ) ) {
            return $this->create_error_response( 'Search term is required' );
        }

        $endpoint = "/api/v1/installations/{$software_id}/plugins";
        $params   = array(
            'search' => $search,
        );

        return $this->make_api_request( $endpoint, $params );
    }

    public function install_plugins( WP_REST_Request $request ): WP_REST_Response {
        $software_id = $this->get_software_id_from_request( $request, true );

        if ( empty( $software_id ) ) {
            return $this->create_error_response( 'Software ID is required' );
        }

        $parameters = $request->get_json_params();
        $plugins    = ! empty( $parameters['plugins'] ) && is_array( $parameters['plugins'] ) ? $parameters['plugins'] : array();

        if ( empty( $plugins ) ) {
            return $this->create_error_response( 'Plugins array is required' );
        }

        $endpoint = "/api/v1/installations/{$software_id}/plugins/install";
        $params   = array(
            'plugins' => $plugins,
        );

        return $this->make_api_post_request( $endpoint, $params );
    }

    public function get_suggested_themes( WP_REST_Request $request ): WP_REST_Response {
        $software_id = $this->get_software_id_from_request( $request );

        if ( empty( $software_id ) ) {
            return $this->create_error_response( 'Software ID is required' );
        }

        $endpoint = "/api/v1/installations/{$software_id}/themes";
        return $this->make_api_request( $endpoint );
    }

    public function get_astra_templates( WP_REST_Request $request ): WP_REST_Response {
        $software_id = $this->get_software_id_from_request( $request );

        if ( empty( $software_id ) ) {
            return $this->create_error_response( 'Software ID is required' );
        }

        $endpoint = "/api/v1/installations/{$software_id}/astra/templates";

        return $this->make_api_request( $endpoint );
    }

    public function get_website_data( WP_REST_Request $request ): WP_REST_Response {
        $domain    = $this->get_domain_from_request( $request );
        $site_path = parse_url( get_site_url(), PHP_URL_PATH );
        $directory = trim( $site_path ? $site_path : '', '/' );

        $params = array(
            'domain' => $domain,
        );

        $params['directory'] = $directory;

        return $this->make_api_request( '/api/v1/installations', $params );
    }

    public function get_astra_template_import_status( WP_REST_Request $request ): WP_REST_Response {
        $software_id = $this->get_software_id_from_request( $request );

        if ( empty( $software_id ) ) {
            return $this->create_error_response( 'Software ID is required' );
        }

        $endpoint = "/api/v1/installations/{$software_id}/astra/templates/import/status";

        return $this->make_api_request( $endpoint );
    }

    public function import_astra_template( WP_REST_Request $request ): WP_REST_Response {
        $software_id = $this->get_software_id_from_request( $request, true );

        if ( empty( $software_id ) ) {
            return $this->create_error_response( 'Software ID is required' );
        }

        $parameters  = $request->get_json_params();
        $template_id = ! empty( $parameters['template_id'] ) ? absint( $parameters['template_id'] ) : 0;

        $endpoint = "/api/v1/installations/{$software_id}/astra/templates/import";
        $params   = array(
            'template_id' => $template_id,
        );

        return $this->make_api_post_request( $endpoint, $params );
    }

    private function get_domain_from_request( WP_REST_Request $request ): string {
        $parameters = $request->get_params();
        $domain     = ! empty( $parameters['domain'] ) ? sanitize_text_field( $parameters['domain'] ) : '';

        if ( empty( $domain ) ) {
            $siteurl = get_option( 'siteurl', $this->helper->getHostInfo() );
            $domain  = parse_url( $siteurl, PHP_URL_HOST );
        }

        return $domain;
    }

    private function get_software_id_from_request( WP_REST_Request $request, bool $use_json_params = false ): string {
        $parameters  = $use_json_params ? $request->get_json_params() : $request->get_params();
        $software_id = ! empty( $parameters['software_id'] ) ? sanitize_text_field( $parameters['software_id'] ) : '';

        return $software_id;
    }

    private function create_error_response( string $message ): WP_REST_Response {
        $response = new WP_REST_Response();
        $response->set_status( \WP_Http::BAD_REQUEST );
        $response->set_data(
            array(
                'status'  => 'error',
                'message' => $message,
            )
        );

        return $response;
    }

    private function make_api_request( string $endpoint, array $params = array(), string $error_prefix = 'Hostinger Easy Onboarding' ): WP_REST_Response {
        $data     = array(
            'status' => 'error',
            'data'   => array(),
        );
        $response = new WP_REST_Response();

        try {
            $response->set_status( \WP_Http::OK );

            $request = $this->proxy_client->get( $endpoint, $params );

            if ( ! empty( $request['body'] ) ) {
                $json = json_decode( $request['body'], true );

                if ( ! empty( $json['data'] ) ) {
                    $data = array(
                        'status' => 'success',
                        'data'   => $json['data'],
                    );
                }
            }
        } catch ( \Exception $exception ) {
            $response->set_status( \WP_Http::BAD_REQUEST );

            $this->helper->errorLog( "$error_prefix: Error sending request: " . $exception->getMessage() );

            $data = array(
                'message' => $exception->getMessage(),
            );
        }

        $response->set_data( $data );
        $response->set_headers( array( 'Cache-Control' => 'no-cache' ) );

        return $response;
    }

    private function make_api_post_request( string $endpoint, array $params = array(), string $error_prefix = 'Hostinger Easy Onboarding' ): WP_REST_Response {
        $data     = array(
            'status' => 'error',
            'data'   => array(),
        );
        $response = new WP_REST_Response();

        try {
            $response->set_status( \WP_Http::OK );

            $request = $this->proxy_client->post( $endpoint, $params );

            if ( ! empty( $request['body'] ) ) {
                $json = json_decode( $request['body'], true );

                if ( ! empty( $json['data'] ) ) {
                    $data = array(
                        'status' => 'success',
                        'data'   => $json['data'],
                    );
                }
            }
        } catch ( \Exception $exception ) {
            $response->set_status( \WP_Http::BAD_REQUEST );

            $this->helper->errorLog( "$error_prefix: Error sending request: " . $exception->getMessage() );

            $data = array(
                'message' => $exception->getMessage(),
            );
        }

        $response->set_data( $data );
        $response->set_headers( array( 'Cache-Control' => 'no-cache' ) );

        return $response;
    }
}
