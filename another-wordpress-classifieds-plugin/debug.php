<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Lightweight backtrace + variable logger used for local debugging.
 *
 * @since 4.4.6
 */
class AWPCP_Debug_Logger {

    /**
     * Singleton instance.
     *
     * @var AWPCP_Debug_Logger|null
     */
    public static $instance = null;

    protected $html;
    protected $from;
    protected $context;
    protected $root;
    protected $log;
    private $wp_filesystem;

    private function __construct() {
        $this->html    = true;
        $this->from    = true;
        $this->context = 3;

        $this->root          = realpath( getenv( 'DOCUMENT_ROOT' ) );
        $this->wp_filesystem = awpcp_get_wp_filesystem();

        $this->log = array();

        if ( is_admin() ) {
            add_action( 'admin_print_footer_scripts', array( $this, 'show' ), 100000 );
        } else {
            add_action( 'print_footer_scripts', array( $this, 'show' ), 100000 );
        }
    }

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function log( $var, $type = 'debug', $print = false, $file = false ) {
        $entry       = array(
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace -- Backtrace captured for the awpcp-debug helper output, which is opt-in.
            'backtrace' => debug_backtrace(),
            'var'       => $var,
            'type'      => $type,
        );
        $this->log[] = $entry;

        if ( $print ) {
            return $this->render( $entry );
        }

        if ( $file ) {
            return $this->write( $entry );
        }

        return true;
    }

    public function debug( $vars, $print = false, $file = false ) {
        if ( count( $vars ) > 1 ) {
            return $this->log( $vars, 'debug', $print, $file );
        }

        return $this->log( $vars[0], 'debug', $print, $file );
    }

    public function render( $entry ) {
        $var       = $entry['var'];
        $backtrace = $entry['backtrace'];

        $start = 2;
        $limit = $this->context + $start;

        $html = '<div class="' . esc_attr( $entry['type'] ) . '">';
        if ( $this->from ) {
            $items = array();
            for ( $k = $start; $k < $limit; $k++ ) {
                if ( ! isset( $backtrace[ $k ] ) || ! isset( $backtrace[ $k ]['file'] ) ) {
                    break;
                }

                $item  = '<strong>';
                $item .= esc_html( substr( str_replace( $this->root, '', $backtrace[ $k ]['file'] ), 1 ) );
                $item .= ':' . esc_html( $backtrace[ $k ]['line'] );
                $item .= ' - function <strong>' . esc_html( $backtrace[ $k + 1 ]['function'] ) . '</strong>()';
                $item .= '</strong>';

                $items[] = $item;
            }
            $html .= join( '<br/>', $items );
        }

        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r -- Used to format the variable for the awpcp-debug helper output, which is opt-in.
        $var = print_r( $var, true );
        if ( $this->html && ! empty( $var ) ) {
            $html .= "\n<pre class=\"cake-debug\" style=\"color:#000; background: #FFF\">\n";
            $html .= esc_html( $var ) . "\n</pre>\n";
        } else {
            $html .= '<br/>';
        }

        $html .= '</div>';

        return $html;
    }

    private function write( $entry ) {
        $log_file = AWPCP_DIR . '/debug.log';
        $content  = sprintf(
            "[%s] %s",
            gmdate( 'Y-m-d H:i:s' ),
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r -- Used to format the variable for the awpcp-debug helper file output, which is opt-in.
            print_r( $entry['var'], true ) . "\n"
        );

        $existing_content = '';
        if ( $this->wp_filesystem->exists( $log_file ) ) {
            $existing_content = $this->wp_filesystem->get_contents( $log_file );
        }
        $this->wp_filesystem->put_contents( $log_file, $existing_content . $content, FS_CHMOD_FILE );
    }

    public function show() {
        if ( ! get_option( 'awpcp-debug', false ) ) {
            return;
        }

        if ( empty( $this->log ) ) {
            return;
        }

        $html = '';
        foreach ( $this->log as $entry ) {
            $html .= $this->render( $entry );
        }

        echo '<div style="background:#000; color: #FFF; padding-bottom: 40px">' . wp_kses_post( $html ) . '</div>';
    }
}

/**
 * Deprecated alias for {@see AWPCP_Debug_Logger}.
 *
 * @since 4.3.3
 * @deprecated 4.4.6 Use {@see AWPCP_Debug_Logger} instead.
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedClassFound -- Deprecated alias kept for backwards compatibility.
class_alias( 'AWPCP_Debug_Logger', 'WP_Skeleton_Logger' );

/**
 * Render and echo the debug entries.
 *
 * @since 4.4.6
 */
function awpcp_debugp( $var = false ) {
    $args = func_get_args();
    echo esc_html( AWPCP_Debug_Logger::instance()->debug( $args, true ) );
}

/**
 * Append the debug entries to debug.log.
 *
 * @since 4.4.6
 */
function awpcp_debugf( $var = false ) {
    $args = func_get_args();
    return AWPCP_Debug_Logger::instance()->debug( $args, false, true );
}

/**
 * Queue debug entries to be displayed in the page footer.
 *
 * @since 4.4.6
 */
function awpcp_debug( $var = false ) {
    $args = func_get_args();
    return AWPCP_Debug_Logger::instance()->debug( $args, false );
}

// phpcs:ignore Universal.Files.SeparateFunctionsFromOO.Mixed
if ( ! function_exists( 'debug' ) ) {
    /**
     * @since 4.3.3
     * @deprecated 4.4.6 Use {@see awpcp_debugp()} instead.
     *
     * TODO: Re-enable _deprecated_function() once first-party add-ons stop
     *       calling this wrapper directly. The runtime notice was suppressed
     *       to avoid flooding debug logs in production.
     */
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
    function debugp( $var = false ) {
        $args = func_get_args();
        echo esc_html( AWPCP_Debug_Logger::instance()->debug( $args, true ) );
    }

    /**
     * @since 4.3.3
     * @deprecated 4.4.6 Use {@see awpcp_debugf()} instead.
     *
     * TODO: Re-enable _deprecated_function() once first-party add-ons stop
     *       calling this wrapper directly. The runtime notice was suppressed
     *       to avoid flooding debug logs in production.
     */
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
    function debugf( $var = false ) {
        $args = func_get_args();
        return AWPCP_Debug_Logger::instance()->debug( $args, false, true );
    }

    /**
     * @since 4.3.3
     * @deprecated 4.4.6 Use {@see awpcp_debug()} instead.
     *
     * TODO: Re-enable _deprecated_function() once first-party add-ons stop
     *       calling this wrapper directly. The runtime notice was suppressed
     *       to avoid flooding debug logs in production.
     */
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
    function debug( $var = false ) {
        $args = func_get_args();
        return AWPCP_Debug_Logger::instance()->debug( $args, false );
    }
}

if ( ! function_exists( 'kaboom' ) ) {
    /**
     * @since 4.3.3
     * @deprecated 4.3.3 Use {@see wp_die()} instead.
     */
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- Deprecated alias kept for backwards compatibility.
    function kaboom( $message = '', $title = '', $args = array() ) {
        _deprecated_function( __FUNCTION__, '4.3.3', 'wp_die' );

        // The historical opt-out token is no longer trusted; only a logged-in
        // administrator with a valid nonce should be able to short-circuit the
        // wp_die() call.
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Capability + nonce checked below before any state-changing branch is entered.
        $token = isset( $_REQUEST['_awpcp_kaboom_nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_awpcp_kaboom_nonce'] ) ) : '';

        $allow_skip = current_user_can( 'manage_options' )
            && $token
            && wp_verify_nonce( $token, 'awpcp-skip-kaboom' );

        if ( ! $allow_skip ) {
            wp_die( esc_html( $message ), esc_html( $title ) );
        }
    }
}

// how to find debug calls
// ^[^/\n]+debugp?\(
