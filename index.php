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


new \Sparrow\Application( $root );