<h3><?php esc_html_e( 'Complete Payment', 'another-wordpress-classifieds-plugin' ); ?></h3>

<?php foreach ( $messages as $message ): ?>
    <?php echo awpcp_print_message( $message ); ?>
<?php endforeach ?>

<?php $payments->show_checkout_page( $transaction, $hidden ); ?>
