<?php

namespace Hostinger\Admin\Jobs;

use Hostinger\Admin\PluginSettings;
use Hostinger\LlmsTxtGenerator\LlmsTxtFileHelper;
use Hostinger\LlmsTxtGenerator\LlmsTxtGenerator;
use Hostinger\LlmsTxtGenerator\LlmsTxtParser;

class LlmsTxtInjectContentJob extends AbstractBatchedJob {

    public const JOB_NAME = 'generate_llmstxt';

    protected LlmsTxtParser $llms_txt_parser;
    protected LlmsTxtFileHelper $llms_txt_file_helper;
    protected PluginSettings $plugin_settings;

    public function __construct( ActionScheduler $action_scheduler, LlmsTxtParser $llms_txt_parser, LlmsTxtFileHelper $llms_txt_file_helper, PluginSettings $plugin_settings ) {
        parent::__construct( $action_scheduler );
        $this->llms_txt_parser      = $llms_txt_parser;
        $this->llms_txt_file_helper = $llms_txt_file_helper;
        $this->plugin_settings      = $plugin_settings;
    }

    protected function get_batch( int $batch_number, $args ): array {
        if ( ! isset( $args['post_type'] ) ) {
            return array();
        }

        $offset = $this->get_query_offset( $batch_number );
        $limit  = $this->get_batch_size();

        return $this->llms_txt_parser->get_by_post_type( $args['post_type'], $limit, $offset );
    }

    public function get_name(): string {
        return self::JOB_NAME;
    }

    protected function process_items( array $args = array() ): void {
        if ( ! $this->is_llms_txt_enabled() ) {
            return;
        }

        $items    = $args['items'] ?? array();
        $job_args = $args['args'] ?? array();

        if ( ! isset( $job_args['post_type'] ) || empty( $items ) ) {
            return;
        }

        $content = $this->llms_txt_parser->get_items( $items );
        $this->inject_content( $job_args['post_type'], $content );
    }

    public function schedule( array $args = array() ): void {
        // Initiate as 2, as the first batch will be created when the user toggles on the option.
        $this->schedule_create_batch_action( 2, $args );
    }

    public function can_schedule( $args = array() ): bool {
        return parent::can_schedule( $args ) && $this->is_llms_txt_enabled();
    }

    public function is_llms_txt_enabled(): bool {
        $settings = $this->plugin_settings->get_plugin_settings();
        return $settings->get_enable_llms_txt();
    }

    public function inject_content( $post_type, $new_content ): void {
        $content         = $this->llms_txt_file_helper->get_content();
        $section         = LlmsTxtGenerator::HOSTINGER_LLMSTXT_SUPPORTED_POST_TYPES[ $post_type ];
        $header          = "## $section\n\n";
        $header_position = strpos( $content, $header );
        $header_length   = strlen( $header );
        if ( $header_position === false ) {
            return;
        }

        $before_injection_slot = substr( $content, 0, $header_position + $header_length );
        $after_injection_slot  = substr( $content, $header_position + $header_length );

        $final_content = $before_injection_slot . $new_content . $after_injection_slot;
        $this->llms_txt_file_helper->create( $final_content );
    }
}
