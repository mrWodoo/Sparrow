<?php
/**
 * SELECT query builder
 */
namespace Sparrow\Database\Query;

use Sparrow\Database\QueryAbstract;

class Select extends QueryAbstract {
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
     * @return \Sparrow\Database\Query\Select
     */
    public function select( $select ) {
        $args = func_get_args();

        $this->_operation = 'select';

        foreach( $args AS $arg ) {
            $this->_select[] = $arg;
        }

        return $this;
    }

    /**
     * Add FROM
     *
     * @param string $from,... unlimited number of additional parameters
     * @return \Sparrow\Database\Query\Select
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
     * @return \Sparrow\Database\Query\Select
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
     * @return \Sparrow\Database\Query\Select
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
     * @return \Sparrow\Database\Query\Select
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
     * @return \Sparrow\Database\Query\Select
     */
    public function limit( $limitStart, $limitEnd = false ) {
        $this->_limit = [ $limitStart, $limitEnd ];

        return $this;
    }

    /**
     * Generate SQL
     *
     * @return string
     */
    public function sql() {
        $sql = '';


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
                    $where .= $whr[0] . ' ' . $whr[1] . ' ' . $whr[2];
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

        $this->_sql = $sql;

        return $this->_sql;
    }
}