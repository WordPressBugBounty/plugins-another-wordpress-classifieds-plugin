<?php
/**
 * @package AWPCP\Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Ajax handler for the action that saves information for the new and existing listings.
 */
class AWPCP_GenerateListingPreviewAjaxHandler extends AWPCP_AjaxHandler {

    /**
     * @var AWPCP_ListingsContentRenderer
     */
    private $listings_content_renderer;

    /**
     * @var AWPCP_ListingsCollection
     */
    private $listings;

    /**
     * @var AWPCP_ListingAuthorization
     */
    private $authorization;

    /**
     * @var AWPCP_Request
     */
    private $request;

    /**
     * @since 4.0.0
     */
    public function __construct( $listings_content_renderer, $listings, $authorization, $response, $request ) {
        parent::__construct( $response );

        $this->listings_content_renderer = $listings_content_renderer;
        $this->listings                  = $listings;
        $this->authorization             = $authorization;
        $this->request                   = $request;
    }

    /**
     * TODO: Is apply_filters( 'the_content' ) going to cause compatibility issues?
     * TODO: Do we really need to run that filter here?
     *
     * @since 4.0.0
     */
    public function ajax() {
        try {
            return $this->try_to_generate_listing_preview();
        } catch ( AWPCP_Exception $e ) {
            return $this->multiple_errors_response( $e->getMessage() );
        }
    }

    /**
     * Generates the listing preview after verifying authorization.
     *
     * @since 4.4.4
     * @throws AWPCP_Exception If authorization fails.
     */
    private function try_to_generate_listing_preview() {
        $listing_id = $this->request->post( 'ad_id' );
        $listing    = $this->listings->get( $listing_id );
        $nonce      = $this->request->post( 'nonce' );

        if ( ! wp_verify_nonce( $nonce, "awpcp-save-listing-information-{$listing->ID}" ) ) {
            throw new AWPCP_Exception( esc_html__( 'You are not authorized to perform this action.', 'another-wordpress-classifieds-plugin' ) );
        }

        if ( ! $this->is_current_user_allowed_to_preview_listing( $listing ) ) {
            throw new AWPCP_Exception( esc_html__( 'You are not authorized to perform this action.', 'another-wordpress-classifieds-plugin' ) );
        }

        $content = apply_filters( 'the_content', $listing->post_content );
        $preview = $this->listings_content_renderer->render_content_without_notices( $content, $listing );

        return $this->success( [ 'preview' => $preview ] );
    }

    /**
     * Checks whether the current user is allowed to preview the listing.
     *
     * @since 4.4.4
     *
     * @param WP_Post $listing The listing post object.
     *
     * @return bool
     */
    private function is_current_user_allowed_to_preview_listing( $listing ) {
        if ( is_user_logged_in() ) {
            return $this->authorization->is_current_user_allowed_to_edit_listing( $listing );
        }

        return 'auto-draft' === $listing->post_status;
    }
}
