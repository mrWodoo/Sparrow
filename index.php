<?php
/**
 * Sparrow Forum Engine
 *
 * @author Denis Wróbel
 * @copyright (c) 2014 Denis Wróbel
 */
$root = './';
$library = $root . 'Library/';


// Initialize autoloader
require_once( $library . 'Sparrow/Autoloader.php' );
$autoloader = new \Sparrow\Autoloader( $library );


$application = new \Sparrow\Application( $root, microtime( true ) );

try {
    $frontController = new \Sparrow\FrontController( $application->getDI() );
} catch( \Sparrow\Exception $exception ) {
    $exception->displayAll();
}