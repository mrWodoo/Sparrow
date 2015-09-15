<?php
/**
 * Exception
 */
namespace Sparrow;

class Exception extends \Exception {
    /**
     * List of thrown exceptions
     *
     * @var Exception[]
     */
    protected static $_exceptions = [];

    /**
     * Re-define constrcutor
     *
     * @param string $message
     * @param integer $code
     * @param \Exception $previous
     */
    public function __construct( $message = null, $code = 0, \Exception $previous = null ) {
        parent::__construct( $message, $code, $previous );

        self::$_exceptions[] = $this;
    }

    /**
     * Re-define toString
     *
     * @return string
     */
    public function toHtml() {
        return '
        <div class="panel panel-danger">
            <div class="panel-heading">
                ' . $this->getMessage() . '
            </div>

            <div class="panel-body">
                <div>File: <b>' . $this->getFile() . '</b></div>
                <div>Line: <b>' . $this->getLine() . '</b></div>
                <div>Trace:<br> ' . highlight_string( $this->getTraceAsString(), true ) . '</div>
            </div>
        </div>';
    }

    /**
     * Get all exceptions thrown
     *
     * @return array
     */
    public static function exceptions() {
        return self::$_exceptions;
    }

    /**
     * Display all exceptions
     */
    public static function displayAll() {
        $exceptions = self::$_exceptions;

        if( count( $exceptions ) ) {
            echo '<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Some problems were encountered!</title>

        <link rel="stylesheet" href="./Public/bootstrap/css/bootstrap.min.css">

        <style>
        html, body {
            padding: 10px;
        }
        </style>
    </head>

    <body>

    <h1>Some problems were encountered when running application!</h1>

    <h3>What can you do?</h3>

    <p><a href="#" onclick="window.location.reload();"><span class="glyphicon glyphicon-refresh"></span> Refresh</a> this page or contact administrator</p>

    <br>

    <p>Here are some informations which only a trained monkey will understand</p>
';

            foreach( $exceptions AS $exception ) {
                echo $exception->toHtml();
            }

            echo '
    </body>
</html>';
        }
    }
}