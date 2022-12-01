<?php

class norm
{

    /**
     * @var string $term
     */
    protected $term = null;

    /**
     * @var string $omschrijving
     */
    protected $omschrijving = null;

    /**
     * @var int $beginnormwaarde
     */
    protected $beginnormwaarde = null;

    /**
     * @var int $eindnormwaarde
     */
    protected $eindnormwaarde = null;

    /**
     * @var normkleur $normkleur
     */
    protected $normkleur = null;

    /**
     * @var float $schoolcijfer_vanaf
     */
    protected $schoolcijfer_vanaf = null;

    /**
     * @var float $schoolcijfer_totenmet
     */
    protected $schoolcijfer_totenmet = null;

    /**
     * @param string $term
     * @param string $omschrijving
     * @param int $beginnormwaarde
     * @param int $eindnormwaarde
     * @param normkleur $normkleur
     * @param float $schoolcijfer_vanaf
     * @param float $schoolcijfer_totenmet
     */
    public function __construct($term, $omschrijving, $beginnormwaarde, $eindnormwaarde, $normkleur, $schoolcijfer_vanaf, $schoolcijfer_totenmet)
    {
      $this->term = $term;
      $this->omschrijving = $omschrijving;
      $this->beginnormwaarde = $beginnormwaarde;
      $this->eindnormwaarde = $eindnormwaarde;
      $this->normkleur = $normkleur;
      $this->schoolcijfer_vanaf = $schoolcijfer_vanaf;
      $this->schoolcijfer_totenmet = $schoolcijfer_totenmet;
    }

    /**
     * @return string
     */
    public function getTerm()
    {
      return $this->term;
    }

    /**
     * @param string $term
     * @return norm
     */
    public function setTerm($term)
    {
      $this->term = $term;
      return $this;
    }

    /**
     * @return string
     */
    public function getOmschrijving()
    {
      return $this->omschrijving;
    }

    /**
     * @param string $omschrijving
     * @return norm
     */
    public function setOmschrijving($omschrijving)
    {
      $this->omschrijving = $omschrijving;
      return $this;
    }

    /**
     * @return int
     */
    public function getBeginnormwaarde()
    {
      return $this->beginnormwaarde;
    }

    /**
     * @param int $beginnormwaarde
     * @return norm
     */
    public function setBeginnormwaarde($beginnormwaarde)
    {
      $this->beginnormwaarde = $beginnormwaarde;
      return $this;
    }

    /**
     * @return int
     */
    public function getEindnormwaarde()
    {
      return $this->eindnormwaarde;
    }

    /**
     * @param int $eindnormwaarde
     * @return norm
     */
    public function setEindnormwaarde($eindnormwaarde)
    {
      $this->eindnormwaarde = $eindnormwaarde;
      return $this;
    }

    /**
     * @return normkleur
     */
    public function getNormkleur()
    {
      return $this->normkleur;
    }

    /**
     * @param normkleur $normkleur
     * @return norm
     */
    public function setNormkleur($normkleur)
    {
      $this->normkleur = $normkleur;
      return $this;
    }

    /**
     * @return float
     */
    public function getSchoolcijfer_vanaf()
    {
      return $this->schoolcijfer_vanaf;
    }

    /**
     * @param float $schoolcijfer_vanaf
     * @return norm
     */
    public function setSchoolcijfer_vanaf($schoolcijfer_vanaf)
    {
      $this->schoolcijfer_vanaf = $schoolcijfer_vanaf;
      return $this;
    }

    /**
     * @return float
     */
    public function getSchoolcijfer_totenmet()
    {
      return $this->schoolcijfer_totenmet;
    }

    /**
     * @param float $schoolcijfer_totenmet
     * @return norm
     */
    public function setSchoolcijfer_totenmet($schoolcijfer_totenmet)
    {
      $this->schoolcijfer_totenmet = $schoolcijfer_totenmet;
      return $this;
    }

}
