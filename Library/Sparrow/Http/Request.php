<?php
/**
 * Request handler
 */
namespace Sparrow\Http;

use Sparrow\Exception\DependencyInjection;
use Sparrow\DependencyInjection AS DI;

class Request {

    /**
     * Dependency Injection
     *
     * @var /Sparrow/DependencyInjection
     */
    protected $_di = null;

    /**
     * Config reader
     *
     * @var /Sparrow/ConfigReader
     */
    protected $_config = null;

    /**
     * Server values
     *
     * @var array
     */
    protected $_server = [];

    /**
     * GET values
     *
     * @var array
     */
    protected $_get = [];

    /**
     * POST values
     *
     * @var array
     */
    protected $_post = [];

    /**
     * COOKIE values
     *
     * @var array
     */
    protected $_cookie = [];

    /**
     * Set data
     *
     * @param boolean $clearArrays Clear $_ arrays?
     */
    public function __construct( $clearArrays = true, DI $di ) {
        $this->_server = $_SERVER;
        $this->_get = $_GET;
        $this->_post = $_POST;
        $this->_cookie = $_COOKIE;

        $this->_di = $di;

        if( $clearArrays ) {
            $_SERVER = [];
            $_GET = [];
            $_POST = [];
            $_COOKIE = [];
        }
    }

    /**
     * Get client agent
     *
     * @return string
     */
    public function clientAgent() {
        return $this->_server['HTTP_USER_AGENT'];
    }

    /**
     * Get client ip
     *
     * @return string
     */
    public function clientIp() {
        if( !empty( $this->_server['HTTP_CLIENT_IP'] ) ) {
            $ip = $this->_server['HTTP_CLIENT_IP'];
        } elseif( !empty( $this->_server['HTTP_X_FORWARDED_FOR'] ) ) {
            $ip = $this->_server['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $this->_server['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * Get or set GET
     *
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public function get( $key, $value = '' ) {
        if( strlen( $value ) ) {
            $this->_get[ $key ] = $value;
            return $value;
        } else {
            return ( isset( $this->_get[ $key ] ) ) ? $this->_get[ $key ] : null;
        }
    }

    /**
     * Get or set POST
     *
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public function post( $key, $value = '' ) {
        if( strlen( $value ) ) {
            $this->_post[ $key ] = $value;
            return $value;
        } else {
            return ( isset( $this->_post[ $key ] ) ) ? $this->_post[ $key ] : null;
        }
    }

    /**
     * Get or set SERVER
     *
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public function server( $key, $value = '' ) {
        if( strlen( $value ) ) {
            $this->_server[ $key ] = $value;
            return $value;
        } else {
            return ( isset( $this->_server[ $key ] ) ) ? $this->_server[ $key ] : null;
        }
    }

    /**
     * Get or set COOKIE
     *
     * @param string $name
     * @param string $value
     * @param int $timeout timeout in seconds
     * @param boolean $permanent
     *
     * @return mixed
     */
    public function cookie( $name, $value = null, $timeout = 3600, $permanent = false ) {
        if( $value !== null ) {
            if( $permanent ) {
                $timeout = 60 * 60 * 24 * 6004;
            }

            $timeout = time() + $timeout;

            $domain = $this->_di->ConfigReader->board->domain->get();

            setcookie( $name, $value, $timeout, $this->_di->ConfigReader->board->path->get(), ( $domain == 'localhost' ) ? false : localhost );

            $this->_cookie[ $name ] = $value;

            return $value;
        } else {
            return ( isset( $this->_cookie[ $name ] ) ) ? $this->_cookie[ $name ] : false;;
        }
    }

    /**
     * Kill cookie
     *
     * @param string $name
     * @return boolean returns true if cookie was killed
     */
    public function killCookie( $name ) {
        if( isset( $this->_cookie[ $name ] ) ) {
            $this->cookie( $name, '', -420 );

            return true;
        } else {
            return false;
        }
    }
}