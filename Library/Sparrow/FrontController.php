<?php
/**
 * Front controller which job is to route to proper controllers and actions
 */
namespace Sparrow;

use \Sparrow\DependencyInjection as DI;

class FrontController {
    /**
     * Dependency Injection
     *
     * @var /Sparrow/DependencyInjection
     */
    protected $_di = null;

    /**
     * Request object
     *
     * @var /Sparrow/Http/Request
     */
    protected $_request = null;

    /**
     * Current controller name
     *
     * @var string
     */
    protected $_controllerName = '';

    /**
     * Current action name
     *
     * @var string
     */
    protected $_actionName = '';

    /**
     * List of routes
     *
     * @var array
     */
    protected $_routes = [];

    /**
     * Default controller
     *
     * @var string
     */
    protected $_defaultController = 'Board';

    /**
     * Default action
     *
     * @var string
     */
    protected $_defaultAction = 'index';

    /**
     * Controllers directory
     *
     * @var string
     */
    protected $_dir = '';

    /**
     * Query string separator
     *
     * @var string
     */
    protected $_separator = '/';

    /**
     * Action parameters
     *
     * @var array
     */
    protected $_params = array();

    /**
     * Initialize and start working
     *
     * @param DependencyInjection $di
     */
    public function __construct( DI $di ) {
        $this->_di = $di;
        $this->_request = $this->_di->HttpRequest;

        $this->_dir = Autoloader::getInstance()->getLibrary() . 'Sparrow/Controller/';
    }

    /**
     * Route!
     *
     * @throws Exception
     */
    public function route() {
        $qs = $this->_formatQueryString();

        $hit = false;

        foreach( $this->_routes AS $controller => $routes ) {
            foreach( $routes AS $data ) {
                $route = $data[0];
                $action = $data[1];

                $regexpPattern = preg_replace( '/\:([a-z0-9\-\_\.]{1,64})/i', '(.*?)', $route );
                $regexpPattern = str_replace( '/', '\/', $regexpPattern );

                $params = [];
                $valid = preg_match( '/' . $regexpPattern . '/i', $this->_formatQueryString(), $params );

                if( $valid ) {
                    $hit = true;
                    $this->_controllerName = $controller;
                    $this->_actionName = $action;

                    $params = array_slice( $params, 1 );

                    break;
                }
            }
        }

        if( !$hit ) {
            $this->_controllerName = $this->_defaultController;
            $this->_actionName = $this->_defaultAction;
        }


        $class = 'Sparrow\\Controller\\' . ucfirst( strtolower( $this->_controllerName ) );
        $method = strtolower( $this->_actionName ) . 'Action';

        $params  = array_values( $params );
        $this->_params = $params;


        $controllerObject = new $class;

        if( !method_exists( $controllerObject, $method ) ) {
            throw new Exception( 'Method ' . $method . ' does not exist in ' . $class );
        }

        if( count( $params ) ) {
            $reflection = new \ReflectionMethod( $controllerObject, $method );
            $reflection->invokeArgs( $controllerObject, $this->_params );
        } else {
            $controllerObject->$method();
        }
    }

    /**
     * Add route
     *
     * @param string $pattern
     * @param string $controller
     * @param string $action
     */
    public function addRoute( $pattern, $controller, $action ) {

        if( !isset( $this->_routes[ $controller ] ) ) {
            $this->_routes[ $controller ] = [];
        }

        if( $pattern[ strlen( $pattern ) - 1 ] != '/' ) {
            $pattern .= '/';
        }

        $this->_routes[ $controller ][] = [ $pattern, $action ];

    }

    /**
     * Add routes
     *
     * @param array $routes
     */
    public function addRoutes( array $routes ) {
        foreach( $routes AS $route ) {
            $this->addRoute( $route[0], $route[1], $route[2] );
        }
    }

    /**
     * Set default controller
     *
     * @param string $controller
     */
    public function setDefaultController( $controller ) {
        $this->_defaultController = $controller;
    }

    /**
     * Get default controller
     *
     * @return string
     */
    public function getDefaultController() {
        return $this->_defaultController;
    }

    /**
     * Set default action
     *
     * @param string $controller
     */
    public function setDefaultAction( $action ) {
        $this->_defaultAction = $action;
    }

    /**
     * Get default action
     *
     * @return string
     */
    public function getDefaultAction() {
        return $this->_defaultAction;
    }

    /**
     * Set separator
     *
     * @param string $separator
     */
    public function setSeparator( $separator ) {
        $this->_separator = $separator;
    }

    /**
     * Get separator
     *
     * @return string
     */
    public function getSeparator() {
        return $this->_separator;
    }

    /**
     * Format query string
     *
     * @return string
     */
    protected function _formatQueryString() {
        $qs = $this->_request->server( 'QUERY_STRING' );

        if( !$qs || strlen( $qs ) == 1 && $qs[0] == '/' ) {
            return '';
        }
        if( $qs[0] != '/' ) {
            $qs = '/' . $qs;
        }

        // Remove slashes at the end
        while( $qs[ strlen( $qs ) - 1 ] == '/' ) {
            $qs = substr( $qs, 0, strlen( $qs ) - 1 );
        }

        $qs .= '/';

        return $qs;
    }

    /**
     * Get data from query string
     *
     * @param string $queryString
     * @return array
     */
    public function getDataFromQS( $queryString ) {
        $data = explode( $this->_separator, $queryString );
        if( $data[0] == '' ) {
            array_shift( $data );
        }
        return $data;
    }
}