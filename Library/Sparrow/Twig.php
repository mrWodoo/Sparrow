<?php
/**
 * Twig integration class
 */
namespace Sparrow;

class Twig {
    /**
     * Twig file system
     *
     * @var \Twig_Loader_Filesystem
     */
    private $_loader = null;

    /**
     * Twig environment
     *
     * @var \Twig_Environment
     */
    private $_environment = null;

    /**
     * Templates variables
     *
     * @var array
     */
    private $_context = array();

    /**
     * Template directory
     *
     * @var string
     */
    private $_curTemplateDir = '';

    /**
     * DI
     *
     * @var DependencyInjection
     */
    private $_di = null;


    /**
     * Constructor, initialize Twig
     *
     * @param DependencyInjection $di
     */
    public function __construct( DependencyInjection $di ) {
        $dir = './Public/Templates/' . $di->ConfigReader->board->template->get() . '/';
        $this->_curTemplateDir = $dir;

        try {
            $this->_loader = new \Twig_Loader_Filesystem( $dir );
        } catch( \Twig_Error_Loader $Exception ) {
            throw new Exception( 'Twig loader exception. ' . $Exception->getMessage() );
        }

        $this->_environment = new \Twig_Environment( $this->_loader, array(
            'cache' => $di->ConfigReader->cache->cache->get(),
            'auto_reload' => true,
            'autoescape' => false,
        ) );
    }

    /**
     * Get current template dir
     *
     * @return string
     */
    public function curTemplateDir() {
        return $this->_curTemplateDir;
    }

    /**
     * Get loader
     *
     * @return \Twig_Loader_Filesystem
     */
    public function getLoader() {
        return $this->_loader;
    }

    /**
     * Get environment
     *
     * @return \Twig_Environment
     */
    public function getEnvironment() {
        return $this->_environment;
    }

    /**
     * Get context
     *
     * @param string $template
     * @return array
     */
    public function getContext( $template = '' ) {
        if( !strlen( $template ) ) {
            return $this->_context;
        } else {
            return $this->_context[ $template ];
        }
    }

    /**
     * Set context
     *
     * @param string $template
     * @param array $context
     */
    public function setContext( $template, $context ) {
        if( !isset( $this->_context[ $template ] ) ) {
            $this->_context[ $template ] = array();
        }

        if( is_array( $context ) ) {
            $this->_context[ $template ] = array_merge_recursive( $this->_context[ $template ], $context );
        }
    }

    /**
     * Render template
     *
     * @param string $template
     * @throws Exception
     * @return string
     */
    public function render( $template ) {
        try {
            $render = $this->_environment->render(
                $template,
                ( isset( $this->_context[ $template ] ) ) ? $this->_context[ $template ] : array() );
        } catch( \Twig_Error_Syntax $Exception ) {
            throw new Exception( 'Twig Syntax Exception. ' . $Exception->getMessage() );
        } catch( \Twig_Error $Exception ) {
            throw new Exception( 'Twig Error Exception. ' . $Exception->getMessage() );
        }

        return $render;
    }
}