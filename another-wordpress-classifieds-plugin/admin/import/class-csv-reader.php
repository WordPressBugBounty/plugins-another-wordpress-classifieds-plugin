<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}



class AWPCP_CSV_Reader {

    private $file = null;

    private $path     = null;
    private $settings = array();

    private $header = null;

    private $number_of_rows            = null;
    private $current_line              = 0;
    private $number_of_lines_processed = 0;

    public function __construct( $path, $settings = array() ) {
        $this->path     = $path;
        $this->settings = wp_parse_args( $settings, array(
            'csv-separator' => ',',
        ) );
    }

    public function get_state() {
        return array(
            'path'                      => $this->path,
            'settings'                  => $this->settings,
            'header'                    => $this->header,
            'number_of_rows'            => $this->get_number_of_rows(),
            'current_line'              => $this->current_line,
            'number_of_lines_processed' => $this->number_of_lines_processed,
        );
    }

    public function get_number_of_rows() {
        if ( is_null( $this->number_of_rows ) ) {
            $this->number_of_rows = $this->get_number_of_lines();
        }

        return $this->number_of_rows;
    }

    private function get_number_of_lines() {
        $auto_detect = $this->enable_auto_detect_line_endings();

        $file = $this->get_file_object();
        $file->seek( PHP_INT_MAX );
        $last_line_number = absint( $file->key() );
        $file             = null;

        $this->restore_auto_detect_line_endings( $auto_detect );

        return $last_line_number;
    }

    public function get_current_line() {
        return $this->current_line;
    }

    public function get_header() {
        if ( is_null( $this->header ) ) {
            $this->header = $this->get_row_data( 0 );
        }

        return $this->header;
    }

    private function get_row_data( $line_number ) {
        $auto_detect = $this->enable_auto_detect_line_endings();

        $file = $this->get_file_object();

        if ( $this->current_line + 1 == $line_number ) {
            $file->next();
            $this->current_line = $line_number;
        } elseif ( $line_number != $this->current_line ) {
            $file->seek( $line_number );
            $this->current_line = $line_number;
        }

        if ( ! $file->eof() ) {
            $row_data = $file->current();
            $row_data = array_map( 'awpcp_remove_utf8_non_characters', $row_data );
            $row_data = array_map( 'awpcp_maybe_convert_to_utf8', $row_data );

            // remove unexpected quotes
            foreach ( $row_data as  $index => $column_value ) {
                $row_data[ $index ] = trim( $column_value, '"' );
            }
        } else {
            $row_data = array();
        }

        $file = null;

        $this->restore_auto_detect_line_endings( $auto_detect );

        return $row_data;
    }

    private function get_file_object() {
        if ( is_null( $this->file ) ) {
            $this->file = new SplFileObject( $this->path );
            $this->file->setFlags( SplFileObject::READ_CSV | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY );

            $this->current_line = 0;
        }

        return $this->file;
    }

    /**
     * Toggle auto_detect_line_endings only on PHP versions that still
     * support the option. PHP 8.1+ deprecated it (and removed it in 9.0)
     * because the SplFileObject CSV flags handle CR-only line endings on
     * their own once they are paired with READ_CSV / DROP_NEW_LINE /
     * READ_AHEAD / SKIP_EMPTY.
     *
     * @since 4.4.6
     *
     * @return string|null Previous value to pass back to restore(), or null when no change was made.
     */
    private function enable_auto_detect_line_endings() {
        if ( PHP_VERSION_ID >= 80100 ) {
            return null;
        }

        $previous = ini_get( 'auto_detect_line_endings' );
        // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- Required to read legacy CR-only CSV files on PHP < 8.1.
        ini_set( 'auto_detect_line_endings', true );

        return $previous;
    }

    /**
     * Restore the previous auto_detect_line_endings ini value.
     *
     * @since 4.4.6
     *
     * @param string|null $previous Value returned by enable_auto_detect_line_endings().
     */
    private function restore_auto_detect_line_endings( $previous ) {
        if ( null === $previous ) {
            return;
        }

        // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged -- Restoring the previous ini value set above on PHP < 8.1.
        ini_set( 'auto_detect_line_endings', $previous );
    }

    public function get_row( $row_number = null ) {
        $header            = $this->get_header();
        $row_data          = $this->get_row_data( $row_number );
        $filtered_row_data = array_filter( $row_data );

        if ( empty( $filtered_row_data ) ) {
            throw new UnexpectedValueException( esc_html__( 'The row was empty.', 'another-wordpress-classifieds-plugin' ) );
        } elseif ( count( $header ) !== count( $row_data ) ) {
            $message = __( "The number of values in the row (<number-of-values-in-row>) does not match the number of columns in the file's header (<number-of-columns>).", 'another-wordpress-classifieds-plugin' );
            $message = str_replace( '<number-of-values-in-row>', count( $row_data ), $message );
            $message = str_replace( '<number-of-columns>', count( $header ), $message );

            throw new UnexpectedValueException( esc_html( $message ) );
        }

        $this->number_of_lines_processed = $this->number_of_lines_processed + 1;

        return array_combine( $header, $row_data );
    }

    public function release() {
        $this->file = null;
    }
}
