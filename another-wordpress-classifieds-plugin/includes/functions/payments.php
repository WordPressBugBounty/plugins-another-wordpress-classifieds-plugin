<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Verify data received from PayPal IPN notifications and returns PayPal's
 * response.
 *
 * PayPal requires byte-for-byte verification, so we read the raw POST body
 * from php://input and send it back exactly as received, prepended with
 * cmd=_notify-validate.
 *
 * Request errors, if any, are returned by reference.
 *
 * @since 2.0.7
 *
 * @param array $data   Deprecated. No longer used. Raw body is read from php://input.
 * @param array $errors Request errors, returned by reference.
 * @return string VERIFIED, INVALID or ERROR.
 */
function awpcp_paypal_verify_received_data( $data = array(), &$errors = array() ) {
    // Read the raw POST body exactly as PayPal sent it.
    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
    $raw_post_body = file_get_contents( 'php://input' );

    // If no raw body, this might be a connectivity test from the debug page.
    if ( empty( $raw_post_body ) ) {
        return awpcp_paypal_test_connection( $errors );
    }

    // Prepend the validation command to the exact bytes received.
    $content = 'cmd=_notify-validate&' . $raw_post_body;

    // Use WordPress HTTP API for verification requests.
    return awpcp_paypal_verify_received_data_with_wp_http( $content, $errors );
}

/**
 * Test PayPal IPN endpoint connectivity.
 *
 * Sends a minimal request to PayPal's IPN endpoint to verify the server
 * can communicate with PayPal. Used by the debug page.
 *
 * @since 4.4.4
 *
 * @param array $errors Request errors, returned by reference.
 * @return string INVALID if connection works (expected response), ERROR otherwise.
 */
function awpcp_paypal_test_connection( &$errors = array() ) {
    // Send a minimal validation request to test connectivity.
    // PayPal will return INVALID since there's no real transaction, but that confirms connectivity.
    $content = 'cmd=_notify-validate';

    return awpcp_paypal_verify_received_data_with_wp_http( $content, $errors );
}

/**
 * Verify data received from PayPal IPN using the WordPress HTTP API.
 *
 * This function was added to replace the legacy functions
 * awpcp_paypal_verify_received_data_with_curl() and
 * awpcp_paypal_verify_received_data_with_fsockopen().
 *
 * @since 4.4.4
 *
 * @param string $postfields IPN request payload.
 * @param array  $errors     Request errors, returned by reference.
 * @return string VERIFIED, INVALID or ERROR.
 */
function awpcp_paypal_verify_received_data_with_wp_http( $postfields = '', &$errors = array() ) {
    $is_test_mode_enabled = intval( get_awpcp_option( 'paylivetestmode' ) ) === 1;

    $paypal_url = $is_test_mode_enabled
        ? 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr'
        : 'https://ipnpb.paypal.com/cgi-bin/webscr';

    $args = array(
        'method'      => 'POST',
        'timeout'     => 30,
        'redirection' => 5,
        'httpversion' => '1.1',
        'blocking'    => true,
        'headers'     => array(
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Connection'   => 'close',
        ),
        'body'        => $postfields,
        'cookies'     => array(),
        'sslverify'   => true,
    );

    $response = wp_remote_post( $paypal_url, $args );

    if ( is_wp_error( $response ) ) {
        $errors = array_merge( $errors, $response->get_error_messages() );
        return 'ERROR';
    }

    $response_code = wp_remote_retrieve_response_code( $response );
    if ( 200 !== $response_code ) {
        $errors[] = sprintf( 'HTTP %d: %s', $response_code, wp_remote_retrieve_response_message( $response ) );
        return 'ERROR';
    }

    $response_body = wp_remote_retrieve_body( $response );
    $response_body = trim( $response_body );

    if ( in_array( $response_body, array( 'VERIFIED', 'INVALID' ), true ) ) {
        return $response_body;
    }

    return 'ERROR';
}

/**
 * Validate the data received from PayFast.
 *
 * @since 3.7.8
 */
function awpcp_payfast_verify_received_data( $data = array() ) {
    $content = '';

    foreach ( $data as $key => $value ) {
        if ( $key === 'signature' ) {
            continue;
        }

        $content .= $key . '=' . urlencode( stripslashes( $value ) ) . '&';
    }

    $content = rtrim( $content, '&' );
    return awpcp_payfast_verify_received_data_with_wp_http( $content );
}

/**
 * This function was added to replace the legacy functions awpcp_payfast_verify_received_data_with_curl() and awpcp_payfast_verify_received_data_with_fsockopen().
 *
 * @since 4.4
 *
 * @return string 'VALID', 'INVALID' or 'ERROR'
 */
function awpcp_payfast_verify_received_data_with_wp_http( $content ) {
    if ( get_awpcp_option( 'paylivetestmode' ) ) {
        $host = 'sandbox.payfast.co.za';
    } else {
        $host = 'www.payfast.co.za';
    }

    $url = 'https://' . $host . '/eng/query/validate';

    $args = array(
        'method'      => 'POST',
        'timeout'     => 30,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking'    => true,
        'headers'     => array(
            'Content-Type' => 'application/x-www-form-urlencoded',
            'User-Agent'   => 'Another WordPress Classifieds Plugin',
        ),
        'body'        => $content,
        'cookies'     => array(),
        'sslverify'   => true,
    );

    $response = wp_remote_post( $url, $args );

    if ( is_wp_error( $response ) ) {
        return 'ERROR';
    }

    $response_code = wp_remote_retrieve_response_code( $response );
    if ( 200 !== $response_code ) {
        return 'ERROR';
    }

    $response_body = wp_remote_retrieve_body( $response );
    $response_body = trim( $response_body );

    if ( in_array( $response_body, array( 'VALID', 'INVALID' ), true ) ) {
        return $response_body;
    } else {
        return 'ERROR';
    }
}

/**
 * email the administrator and the user to notify that the payment process was failed
 * @since  2.1.4
 */
function awpcp_payment_failed_email($transaction, $message='') {
    // $message parameter is kept for backward compatibility but not currently used
    $user = get_userdata($transaction->user_id);

    // user email

    $mail           = new AWPCP_Email();
    $mail->to[]     = awpcp_format_recipient_address( $user->user_email, $user->display_name );
    $mail->subject  = get_awpcp_option('paymentabortedsubjectline');

    $template = AWPCP_DIR . '/frontend/templates/email-abort-payment-user.tpl.php';
    $mail->prepare($template, compact('message', 'user', 'transaction'));

    $mail->send();

    // admin email

    $mail           = new AWPCP_Email();
    $mail->to[]     = awpcp_admin_email_to();
    $mail->subject  = __( 'Customer attempt to pay has failed', 'another-wordpress-classifieds-plugin');

    $template = AWPCP_DIR . '/frontend/templates/email-abort-payment-admin.tpl.php';
    $mail->prepare($template, compact('message', 'user', 'transaction'));

    $mail->send();
}

function awpcp_paypal_supported_currencies() {
    return array(
        'AUD', 'BRL', 'CAD', 'CZK', 'DKK', 'EUR', 'HKD', 'HUF', 'ILS', 'JPY', 'MYR',
        'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'GBP', 'RUB', 'SGD', 'SEK', 'CHF', 'TWD',
        'THB', 'TRY', 'USD',
    );
}

function awpcp_paypal_supports_currency( $currency_code ) {
    $currency_codes = awpcp_paypal_supported_currencies();

    if ( ! in_array( $currency_code, $currency_codes, true ) ) {
        return false;
    }

    return true;
}
