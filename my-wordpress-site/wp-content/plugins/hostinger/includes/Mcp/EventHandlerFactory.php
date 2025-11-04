<?php

namespace Hostinger\Mcp;

use Hostinger\Admin\Proxy;
use Hostinger\Mcp\Handlers\WebsiteMcpOptInToggled;
use Hostinger\Mcp\Handlers\WebsitePageUpdated;
use Hostinger\Mcp\Handlers\WebsiteUpdated;

defined( 'ABSPATH' ) || exit;

class EventHandlerFactory {

    public const MCP_EVENT_UPDATED       = 'wordpress.website.updated';
    public const MCP_EVENT_PAGE_UPDATED  = 'wordpress.website.page_updated';
    public const MCP_EVENT_OPTIN_TOGGLED = 'wordpress.website.mcp.opt_in_toggled';

    private array $handlers;
    private Proxy $proxy;

    public function __construct( Proxy $proxy ) {
        $this->proxy    = $proxy;
        $this->handlers = array(
            self::MCP_EVENT_UPDATED       => WebsiteUpdated::class,
            self::MCP_EVENT_PAGE_UPDATED  => WebsitePageUpdated::class,
            self::MCP_EVENT_OPTIN_TOGGLED => WebsiteMcpOptInToggled::class,
        );
    }

    public function get_handler( string $event ) {
        $handler = $this->handlers[ $event ] ?? false;
        if ( ! $handler ) {
            throw new \WP_Exception( 'Invalid event' );
        }

        return new $handler( $this->proxy );
    }
}
