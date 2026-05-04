<?php
/**
 * @package AWPCP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// phpcs:disable WordPress.DB.DirectDatabaseQuery,WordPress.DB.SlowDBQuery -- Direct DB access intentional for installer/upgrade/legacy collection code; caching not applicable to write/migration paths or rich listing queries.
/**
 * Count listings in the database.
 *
 * @since 1.8.9.4
 * @deprecated 4.0.0 awpcp_listings_collection()->count_enabled_listings()
 *                   awpcp_listings_collection()->count_disabled_listings()
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function countlistings( $is_active ) {
    if ( $is_active ) {
        _deprecated_function( __FUNCTION__, '4.0.0', 'awpcp_listings_collection()->count_enabled_listings()' );

        return awpcp_listings_collection()->count_enabled_listings();
    }

    _deprecated_function( __FUNCTION__, '4.0.0', 'awpcp_listings_collection()->count_disabled_listings()' );

    return awpcp_listings_collection()->count_disabled_listings();
}

/**
 * Read a single column from the legacy awpcp_adsettings table.
 *
 * @since 4.4.6
 *
 * @param string $column Column name to read.
 * @param string $option Option key.
 * @return mixed
 */
function awpcp_get_legacy_setting( $column, $option ) {
    global $wpdb;

    $tbl_ad_settings = $wpdb->prefix . 'awpcp_adsettings';

    if ( ! awpcp_table_exists( $tbl_ad_settings ) ) {
        return 0;
    }

    $res = $wpdb->get_var(
        $wpdb->prepare(
            'SELECT %i FROM %i WHERE config_option=%s',
            $column,
            $tbl_ad_settings,
            $option
        )
    );

    return stripslashes_deep( $res );
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_get_legacy_setting()} instead.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function get_awpcp_setting( $column, $option ) {
    return awpcp_get_legacy_setting( $column, $option );
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_get_legacy_setting()} with `config_group_id` instead.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function get_awpcp_option_group_id( $option ) {
    return awpcp_get_legacy_setting( 'config_group_id', $option );
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_get_legacy_setting()} with `option_type` instead.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function get_awpcp_option_type( $option ) {
    return awpcp_get_legacy_setting( 'option_type', $option );
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_get_legacy_setting()} with `config_diz` instead.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function get_awpcp_option_config_diz( $option ) {
    return awpcp_get_legacy_setting( 'config_diz', $option );
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_current_user_is_admin()} instead. Runtime
 *             deprecation notice intentionally omitted to avoid flooding
 *             the debug log from legacy templates that still depend on it.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function checkifisadmin() {
    return awpcp_current_user_is_admin() ? 1 : 0;
}

/**
 * Returns true when the given table contains zero rows.
 *
 * @since 4.4.6
 *
 * @param string $table Fully qualified table name.
 * @return bool
 */
function awpcp_is_table_empty( $table ) {
    global $wpdb;

    $results = $wpdb->get_var(
        $wpdb->prepare(
            'SELECT COUNT(*) FROM %i',
            $table
        )
    );

    return false !== $results && 0 === intval( $results );
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_is_table_empty()} instead. Runtime
 *             deprecation notice intentionally omitted to avoid
 *             flooding the debug log from legacy callers.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function awpcpistableempty( $table ) {
    return awpcp_is_table_empty( $table );
}

/**
 * @since 1.0
 * @deprecated 4.0.0
 *
 * TODO: Re-enable _deprecated_function() once first-party add-ons stop
 *       calling this wrapper directly. The runtime notice was suppressed
 *       to avoid flooding debug logs in production.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function awpcpisqueryempty( $table, $where ) {
    return null;
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_is_table_empty()} on AWPCP_TABLE_ADFEES
 *             instead. Runtime deprecation notice intentionally omitted
 *             to avoid flooding the debug log from legacy templates that
 *             still depend on it.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function adtermsset() {
    return ! awpcp_is_table_empty( AWPCP_TABLE_ADFEES );
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_categories_collection()} instead.
 *             Runtime deprecation notice intentionally omitted to avoid
 *             flooding the debug log from legacy templates that still
 *             depend on it.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function categoriesexist() {
    return count( awpcp_categories_collection()->find_categories() ) > 0;
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_categories_collection()->count_categories()}
 *             instead. Runtime deprecation notice intentionally omitted
 *             to avoid flooding the debug log from legacy templates that
 *             still depend on it.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function countcategories() {
    return awpcp_categories_collection()->count_categories();
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_categories_collection()->count_categories()}
 *             instead. Runtime deprecation notice intentionally omitted
 *             to avoid flooding the debug log from legacy templates that
 *             still depend on it.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function countcategoriesparents() {
    $all_categories_count       = awpcp_categories_collection()->count_categories();
    $childless_categories_count = awpcp_categories_collection()->count_categories( array( 'childless' => true ) );

    if ( $all_categories_count === $childless_categories_count ) {
        return 0;
    }

    return $all_categories_count - $childless_categories_count;
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_categories_collection()->count_categories()}
 *             instead. Runtime deprecation notice intentionally omitted
 *             to avoid flooding the debug log from legacy templates that
 *             still depend on it.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function countcategorieschildren() {
    $childless_categories_count = awpcp_categories_collection()->count_categories( array( 'childless' => true ) );

    if ( awpcp_categories_collection()->count_categories() === $childless_categories_count ) {
        return 0;
    }

    return $childless_categories_count;
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_listing_renderer()->get_contact_email()}
 *             instead. Runtime deprecation notice intentionally omitted
 *             to avoid flooding the debug log from legacy templates.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function get_adposteremail( $adid ) {
    try {
        $listing = awpcp_listings_collection()->get( $adid );
    } catch ( AWPCP_Exception $e ) {
        return null;
    }

    return awpcp_listing_renderer()->get_contact_email( $listing );
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_listing_renderer()->get_plain_start_date()}
 *             instead. Runtime deprecation notice intentionally omitted
 *             to avoid flooding the debug log; first-party add-ons still
 *             call this wrapper. TODO: Re-enable _deprecated_function()
 *             once those callers have been migrated.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function get_adstartdate( $adid ) {
    try {
        $listing = awpcp_listings_collection()->get( $adid );
    } catch ( AWPCP_Exception $e ) {
        return null;
    }

    return awpcp_listing_renderer()->get_plain_start_date( $listing );
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_listing_renderer()->get_views_count()}
 *             instead. Runtime deprecation notice intentionally omitted
 *             to avoid flooding the debug log from legacy templates.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function get_numtimesadviewd( $adid ) {
    try {
        $listing = awpcp_listings_collection()->get( $adid );
    } catch ( AWPCP_Exception $e ) {
        return null;
    }

    return awpcp_listing_renderer()->get_views_count( $listing );
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_listing_renderer()->get_listing_title()}
 *             instead. Runtime deprecation notice intentionally omitted
 *             to avoid flooding the debug log; first-party add-ons still
 *             call this wrapper. TODO: Re-enable _deprecated_function()
 *             once those callers have been migrated.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function get_adtitle( $adid ) {
    try {
        $listing = awpcp_listings_collection()->get( $adid );
    } catch ( AWPCP_Exception $e ) {
        return null;
    }

    return awpcp_listing_renderer()->get_listing_title( $listing );
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_categories_collection()->find_categories()}
 *             instead. Runtime deprecation notice intentionally omitted
 *             to avoid flooding the debug log from legacy templates.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function get_categorynameid( $cat_id = 0, $cat_parent_id = 0, $exclude = array() ) {
    $parent_categories = awpcp_categories_collection()->find_categories( array(
        'fields'     => 'id=>name',
        'parent'     => 0,
        // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Not a get_posts() call; this is the AWPCP categories collection where the exclude parameter operates on the awpcp-listing-category taxonomy.
        'exclude'    => $exclude,
        'hide_empty' => false,
    ) );

    $params = array(
        'current-value' => $cat_parent_id,
        'options'       => $parent_categories,
    );

    return awpcp_html_options( $params );
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_categories_collection()->get()} instead.
 *             Runtime deprecation notice intentionally omitted to avoid
 *             flooding the debug log from legacy templates.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function get_adcatname( $cat_id ) {
    try {
        $category = awpcp_categories_collection()->get( $cat_id );
        return stripslashes_deep( $category->name );
    } catch ( AWPCP_Exception $e ) {
        return null;
    }
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_categories_collection()->get()} instead.
 *             Runtime deprecation notice intentionally omitted to avoid
 *             flooding the debug log from legacy templates.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function get_adparentcatname( $cat_id ) {
    if ( 0 === $cat_id ) {
        return __( 'Top Level Category', 'another-wordpress-classifieds-plugin' );
    }

    try {
        $category = awpcp_categories_collection()->get( $cat_id );
        return stripslashes_deep( $category->name );
    } catch ( AWPCP_Exception $e ) {
        return null;
    }
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_categories_collection()->get()} instead.
 *             Runtime deprecation notice intentionally omitted to avoid
 *             flooding the debug log; first-party add-ons still call
 *             this wrapper. TODO: Re-enable _deprecated_function() once
 *             those callers have been migrated.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound,WordPress.NamingConventions.ValidFunctionName -- Deprecated alias kept for backwards compatibility.
function get_cat_parent_ID( $cat_id ) {
    global $wpdb;

    return intval(
        $wpdb->get_var(
            $wpdb->prepare(
                'SELECT category_parent_id FROM %i WHERE category_id = %d',
                AWPCP_TABLE_CATEGORIES,
                $cat_id
            )
        )
    );
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_listings_collection()->find_listings()}
 *             instead. Runtime deprecation notice intentionally omitted
 *             to avoid flooding the debug log from legacy templates.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function ads_exist_cat( $catid ) {
    $listings = awpcp_listings_collection()->find_listings(array(
        'tax_query' => array(
            array(
                'taxonomy'         => AWPCP_CATEGORY_TAXONOMY,
                'terms'            => (int) $catid,
                'include_children' => true,
            ),
        ),
    ));

    return count( $listings ) > 0;
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_categories_collection()} instead.
 *             Runtime deprecation notice intentionally omitted to avoid
 *             flooding the debug log from legacy templates.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function category_has_children( $catid ) {
    global $wpdb;

    $tbl_categories = $wpdb->prefix . 'awpcp_categories';

    $count = $wpdb->get_var(
        $wpdb->prepare(
            'SELECT COUNT(*) FROM %i WHERE category_parent_id = %d',
            $tbl_categories,
            $catid
        )
    );

    return $count && intval( $count ) > 0;
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_categories_collection()->get()} instead.
 *             Runtime deprecation notice intentionally omitted to avoid
 *             flooding the debug log from legacy templates.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function category_is_child( $catid ) {
    try {
        $category = awpcp_categories_collection()->get( $catid );
    } catch ( AWPCP_Exception $e ) {
        return false;
    }

    return 0 !== $category->parent;
}

/**
 * Originally developed by Dan Caragea.
 * Permission is hereby granted to AWPCP to release this code
 * under the license terms of GPL2
 * @author Dan Caragea
 * @link http://datemill.com
 *
 * @since 1.0
 * @deprecated 4.4.6 No replacement; templating handled by the modern Listings layer.
 *             Runtime deprecation notice intentionally omitted to avoid
 *             flooding the debug log; first-party add-ons still call
 *             this wrapper. TODO: Re-enable _deprecated_function() once
 *             those callers have been migrated.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function smart_table( $array, $table_cols, $opentable, $closetable ) {
    $usingtable = ! empty( $opentable ) && ! empty( $closetable );

    return awpcp_legacy_smart_table( $array, $table_cols, $opentable, $closetable, $usingtable );
}

/**
 * @since 1.0
 * @deprecated 4.4.6 Use {@see awpcp_legacy_smart_table()} instead. Runtime
 *             deprecation notice intentionally omitted to avoid flooding
 *             the debug log from legacy admin templates.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
function smart_table2( $array, $table_cols, $opentable, $closetable, $usingtable ) {
    return awpcp_legacy_smart_table( $array, $table_cols, $opentable, $closetable, $usingtable );
}

/**
 * Generates a simple HTML table from a flat array.
 *
 * Preserved verbatim from the original smart_table2() implementation
 * because legacy templates may still rely on its exact markup.
 *
 * @since 4.4.6
 */
function awpcp_legacy_smart_table( $array, $table_cols, $opentable, $closetable, $usingtable ) {
    $myreturn                = "$opentable\n";
    $row                     = 0;
    $i                       = 1;
    $awpcpdisplayaditemclass = '';

    foreach ( $array as $v ) {
        if ( 0 === $i % 2 ) {
            $awpcpdisplayaditemclass = 'displayaditemsodd';
        } else {
            $awpcpdisplayaditemclass = 'displayaditemseven';
        }

        $v = str_replace( '$awpcpdisplayaditems', $awpcpdisplayaditemclass, $v );

        if ( 0 === ( $i - 1 ) % $table_cols ) {
            if ( $usingtable ) {
                $myreturn .= "<tr>\n";
            }

            ++$row;
        }
        if ( $usingtable ) {
            $myreturn .= "\t<td valign=\"top\">";
        }
        $myreturn .= "$v";
        if ( $usingtable ) {
            $myreturn .= "</td>\n";
        }
        if ( 0 === $i % $table_cols ) {
            if ( $usingtable ) {
                $myreturn .= "</tr>\n";
            }
        }
        ++$i;
    }
    $rest = ( $i - 1 ) % $table_cols;
    if ( 0 !== $rest ) {
        $colspan   = $table_cols - $rest;
        $myreturn .= "\t<td" . ( 1 === $colspan ? '' : ' colspan="' . esc_attr( $colspan ) . '"' ) . "></td>\n</tr>\n";
    }
    $myreturn .= "$closetable\n";

    return $myreturn;
}

/**
 * Displays a message explaining that the XML Sitemap module is no longer
 * available and users should install or configure a SEO or XML Sitemap plugin
 * that supports Custom Post Types.
 *
 * @since 4.0.0
 * @deprecated 4.2
 */
function awpcp_xml_sitemap_module_removed_notice() {
    _deprecated_function( __FUNCTION__, '4.2' );

    return '';
}
