<?php

class ctpToetsOnderdeel
{

    /**
     * @var int $toetsonderdeelvolgnummer
     */
    protected $toetsonderdeelvolgnummer = null;

    /**
     * @var ctpVocabulaireGebondenVeld $toetsonderdeelcode
     */
    protected $toetsonderdeelcode = null;

    /**
     * @var string $toetsonderdeelnaam
     */
    protected $toetsonderdeelnaam = null;

    /**
     * @var ctpNormering $toetsonderdeelnormering
     */
    protected $toetsonderdeelnormering = null;

    
    public function __construct()
    {
    
    }

    /**
     * @return int
     */
    public function getToetsonderdeelvolgnummer()
    {
      return $this->toetsonderdeelvolgnummer;
    }

    /**
     * @param int $toetsonderdeelvolgnummer
     * @return ctpToetsOnderdeel
     */
    public function setToetsonderdeelvolgnummer($toetsonderdeelvolgnummer)
    {
      $this->toetsonderdeelvolgnummer = $toetsonderdeelvolgnummer;
      return $this;
    }

    /**
     * @return ctpVocabulaireGebondenVeld
     */
    public function getToetsonderdeelcode()
    {
      return $this->toetsonderdeelcode;
    }

    /**
     * @param ctpVocabulaireGebondenVeld $toetsonderdeelcode
     * @return ctpToetsOnderdeel
     */
    public function setToetsonderdeelcode($toetsonderdeelcode)
    {
      $this->toetsonderdeelcode = $toetsonderdeelcode;
      return $this;
    }

    /**
     * @return string
     */
    public function getToetsonderdeelnaam()
    {
      return $this->toetsonderdeelnaam;
    }

    /**
     * @param string $toetsonderdeelnaam
     * @return ctpToetsOnderdeel
     */
    public function setToetsonderdeelnaam($toetsonderdeelnaam)
    {
      $this->toetsonderdeelnaam = $toetsonderdeelnaam;
      return $this;
    }

    /**
     * @return ctpNormering
     */
    public function getToetsonderdeelnormering()
    {
      return $this->toetsonderdeelnormering;
    }

    /**
     * @param ctpNormering $toetsonderdeelnormering
     * @return ctpToetsOnderdeel
     */
    public function setToetsonderdeelnormering($toetsonderdeelnormering)
    {
      $this->toetsonderdeelnormering = $toetsonderdeelnormering;
      return $this;
    }

}
