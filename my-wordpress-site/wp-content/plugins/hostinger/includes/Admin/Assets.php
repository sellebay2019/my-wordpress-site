<?php

namespace Hostinger\Admin;

use Hostinger\Admin\Menu;
use Hostinger\LlmsTxtGenerator\LlmsTxtFileHelper;
use Hostinger\WpMenuManager\Menus;
use Hostinger\Helper;
use Hostinger\WpHelper\Utils;

defined( 'ABSPATH' ) || exit;

/**
 * Class Hostinger_Admin_Assets
 *
 * Handles the enqueueing of styles and scripts for the Hostinger admin pages.
 */
class Assets {
    /**
     * @var Helper Instance of the Hostinger_Helper class.
     */
    private Helper $helper;

    /**
     * @var LlmsTxtFileHelper
     */
    private LlmsTxtFileHelper $llms_txt_file_helper;

    /**
     * @var Utils
     */
    private Utils $utils;

    public function __construct() {
        $this->llms_txt_file_helper = new LlmsTxtFileHelper();
        $this->helper               = new Helper();
        $this->utils                = new Utils();
        $admin_path                 = parse_url( admin_url(), PHP_URL_PATH );

        // Load assets only on Hostinger admin pages.
        if ( $this->utils->isThisPage( $admin_path . 'admin.php?page=' . Menu::MENU_SLUG ) || $this->utils->isThisPage( $admin_path . 'admin.php?page=' . Menus::MENU_SLUG ) ) {
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
        }
        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', array( $this, 'global_styles' ) );

        }
    }

    /**
     * Enqueues styles for the Hostinger admin pages.
     */
    public function admin_styles(): void {
        // Vue frontend styles.
        wp_enqueue_style(
            'hostinger_tools_styles',
            HOSTINGER_VUE_ASSETS_URL . '/main.css',
            array(),
            HOSTINGER_VERSION
        );

        // Plugin rating styles.
        wp_enqueue_style(
            'hostinger_rating_styles',
            HOSTINGER_ASSETS_URL . '/css/plugin-rating.min.css',
            array(),
            HOSTINGER_VERSION
        );
    }

    public function global_styles(): void {
        wp_enqueue_style(
            'hostinger_tools_global_styles',
            HOSTINGER_ASSETS_URL . 'css/hostinger-global.min.css',
            array(),
            HOSTINGER_VERSION
        );
    }



    /**
     * Enqueues scripts for the Hostinger admin pages.
     */
    public function admin_scripts(): void {
        global $wp_version;

        wp_enqueue_script(
            'hostinger_tools_main_scripts',
            HOSTINGER_VUE_ASSETS_URL . '/main.js',
            array(
                'jquery',
                'wp-i18n',
            ),
            HOSTINGER_VERSION,
            false
        );

        wp_localize_script(
            'hostinger_tools_main_scripts',
            'hostinger_tools_data',
            array(
                'home_url'                    => home_url(),
                'site_url'                    => get_site_url(),
                'plugin_url'                  => $this->helper->get_hostinger_plugin_url(),
                'asset_url'                   => HOSTINGER_PLUGIN_URL,
                'hplatform'                   => ! empty( $_SERVER['H_PLATFORM'] ) ? 1 : 0,
                'edit_site_url'               => $this->helper->get_edit_site_url(),
                'llmstxt_file_url'            => $this->llms_txt_file_helper->get_llmstxt_file_url(),
                'llmstxt_file_user_generated' => $this->llms_txt_file_helper->is_user_generated_file(),
                'translations'                => array(
                    'routes_tools'                                            => __( 'Tools', 'hostinger' ),
                    'hostinger_tools_open_guide'                              => __( 'Open guide', 'hostinger' ),
                    'hostinger_tools_preview_site'                            => __( 'Preview site', 'hostinger' ),
                    'hostinger_tools_edit_site'                               => __( 'Edit site', 'hostinger' ),
                    'hostinger_tools_disable_public_access'                   => __( 'Disable public access to the site (WordPress admins will still be able to access)', 'hostinger' ),
                    'hostinger_tools_skip_link_maintenance_mode'              => __( 'Skip-link that bypasses the maintenance mode', 'hostinger' ),
                    'hostinger_tools_reset_link'                              => __( 'Reset link', 'hostinger' ),
                    'hostinger_tools_disable_xml_rpc'                         => __( 'Disable XML-RPC', 'hostinger' ),
                    'hostinger_tools_xml_rpc_description'                     => __( 'XML-RPC allows apps to connect to your WordPress site, but might expose your site\'s security. Disable this feature if you don\'t need it', 'hostinger' ),
                    'hostinger_tools_disable_authentication_password'         => __( 'Disable application passwords', 'hostinger' ),
                    'hostinger_tools_authentication_password_description'     => __( 'WordPress application passwords allow users to authenticate API requests without using their main login credentials, allowing for third-party integrations.', 'hostinger' ),

                    'hostinger_tools_force_https'                             => __( 'Force HTTPS', 'hostinger' ),
                    'hostinger_tools_force_https_description'                 => __( 'Redirects all HTTP URLs to HTTPS sites', 'hostinger' ),
                    'hostinger_tools_force_www'                               => __( 'Force WWW', 'hostinger' ),
                    'hostinger_tools_force_www_description'                   => __( 'Redirects all WWW URLs to non-WWW ones', 'hostinger' ),
                    'hostinger_tools_force_www_description_not_available'     => __( 'WWW and non-WWW domain records are not pointing to the same host. Redirect not possible.', 'hostinger' ),
                    'hostinger_tools_php_version'                             => __( 'PHP version', 'hostinger' ),
                    'hostinger_tools_wordpress_version'                       => __( 'WordPress version', 'hostinger' ),
                    'hostinger_tools_php_version_description'                 => __( 'Various updates and fixes available in the newest version.', 'hostinger' ),
                    'hostinger_tools_running_latest_version'                  => __( 'Running the latest version', 'hostinger' ),
                    'hostinger_tools_update'                                  => __( 'Update', 'hostinger' ),
                    'hostinger_tools_update_to'                               => __( 'Update to', 'hostinger' ),
                    'hostinger_tools_update_to_recommended'                   => __( 'is recommended', 'hostinger' ),
                    'hostinger_tools_update_to_wordpress_version_description' => __( 'For improved security, ensure you use the latest version of WordPress', 'hostinger' ),
                    'hostinger_tools_maintenance'                             => __( 'Maintenance', 'hostinger' ),
                    'hostinger_tools_preview_my_website'                      => __( 'Preview my website', 'hostinger' ),
                    'hostinger_tools_security'                                => __( 'Security', 'hostinger' ),
                    'hostinger_tools_redirects'                               => __( 'Redirects', 'hostinger' ),
                    'hostinger_tools_ai'                                      => __( 'AI Tools', 'hostinger' ),
                    'hostinger_tools_llms'                                    => __( 'LLM Optimization', 'hostinger' ),
                    'hostinger_tools_enable_llms_txt'                         => __( 'Create LLMs.txt file', 'hostinger' ),
                    'hostinger_tools_llms_txt_description'                    => __( 'Let AI explore, understand, and interact with your WordPress site.', 'hostinger' ),
                    'hostinger_tools_optin_mcp'                               => __( 'Web2Agent', 'hostinger' ),
                    'hostinger_tools_optin_mcp_description'                   => __( 'Make your website easier for AI tools to understand. Website content updates will be tracked to keep the AI discovery service up to date.', 'hostinger' ),
                    'hostinger_tools_llms_txt_learn_more'                     => __( 'Learn more', 'hostinger' ),
                    'hostinger_tools_llms_txt_check_validity'                 => __( 'Check validity', 'hostinger' ),
                    'hostinger_tools_llms_txt_llmstxt'                        => __( 'LLMS.txt', 'hostinger' ),
                    'hostinger_tools_llms_txt_external_file_found'            => __( 'An external LLMs.txt file was found. Switching on the toggle will replace it with a new one.', 'hostinger' ),
                    'hostinger_tools_llms_txt_modal_title'                    => __( 'Create new LLMs.txt file?', 'hostinger' ),
                    'hostinger_tools_llms_txt_modal_description'              => __( 'This will replace the existing LLMs.txt file with a new one by Hostinger Tools. The original file can’t be restored.', 'hostinger' ),
                    'hostinger_tools_llms_txt_modal_cancel'                   => __( 'Cancel', 'hostinger' ),
                    'hostinger_tools_llms_txt_modal_create_file'              => __( 'Create file', 'hostinger' ),
                    'hostinger_tools_maintenance_mode'                        => __( 'Maintenance mode', 'hostinger' ),
                    'hostinger_tools_bypass_link'                             => __( 'Bypass link', 'hostinger' ),
                    'xml_security_modal_description'                          => __( ' Turning on XML-RPC might make your site less secure. Do you want to proceed?', 'hostinger' ),
                    'xml_security_modal_title'                                => __( 'Disclaimer', 'hostinger' ),
                    'xml_security_modal_cancel'                               => __( 'Cancel', 'hostinger' ),
                    'xml_security_modal_proceed_anyway'                       => __( 'Proceed anyway', 'hostinger' ),
                    'bypass_link_reset_modal_title'                           => __( 'Bypass link reset', 'hostinger' ),
                    'bypass_link_reset_modal_description'                     => __( 'This will invalidate the currently generated link in use. This action cannot be undone, are you sure you want to proceed?', 'hostinger' ),
                    'bypass_link_reset_modal_cancel'                          => __( 'Cancel', 'hostinger' ),
                    'bypass_link_reset_modal_reset_link'                      => __( 'Reset link', 'hostinger' ),
                    'bypass_link_reset_success'                               => __( 'Link has been reset', 'hostinger' ),
                    'hostinger_tools_settings_updated'                        => __( 'Your settings have been updated', 'hostinger' ),
                    'hostinger_tools_settings_error'                          => __( 'It was an error updating your settings', 'hostinger' ),
                    'hostinger_tools_mcp_choice'                              => __( 'Allow Kodee to manage your site', 'hostinger' ),
                    'hostinger_tools_mcp_description'                         => __( 'Let Kodee manage your site on your behalf. This allows Kodee to perform actions like creating pages or updating settings. We will install and pre-configure the WordPress MCP plugin for you.', 'hostinger' ),
                    'hostinger_tools_copied_successfully'                     => __( 'Copied successfully', 'hostinger' ),
                    'hostinger_tools_text_copied_successfully'                => __( 'Text has been copied successfully', 'hostinger' ),
                    'hostinger_tools_free_domain_llm_unavailable'             => __( 'LLM optimization features are not available for temporary subdomains. Connect a domain to unlock these features.', 'hostinger' ),
                    'hostinger_tools_connect_domain_cta'                      => __( 'Connect domain', 'hostinger' ),
                ),
                'rest_base_url'               => esc_url_raw( rest_url() ),
                'nonce'                       => wp_create_nonce( 'wp_rest' ),
                'wp_version'                  => $wp_version,
                'php_version'                 => phpversion(),
                'recommended_php_version'     => $this->helper->get_recommended_php_version(),
                'mcp_choice'                  => get_option( 'hostinger_mcp_choice', 0 ),
                'ai_plugin_compatibility'     => $this->check_ai_mcp_compatibility(),
            )
        );
    }

    public function check_ai_mcp_compatibility(): bool {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $plugin_path      = 'hostinger-ai-assistant/hostinger-ai-assistant.php';
        $required_version = '3.0.0';

        if ( is_plugin_active( $plugin_path ) ) {
            $plugin_file = WP_PLUGIN_DIR . '/' . $plugin_path;
            if ( file_exists( $plugin_file ) ) {
                $plugin_data    = get_plugin_data( $plugin_file, false, false );
                $active_version = isset( $plugin_data['Version'] ) ? $plugin_data['Version'] : '';

                if ( $active_version && version_compare( $active_version, $required_version, '>=' ) ) {
                    return true;
                }
            }
        }

        return false;
    }
}
