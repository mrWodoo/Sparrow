<?php
/**
 * DELETE query builder
 */
namespace Sparrow\Database\Query;

use Sparrow\Database\QueryAbstract;

class Delete extends QueryAbstract {
    /**
     * Delete FROM
     *
     * @var string
     */
    protected $_from = '';

    /**
     * WHERE clause
     *
     * @var array
     */
    protected $_where = [];

    /**
     * Set FROM
     *
     * @param string $from
     * @return \Sparrow\Database\Query\Delete
     */
    public function from( $from ) {
        $this->_from = $from;

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
     * Generate sql
     *
     * @return string
     */
    public function sql() {
        if( $this->_from ) {
            $sql = 'DELETE FROM ' . $this->_from;

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

            $this->_sql = $sql;

            return $this->_sql;
        } else {

        }
    }
}
