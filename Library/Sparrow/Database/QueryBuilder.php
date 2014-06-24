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
     * Build SELECR query
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
                $from .= $frm . ', ';
            }


            // Remove last character (,)
            $from = substr( $from, 0, -2 );
            $sql .= ' FROM ' . $from;
        }

        // WHERE
        if( count( $this->_where ) ) {
            $where = '';

            foreach( $this->_where AS $whr ) {
                if( $whr[3] === false ) {
                    $where .= $whr[0] . ' ' . $whr[1] . ' \'' . $whr[2] . '\'';
                } else {
                    $where .= $whr[0] . ' ' . $whr[1] . ' :' . $whr[3];
                }

                $where .= ', ';
            }


            // Remove last character (,)
            $where = substr( $where, 0, -2 );
            $sql .= ' WHERE ' . $where;
        }

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
            $sql = 'DELETE FROM ' . $this->_from[0];

            // WHERE
            if( count( $this->_where ) ) {
                $where = '';

                foreach( $this->_where AS $whr ) {
                    if( $whr[3] === false ) {
                        $where .= $whr[0] . ' ' . $whr[1] . ' \'' . $whr[2] . '\'';
                    } else {
                        $where .= $whr[0] . ' ' . $whr[1] . ' :' . $whr[3];
                    }

                    $where .= ', ';
                }


                // Remove last character (,)
                $where = substr( $where, 0, -2 );
                $sql .= ' WHERE ' . $where;
            }
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