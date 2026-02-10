<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



function awpcp_plugin_integrations() {
    return new AWPCP_Plugin_Integrations();
}

class AWPCP_Plugin_Integrations {

    private $plugin_integrations = array();

    public function add_plugin_integration( $plugin, $constructor ) {
        $this->plugin_integrations[ $plugin ] = $constructor;
    }

    public function maybe_enable_plugin_integration( $plugin, $network_wide ) {
        if ( isset( $this->plugin_integrations[ $plugin ] ) ) {
            $this->enable_plugin_integration( $plugin );
        }
    }

    private function enable_plugin_integration( $plugin ) {
        $enabled_integrations = $this->get_enabled_plugin_integrations();

        if ( ! in_array( $plugin, $enabled_integrations, true ) ) {
            array_push( $enabled_integrations, $plugin );
            update_option( 'awpcp_plugin_integrations', $enabled_integrations );
        }
    }

    public function get_enabled_plugin_integrations() {
        $integrations = get_option( 'awpcp_plugin_integrations', array() );

        return is_array( $integrations ) ? $integrations : array();
    }

    public function maybe_disable_plugin_integration( $plugin ) {
        $enabled_integrations = $this->get_enabled_plugin_integrations();

        $key = array_search( $plugin, $enabled_integrations, true );

        if ( false !== $key && null !== $key ) {
            unset( $enabled_integrations[ $key ] );
            update_option( 'awpcp_plugin_integrations', $enabled_integrations );
        }
    }

    public function load_plugin_integrations() {
        foreach ( $this->get_enabled_plugin_integrations() as $plugin ) {
            if ( ! isset( $this->plugin_integrations[ $plugin ] ) ) {
                continue;
            }

            if ( ! is_callable( $this->plugin_integrations[ $plugin ] ) ) {
                continue;
            }

            $integration = call_user_func( $this->plugin_integrations[ $plugin ] );
            $integration->load();
        }
    }

    public function discover_supported_plugin_integrations() {
        delete_option( 'awpcp_plugin_integrations' );

        $active_plugins = get_option( 'active_plugins', array() );
        $active_plugins = is_array( $active_plugins ) ? $active_plugins : array();

        foreach ( $active_plugins as $plugin ) {
            $this->maybe_enable_plugin_integration( $plugin, false );
        }

        $sitewide_plugins = get_option( 'active_sitewide_plugins', array() );
        $sitewide_plugins = is_array( $sitewide_plugins ) ? $sitewide_plugins : array();

        foreach ( $sitewide_plugins as $plugin ) {
            $this->maybe_enable_plugin_integration( $plugin, true );
        }
    }
}
