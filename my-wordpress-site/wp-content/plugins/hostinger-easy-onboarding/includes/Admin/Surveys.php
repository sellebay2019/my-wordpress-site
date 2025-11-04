<?php

namespace Hostinger\EasyOnboarding\Admin;

use Hostinger\Surveys\SurveyManager;
use Hostinger\WpHelper\Utils as Helper;
use Hostinger\EasyOnboarding\Helper as EasyOnboardingHelper;

class Surveys {
    public const TIME_15_MINUTES                  = 900;
    public const DAY_IN_SECONDS                   = 86400;
    public const WOO_SURVEY_ID                    = 'woo_survey';
    public const PREBUILD_WEBSITE_SURVEY_ID       = 'prebuild_website';
    public const AI_ONBOARDING_SURVEY_ID          = 'ai_onboarding';
    public const PREBUILD_WEBSITE_SURVEY_LOCATION = 'wordpress_prebuild_website';
    public const WOO_SURVEY_LOCATION              = 'wordpress_woocommerce_onboarding';
    public const AI_ONBOARDING_SURVEY_LOCATION    = 'wordpress_ai_onboarding';
    public const WOO_SURVEY_PRIORITY              = 100;
    public const PREBUILD_WEBSITE_SURVEY_PRIORITY = 90;
    public const AI_ONBOARDING_SURVEY_PRIORITY    = 80;
    public const SUBMITTED_SURVEY_TRANSIENT       = 'submitted_survey_transient';
    private SurveyManager $survey_manager;

    public function __construct( SurveyManager $survey_manager ) {
        $this->survey_manager = $survey_manager;
    }

    public function init() {
        add_filter( 'hostinger_add_surveys', array( $this, 'create_surveys' ) );
    }

    public function create_surveys( $surveys ) {
        if ( $this->is_woocommerce_survey_enabled() ) {
            $score_question   = esc_html__( 'How would you rate your experience setting up a WooCommerce site on our hosting?', 'hostinger-easy-onboarding' );
            $comment_question = esc_html__( 'Do you have any comments/suggestions to improve our WooCommerce onboarding?', 'hostinger-easy-onboarding' );
            $woo_survey       = SurveyManager::addSurvey( self::WOO_SURVEY_ID, $score_question, $comment_question, self::WOO_SURVEY_LOCATION, self::WOO_SURVEY_PRIORITY );
            $surveys[]        = $woo_survey;
        }

        if ( $this->is_prebuild_website_survey_enabled() ) {
            $score_question          = esc_html__( 'How would you rate your experience building a website based on a pre-built template? (Score 1-10)', 'hostinger-easy-onboarding' );
            $comment_question        = esc_html__( 'How could we make it easier to create a new WordPress website?', 'hostinger-easy-onboarding' );
            $prebuild_website_survey = SurveyManager::addSurvey( self::PREBUILD_WEBSITE_SURVEY_ID, $score_question, $comment_question, self::PREBUILD_WEBSITE_SURVEY_LOCATION, self::PREBUILD_WEBSITE_SURVEY_PRIORITY );
            $surveys[]               = $prebuild_website_survey;
        }

        if ( $this->is_ai_onboarding_survey_enabled() ) {
            $score_question          = esc_html__( 'How would you rate your experience using our AI content generation tools in onboarding? (Scale 1-10)', 'hostinger-easy-onboarding' );
            $comment_question        = esc_html__( 'Do you have any comments/suggestions to improve our AI tools?', 'hostinger-easy-onboarding' );
            $prebuild_website_survey = SurveyManager::addSurvey( self::AI_ONBOARDING_SURVEY_ID, $score_question, $comment_question, self::AI_ONBOARDING_SURVEY_LOCATION, self::AI_ONBOARDING_SURVEY_PRIORITY );
            $surveys[]               = $prebuild_website_survey;
        }

        return $surveys;
    }

    public function is_woocommerce_survey_enabled(): bool {

        if ( defined( 'DOING_AJAX' ) && \DOING_AJAX ) {
            return false;
        }
        $helper = new EasyOnboardingHelper();

        $not_submitted            = ! get_transient( self::SUBMITTED_SURVEY_TRANSIENT );
        $not_completed            = $this->survey_manager->isSurveyNotCompleted( self::WOO_SURVEY_ID );
        $is_woocommerce_page      = $this->survey_manager->isWoocommerceAdminPage();
        $woo_onboarding_completed = $helper->is_woocommerce_onboarding_completed();
        $oldest_product_date      = $this->survey_manager->getOldestProductDate();
        $seven_days_ago           = strtotime( '-7 days' );
        $is_client_eligible       = $this->survey_manager->isClientEligible();

        if ( $oldest_product_date < $seven_days_ago || ! $this->is_within_creation_date_limit() ) {
            return false;
        }

        return $not_submitted && $not_completed && $is_woocommerce_page && $woo_onboarding_completed && $is_client_eligible;
    }

    public function is_prebuild_website_survey_enabled(): bool {

        if ( defined( 'DOING_AJAX' ) && \DOING_AJAX ) {
            return false;
        }

        $helper                  = new Helper();
        $not_submitted           = ! get_transient( self::SUBMITTED_SURVEY_TRANSIENT );
        $not_completed           = $this->survey_manager->isSurveyNotCompleted( self::PREBUILD_WEBSITE_SURVEY_ID );
        $is_hostinger_admin_page = $helper->isThisPage( 'hostinger-get-onboarding' );
        $is_client_eligible      = $this->survey_manager->isClientEligible();
        $astra_templates_active  = $helper->isPluginActive( 'astra-sites' );

        if ( ! $is_hostinger_admin_page || ! $this->is_within_creation_date_limit() ) {
            return false;
        }

        return $not_submitted && $not_completed && $is_client_eligible && $astra_templates_active;
    }

    public function is_ai_onboarding_survey_enabled(): bool {
        if ( defined( 'DOING_AJAX' ) && \DOING_AJAX ) {
            return false;
        }
        $helper                  = new Helper();
        $first_login_at          = strtotime( get_option( 'hostinger_first_login_at', time() ) );
        $not_submitted           = ! get_transient( self::SUBMITTED_SURVEY_TRANSIENT );
        $not_completed           = $this->survey_manager->isSurveyNotCompleted( self::AI_ONBOARDING_SURVEY_ID );
        $is_client_eligible      = $this->survey_manager->isClientEligible();
        $is_ai_onboarding_passed = get_option( 'hostinger_ai_onboarding', '' );
        $is_hostinger_admin_page = $helper->isThisPage( 'hostinger-get-onboarding' );

        if ( ! $is_ai_onboarding_passed || ! $this->is_within_creation_date_limit() ) {
            return false;
        }

        if ( isset( $_SERVER['H_STAGING'] ) && $_SERVER['H_STAGING'] ) {
            return $not_submitted && $not_completed && $is_client_eligible && $is_hostinger_admin_page;
        }

        if ( $first_login_at && ! $this->is_time_elapsed( $first_login_at, self::TIME_15_MINUTES ) ) {
            return false;
        }

        return $not_submitted && $not_completed && $is_client_eligible && $is_hostinger_admin_page;
    }


    public function is_time_elapsed( string $first_login_at, int $time_in_seconds ): bool {
        $current_time = time();
        $time_elapsed = $current_time - $time_in_seconds;

        return $time_elapsed >= $first_login_at;
    }

    private function is_within_creation_date_limit(): bool {
        $oldest_user = get_users(
            array(
                'number'  => 1,
                'orderby' => 'registered',
                'order'   => 'ASC',
                'fields'  => array( 'user_registered' ),
            )
        );

        $oldest_user_date = isset( $oldest_user[0]->user_registered ) ? strtotime( $oldest_user[0]->user_registered ) : false;

        return $oldest_user_date && ( time() - $oldest_user_date ) <= ( 7 * self::DAY_IN_SECONDS );
    }
}
