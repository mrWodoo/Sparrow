<?php
/**
 * SQL Query abstraction
 */
namespace Sparrow\Database;

abstract class QueryAbstract {
    /**
     * Current sql syntax
     *
     * @var string
     */
    protected $_sql = '';

    /**
     * Parameters to bind
     *
     * @var array
     */
    protected $_bind = [];

    /**
     * Generate SQL
     *
     * @return string
     */
    abstract  public function sql();

    /**
     * Get binds
     *
     * @return array
     */
    public function getBinds() {
        return $this->_bind;
    }
}