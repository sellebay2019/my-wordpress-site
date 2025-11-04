<?php

namespace Hostinger\Admin\Jobs;

defined( 'ABSPATH' ) || exit;

class ActionScheduler {

    public const STATUS_PENDING  = 'pending';
    public const STATUS_COMPLETE = 'complete';
    public const STATUS_FAILED   = 'failed';

    public function get_group(): string {
        return defined( 'HOSTINGER_PLUGIN_SETTINGS_OPTION' ) ? HOSTINGER_PLUGIN_SETTINGS_OPTION : 'hostinger_tools';
    }

    public function schedule_single( int $timestamp, string $hook, $args = array() ): int {
        if ( ! function_exists( 'as_schedule_single_action' ) ) {
            return 0;
        }

        return as_schedule_single_action( $timestamp, $hook, $args, $this->get_group() );
    }

    public function schedule_immediate( string $hook, $args = array() ): int {
        if ( ! function_exists( 'as_schedule_single_action' ) ) {
            return 0;
        }

        return as_schedule_single_action( gmdate( 'U' ) - 1, $hook, $args, $this->get_group() );
    }

    public function has_scheduled_action( string $hook, $args = array() ): bool {
        if ( ! function_exists( 'as_next_scheduled_action' ) ) {
            return false;
        }

        return as_next_scheduled_action( $hook, $args, $this->get_group() ) !== false;
    }
}
