<?php
/**
 * Per-IP rate-limit and redirect-validation helpers used by anonymous-facing
 * endpoints to make abuse and open-redirect attacks more expensive.
 *
 * @package AWPCP\Helpers
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Returns the remote IP address used for throttle bucket keys.
 *
 * @since 4.4.6.1
 *
 * @return string
 */
function awpcp_throttle_get_client_ip() {
    $ip = filter_input( INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP );

    if ( ! $ip && isset( $_SERVER['REMOTE_ADDR'] ) ) {
        $ip = wp_strip_all_tags( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
    }

    return $ip ? (string) $ip : 'unknown';
}

/**
 * Increments a per-IP counter for the given bucket and reports whether the
 * request exceeds the configured limit.
 *
 * @since 4.4.6.1
 *
 * @param string $bucket         Logical bucket name (for example, `reply_email`).
 *                               Passed through `sanitize_key()` and used in the
 *                               filter name and transient key.
 * @param int    $limit          Maximum number of allowed requests within the window.
 * @param int    $window_seconds Window duration in seconds.
 *
 * @return bool True when the request exceeds the limit and should be rejected.
 */
function awpcp_throttle( $bucket, $limit, $window_seconds ) {
    $bucket = sanitize_key( $bucket );

    if ( '' === $bucket ) {
        return false;
    }

    $filter_slug = 'awpcp_throttle_' . $bucket;
    $config      = apply_filters(
        $filter_slug,
        array(
            'limit'  => (int) $limit,
            'window' => (int) $window_seconds,
        )
    );

    $limit  = isset( $config['limit'] ) ? max( 0, (int) $config['limit'] ) : 0;
    $window = isset( $config['window'] ) ? max( 1, (int) $config['window'] ) : 1;

    if ( $limit <= 0 ) {
        return false;
    }

    $key   = 'awpcp_throttle_' . $bucket . '_' . sha1( awpcp_throttle_get_client_ip() );
    $count = (int) get_transient( $key );

    if ( $count >= $limit ) {
        return true;
    }

    set_transient( $key, $count + 1, $window );

    return false;
}

/**
 * Validates a user-supplied redirect URL and falls back to the AWPCP main page
 * when the destination is not on the same host.
 *
 * @since 4.4.6.1
 *
 * @param mixed $url Candidate redirect URL. Accepts any type because callers
 *                   typically forward raw request data; non-strings collapse
 *                   to the fallback URL.
 *
 * @return string A safe, same-host URL.
 */
function awpcp_validate_internal_redirect_url( $url ) {
    $url      = is_string( $url ) ? trim( $url ) : '';
    $fallback = awpcp_get_main_page_url();

    if ( empty( $fallback ) ) {
        $fallback = home_url( '/' );
    }

    if ( '' === $url ) {
        return $fallback;
    }

    $validated = wp_validate_redirect( $url, '' );

    if ( '' === $validated ) {
        return $fallback;
    }

    $validated_host = wp_parse_url( $validated, PHP_URL_HOST );

    if ( null === $validated_host || '' === $validated_host ) {
        return $validated;
    }

    $site_host = wp_parse_url( home_url(), PHP_URL_HOST );

    if ( strtolower( (string) $validated_host ) === strtolower( (string) $site_host ) ) {
        return $validated;
    }

    return $fallback;
}
