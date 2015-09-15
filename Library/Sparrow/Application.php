<?php
/**
 * Sparrow Application
 */
namespace Sparrow;

use Sparrow\Cache\File;
use Sparrow\Http\Request AS Request;

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
     * Application start time
     *
     * @var float
     */
    protected $_start = 0;

    /**
     * Database
     *
     * @var /Sparrow/Database
     */
    protected $_database = null;

    /**
     * Http Request
     *
     * @var /Sparrow/Http/Request
     */
    protected $_httpRequest = null;

    /**
     * Start application
     *
     * @param string $root Root directory
     * @param float $start
     */
    public function __construct( $root, $start = 0 ) {
        $this->_start = $start;

        try {
            $this->_di = new DependencyInjection();

            $this->_di->Application = $this;

            $config = $this->_di->ConfigReader = new ConfigReader( $root . 'Storage/Config.php' );

            // Initialize database connection
            $this->_database = $this->_di->Database = new Database(
                $config->database->host->get(),
                $config->database->name->get(),
                $config->database->user->get(),
                $config->database->password->get(),
                $config->database->prefix->get(),
                $config->database->port->get() );


            // Initialize http request handler
            $this->_httpRequest = $this->_di->HttpRequest = new Request( ( DEV_MODE ) ? true : false, $this->_di );

            // Initialize file cache system
            $this->_di->FileCache = new File( $this->_di );

            // Initialize twig
            $this->_di->Twig = new Twig( $this->_di );

            $twig = $this->_di->Twig;

            // Session test
            $session = new Session( $this->_di );
            $session->setData( [
                'test' =>  time()
            ] );

            $twig->setContext( 'index.html', [
                'url' => $this->getUrl()
            ] );

            echo $this->_di->Twig->render( 'index.html' );



        } catch( Exception $exception ) {
            Exception::displayAll();
        }
    }

    /**
     * Get DI
     *
     * @return \Sparrow\DependencyInjection
     */
    public function getDI() {
        return $this->_di;
    }

    /**
     * Get time
     *
     * @return integer
     */
    public function getTime() {
        return $this->_start;
    }

    /**
     * Get root URL
     *
     * @return string
     */
    public function getUrl() {
        return 'http://' . $this->_di->ConfigReader->board->domain->get() . $this->_di->ConfigReader->board->path->get();
    }
}