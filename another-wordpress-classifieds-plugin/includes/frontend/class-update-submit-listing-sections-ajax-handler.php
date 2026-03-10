<?php
/**
 * @package AWPCP\Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Ajax handler for the action that retrieves up to date versions of the specified
 * submit listing sections.
 */
class AWPCP_UpdateSubmitListingSectionsAjaxHandler extends AWPCP_AjaxHandler {

    /**
     * @var AWPCP_SubmitLisitngSectionsGenerator
     */
    private $sections_generator;

    /**
     * @var AWPCP_ListingsCollection
     */
    private $listings;

    /**
     * @var AWPCP_PaymentsAPI
     */
    private $payments;

    /**
     * @var AWPCP_ListingAuthorization
     */
    private $authorization;

    /**
     * @since 4.0.0
     *
     * @param AWPCP_SubmitLisitngSectionsGenerator $sections_generator Sections generator.
     * @param AWPCP_ListingsCollection             $listings           Listings collection.
     * @param AWPCP_PaymentsAPI                    $payments           Payments API.
     * @param AWPCP_ListingAuthorization           $authorization      Listing authorization.
     * @param object                               $response           Ajax response object.
     */
    public function __construct( $sections_generator, $listings, $payments, $authorization, $response ) {
        parent::__construct( $response );

        $this->sections_generator = $sections_generator;
        $this->listings           = $listings;
        $this->payments           = $payments;
        $this->authorization      = $authorization;
    }

    /**
     * @since 4.0.0
     */
    public function ajax() {
        $transaction  = $this->payments->get_transaction();
        $listing_id   = awpcp_get_var( array( 'param' => 'listing' ) );
        $sections_ids = awpcp_get_var( array( 'param' => 'sections' ), 'post' );
        $mode         = awpcp_get_var( array( 'param' => 'mode' ) );

        if ( 'edit' !== $mode ) {
            $mode = 'create';
        }

        try {
            $listing = $this->listings->get( $listing_id );
        } catch ( AWPCP_Exception $e ) {
            return $this->error_response( $e->getMessage() );
        }

        if ( ! $this->authorization->is_current_user_allowed_to_manage_listing( $listing ) ) {
            return $this->error_response( esc_html__( 'You are not authorized to perform this action.', 'another-wordpress-classifieds-plugin' ) );
        }

        $response = [
            'sections' => $this->sections_generator->get_sections( $sections_ids, $mode, $listing, $transaction ),
        ];

        return $this->success( $response );
    }
}
