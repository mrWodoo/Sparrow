<?php
/**
 * Config reader, allows you to read config
 */
namespace Sparrow;

class ConfigReader {
    /**
     * Config file
     *
     * @var string
     */
    protected $_file = '';

    /**
     * Config data
     *
     * @var array
     */
    protected $_data = [];

    /**
     * Anything modified?
     *
     * @var boolean
     */
    protected $_modified = false;

    /**
     * Last element called from __get()
     *
     * @var mixed
     */
    protected $_last = false;

    /**
     * Map for changes
     *
     * @var array
     */
    protected $_map = [];

    /**
     * Initialize config reader
     *
     * @param string $file Config file
     * @throws /Sparrow/Exception
     */
    public function __construct( $file ) {
        if( file_exists( $file ) && is_readable( $file ) && is_writeable( $file ) ) {
            $this->_file = $file;

            $config = require_once( $this->_file );

            if( is_array( $config ) ) {
                $this->_data = $config;
            }

            $this->_last = $this->_data;
        } else {
            throw new Exception( 'Cannot use config file <b>' . $file . '</b>, it\'s not reachable! Not found or not readable or not writeable!' );
        }
    }

    /**
     * Get modified
     *
     * @return boolean
     */
    public function isModfied() {
        return $this->_modified;
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFile() {
        return $this->_file;
    }

    /**
     * Get config element
     *
     * @param string $key
     * @eturn \Sparrow\ConfigReader
     */
    public function __get( $key ) {
        if( isset( $this->_last[ $key ] ) ) {
            $this->_last = $this->_last[ $key ];
        } else {
            // Element can't be accessed
            // so we make sure we return
            // false
            $this->_last = false;
        }

        $this->_map[] = $key;

        return $this;
    }

    /**
     * Get value (from last called config element)
     *
     * @return mixed
     */
    public function get() {
        $last = $this->_last;

        // Reset
        $this->_last = $this->_data;
        $this->_map = [];

        return $last;
    }

    /**
     * Set value for last accessed element
     *
     * @param mixed $value
     * @return boolean returns false if value not set
     */
    public function set( $value ) {

        if( count( $this->_map ) ) {
            $join = $this->nestedArray( $this->_map, '.', $value );


            $new = array_replace_recursive( $this->_data, $join );

            $this->_data = $new;
            $this->_modified = true;

            // Reset
            $this->_last = $this->_data;
            $this->_map = [];

            return true;
        } else {
           return false;
        }
    }

    /**
     * Generate nested array with value
     *
     * @param array $array
     * @param string $delimeter
     * @param mixed $value
     */
    public function nestedArray( array $array, $delimeter = '.', $value ) {
        if( empty( $array ) ) {
            return $value;
        }

        return [ array_shift( $array ) => $this->nestedArray( $array, $delimeter, $value ) ];
    }

    /**
     * Save config
     *
     * @return boolean
     */
    public function save() {
        if( $this->_modified ) {

            $content = '<?php' . "\n";

            $content .= var_export( $this->_data, true ) . ';' . "\n";

            $content .= '?>';


            if( file_put_contents( $this->_file, $content ) ) {
                $this->_modified = false;
                return true;
            } else {
                return false;
            }

        } else {
            return false;
        }
    }
}