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

            $config = $this->_di->ConfigReader = new ConfigReader( $root . 'Storage/Config.php' );

            $this->_di->Database = new Database(
                $config->database->host->get(),
                $config->database->name->get(),
                $config->database->user->get(),
                $config->database->password->get(),
                $config->database->prefix->get(),
                $config->database->port->get() );

            $query = new \Sparrow\Database\QueryBuilder();


            $query->update( 'users' )
                ->set( 'id', 10 )
                ->set( 'test', 50 )
                ->where( 'id', '=', 10, true )
                ->where( 'dupa', '=', 50, true );

            echo $query->sql();

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