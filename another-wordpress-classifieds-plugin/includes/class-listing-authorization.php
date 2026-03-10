<?php
/**
 * @package AWPCP\Listings
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Authorization logic for several listing related operations.
 */
class AWPCP_ListingAuthorization {

    /**
     * @var object
     */
    private $listing_renderer;

    /**
     * @var object
     */
    private $roles;

    /**
     * @var object
     */
    private $settings;

    /**
     * @var object
     */
    private $request;

    /**
     * @param object $listing_renderer  An instance of Listing Renderer.
     * @param object $roles             An instance of Roles And Capabilities.
     * @param object $settings          An instance of SettingsAPI.
     * @param object $request           An instance of Request.
     */
    public function __construct( $listing_renderer, $roles, $settings, $request ) {
        $this->listing_renderer = $listing_renderer;
        $this->roles            = $roles;
        $this->settings         = $settings;
        $this->request          = $request;
    }

    /**
     * @since 4.0.0
     */
    public function is_current_user_allowed_to_submit_listing() {
        if ( ! $this->settings->get_option( 'onlyadmincanplaceads' ) ) {
            return true;
        }

        if ( $this->roles->current_user_is_administrator() ) {
            return true;
        }

        return false;
    }

    /**
     * @param object $listing   An instance of WP_Post.
     */
    public function is_current_user_allowed_to_edit_listing( $listing ) {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        if ( $this->roles->current_user_is_moderator() ) {
            return true;
        }

        if ( absint( $listing->post_author ) === get_current_user_id() ) {
            return true;
        }

        return false;
    }

    /**
     * Checks whether the current user is allowed to manage a listing.
     *
     * Logged-in users must be the listing owner or a moderator.
     * Non-logged-in users can manage listings while creating one (auto-draft)
     * or while editing one using a valid edit nonce.
     *
     * @since 4.4.5
     *
     * @param object $listing An instance of WP_Post.
     *
     * @return bool
     */
    public function is_current_user_allowed_to_manage_listing( $listing ) {
        if ( is_user_logged_in() ) {
            return $this->is_current_user_allowed_to_edit_listing( $listing );
        }

        if ( 'auto-draft' === $listing->post_status ) {
            return true;
        }

        return $this->request_includes_valid_edit_nonce( $listing );
    }

    /**
     * Checks whether the current request includes a valid edit nonce.
     *
     * @since 4.4.5
     *
     * @param object $listing An instance of WP_Post.
     *
     * @return bool
     */
    private function request_includes_valid_edit_nonce( $listing ) {
        $nonce  = awpcp_get_var( array( 'param' => 'edit_nonce' ) );
        $nonce  = $this->request->post( 'edit_nonce', $nonce );
        $action = "awpcp-edit-listing-{$listing->ID}";

        return wp_verify_nonce( $nonce, $action );
    }

    /**
     * Determine whether current user can edit the start date of the listing.
     *
     * See https://github.com/drodenbaugh/awpcp/issues/1906#issuecomment-328189213
     * for a description of the editable start date feature.
     *
     * @since 4.0.0
     *
     * @param object $listing An instance of WP_Post.
     */
    public function is_current_user_allowed_to_edit_listing_start_date( $listing ) {
        if ( $this->roles->current_user_is_moderator() ) {
            return true;
        }

        if ( ! $this->settings->get_option( 'allow-start-date-modification' ) ) {
            return false;
        }

        $start_date = $this->listing_renderer->get_start_date( $listing );
        if ( empty( $start_date ) ) {
            return true;
        }

        $is_future_date    = strtotime( $start_date ) > current_time( 'timestamp' );
        $is_create_listing = $this->request->post( 'mode' ) === 'create';
        $is_edit_listing   = $this->request->post( 'mode' ) === 'edit';
        if ( ! $is_future_date && $is_create_listing ) {
            return true;
        }

        if ( ! $is_future_date && $is_edit_listing ) {
            return false;
        }

        if ( $is_future_date ) {
            return true;
        }

        if ( $this->request->post( 'action' ) === 'awpcp_save_listing_information' ) {
            return true;
        }

        return false;
    }

    /**
     * @param object $listing   An instance of WP_Post.
     * @since 4.0.0
     */
    public function is_current_user_allowed_to_edit_listing_end_date( $listing ) {
        return $this->roles->current_user_is_moderator();
    }
}
