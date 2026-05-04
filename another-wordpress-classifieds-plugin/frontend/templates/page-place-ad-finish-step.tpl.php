<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Variables are extracted from template params (see AWPCP_Template_Renderer).
/**
 * @package AWPCP\Templates
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( isset( $transaction ) && get_awpcp_option( 'show-create-listing-form-steps' ) ) {
    awpcp_listing_form_steps_componponent()->show( 'finish', compact( 'transaction' ) );
}

foreach ( (array) $messages as $message ) :
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo awpcp_print_message( $message );
endforeach;

// TODO: ContentRenderer or the rendered content itself should be available as a parameter for this view.
awpcp()->container['ListingsContentRenderer']->show_content_without_notices(
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core filter applied so other plugins/themes can format the listing body.
    apply_filters( 'the_content', $ad->post_content ),
    $ad
);
