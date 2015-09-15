<?php
/**
 * Dependency Injection
 */
namespace Sparrow;


class DependencyInjection {
    /**
     * Container for objects/instructions
     *
     * @var array
     */
    protected static $_container = array();

    /**
     * Set dependency
     *
     * @param string $key
     * @param array|object|callback $value
     */
    public function set( $key, $value ) {
        self::$_container[ $key ] = $value;
    }

    /**
     * Magic set
     *
     * @param string $key Dependency name
     * @param array|object|callback $value
     */
    public function __set( $key, $value ) {
        $this->set( $key, $value );
    }

    /**
     * Get dependency
     *
     * @param string $key Dependency name
     * @return object
     * @throws \Sparrow\Exception\DependencyInjection
     */
    public function get( $key ) {
        if( $this->has( $key ) ) {

            $element = self::$_container[ $key ];

            if( is_callable( $key ) ) {
                // We are dealing with a callback
                // so create and return new instnce
                // each time we ask for this dependency
                return $element();
            } else if( is_object( $element ) ) {
                // Allways use same instance from our
                // container
                return $element;
            } else if( is_array( $element ) ) {
                // We are dealing with array which are
                // parameters for constructor
                // Lazy-loading
                $reflection = new \ReflectionClass( $key );
                $reflection->newInstanceArgs( $element[ 1 ] );

                return $reflection;
            } else {
                throw new \Sparrow\Exception\DependencyInjection( 'You can\'t work with dependency <b>' . $key . '</b>!' );
            }

        } else {
            throw new \Sparrow\Exception\DependencyInjection( 'Dependency ' . $key . ' not found!' );
        }
    }

    /**
     * Magic get
     *
     * @param string $key Dependency name
     * @return object
     * @throws \Sparrow\Exception\d
     */
    public function __get( $key ) {
        return $this->get( $key );
    }

    /**
     * Is this dependency already defined?
     *
     * @param string $key Dependency name
     * @return boolean
     */
    public function has( $key ) {
        return ( isset( self::$_container[ $key ] ) );
    }
}