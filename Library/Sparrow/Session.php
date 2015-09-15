<?php
namespace Sparrow;

use Sparrow\Database\QueryBuilder;

class Session {
    /**
     * Session id
     *
     * @var string
     */
    private $_sid = '';

    /**
     * Session data
     *
     * @var array
     */
    private $_data = [];

    /**
     * Has data been modified?
     *
     * @var boolean
     */
    private  $_modified = false;

    /**
     * DI
     *
     * @var DependencyInjection
     */
    private $_di = null;

    /**
     * Initialize session
     *
     * @param DependencyInjection $di
     */
    public function __construct( DependencyInjection $di ) {
        $this->_di = $di;

        $sessionStarted = false;

        $sidCookie = $di->HttpRequest->cookie( 'session_id' );

        if( $sidCookie ) {
            // SID found in cookie
            $this->_sid = substr( $sidCookie, 0, 32 );
        } else {
            // Not found, create new session
            try {
                $this->_startNewSession();
            } catch( Exception $Exception ) {
                throw new Exception( 'Can\'t start new session' );
            }

            $sessionStarted = true;
        }

        if( !$sessionStarted ) {
            $session = $this->fetchSession( $this->_sid );

            $this->_data = unserialize( $session['data'] );

            // Compare IP addresses
            if( $session['client_ip'] != $this->_di->HttpRequest->clientIp() ) {
                // Kill session
                try {
                    $this->killSession( $this->_sid );
                } catch( Exception $exception ) {
                    throw new Exception( 'Tried to kill session but failed.' );
                }

                try {
                    $this->_startNewSession();
                } catch( Exception $exception ) {
                    throw new Exception( 'Couldn\'t create new session after killing previous one.' );
                }
            }
        }

        // Update session time
        try {
            $query = new QueryBuilder();

            $query->update( 'sessions' )
                ->set( 'time', $this->_di->Application->getTime() )
                ->where( 'id', '=', $this->_sid, true );

            $this->_di->Database->query( $query->sql(), $query->getBinds() );
        } catch( Exception $exception ) {
            throw new Exception( 'Can\'t update session\'s time.' );
        }
    }

    /**
     * Start new session
     */
    private function _startNewSession() {
        $sid = $this->_sid = $this->generateSid();

        $this->_data = [];

        $this->_di->HttpRequest->cookie( 'session_id', $sid );

        $query = new QueryBuilder();

        $query->insert( [
            'id' => $sid,
            'data' => serialize( $this->_data ),
            'time' => $this->_di->Application->getTime(),
            'client_ip' => $this->_di->HttpRequest->clientIp(),
            'client_agent' => $this->_di->HttpRequest->clientAgent()
        ] )->into( 'sessions' );

        try {
            $this->_di->Database->query( $query->sql() );
        } catch( Exception $exception ) {
            throw new Exception( 'Can\' create new session!' );
        }
    }

    /**
     * Kill session
     *
     * @param string $sid
     */
    public function killSession( $sid ) {
        $query = new QueryBuilder();

        $query->delete()->from( 'sessions' )->where( 'id', '=', $sid, true );

        $this->_di->Database->query( $query->sql(), $query->getBinds() );
    }

    /**
     * Load session data
     *
     * @param string $sid
     * @throws Exception
     * @return []
     */
    public function fetchSession( $sid ) {
        $sid = substr( $sid, 0, 32 );

        $query = new QueryBuilder();

        $query->select( '*' )->from( 'sessions' )->where( 'id', '=', $sid, true );

        $session = $this->_di->Database->query( $query->sql(), $query->getBinds() )->fetch( \PDO::FETCH_ASSOC );

        return $session;
    }

    /**
     * Generate SID
     *
     * @return string
     */
    public function generateSid() {
        return substr( md5( microtime( true ) ), 0, 32 );
    }

    /**
     * Get session data
     *
     * @param string $key if not set, return whole
     *
     * @return mixed
     */
    public function getData( $key = '' ) {
        if( !$key ) {
            return $this->_data;
        } else {
            return isset( $this->_data[ $key ] ) ? $this->_data[ $key ] : null;
        }
    }

    /**
     * Set new data
     *
     * @param array $data
     */
    public function setData( array $data ) {
        foreach( $data AS $key => $value ) {
            $this->_data[ $key ] = $value;
        }

        $this->_modified = true;
    }

    /**
     * Get session id
     *
     * @return string
     */
    public function getSid() {
        return $this->_sid;
    }

    /**
     * Destructor, save changes made to session
     */
    public function __destruct() {
        if( $this->_modified ) {
            $query = new QueryBuilder();

            $query->update( 'sessions' )
                ->set( 'data', serialize( $this->_data ), true )
                ->where( 'id', '=', $this->_sid, true );

            $this->_di->Database->query( $query->sql(), $query->getBinds() );
        }
    }
}