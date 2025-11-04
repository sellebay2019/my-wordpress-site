<?php
declare( strict_types=1 );

namespace Hostinger\Admin\Jobs;

use Exception;

defined( 'ABSPATH' ) || exit;

abstract class AbstractJob implements JobInterface {

    protected ActionScheduler $action_scheduler;

    public function __construct( ActionScheduler $action_scheduler ) {
        $this->action_scheduler = $action_scheduler;
    }

    public function init(): void {
        add_action( $this->get_process_item_hook(), array( $this, 'handle_process_items_action' ) );
        add_action(
            $this->get_start_hook(),
            function ( $args ) {
                $this->schedule( $args );
            }
        );
    }

    public function can_schedule( $args = array() ): bool {
        return ! $this->is_running( $args );
    }

    public function handle_process_items_action( array $args = array() ): void {
        $this->process_items( $args );
    }

    public function get_process_item_hook(): string {
        return "{$this->get_hook_base_name()}process_item";
    }

    public function get_start_hook(): string {
        return $this->get_name();
    }

    protected function is_running( ?array $args = array() ): bool {
        return $this->action_scheduler->has_scheduled_action( $this->get_process_item_hook(), array( $args ) );
    }

    protected function get_hook_base_name(): string {
        return "{$this->action_scheduler->get_group()}/jobs/{$this->get_name()}/";
    }

    abstract public function get_name(): string;
    abstract protected function process_items( array $args );
}
