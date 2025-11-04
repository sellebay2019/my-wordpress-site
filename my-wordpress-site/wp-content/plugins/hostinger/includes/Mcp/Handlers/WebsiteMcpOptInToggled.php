<?php

namespace Hostinger\Mcp\Handlers;

defined( 'ABSPATH' ) || exit;
class WebsiteMcpOptInToggled extends EventHandler {

    public function send( array $args = array() ): void {
        $this->send_to_proxy( $args );
    }
}
