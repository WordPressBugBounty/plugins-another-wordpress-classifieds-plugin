<?php
/**
 * @package AWPCP\Settings\Renderers
 */

/**
 * @since 4.0.0
 */
class AWPCP_LicenseSettingsRenderer {

    /**
     * @var AWPCP_LicensesManager
     */
    private $licenses_manager;

    /**
     * @var AWPCP_Settings_API
     */
    private $settings;

    /**
     * @since 4.0.0
     */
    public function __construct( $licenses_manager, $settings ) {
        $this->licenses_manager = $licenses_manager;
        $this->settings         = $settings;
    }

    /**
     * @since 4.0.0
     */
    public function render_setting( $setting ) {
        $module_name = $setting['params']['module_name'];
        $module_slug = $setting['params']['module_slug'];

        $license = $this->settings->get_option( $setting['id'] );

        echo '<input id="' . esc_attr( $setting['id'] ) . '" class="regular-text" type="text" name="awpcp-options[' . esc_attr( $setting['id'] ) . ']" value="' . esc_attr( $license ) . '">';

        if ( ! empty( $license ) ) {
            if ( $this->licenses_manager->is_license_valid( $module_name, $module_slug ) ) {
                echo '<input class="button-secondary" type="submit" name="awpcp-deactivate-' . esc_attr( $module_slug ) . '-license" value="' . esc_attr__( 'Deactivate', 'another-wordpress-classifieds-plugin' ) . '"/>';
                echo '<br>';
                $this->show_status( 'valid' );
            } elseif ( $this->licenses_manager->is_license_inactive( $module_name, $module_slug ) ) {
                echo '<input class="button-secondary" type="submit" name="awpcp-activate-' . esc_attr( $module_slug ) . '-license" value="' . esc_attr__( 'Activate', 'another-wordpress-classifieds-plugin' ) . '"/>';
                echo '<br>';
                $this->show_status( 'inactive' );
            } else {
                echo '<input class="button-secondary" type="submit" name="awpcp-activate-' . esc_attr( $module_slug ) . '-license" value="' . esc_attr__( 'Activate', 'another-wordpress-classifieds-plugin' ) . '"/>';

                echo '<br>';
                printf(
                    /* translators: %1$s is the opening anchor tag, %2$s is the closing anchor tag */
                    esc_html__( 'Click the button above to check the status of your license. Please %1$scontact customer support%2$s if you think the reported status is wrong.', 'another-wordpress-classifieds-plugin' ),
                    '<a href="https://awpcp.com/contact" target="_blank">',
                    '</a>'
                );

                echo '<br>';
                if ( $this->licenses_manager->is_license_expired( $module_name, $module_slug ) ) {
                    $this->show_status( 'expired' );
                } elseif ( $this->licenses_manager->is_license_disabled( $module_name, $module_slug ) ) {
                    $this->show_status( 'disabled' );
                } else {
                    $this->show_status( 'unknown' );
                }
            }
            wp_nonce_field( 'awpcp-update-license-status-nonce', 'awpcp-update-license-status-nonce' );
        }
    }

    private function show_status( $status ) {
        $labels = [
            'disabled' => __( 'disabled', 'another-wordpress-classifieds-plugin' ),
            'inactive' => __( 'inactive', 'another-wordpress-classifieds-plugin' ),
            'valid'    => __( 'active', 'another-wordpress-classifieds-plugin' ),
            'expired'  => __( 'expired', 'another-wordpress-classifieds-plugin' ),
            'unknown'  => __( 'unknown', 'another-wordpress-classifieds-plugin' ),
        ];
        $classes = [ 'inactive', 'valid', 'expired' ];
        $class   = in_array( $status, $classes, true ) ? $status : 'invalid';
        printf(
            esc_html__( 'Status: %s', 'another-wordpress-classifieds-plugin' ),
            '<span class="awpcp-license-status awpcp-license-' . esc_attr( $status ) . '">' .
                esc_html( $labels[ $status ] ) .
                '</span>.'
        );
    }
}
