<?php

namespace SocialNetwork\API\Provider;


/**
 * Class Criteria
 * @package SocialNetwork\API\Provider
 */
class Criteria
{

    private  $stack = array();

    private  $operators = array( '=' , '!=' , '*' , '^' , '$' , '>' , '<' , 'in' );

    /**
     * Add new condition to criteria
     *
     * @param $att Attribute to compare
     * @param $val Value to compare
     * @param string $operator Operator used to compare
     * @return $this
     */
    public function add($att , $val , $operator = '=' )
    {
        $operator = strtolower($operator);
        if(in_array( $operator , $this->operators ))
            $this->stack[] = array( $operator , $att , $val );

        return $this;
    }

    /**
     * Add OR criteria
     *
     * EX: $criteria->add('authorized' => 'true' )->orC( $criteria->c()->add('age' , '18' , '>') );
     *     "authorized = true OR age > 18"
     *
     * @param Criteria $criteria
     * @return $this
     */
    public function orC( Criteria $criteria )
    {
        $this->stack[] = array( 'or' , $criteria );
        return $this;
    }

    /**
     * Add AND criteria
     *
     * EX: $criteria->add('authorized' => 'true' )->andC( $criteria->c()->add('age' , '18' , '>') );
     *     "authorized = true AND age > 18"
     *
     * @param Criteria $criteria
     * @return $this
     */
    public function andC( Criteria $criteria )
    {
        $this->stack[] = array( 'and' , $criteria );
        return $this;
    }

    /**
     * Return new instance of object Criteria.
     *
     * @return Criteria
     */
    public function c()
    {
        return new Criteria();
    }

    /**
     * Return internal stack of criteria
     *
     * @return array
     */
    public function getStack()
    {
        return $this->stack;
    }

}