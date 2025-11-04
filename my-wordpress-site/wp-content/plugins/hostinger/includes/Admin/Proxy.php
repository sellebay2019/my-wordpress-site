<?php

namespace Hostinger\Admin;

use Hostinger\Mcp\EventHandlerFactory;
use Hostinger\WpHelper\Requests\Client;
use Hostinger\WpHelper\Utils;
use WP_Error;

defined( 'ABSPATH' ) || exit;

class Proxy {

    public const HOSTINGER_FREE_SUBDOMAIN_URL     = 'hostingersite.com';
    public const HOSTINGER_DEV_FREE_SUBDOMAIN_URL = 'hostingersite.dev';


    private Client $client;
    private Utils $utils;
    private string $rest_base;

    public function __construct( Client $client, Utils $utils ) {
        $this->client    = $client;
        $this->utils     = $utils;
        $this->rest_base = '/api/v1/events/trigger';
    }

    public function trigger_event( string $event, array $params = array() ): array|WP_Error {
        if ( $this->is_free_subdomain() || ! $_SERVER['H_PLATFORM'] ) {
            return new WP_Error( 'domain-not_allowed', 'This domain is not eligible for triggering Hostinger events' );
        }

        $args = array(
            'domain' => $this->remove_www( $this->utils->getHostInfo() ),
            'event'  => array(
                'name'   => $event,
                'params' => $params,
            ),
        );

        return $this->client->post( $this->rest_base, $args );
    }

    private function is_free_subdomain(): bool {
        return str_contains( $this->utils->getHostInfo(), self::HOSTINGER_FREE_SUBDOMAIN_URL ) ||
                str_contains( $this->utils->getHostInfo(), self::HOSTINGER_DEV_FREE_SUBDOMAIN_URL );
    }

    private function remove_www( string $url ): string {
        if ( str_starts_with( $url, 'www.' ) ) {
            return substr( $url, 4 );
        }

        return $url;
    }
}
