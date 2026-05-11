<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Variables are extracted from template params (see AWPCP_Template_Renderer).
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

foreach ($messages as $message): ?>
<?php
echo awpcp_esc_plaintext( $message ); ?>

<?php endforeach; ?>

<?php echo esc_html( awpcp_get_blog_name() ); ?>
<?php
echo esc_url_raw( home_url() );
