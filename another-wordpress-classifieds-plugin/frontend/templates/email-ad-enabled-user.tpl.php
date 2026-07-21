<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// Emails are sent in plain text, blank lines are required for proper formatting.
printf(
    // translators: %s is the contact name.
    awpcp_esc_plaintext( __( 'Hello %s,', 'another-wordpress-classifieds-plugin' ) ),
    awpcp_esc_plaintext( $contact_name )
);
echo PHP_EOL . PHP_EOL;

printf(
    // translators: %1$s is the listing title, %2$s is the listing URL.
    awpcp_esc_plaintext( __( 'Your Ad "%1$s" was recently approved by the admin. You should be able to see the Ad published here: %2$s.', 'another-wordpress-classifieds-plugin' ) ),
    awpcp_esc_plaintext( $listing_title ),
    esc_url_raw( get_permalink( $listing->ID ) )
);
echo PHP_EOL . PHP_EOL;

echo awpcp_esc_plaintext( awpcp_get_blog_name() ) . PHP_EOL;
echo esc_url_raw( home_url() );
