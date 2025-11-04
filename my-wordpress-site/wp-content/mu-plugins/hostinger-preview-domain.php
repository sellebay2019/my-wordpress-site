<?php
/**
 * Plugin Name:       Hostinger Preview Domain
 * Plugin URI:        https://www.hostinger.com
 * Description:       Enable access to the website through a temporary domain while the main domain is not yet configured.
 * Version:           1.3.4
 * Author:            Hostinger
 * Author URI:        https://www.hostinger.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       hostinger-preview-domain
 * Domain Path:       /languages
 * Requires at least: 5.0
 * Requires PHP:      7.0
 * MU Plugin:         Yes
 */

if ( ! class_exists( 'Hostinger_Temporary_Domain_Handler' ) ) {
    class Hostinger_Temporary_Domain_Handler {
        private $site_domain;
        private $current_domain;
        private $db_site_url;

        public function __construct() {
            $this->initialize_domains();
            $this->setup_hooks();
        }

        /**
         * Initialize the handler if the preview indicator is present.
         *
         * @return self|null Returns a new instance if preview indicator is found, otherwise null.
         */
        public static function init() {
            if ( function_exists( 'getallheaders' ) ) {
                $headers = getallheaders();
                if ( isset( $headers['X-Preview-Indicator'] ) && $headers['X-Preview-Indicator'] ) {
                    return new self();
                }
            }

            if ( isset( $_SERVER['HTTP_X_PREVIEW_INDICATOR'] ) && $_SERVER['HTTP_X_PREVIEW_INDICATOR'] ) {
                return new self();
            }

            return null;
        }

        /**
         * Filter and rewrite the URL if necessary.
         *
         * @param string      $url     The original URL.
         * @param mixed       $path    Optional. Path relative to the URL.
         * @param string|null $scheme  Optional. Scheme to give the URL context.
         * @param int|null    $blog_id Optional. Blog ID.
         *
         * @return string The filtered URL.
         */
        public function filter_url( $url, $path = '', $scheme = null, $blog_id = null ) {
            if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
                return $url;
            }

            $filtered_url = str_replace( [ 'http://' . $this->site_domain, 'https://' . $this->site_domain ], 'https://' . $this->current_domain, $url );

            return filter_var( $filtered_url, FILTER_SANITIZE_URL ) ?: '';
        }

        /**
         * Filter site icon URL to remove www prefix
         *
         * @param string $url The site icon URL
         * @return string The filtered URL
         */
        public function filter_site_icon_url( $url ) {
            if ( empty( $url ) ) {
                return $url;
            }

            $url = $this->filter_url( $url );

            $url = preg_replace( '/https?:\/\/www\./i', 'https://', $url );

            return $url;
        }

        /**
         * Filter and rewrite the content if necessary.
         *
         * @param string $content The original content.
         *
         * @return string The filtered content.
         */
        public function filter_content( $content ) {
            $patterns = [
                // HTML attributes and content URLs
                '/(href|src|action|srcset|data-img-url)\s*=\s*[\'"]https?:\/\/' . preg_quote( $this->site_domain, '/' ) . '[^\s\'"<>]*/i',
                // CSS imports and urls
                '/(@import\s+["\']|url\(["\']?)https?:\/\/' . preg_quote( $this->site_domain, '/' ) . '[^\s\'"<>)]*/i',
            ];

            foreach ( $patterns as $pattern ) {
                $content = preg_replace_callback( $pattern, function ( $matches ) {
                    $url = substr( $matches[0], strpos( $matches[0], 'http' ) );

                    return str_replace( $url, $this->filter_url( $url ), $matches[0] );
                }, $content );
            }

            return $content ?: '';
        }

        /**
         * Handle CORS headers.
         *
         * @return void
         */
        public function handle_cors() {
            $allowed_origin = 'https://' . filter_var( $this->current_domain, FILTER_SANITIZE_URL );

            if ( isset( $_SERVER['HTTP_ORIGIN'] ) && $_SERVER['HTTP_ORIGIN'] === $allowed_origin ) {
                header( 'Access-Control-Allow-Origin: ' . $allowed_origin );
                header( 'Access-Control-Allow-Methods: GET, POST, OPTIONS' );
                header( 'Access-Control-Allow-Credentials: true' );
                header( 'Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept' );
                header( 'Access-Control-Max-Age: 86400' );

                if ( $_SERVER['REQUEST_METHOD'] === 'OPTIONS' ) {
                    header( 'HTTP/1.1 204 No Content' );
                    exit();
                }
            }
        }

        /**
         * Start output buffering if URL rewriting is needed.
         *
         * @return void
         */
        public function start_output_buffer() {
            ob_start( [ $this, 'filter_content' ] );
        }

        /**
         * Initialize site and current domains.
         *
         * @return void
         */
        private function initialize_domains() {
            $this->site_domain = $this->sanitize_domain( parse_url( get_site_url(), PHP_URL_HOST ) ?: '' );
            $this->current_domain = $this->sanitize_domain( isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '' );
            $this->db_site_url = $this->get_site_url_from_db();
        }

        /**
         * Get site URL from database directly without WordPress functions
         *
         * @return string The site URL without http:// or https://
         */
        private function get_site_url_from_db() {
            global $wpdb;

            $site_url = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT REPLACE(REPLACE(option_value, 'https://', ''), 'http://', '') 
                    FROM {$wpdb->options} 
                    WHERE option_name = %s 
                    LIMIT 1",
                    'siteurl'
                )
            );

            return $this->sanitize_domain($site_url ?: '');
        }

        /**
         * Setup hooks for URL and content filtering.
         *
         * @return void
         */
        private function setup_hooks() {
            if ( $this->should_skip_hooks() ) {
                return;
            }

            // Basic URL filters
            add_filter( 'home_url', [ $this, 'filter_url' ], 10, 4 );
            add_filter( 'site_url', [ $this, 'filter_url' ], 10, 4 );
            add_filter( 'wp_redirect', [ $this, 'filter_url' ], 10 );

            // Site icon URL filter
            add_filter( 'get_site_icon_url', [ $this, 'filter_site_icon_url' ], 10, 1 );

            // Content filters
            $content_filters = [ 'the_content', 'widget_text', 'wp_nav_menu_items' ];
            foreach ( $content_filters as $filter ) {
                add_filter( $filter, [ $this, 'filter_content' ], 999 );
            }

            // Media handling
            add_filter( 'wp_get_attachment_url', [ $this, 'filter_url' ] );

            // Admin filters
            if ( is_admin() ) {
                $this->setup_admin_filters();
            }

            // Output buffering and CORS
            add_action( 'init', [ $this, 'start_output_buffer' ], 0 );
            add_action( 'init', [ $this, 'handle_cors' ], 0 );

            // Content save hooks to replace domain in content
            add_filter( 'wp_insert_post_data', [ $this, 'replace_host_in_content' ], 10, 2 );
            add_filter( 'content_save_pre', [ $this, 'replace_host_in_content_simple' ], 10, 1 );
            add_filter( 'pre_update_option', [ $this, 'replace_host_in_option' ], 10, 3 );
        }

        /**
         * Setup filters for admin URLs.
         *
         * @return void
         */
        private function setup_admin_filters() {
            $admin_filters = [
                'admin_url',
                'plugins_url',
                'theme_file_uri',
                'includes_url',
                'content_url',
                'style_loader_src',
                'script_loader_src',
                'preview_post_link',
            ];

            foreach ( $admin_filters as $filter ) {
                add_filter( $filter, [ $this, 'filter_url' ], 10, 3 );
            }
        }

        /**
         * Sanitize a domain name.
         *
         * @param string $domain The domain name to sanitize.
         *
         * @return string The sanitized domain name.
         */
        private function sanitize_domain( $domain ) {
            return preg_replace( '/[^a-z0-9\-\.]/', '', strtolower( trim( $domain ) ) );
        }

        /**
         * Check if we should skip applying hooks for this request.
         *
         * @return bool True if hooks should be skipped
         */
        private function should_skip_hooks() {
            if ( php_sapi_name() === 'cli' ) {
                if ( $this->is_litespeed_command() ) {
                    return true;
                }
            }

            if ( ! $this->should_rewrite_url() ) {
                return true;
            }

            $script_name = isset( $_SERVER['SCRIPT_FILENAME'] ) ? basename( $_SERVER['SCRIPT_FILENAME'] ) : '';
            if ( strpos( $script_name, 'autologin' ) !== false || isset( $_GET['platform'] ) ) {
                return true;
            }

            return false;
        }

        /**
         * Simple check if current command contains LiteSpeed references
         *
         * @return bool True if litespeed command detected
         */
        private function is_litespeed_command() {
            global $argv;

            if ( ! empty( $argv ) ) {
                $command = implode( ' ', $argv );
                if ( stripos( $command, 'litespeed' ) !== false || stripos( $command, 'lscache' ) !== false ) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Determine if the URL should be rewritten.
         *
         * @return bool True if the URL should be rewritten, false otherwise.
         */
        private function should_rewrite_url() {
            return $this->current_domain !== $this->site_domain;
        }

        /**
         * Replace current host with site URL in post content during save
         *
         * @param array $data    Post data array
         * @param array $postarr Original post array
         *
         * @return array Modified post data
         */
        public function replace_host_in_content( $data, $postarr ) {
            // Only proceed if current host and DB site URL are different
            if ( $this->current_domain && $this->db_site_url && $this->current_domain !== $this->db_site_url ) {
                if ( isset( $data['post_content'] ) ) {
                    $data['post_content'] = str_replace( $this->current_domain, $this->db_site_url, $data['post_content'] );
                }

                if ( isset( $data['post_title'] ) ) {
                    $data['post_title'] = str_replace( $this->current_domain, $this->db_site_url, $data['post_title'] );
                }

                if ( isset( $data['post_excerpt'] ) ) {
                    $data['post_excerpt'] = str_replace( $this->current_domain, $this->db_site_url, $data['post_excerpt'] );
                }
            }

            return $data;
        }

        /**
         * Replace current host with site URL in regular content during save
         *
         * @param string $content The content being saved
         *
         * @return string Modified content
         */
        public function replace_host_in_content_simple( $content ) {
            // Only proceed if current host and DB site URL are different
            if ( $this->current_domain && $this->db_site_url && $this->current_domain !== $this->db_site_url ) {
                $content = str_replace( $this->current_domain, $this->db_site_url, $content );
            }

            return $content;
        }

        /**
         * Replace current host with site URL in options
         *
         * @param mixed  $value     The new option value
         * @param string $option    Option name
         * @param mixed  $old_value The old option value
         *
         * @return mixed Modified option value
         */
        public function replace_host_in_option( $value, $option, $old_value ) {
            // Skip if it's the siteurl or home option to avoid conflicts
            if ( in_array( $option, array( 'siteurl', 'home' ) ) ) {
                return $value;
            }

            // Only proceed if current host and DB site URL are different
            if ( $this->current_domain && $this->db_site_url && $this->current_domain !== $this->db_site_url ) {
                if ( is_string( $value ) ) {
                    $value = str_replace( $this->current_domain, $this->db_site_url, $value );
                } elseif ( is_array( $value ) ) {
                    $value = $this->replace_in_array_recursive( $value, $this->current_domain, $this->db_site_url );
                }
            }

            return $value;
        }

        /**
         * Recursively replace values in arrays
         *
         * @param array  $array   The array to process
         * @param string $search  The search string
         * @param string $replace The replacement string
         *
         * @return array The processed array
         */
        private function replace_in_array_recursive( $array, $search, $replace ) {
            $result = array();

            foreach ( $array as $key => $value ) {
                if ( is_array( $value ) ) {
                    $result[$key] = $this->replace_in_array_recursive( $value, $search, $replace );
                } elseif ( is_string( $value ) ) {
                    $result[$key] = str_replace( $search, $replace, $value );
                } else {
                    $result[$key] = $value;
                }
            }

            return $result;
        }
    }

    Hostinger_Temporary_Domain_Handler::init();
}
