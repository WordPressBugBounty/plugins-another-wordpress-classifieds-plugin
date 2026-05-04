<?php
/**
 * @package AWPCP\Frontend
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Math CAPTCHA provider.
 */
class AWPCP_DefaultCAPTCHAProvider implements AWPCP_CAPTCHAProviderInterface {

    /**
     * @var int
     */
    private $max_number;

    /**
     * Constructor.
     */
    public function __construct( $max_number ) {
        $this->max_number = $max_number;
    }

    /**
     * @since 4.3.3
     *
     * @return void
     */
    public function show() {
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $this->render();
    }

    /**
     * Renders the form field to enter the answer to the challenge.
     */
    public function render() {
        $left  = wp_rand( 1, $this->max_number );
        $right = wp_rand( 1, $this->max_number );

        $token  = $this->issue_challenge_token( $left + $right );
        $answer = awpcp_get_var( array( 'param' => 'captcha' ), 'post' );

        $label = sprintf(
            /* translators: the numbers that need to be added up for the math challenge. */
            _x( 'Enter the value of the following sum: %1$d + %2$d', 'CAPTCHA', 'another-wordpress-classifieds-plugin' ),
            $left,
            $right
        );

        $html  = '<label for="captcha"><span>' .
            esc_html( $label ) .
            '<span class="required">*</span>' .
            '</span></label>';
        $html .= '<input type="hidden" name="captcha-hash" value="' . esc_attr( $token ) . '" />';
        $html .= '<input id="captcha" class="awpcp-textfield inputbox required" type="text" ' .
            'name="captcha" value="' . esc_attr( $answer ) . '" size="5" autocomplete="off"/>';

        return $html;
    }

    /**
     * Validates the visitor's answer to the math challenge.
     *
     * Each challenge is bound to a per-render random token stored in a
     * transient. The token is consumed on every validation attempt
     * (correct or not) so a single token cannot be replayed or
     * brute-forced, and unlike the previous nonce-based scheme two
     * visitors never share the same token, so consuming one cannot lock
     * anyone else out of the form.
     *
     * @since 4.3.3
     * @since 4.4.6.1 Replaced the shared nonce hash with a per-challenge transient
     *            token to prevent replay and DoS against the math captcha.
     *
     * @throws AWPCP_Exception  If the answer to the challenge is not valid.
     *
     * @return bool
     */
    public function validate() {
        $answer = awpcp_get_var( array( 'param' => 'captcha' ), 'post' );
        $token  = awpcp_get_var( array( 'param' => 'captcha-hash' ), 'post' );

        if ( empty( $answer ) ) {
            $error = __( 'You did not solve the math problem. Please solve the math problem to proceed.', 'another-wordpress-classifieds-plugin' );
            throw new AWPCP_Exception( esc_html( $error ) );
        }

        $generic_error = __( 'Your solution to the math problem was incorrect. Please try again.', 'another-wordpress-classifieds-plugin' );

        if ( empty( $token ) ) {
            throw new AWPCP_Exception( esc_html( $generic_error ) );
        }

        $transient_key   = $this->get_challenge_transient_key( $token );
        $expected_answer = get_transient( $transient_key );

        if ( false === $expected_answer ) {
            throw new AWPCP_Exception( esc_html( $generic_error ) );
        }

        // Burn the token on every attempt so a wrong guess cannot be
        // brute-forced against the same challenge.
        delete_transient( $transient_key );

        if ( (string) $answer !== (string) $expected_answer ) {
            throw new AWPCP_Exception( esc_html( $generic_error ) );
        }

        return true;
    }

    /**
     * Issues a fresh per-challenge token bound to the expected answer.
     *
     * @since 4.4.6.1
     *
     * @param int $expected_answer The correct answer to the rendered math problem.
     *
     * @return string The opaque token to embed in the form.
     */
    private function issue_challenge_token( $expected_answer ) {
        $token = wp_generate_password( 32, false );

        set_transient(
            $this->get_challenge_transient_key( $token ),
            (string) (int) $expected_answer,
            DAY_IN_SECONDS
        );

        return $token;
    }

    /**
     * Builds the transient key used to store and look up a challenge answer.
     *
     * @since 4.4.6.1
     *
     * @param string $token The opaque per-challenge token.
     *
     * @return string
     */
    private function get_challenge_transient_key( $token ) {
        return 'awpcp_captcha_' . sha1( (string) $token );
    }
}
