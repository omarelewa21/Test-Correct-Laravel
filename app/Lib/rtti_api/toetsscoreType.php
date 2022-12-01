<?php

class toetsscoreType
{

    /**
     * @var int $aantalopgaven
     */
    protected $aantalopgaven = null;

    /**
     * @var int $aantalgoed
     */
    protected $aantalgoed = null;

    /**
     * @var int $aantalfout
     */
    protected $aantalfout = null;

    /**
     * @var int $aantalgelezen
     */
    protected $aantalgelezen = null;

    /**
     * @var float $tijd
     */
    protected $tijd = null;

    /**
     * @var float $vaardigheidsscore
     */
    protected $vaardigheidsscore = null;

    /**
     * @var string $codevaardigheidsschaal
     */
    protected $codevaardigheidsschaal = null;

    /**
     * @var betrouwbaarheidsinterval $betrouwbaarheidsinterval
     */
    protected $betrouwbaarheidsinterval = null;

    
    public function __construct()
    {
    
    }

    /**
     * @return int
     */
    public function getAantalopgaven()
    {
      return $this->aantalopgaven;
    }

    /**
     * @param int $aantalopgaven
     * @return toetsscoreType
     */
    public function setAantalopgaven($aantalopgaven)
    {
      $this->aantalopgaven = $aantalopgaven;
      return $this;
    }

    /**
     * @return int
     */
    public function getAantalgoed()
    {
      return $this->aantalgoed;
    }

    /**
     * @param int $aantalgoed
     * @return toetsscoreType
     */
    public function setAantalgoed($aantalgoed)
    {
      $this->aantalgoed = $aantalgoed;
      return $this;
    }

    /**
     * @return int
     */
    public function getAantalfout()
    {
      return $this->aantalfout;
    }

    /**
     * @param int $aantalfout
     * @return toetsscoreType
     */
    public function setAantalfout($aantalfout)
    {
      $this->aantalfout = $aantalfout;
      return $this;
    }

    /**
     * @return int
     */
    public function getAantalgelezen()
    {
      return $this->aantalgelezen;
    }

    /**
     * @param int $aantalgelezen
     * @return toetsscoreType
     */
    public function setAantalgelezen($aantalgelezen)
    {
      $this->aantalgelezen = $aantalgelezen;
      return $this;
    }

    /**
     * @return float
     */
    public function getTijd()
    {
      return $this->tijd;
    }

    /**
     * @param float $tijd
     * @return toetsscoreType
     */
    public function setTijd($tijd)
    {
      $this->tijd = $tijd;
      return $this;
    }

    /**
     * @return float
     */
    public function getVaardigheidsscore()
    {
      return $this->vaardigheidsscore;
    }

    /**
     * @param float $vaardigheidsscore
     * @return toetsscoreType
     */
    public function setVaardigheidsscore($vaardigheidsscore)
    {
      $this->vaardigheidsscore = $vaardigheidsscore;
      return $this;
    }

    /**
     * @return string
     */
    public function getCodevaardigheidsschaal()
    {
      return $this->codevaardigheidsschaal;
    }

    /**
     * @param string $codevaardigheidsschaal
     * @return toetsscoreType
     */
    public function setCodevaardigheidsschaal($codevaardigheidsschaal)
    {
      $this->codevaardigheidsschaal = $codevaardigheidsschaal;
      return $this;
    }

    /**
     * @return betrouwbaarheidsinterval
     */
    public function getBetrouwbaarheidsinterval()
    {
      return $this->betrouwbaarheidsinterval;
    }

    /**
     * @param betrouwbaarheidsinterval $betrouwbaarheidsinterval
     * @return toetsscoreType
     */
    public function setBetrouwbaarheidsinterval($betrouwbaarheidsinterval)
    {
      $this->betrouwbaarheidsinterval = $betrouwbaarheidsinterval;
      return $this;
    }

}
