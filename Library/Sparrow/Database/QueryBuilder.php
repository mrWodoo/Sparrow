<?php
/**
 * Created by PhpStorm.
 * User: denis
 * Date: 25.06.2014
 * Time: 00:27
 */
namespace Sparrow\Database;

class QueryBuilder {
    /**
     * Table prefix
     * 
     * @var string
     */
    private static $_prefix = 'sp_';

    /**
     * Query type
     *
     * @var int
     */
    protected $_type = 0;

    /**
     * SELECT type
     */
    const TYPE_SELECT = 1;

    /**
     * UPDATE
     */
    const TYPE_UPDATE = 2;

    /**
     * DELETE
     */
    const TYPE_DELETE = 4;

    /**
     * INSERT
     */
    const TYPE_INSERT = 8;

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
     * What to select
     *
     * @var array
     */
    protected $_select = [];

    /**
     * FROM part
     *
     * @var array
     */
    protected $_from = [];

    /**
     * UPDATE table
     *
     * @var string
     */
    protected $_update = '';

    /**
     * SET
     *
     * @var array
     */
    protected $_set = [];

    /**
     * INTO, table name to which we will be
     * inserting data
     *
     * @var string
     */
    protected $_into = '';

    /**
     * INSERT, set of values to insert
     *
     * @var array
     */
    protected $_insert = [];

    /**
     * WHERE clause
     *
     * @var array
     */
    protected $_where = [];

    /**
     * GROUP BY
     *
     * @var array
     */
    protected $_group = [];

    /**
     * ORDER BY
     *
     * @var array
     */
    protected $_order = [];

    /**
     * Limit
     *
     * @var array
     */
    protected $_limit = [];

    /**
     * Set table prefix
     * 
     * @param string $prefix
     */
    public static function setPrefix( $prefix ) {
        self::$_prefix = $prefix;
    }

    /**
     * Add data to select
     *
     * @param string $select,... Unlimited number of additional parameters
     * @return \Sparrow\Database\QueryBuilder
     */
    public function select( $select ) {
        $args = func_get_args();

        $this->_type = self::TYPE_SELECT;

        foreach( $args AS $arg ) {
            $this->_select[] = $arg;
        }

        return $this;
    }

    /**
     * FROM
     *
     * @param string $from,... unlimited number of additional parameters
     * @return \Sparrow\Database\QueryBuilder
     */
    public function from( $from ) {
        $args = func_get_args();

        foreach( $args AS $arg ) {
            $this->_from[] = $arg;
        }

        return $this;
    }

    /**
     * Add WHERE clause
     *
     * @param mixed $val1
     * @param string $sign Comparative sign
     * @param mixed $val2
     * @param string|boolean $bind If != false then bind with given $bind key, if true then generate random bind key
     * @return \Sparrow\Database\QueryBuilder
     */
    public function where( $val1, $sign, $val2, $bind = false ) {

        if( $bind === true ) {
            $key = 'bind' . count( $this->_bind );
        } else if( $bind != false ) {
            $key = $bind;
        } else if( $bind === false ) {
            $key = false;
        }

        $this->_bind[ $key ] = $val2;

        $this->_where[] = [ $val1, $sign, $val2, $key ];

        return $this;
    }

    /**
     * Add GROUP BY
     *
     * @param string $group,...
     * @return \Sparrow\Database\QueryBuilder
     */
    public function groupBy( $group ) {
        $args = func_get_args();

        foreach( $args AS $arg ) {
            $this->_group[] = $arg;
        }

        return $this;
    }


    /**
     * ORDER BY
     *
     * @param string $order,...
     * @return \Sparrow\Database\QueryBuilder
     */
    public function orderBy( $order ) {
        $args = func_get_args();

        foreach( $args AS $arg ) {
            $this->_order[] = $arg;
        }

        return $this;
    }

    /**
     * Set limit
     *
     * @param integer $limitStart
     * @param integer $limitEnd
     * @return \Sparrow\Database\QueryBuilder
     */
    public function limit( $limitStart, $limitEnd = false ) {
        $this->_limit = [ $limitStart, $limitEnd ];

        return $this;
    }

    /**
     * DELETE
     *
     * @return \Sparrow\Database\QueryBuilder
     */
    public function delete() {
        $this->_type = self::TYPE_DELETE;

        return $this;
    }

    /**
     * UPDATE
     *
     * @param string $table Table to update data
     * @return \Sparrow\Database\QueryBuilder
     */
    public function update( $table ) {
        $this->_type = self::TYPE_UPDATE;
        $this->_update = self::$_prefix . $table;

        return $this;
    }

    /**
     * SET
     *
     * @param string $column
     * @param mixed $value
     * @param string|boolean $bind
     * @return \Sparrow\Database\QueryBuilder
     */
    public function set( $column, $value, $bind = true ) {
        if( $bind === true ) {
            $key = 'bind' . count( $this->_bind );
        } else if( $bind != false ) {
            $key = $bind;
        } else if( $bind === false ) {
            $key = false;
        }

        $this->_bind[ $key ] = $value;


        $this->_set[ $column ] = [ $value, $key ];

        return $this;
    }

    /**
     * Set INTO
     *
     * @param string $table
     * @return \Sparrow\Database\QueryBuilder
     */
    public function into( $table ) {
        $this->_type = self::TYPE_INSERT;
        $this->_into = self::$_prefix . $table;

        return $this;
    }

    /**
     * Set data to insert
     *
     * @param array $data [ column => [ value, bind ] ]
     * @return \Sparrow\Database\QueryBuilder
     */
    public function insert( array $data ) {

        foreach( $data AS $column => $insert ) {
            if( is_array( $insert ) ) {
                $bind = $insert[1];

                if( $bind === true ) {
                    $key = 'bind' . count( $this->_bind );
                } else if( $bind != false ) {
                    $key = $bind;
                } else if( $bind === false ) {
                    $key = false;
                }

                $this->_bind[ $key ] = $insert[0];

                $this->_insert[ $column ] = [ $insert, $key ];
            } else {
                $this->_insert[ $column ] = [ $insert, false ];
            }
        }

        return $this;
    }

    /**
     * Build SELECT query
     *
     * @return string
     */
    protected function _selectQuery() {
        $sql = '';

        if( $this->_type != self::TYPE_SELECT ) {
            return '';
        }


        if( count( $this->_select ) ) {
            $sql .= 'SELECT ';

            $select = '';
            foreach( $this->_select AS $slct ) {
                $select .= $slct . ', ';
            }

            // Remove last character (,)
            $select = substr( $select, 0, -2 );
            $sql .= $select;
        }

        // FROM
        if( count( $this->_from ) ) {
            $from = '';

            foreach( $this->_from AS $frm ) {
                $from .= self::$_prefix . $frm . ', ';
            }


            // Remove last character (,)
            $from = substr( $from, 0, -2 );
            $sql .= ' FROM ' . $from;
        }

        // WHERE
        $sql .= $this->_whereClause();

        // GROUP BY
        if( count( $this->_group ) ) {
            $group = '';

            foreach( $this->_group AS $grp ) {
                $group .= $grp . ', ';
            }


            // Remove last character (,)
            $group = substr( $group, 0, -2 );
            $sql .= ' GROUP BY ' . $group;
        }

        // ORDER BY
        if( count( $this->_order ) ) {
            $order = '';

            foreach( $this->_order AS $ordr ) {
                $order .= $ordr . ', ';
            }


            // Remove last character (,)
            $order = substr( $order, 0, -2 );
            $sql .= ' ORDER BY ' . $order;
        }

        // LIMIT
        if( count( $this->_limit ) ) {
            $limit = '';

            foreach( $this->_limit AS $lmt ) {
                if( $lmt !== false ) {
                    $limit .= $lmt . ', ';
                }
            }


            // Remove last character (,)
            $limit = substr( $limit, 0, -2 );
            $sql .= ' LIMIT  ' . $limit;
        }

        return $sql;
    }

    /**
     * Build DELETE query
     * @return string
     */
    protected function _deleteQuery() {
        if( $this->_from && $this->_type == self::TYPE_DELETE ) {
            $sql = 'DELETE FROM ' . self::$_prefix . $this->_from[0];

            // WHERE
            $sql .= $this->_whereClause();

            return $sql;
        } else {
            return '';
        }
    }

    /**
     * Build UPDATE query
     *
     * @return string
     */
    protected function _updateQuery() {
        if( $this->_update && count( $this->_set ) && $this->_type == self::TYPE_UPDATE ) {
            $sql = 'UPDATE ' . $this->_update;
            $set = ' SET ';

            foreach( $this->_set AS $column => $data ) {
                if( $data[1] === false ) {
                    //no bind
                    if( is_int( $data[0] ) ) {
                        $set .= $column . ' = ' . $data[0] . ', ';
                    } else {
                        $set .= $column . ' = \'' . $data[0] . '\', ';
                    }
                } else {
                    $set .= $column . ' = :' . $data[1] . ', ';
                }
            }

            // Remove last character (,)
            $set = substr( $set, 0, -2 );
            $sql .= $set;

            // WHERE
            $sql .= $this->_whereClause();

            return $sql;
        } else {
            return '';
        }
    }

    /**
     * Build INSERT query
     *
     * @return string
     */
    protected function _insertQuery() {
        if( $this->_into && count( $this->_insert ) && $this->_type == self::TYPE_INSERT ) {
            $sql = 'INSERT INTO ' . $this->_into;

            $columns = '';
            $values = '';

            foreach( $this->_insert AS $column => $insert ) {
                $columns .= $column . ', ';

                if( $insert[1] !== false ) {
                    $values .= ':' . $insert[1] . ', ';
                } else {
                    if( is_int( $insert[0] ) ) {
                        $values .= '' . $insert[0] . ', ';
                    } else {
                        $values .= '\'' . $insert[0] . '\', ';
                    }
                }
            }

            $columns = substr( $columns, 0, -2 );
            $values = substr( $values, 0, -2 );

            $sql .= '(' . $columns . ')';
            $sql .= ' VALUES(' . $values . ')';


            return $sql;
        } else {
            return '';
        }
    }

    /**
     * Build WHERE clause
     *
     * @return string
     */
    public function _whereClause() {
        if( count( $this->_where ) ) {
            $sql = '';
            $where = '';

            foreach( $this->_where AS $whr ) {
                if( $whr[3] === false ) {
                    $where .= $whr[0] . ' ' . $whr[1] . ' \'' . $whr[2] . '\'';
                } else {
                    $where .= $whr[0] . ' ' . $whr[1] . ' :' . $whr[3];
                }

                $where .= ' AND ';
            }


            // Remove last character (,)
            $where = substr( $where, 0, -4 );
            $sql .= ' WHERE ' . $where;

            return $sql;
        } else {
            return '';
        }
    }

    /**
     * Generate SQL
     *
     * @return string
     */
    public function sql() {
        $sql = '';

        if( $this->_type === self::TYPE_SELECT ) {
            $sql = $this->_selectQuery();
        } else if( $this->_type === self::TYPE_DELETE ) {
            $sql = $this->_deleteQuery();
        } else if( $this->_type === self::TYPE_UPDATE ) {
            $sql = $this->_updateQuery();
        } else if( $this->_type === self::TYPE_INSERT ) {
            $sql = $this->_insertQuery();
        }


        $this->_sql = $sql;

        return $this->_sql;
    }

    /**
     * Get binds
     *
     * @return array
     */
    public function getBinds() {
        return $this->_bind;
    }
}