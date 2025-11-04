<?php

namespace Hostinger\Mcp\Handlers;

defined( 'ABSPATH' ) || exit;

class WebsitePageUpdated extends EventHandler {

    const ALLOWED_POST_TYPES = array( 'post', 'product', 'page' );

    public function send( array $args = array() ): void {
        if ( ! $this->can_send( $args ) ) {
            return;
        }

        $post        = get_post( $args['post_id'] );
        $args['url'] = get_permalink( $post );
        $this->send_to_proxy( $args );
    }

    public function can_send( array $args = array() ): bool {
        $post_id = $args['post_id'] ?? false;

        if ( ! $post_id ) {
            return false;
        }

        $post = get_post( $post_id );
        if ( ! $post ) {
            return false;
        }

        return parent::can_send( $args ) && in_array( $post->post_type, self::ALLOWED_POST_TYPES, true );
    }
}
