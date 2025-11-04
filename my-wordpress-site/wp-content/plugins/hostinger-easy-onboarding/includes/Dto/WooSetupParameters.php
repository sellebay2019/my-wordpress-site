<?php
namespace Hostinger\EasyOnboarding\Dto;

if ( ! defined( 'ABSPATH' ) ) {
    die;
}

class WooSetupParameters {
    private string $store_name;
    private string $industry;
    private string $store_location;
    private string $business_email;
    private bool $is_agree_marketing;

    public function __construct( string $store_name, string $industry, string $store_location, string $business_email, bool $is_agree_marketing ) {
        $this->store_name         = $store_name;
        $this->industry           = $industry;
        $this->store_location     = $store_location;
        $this->business_email     = $business_email;
        $this->is_agree_marketing = $is_agree_marketing;
    }

    public static function from_array( array $data ): WooSetupParameters {
        return new self(
            $data['store_name'] ?? '',
            $data['industry'] ?? '',
            $data['store_location'] ?? '',
            $data['business_email'] ?? '',
            isset( $data['is_agree_marketing'] ) && (bool) $data['is_agree_marketing']
        );
    }

    public function get_store_name(): string {
        return $this->store_name;
    }

    public function get_industry(): string {
        return $this->industry;
    }

    public function get_store_location(): string {
        return $this->store_location;
    }

    public function get_formatted_store_location(): string {
        if ( str_contains( $this->get_store_location(), ':' ) ) {
            $store_location_parts = explode( ':', $this->get_store_location() );
            return $store_location_parts[0];
        }

        return $this->get_store_location();
    }

    public function get_business_email(): string {
        return $this->business_email;
    }

    public function get_is_agree_marketing(): bool {
        return $this->is_agree_marketing;
    }
}
