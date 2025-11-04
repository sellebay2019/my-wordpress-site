<?php

namespace Hostinger\Admin\Jobs;
use Hostinger\Mcp\EventHandlerFactory;
use Hostinger\Mcp\Handlers\EventHandler;
use PHPUnit\Exception;

class NotifyMcpJob extends AbstractJob implements JobInterface {

    public const JOB_NAME = 'notify_mcp';

    private EventHandlerFactory $event_handler_factory;

    public function __construct( ActionScheduler $action_scheduler, EventHandlerFactory $event_handler_factory ) {
        $this->event_handler_factory = $event_handler_factory;
        parent::__construct( $action_scheduler );
    }

    public function get_name(): string {
        return self::JOB_NAME;
    }

    public function event_handler( string $event ): EventHandler {
        return $this->event_handler_factory->get_handler( $event );
    }

    public function process_items( array $args = array() ): void {
        $handler = $this->event_handler( $args['event'] );
        $handler->send( $args );
    }

    public function schedule( array $args = array() ): void {
        if ( $this->can_schedule( $args ) ) {
            $this->action_scheduler->schedule_immediate( $this->get_process_item_hook(), array( $args ) );
        }
    }

    public function can_schedule( $args = array() ): bool {
        if ( ! parent::can_schedule( $args ) ) {
            return false;
        }

        try {
            $handler = $this->event_handler( $args['event'] );
            return $handler->can_send( $args );
        } catch ( Exception $e ) {
            return false;
        }
    }
}
