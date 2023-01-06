<?php

class ctpOsoResultaat
{

    /**
     * @var toetsscoreType $toetsscore
     */
    protected $toetsscore = null;

    /**
     * @var referentiescoreType $referentiescore
     */
    protected $referentiescore = null;

    /**
     * @param toetsscoreType $toetsscore
     * @param referentiescoreType $referentiescore
     */
    public function __construct($toetsscore, $referentiescore)
    {
      $this->toetsscore = $toetsscore;
      $this->referentiescore = $referentiescore;
    }

    /**
     * @return toetsscoreType
     */
    public function getToetsscore()
    {
      return $this->toetsscore;
    }

    /**
     * @param toetsscoreType $toetsscore
     * @return ctpOsoResultaat
     */
    public function setToetsscore($toetsscore)
    {
      $this->toetsscore = $toetsscore;
      return $this;
    }

    /**
     * @return referentiescoreType
     */
    public function getReferentiescore()
    {
      return $this->referentiescore;
    }

    /**
     * @param referentiescoreType $referentiescore
     * @return ctpOsoResultaat
     */
    public function setReferentiescore($referentiescore)
    {
      $this->referentiescore = $referentiescore;
      return $this;
    }

}
