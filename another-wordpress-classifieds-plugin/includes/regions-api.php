<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



function awpcp_basic_regions_api() {
    static $instance = null;

    if ( is_null( $instance ) ) {
        $instance = new AWPCP_BasicRegionsAPI( $GLOBALS['wpdb'] );
    }

    return $instance;
}

class AWPCP_BasicRegionsAPI {

    private $db;

    public function __construct( $db ) {
        $this->db = $db;
    }

    /**
     * TODO: trigger an exception on SQL errors
     */
    public function find_by_type($type) {
        _deprecated_function( __METHOD__, '4.2' );
        // column are named after the type of the reigon
        $sql = 'SELECT DISTINCT `%s` FROM ' . AWPCP_TABLE_AD_REGIONS;

        $rows = $this->db->get_col( sprintf( $sql, $type ) );

        return false !== $rows ? $rows : array();
    }

    public function find_by_parent_name($parent_name, $parent_type, $type) {
        _deprecated_function( __METHOD__, '4.2' );
        $sql  = 'SELECT DISTINCT `%s` FROM ' . AWPCP_TABLE_AD_REGIONS . ' AS r1 INNER JOIN ( ';
        $sql .= '    SELECT id FROM ' . AWPCP_TABLE_AD_REGIONS . ' WHERE `%s` = %%s';
        $sql .= ') AS r2 ON ( r1.id = r2.id )';

        $sql = sprintf( $sql, $type, $parent_type );

        return $this->db->get_col( $this->db->prepare( $sql, $parent_name ) );
    }

    public function save( $region ) {
        $region = $this->filter_region_columns( stripslashes_deep( $region ) );

        $region['ad_id'] = absint( isset( $region['ad_id'] ) ? $region['ad_id'] : 0 );

        if ( empty( $region['ad_id'] ) ) {
            return false;
        }

        $region_id = intval( awpcp_array_data( 'id', 0, $region ) );

        unset( $region['id'] );

        if ( $region_id > 0 ) {
            $result = $this->db->update( AWPCP_TABLE_AD_REGIONS, $region, array( 'id' => $region_id ) );
        } else {
            $result = $this->db->insert( AWPCP_TABLE_AD_REGIONS, $region );
        }

        return $result !== false;
    }

    /**
     * Allowlist region array keys to valid DB columns only.
     *
     * @since 4.4.8
     *
     * @param mixed $region Region data.
     *
     * @return array
     */
    private function filter_region_columns( $region ) {
        if ( ! is_array( $region ) ) {
            return array();
        }

        $allowed = array( 'id', 'ad_id', 'country', 'county', 'state', 'city', 'region_id' );

        return array_intersect_key( $region, array_flip( $allowed ) );
    }

    /**
     * Sanitise a user-submitted region to editable fields only.
     *
     * @since 4.4.8
     *
     * @param mixed $region Region data.
     *
     * @return array
     */
    private function prepare_submitted_region( $region ) {
        if ( ! is_array( $region ) ) {
            return array();
        }

        $allowed = array( 'country', 'county', 'state', 'city', 'region_id' );
        $region  = array_intersect_key( $region, array_flip( $allowed ) );

        $region = array_filter( $region, 'is_scalar' );

        return array_map( 'trim', $region );
    }

    public function delete_by_ad_id($ad_id) {
        $result = $this->db->query( $this->db->prepare( "DELETE FROM " . AWPCP_TABLE_AD_REGIONS . " WHERE ad_id = %s", $ad_id ) );
        return $result !== false;
    }

    /**
     * @return object|null
     */
    public function find_by_ad_id($ad_id) {
        $sql  = 'SELECT * FROM ' . AWPCP_TABLE_AD_REGIONS . ' WHERE ad_id = %d ';
        $sql .= 'ORDER BY id ASC';

        return $this->db->get_results( $this->db->prepare( $sql, $ad_id ) );
    }

    public function update_ad_regions( $ad, $regions, $max_regions = 1 ) {
        $this->delete_by_ad_id( $ad->ID );

        $count = 0;

        foreach ( $regions as $region ) {
            $data = $this->prepare_submitted_region( $region );

            if ( empty( implode( $data ) ) ) {
                continue;
            }

            if ( $count < $max_regions ) {
                $this->save( array_merge( $data, array( 'ad_id' => $ad->ID ) ) );
            }

            ++$count;
        }
    }
}
