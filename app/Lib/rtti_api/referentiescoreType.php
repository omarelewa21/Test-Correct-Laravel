<?php

class referentiescoreType
{

    /**
     * @var string $codereferentiescore
     */
    protected $codereferentiescore = null;

    /**
     * @var string $codevergelijkingsgroep
     */
    protected $codevergelijkingsgroep = null;

    /**
     * @var string $waarde
     */
    protected $waarde = null;

    /**
     * @var string $kwalificatie
     */
    protected $kwalificatie = null;

    
    public function __construct()
    {
    
    }

    /**
     * @return string
     */
    public function getCodereferentiescore()
    {
      return $this->codereferentiescore;
    }

    /**
     * @param string $codereferentiescore
     * @return referentiescoreType
     */
    public function setCodereferentiescore($codereferentiescore)
    {
      $this->codereferentiescore = $codereferentiescore;
      return $this;
    }

    /**
     * @return string
     */
    public function getCodevergelijkingsgroep()
    {
      return $this->codevergelijkingsgroep;
    }

    /**
     * @param string $codevergelijkingsgroep
     * @return referentiescoreType
     */
    public function setCodevergelijkingsgroep($codevergelijkingsgroep)
    {
      $this->codevergelijkingsgroep = $codevergelijkingsgroep;
      return $this;
    }

    /**
     * @return string
     */
    public function getWaarde()
    {
      return $this->waarde;
    }

    /**
     * @param string $waarde
     * @return referentiescoreType
     */
    public function setWaarde($waarde)
    {
      $this->waarde = $waarde;
      return $this;
    }

    /**
     * @return string
     */
    public function getKwalificatie()
    {
      return $this->kwalificatie;
    }

    /**
     * @param string $kwalificatie
     * @return referentiescoreType
     */
    public function setKwalificatie($kwalificatie)
    {
      $this->kwalificatie = $kwalificatie;
      return $this;
    }

}
