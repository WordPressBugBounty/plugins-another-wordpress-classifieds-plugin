<?php
/**
 * @package AWPCP
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function awpcp_browse_listings_page() {
    return new AWPCP_BrowseAdsPage(
        'awpcp-browse-ads',
        __( 'Browse Ads', 'another-wordpress-classifieds-plugin' ),
        awpcp_template_renderer()
    );
}

class AWPCP_BrowseAdsPage extends AWPCP_Page {

    private $request;

    public function __construct( $page, $title, $template_renderer ) {
        parent::__construct( $page, $title, $template_renderer );

        $this->request = awpcp_request();
    }

    public function url($params=array()) {
        $url = awpcp_get_page_url('browse-ads-page-name');
        return add_query_arg( urlencode_deep( $params ), $url );
    }

    public function dispatch() {
        return $this->_dispatch();
    }

    protected function _dispatch() {
        awpcp_enqueue_select2();

        awpcp_enqueue_main_script();

        $category_id = absint( $this->request->get_category_id() );

        $output = apply_filters( 'awpcp-browse-listings-content-replacement', null, $category_id );

        if ( is_null( $output ) && $category_id ) {
            return $this->render_listings_from_category( $category_id );
        } elseif ( is_null( $output ) ) {
            return $this->render_all_listings();
        } else {
            return $output;
        }
    }

    private function render_listings_from_category( $category_id ) {
        $query = [
            'classifieds_query' => [
                'context'  => 'public-listings',
                'category' => $category_id,
            ],
            'orderby'           => get_awpcp_option( 'groupbrowseadsby' ),
        ];

        if ( $category_id == -1 ) {
            $message = __( "No specific category was selected for browsing so you are viewing listings from all categories." , 'another-wordpress-classifieds-plugin' );

            $output  = awpcp_print_message( $message );
            $output .= $this->render_listings_in_page( $query );
        } else {
            $output = $this->render_listings_in_page(
                $query,
                [
                    'before_pagination' => [
                        10 => [ 'category-title' => $this->render_category_title( $category_id ) ],
                    ],
                ]
            );
        }

        return $output;
    }

    /**
     * Renders the heading with the name of the category being browsed.
     *
     * @since 4.4.9
     *
     * @param int $category_id The ID of the category being browsed.
     *
     * @return string
     */
    private function render_category_title( $category_id ) {
        try {
            $category = awpcp_categories_collection()->get( $category_id );
        } catch ( AWPCP_Exception $e ) {
            return '';
        }

        return '<h2 class="awpcp-category-title">' . esc_html( $category->name ) . '</h2>';
    }

    /**
     * Renders the listings page for the given query.
     *
     * @since 4.4.9
     *
     * @param array $query   Query arguments used to load listings.
     * @param array $options Display options merged with the page identifier.
     *
     * @return string
     */
    protected function render_listings_in_page( $query, $options = [] ) {
        $options = array_merge(
            [ 'page' => $this->page ],
            $options
        );

        return awpcp_display_listings_in_page( $query, 'browse-listings', $options );
    }

    protected function render_all_listings() {
        $query = array(
            'classifieds_query' => [
                'context' => 'public-listings',
            ],
            'orderby'           => get_awpcp_option( 'groupbrowseadsby' ),
        );

        return $this->render_listings_in_page( $query );
    }
}
