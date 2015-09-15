<?php
/**
 * Database
 */
namespace Sparrow;

class Database {
    /**
     * PDO Handler
     *
     * @var \PDO
     */
    protected $_pdo = null;

    /**
     * Table name prefix
     *
     * @var string
     */
    protected $_prefix = '';

    /**
     * Executed queries count
     *
     * @var integer
     */
    protected $_queriesCount = 0;

    /**
     * Pointer to last query
     *
     * @var \PDOStatement
     */
    protected $_last = null;

    /**
     * Constructor. Connect with database
     *
     * @param string $host
     * @param string $name Database name
     * @param string $user
     * @param string $password
     * @param string $prefix Table name prefix
     * @param integer $port
     * @throws \Sparrow\Exception\Database
     */
    public function __construct( $host, $name, $user, $password, $prefix = 'sp_', $port = 3306 ) {
        try {
            $this->_pdo = new \PDO( 'mysql:host=' . $host . ';dbname=' . $name . ';port=' . $port, $user, $password );

            $this->_prefix = $prefix;
        } catch( \PDOException $exception ) {
            throw new \Sparrow\Exception\Database( 'Cannot connect with database. ' . $exception->getMessage() );
        }
    }

    /**
     * Get PDO
     *
     * @return \PDO
     */
    public function getPDO() {
        return $this->_pdo;
    }

    /**
     * Get last query
     *
     * @return \PDOStatement
     */
    public function getLast() {
        return $this->_last;
    }

    /**
     * Execute SQL query
     *
     * @param string $sql
     * @param array $bind
     * @return \PDOStatement
     * @throws \Sparrow\Exception\Database
     */
    public function sql( $sql, array $bind = [] ) {
        $query = null;

        // Close cursor from last query
        if( $this->_last ) {
            $this->_last->closeCursor();
            $this->_last = null;
        }

        try {
            $query = $this->_pdo->prepare( $sql );

            $this->_last = $query;

            $execute = $query->execute( $bind );
        } catch( \PDOException $exception ) {
            throw new \Sparrow\Exception\Database( 'Given SQL query cannot be executed. ' . $exception->getMessage() );
        }

        return $query;
    }

    /**
     * Alias to sql method
     * Execute SQL query
     *
     * @param string $sql
     * @param array $bind
     * @return \PDOStatement
     * @throws \Sparrow\Exception\Database
     */
    public function query( $sql, array $bind = [] ) {
        return $this->sql( $sql, $bind );
    }

    /**
     * Last insert id
     *
     * @return integer
     */
    public function getLastId() {
        return $this->_pdo->lastInsertId();
    }
}