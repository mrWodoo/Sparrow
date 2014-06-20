<?php
/**
 * Autoloader for classes
 */
namespace Sparrow;

class Autoloader {
    /**
     * Singleton instance
     *
     * @var \Sparrow\Autoloader
     */
    protected static $_instance = null;

    /**
     * Path to library
     *
     * @var string
     */
    protected $_library = './';

    /**
     * Loaded classes
     *
     * @var array
     */
    protected $_loaded = array();

    /**
     * Constructor, initialize autoloader
     *
     * @param string $library Path to library
     */
    public function __construct( $library ) {
        if( self::$_instance == null ) {
            self::$_instance = $this;

            if( file_exists( $library ) ) {
                $this->_library = $library;

                $this->register();
            } else {
                die( 'Library <b>' . $library . '</b> does not exist!' );
            }
        } else {
            die( 'Only one instance of Autoloader is allowed' );
        }
    }

    /**
     * Register autoloader
     */
    public function register() {
        spl_autoload_register( array( $this, 'autoload' ) );
    }

    /**
     * Get library
     *
     * @return string
     */
    public function getLibrary() {
        return $this->_library;
    }

    /**
     * Get loaded classes
     *
     * @return array
     */
    public function getClasses() {
        return $this->_loaded;
    }

    /**
     * Autoload
     *
     * @param string $className
     */
    public function autoload( $className ) {
        // Let's check if we have already
        // included class' file
        if( !isset( $this->_loaded[ $className ] ) ) {
            $classFile = $this->_library . str_replace( '\\', '/', $className ) . '.php';
            require_once( $classFile );
            $this->_loaded[ $className ] = true;
        }
    }
}