<?php

class ctpToets
{

    /**
     * @var ctpVocabulaireGebondenVeld $toetscode
     */
    protected $toetscode = null;

    /**
     * @var ctpVocabulaireGebondenVeld $versie
     */
    protected $versie = null;

    /**
     * @var string $toetsnaam
     */
    protected $toetsnaam = null;

    /**
     * @var ctpVocabulaireGebondenVeld $leerjaar
     */
    protected $leerjaar = null;

    /**
     * @var ctpVocabulaireGebondenVeld $vakgebied
     */
    protected $vakgebied = null;

    /**
     * @var ctpNormering $toetsnormering
     */
    protected $toetsnormering = null;

    /**
     * @var ArrayOfCtpToetsIngang $toetshierarchie
     */
    protected $toetshierarchie = null;

    /**
     * @var ArrayOfCtpToetsOnderdeel $toetsonderdelen
     */
    protected $toetsonderdelen = null;

    
    public function __construct()
    {
    
    }

    /**
     * @return ctpVocabulaireGebondenVeld
     */
    public function getToetscode()
    {
      return $this->toetscode;
    }

    /**
     * @param ctpVocabulaireGebondenVeld $toetscode
     * @return ctpToets
     */
    public function setToetscode($toetscode)
    {
      $this->toetscode = $toetscode;
      return $this;
    }

    /**
     * @return ctpVocabulaireGebondenVeld
     */
    public function getVersie()
    {
      return $this->versie;
    }

    /**
     * @param ctpVocabulaireGebondenVeld $versie
     * @return ctpToets
     */
    public function setVersie($versie)
    {
      $this->versie = $versie;
      return $this;
    }

    /**
     * @return string
     */
    public function getToetsnaam()
    {
      return $this->toetsnaam;
    }

    /**
     * @param string $toetsnaam
     * @return ctpToets
     */
    public function setToetsnaam($toetsnaam)
    {
      $this->toetsnaam = $toetsnaam;
      return $this;
    }

    /**
     * @return ctpVocabulaireGebondenVeld
     */
    public function getLeerjaar()
    {
      return $this->leerjaar;
    }

    /**
     * @param ctpVocabulaireGebondenVeld $leerjaar
     * @return ctpToets
     */
    public function setLeerjaar($leerjaar)
    {
      $this->leerjaar = $leerjaar;
      return $this;
    }

    /**
     * @return ctpVocabulaireGebondenVeld
     */
    public function getVakgebied()
    {
      return $this->vakgebied;
    }

    /**
     * @param ctpVocabulaireGebondenVeld $vakgebied
     * @return ctpToets
     */
    public function setVakgebied($vakgebied)
    {
      $this->vakgebied = $vakgebied;
      return $this;
    }

    /**
     * @return ctpNormering
     */
    public function getToetsnormering()
    {
      return $this->toetsnormering;
    }

    /**
     * @param ctpNormering $toetsnormering
     * @return ctpToets
     */
    public function setToetsnormering($toetsnormering)
    {
      $this->toetsnormering = $toetsnormering;
      return $this;
    }

    /**
     * @return ArrayOfCtpToetsIngang
     */
    public function getToetshierarchie()
    {
      return $this->toetshierarchie;
    }

    /**
     * @param ArrayOfCtpToetsIngang $toetshierarchie
     * @return ctpToets
     */
    public function setToetshierarchie($toetshierarchie)
    {
      $this->toetshierarchie = $toetshierarchie;
      return $this;
    }

    /**
     * @return ArrayOfCtpToetsOnderdeel
     */
    public function getToetsonderdelen()
    {
      return $this->toetsonderdelen;
    }

    /**
     * @param ArrayOfCtpToetsOnderdeel $toetsonderdelen
     * @return ctpToets
     */
    public function setToetsonderdelen($toetsonderdelen)
    {
      $this->toetsonderdelen = $toetsonderdelen;
      return $this;
    }

}
