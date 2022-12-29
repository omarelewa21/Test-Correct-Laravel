<?php

class betrouwbaarheidsinterval
{

    /**
     * @var float $bivlaag
     */
    protected $bivlaag = null;

    /**
     * @var float $bivhoog
     */
    protected $bivhoog = null;

    /**
     * @param float $bivlaag
     * @param float $bivhoog
     */
    public function __construct($bivlaag, $bivhoog)
    {
      $this->bivlaag = $bivlaag;
      $this->bivhoog = $bivhoog;
    }

    /**
     * @return float
     */
    public function getBivlaag()
    {
      return $this->bivlaag;
    }

    /**
     * @param float $bivlaag
     * @return betrouwbaarheidsinterval
     */
    public function setBivlaag($bivlaag)
    {
      $this->bivlaag = $bivlaag;
      return $this;
    }

    /**
     * @return float
     */
    public function getBivhoog()
    {
      return $this->bivhoog;
    }

    /**
     * @param float $bivhoog
     * @return betrouwbaarheidsinterval
     */
    public function setBivhoog($bivhoog)
    {
      $this->bivhoog = $bivhoog;
      return $this;
    }

}
