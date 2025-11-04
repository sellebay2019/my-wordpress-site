<?php

namespace Hostinger\Cli\Commands;

use Exception;
use Hostinger\Admin\PluginSettings;
use WP_CLI;

defined( 'ABSPATH' ) || exit;

class AI {
    protected const WEB2AGENT_FEATURE_NAME = 'Web2Agent feature';
    protected const LLMS_TXT_FEATURE_NAME  = 'LLMS.txt file generation feature';

    public static function define_command(): void {
        if ( ! class_exists( 'WP_CLI' ) ) {
            return;
        }

        WP_CLI::add_command(
            'hostinger ai',
            self::class,
            array(
                'shortdesc' => 'Check the status of AI Discovery Features',
                'longdesc'  => 'Available Hostinger AI commands:' . "\n\n" .
                                '  wp hostinger ai llmstxt <0|1>' . "\n" .
                                '  Manage the ' . self::LLMS_TXT_FEATURE_NAME . '. Use 1 to enable and 0 to disable it.' . "\n\n" .
                                '  wp hostinger ai web2agent <0|1>' . "\n" .
                                '  Manage the ' . self::WEB2AGENT_FEATURE_NAME . '. Use 1 to enable and 0 to disable it.' . "\n\n" .
                                '  wp hostinger ai status' . "\n" .
                                '  Display the current status for AI Discovery features.' . "\n\n" .
                                '## EXAMPLES' . "\n\n" .
                                '  wp hostinger ai status' . "\n" .
                                '  Display the current status for AI Discovery features.' . "\n\n" .
                                '  wp hostinger ai llmstxt 0' . "\n" .
                                '  Disables ' . self::LLMS_TXT_FEATURE_NAME . '.' . "\n\n" .
                                '  wp hostinger ai llmstxt 1' . "\n" .
                                '  Enables ' . self::LLMS_TXT_FEATURE_NAME . '.' . "\n",

            )
        );
    }

    /**
     * Command allows enable/disable Web2Agent feature.
     *
     * @param array $args
     *
     * @return bool
     * @throws Exception
     */
    public function web2agent( array $args ): bool {
        $plugin_settings = new PluginSettings();

        if ( ! empty( $args ) ) {
            $this->validate_args( $args );
            $this->set_setting_status( $plugin_settings, self::WEB2AGENT_FEATURE_NAME, (bool) $args[0] );
        }

        return $this->get_setting_status( $plugin_settings, self::WEB2AGENT_FEATURE_NAME );
    }

    /**
     * Command allows enable/disable LLMS.txt file generation feature.
     *
     * @param array $args
     *
     * @return bool
     * @throws Exception
     */
    public function llmstxt( array $args ): bool {
        $plugin_settings = new PluginSettings();

        if ( ! empty( $args ) ) {
            $this->validate_args( $args );
            $this->set_setting_status( $plugin_settings, self::LLMS_TXT_FEATURE_NAME, (bool) $args[0] );
        }

        return $this->get_setting_status( $plugin_settings, self::LLMS_TXT_FEATURE_NAME );
    }

    /**
     * Get the current status of AI Discovery features.
     * @return string
     */
    public function status(): string {
        $plugin_settings = new PluginSettings();
        $plugin_options  = $plugin_settings->get_plugin_settings();

        $data = array(
            'llmstxt'   => $plugin_options->get_enable_llms_txt(),
            'web2agent' => $plugin_options->get_optin_mcp(),
        );

        $status = wp_json_encode( $data );
        WP_CLI::line( $status );
        return $status;
    }

    private function set_setting_status( PluginSettings $plugin_settings, string $setting, bool $is_enabled ): void {
        $plugin_options = $plugin_settings->get_plugin_settings();

        switch ( $setting ) {
            case self::WEB2AGENT_FEATURE_NAME:
                $plugin_options->set_optin_mcp( $is_enabled );
                break;
            case self::LLMS_TXT_FEATURE_NAME:
                $plugin_options->set_enable_llms_txt( $is_enabled );
                break;
            default:
                throw new Exception( 'Invalid setting' );
        }

        $plugin_settings->save_plugin_settings( $plugin_options );
        $this->clear_lightspeed_cache();
    }

    private function get_setting_status( PluginSettings $plugin_settings, string $setting ): bool {
        $plugin_options = $plugin_settings->get_plugin_settings();
        switch ( $setting ) {
            case self::WEB2AGENT_FEATURE_NAME:
                $is_enabled = $plugin_options->get_optin_mcp();
                break;
            case self::LLMS_TXT_FEATURE_NAME:
                $is_enabled = $plugin_options->get_enable_llms_txt();
                break;
            default:
                throw new Exception( 'Invalid setting' );
        }

        $enabled = $is_enabled ? 'ENABLED' : 'DISABLED';

        WP_CLI::success( $setting . ' ' . $enabled );
        return $is_enabled;
    }

    private function validate_args( mixed $args ): void {
        if ( $args[0] !== '0' && $args[0] !== '1' ) {
            throw new Exception( 'Invalid argument. Use 0 or 1' );
        }
    }

    private function clear_lightspeed_cache(): void {
        if ( has_action( 'litespeed_purge_all' ) ) {
            do_action( 'litespeed_purge_all' );
        }
    }
}
