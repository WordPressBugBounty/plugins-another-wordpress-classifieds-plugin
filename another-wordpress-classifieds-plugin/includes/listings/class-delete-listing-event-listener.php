<?php
/**
 * @package AWPCP\Listings
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * This class integrates with the events fired inside {@see wp_trash_post()}
 * and {@see wp_delete_post()} to allow the plugin and modules to react when
 * a listing is sent to Trash or permanently deleted.
 *
 * This class attemtps to follow some of the principles of a service based plugin
 * implementation, as in https://github.com/mwpd/basic-scaffold/tree/4e2d4cf
 *
 * @since 4.0.0
 */
class AWPCP_DeleteListingEventListener {

    /**
     * @var string
     */
    private $listing_post_type;

    public function __construct( $listing_post_type ) {
        $this->listing_post_type = $listing_post_type;
    }

    /**
     * Add handlers for the actions and filters of our interest.
     *
     * @since 4.0.0
     */
    public function register() {
        add_action( 'wp_trash_post', [ $this, 'before_trash_post' ] );
        add_action( 'trashed_post', [ $this, 'after_trash_post' ] );

        add_action( 'untrash_post', [ $this, 'before_untrash_post' ] );
        add_action( 'untrashed_post', [ $this, 'after_untrash_post' ] );

        add_action( 'before_delete_post', [ $this, 'before_delete_post' ] );
        add_action( 'after_delete_post', [ $this, 'after_delete_post' ], 10, 2 );
    }

    /**
     * @since 4.0.0
     */
    public function before_trash_post( $post_id ) {
        $this->maybe_do_action( 'awpcp_before_trash_ad', $post_id );
    }

    /**
     * @since 4.0.0
     */
    private function maybe_do_action( $action, $post_id ) {
        $post = get_post( $post_id );

        if ( ! isset( $post->post_type ) ) {
            return;
        }

        if ( $this->listing_post_type !== $post->post_type ) {
            return;
        }

        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- $action is always one of awpcp_before_delete_listing / awpcp_before_trash_listing (set by the caller), the prefix is statically present.
        do_action( $action, $post );
    }

    /**
     * @since 4.0.0
     */
    public function after_trash_post( $post_id ) {
        $this->maybe_do_action( 'awpcp_after_trash_ad', $post_id );
    }

    /**
     * @since 4.0.0
     */
    public function before_untrash_post( $post_id ) {
        $this->maybe_do_action( 'awpcp_before_untrash_ad', $post_id );
    }

    /**
     * @since 4.0.0
     */
    public function after_untrash_post( $post_id ) {
        $this->maybe_do_action( 'awpcp_after_untrash_ad', $post_id );
    }

    /**
     * @since 4.0.0
     */
    public function before_delete_post( $post_id ) {
        $this->maybe_do_action( 'awpcp_before_delete_ad', $post_id );
    }

    /**
     * Fires after a post is permanently deleted.
     *
     * Uses the WP_Post object passed by WordPress because get_post() returns
     * null after the post row has been removed.
     *
     * @since 4.0.0
     * @since 4.4.8 Accepts the deleted WP_Post from after_delete_post.
     *
     * @param int          $post_id Post ID.
     * @param WP_Post|null $post    Deleted post object (available since WP 5.5).
     */
    public function after_delete_post( $post_id, $post = null ) {
        if ( $post instanceof WP_Post ) {
            if ( $this->listing_post_type !== $post->post_type ) {
                return;
            }

            do_action( 'awpcp_delete_ad', $post );
            return;
        }

        $this->maybe_do_action( 'awpcp_delete_ad', $post_id );
    }
}
