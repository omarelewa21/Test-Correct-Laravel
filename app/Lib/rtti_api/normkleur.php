<?php

class normkleur
{

    /**
     * @var int $rood_code
     */
    protected $rood_code = null;

    /**
     * @var int $groen_code
     */
    protected $groen_code = null;

    /**
     * @var int $blauw_code
     */
    protected $blauw_code = null;

    /**
     * @var float $transparantie
     */
    protected $transparantie = null;

    /**
     * @param int $rood_code
     * @param int $groen_code
     * @param int $blauw_code
     * @param float $transparantie
     */
    public function __construct($rood_code, $groen_code, $blauw_code, $transparantie)
    {
      $this->rood_code = $rood_code;
      $this->groen_code = $groen_code;
      $this->blauw_code = $blauw_code;
      $this->transparantie = $transparantie;
    }

    /**
     * @return int
     */
    public function getRood_code()
    {
      return $this->rood_code;
    }

    /**
     * @param int $rood_code
     * @return normkleur
     */
    public function setRood_code($rood_code)
    {
      $this->rood_code = $rood_code;
      return $this;
    }

    /**
     * @return int
     */
    public function getGroen_code()
    {
      return $this->groen_code;
    }

    /**
     * @param int $groen_code
     * @return normkleur
     */
    public function setGroen_code($groen_code)
    {
      $this->groen_code = $groen_code;
      return $this;
    }

    /**
     * @return int
     */
    public function getBlauw_code()
    {
      return $this->blauw_code;
    }

    /**
     * @param int $blauw_code
     * @return normkleur
     */
    public function setBlauw_code($blauw_code)
    {
      $this->blauw_code = $blauw_code;
      return $this;
    }

    /**
     * @return float
     */
    public function getTransparantie()
    {
      return $this->transparantie;
    }

    /**
     * @param float $transparantie
     * @return normkleur
     */
    public function setTransparantie($transparantie)
    {
      $this->transparantie = $transparantie;
      return $this;
    }

}
