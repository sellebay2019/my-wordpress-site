<?php

namespace Hostinger\LlmsTxtGenerator;

defined( 'ABSPATH' ) || exit;

class LlmsTxtFileHelper {

    public const HOSTINGER_LLMSTXT_FILENAME = 'llms.txt';

    public function is_user_generated_file(): bool {
        if ( ! $this->llmstxt_file_exists() ) {
            return false;
        }

        $content = $this->get_content();

        if ( $content === false ) {
            return false;
        }

        return ! str_contains( $content, LlmsTxtGenerator::HOSTINGER_LLMSTXT_SIGNATURE );
    }

    public function get_content(): string {
        global $wp_filesystem;
        $this->init_wp_filesystem();
        return $wp_filesystem->get_contents( $this->get_llmstxt_file_path() );
    }

    public function create( string $content ): void {
        global $wp_filesystem;
        $this->init_wp_filesystem();
        $wp_filesystem->put_contents( $this->get_llmstxt_file_path(), $content );
    }

    public function delete(): void {
        if ( $this->llmstxt_file_exists() && ! $this->is_user_generated_file() ) {
            global $wp_filesystem;
            $this->init_wp_filesystem();
            $wp_filesystem->delete( $this->get_llmstxt_file_path() );
        }
    }

    public function get_llmstxt_file_path(): string {
        return ABSPATH . self::HOSTINGER_LLMSTXT_FILENAME;
    }

    public function get_llmstxt_file_url(): string {
        return site_url( LlmsTxtFileHelper::HOSTINGER_LLMSTXT_FILENAME );
    }

    public function llmstxt_file_exists(): bool {
        return file_exists( $this->get_llmstxt_file_path() );
    }

    protected function init_wp_filesystem(): void {
        require_once ABSPATH . '/wp-admin/includes/file.php';
        WP_Filesystem();
    }
}
