<?php

abstract class ctpAnderResultaatType
{

    /**
     * @var string $andere_score
     */
    protected $andere_score = null;

    
    public function __construct()
    {
    
    }

    /**
     * @return string
     */
    public function getAndere_score()
    {
      return $this->andere_score;
    }

    /**
     * @param string $andere_score
     * @return ctpAnderResultaatType
     */
    public function setAndere_score($andere_score)
    {
      $this->andere_score = $andere_score;
      return $this;
    }

}
