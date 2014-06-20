<?php
/**
 * Sparrow Application
 */
namespace Sparrow;

final class Application {
    /**
     * Version
     *
     * @const Application version
     * p - pre
     * a - alpha
     * b - beta
     * rc - release candidate
     */
    const VERSION = '0.1pa';

    /**
     * Depedency Injection
     *
     * @var /Sparrow/DependencyInjection
     */
    protected $_di = null;

    /**
     * Start application
     *
     * @param string $root Root directory
     */
    public function __construct( $root ) {
        try {
            $this->_di = new DependencyInjection();

            $this->_di->ConfigReader = new ConfigReader( $root . 'Storage/Config.php' );

            $this->_di->Database = new Database();
        } catch( Exception $exception ) {
            echo $exception;
        }
    }

    /**
     * Get DI
     *
     * @return \Sparrow\DependencyInjection
     */
    public function getDi() {
        return $this->_di;
    }
}