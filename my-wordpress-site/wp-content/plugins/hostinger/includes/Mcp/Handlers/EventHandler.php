<?php

namespace Hostinger\Mcp\Handlers;

use Hostinger\Admin\Proxy;
use Hostinger\Mcp\EventHandlerFactory;

defined( 'ABSPATH' ) || exit;

abstract class EventHandler {

    public Proxy $proxy;

    public function __construct( Proxy $proxy ) {
        $this->proxy = $proxy;
    }

    protected function send_to_proxy( array $args = array() ): bool {
        if ( ! $this->can_send( $args ) ) {
            $this->debug_mcp( 'Event failed: User is not opted in' . ' -- ' . print_r( $args, true ) );
            return false;
        }

        $event   = $args['event'];
        $request = $this->proxy->trigger_event( $event, $args );
        if ( is_wp_error( $request ) ) {
            $this->debug_mcp( 'Event failed: ' . $event . $request->get_error_message() . ' -- ' . print_r( $args, true ) );
            return false;
        }

        $response_code = wp_remote_retrieve_response_code( $request );
        if ( $response_code < 300 ) {
            $this->debug_mcp( 'Event sent: ' . $event . ' -- ' . print_r( $args, true ) );
            return true;
        }

        $this->debug_mcp( 'Event failed: ' . $event . ' --' . $response_code . ' -- ' . print_r( $args, true ) );
        return false;
    }

    public function can_send( array $args = array() ): bool {
        return isset( $args['event'] ) && ( $this->is_optin_event( $args['event'] ) || $this->is_opted_in() );
    }

    private function is_opted_in(): bool {
        $settings = get_option( HOSTINGER_PLUGIN_SETTINGS_OPTION, array() );
        return $settings['optin_mcp'] ?? false;
    }

    private function is_optin_event( $event ): bool {
        return $event === EventHandlerFactory::MCP_EVENT_OPTIN_TOGGLED;
    }

    private function debug_mcp( string $msg ): void {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( 'Hostinger Tools MCP: ' . $msg . PHP_EOL, 3, WP_CONTENT_DIR . '/mcp.log' );
        }
    }

    abstract public function send( array $args = array() ): void;
}
