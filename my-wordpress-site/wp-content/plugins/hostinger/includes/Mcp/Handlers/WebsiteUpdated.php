<?php

namespace Hostinger\Mcp\Handlers;


use Hostinger\Admin\Jobs\ActionScheduler;

defined( 'ABSPATH' ) || exit;
class WebsiteUpdated extends EventHandler {

    const MCP_SITE_TRANSIENT          = 'hostinger_mcp_site_status';
    const MCP_SITE_TRANSIENT_LIFETIME = 1200; // 20 mins

    public function send( array $args = array() ): void {
        if ( ! $this->can_send( $args ) ) {
            return;
        }

        $status = ActionScheduler::STATUS_FAILED;

        if ( $this->send_to_proxy( $args ) ) {
            $status = ActionScheduler::STATUS_COMPLETE;
        }

        set_transient( self::MCP_SITE_TRANSIENT, $status, self::MCP_SITE_TRANSIENT_LIFETIME );
    }

    public function can_send( array $args = array() ): bool {
        return parent::can_send( $args ) && get_transient( self::MCP_SITE_TRANSIENT ) !== ActionScheduler::STATUS_COMPLETE;
    }
}
