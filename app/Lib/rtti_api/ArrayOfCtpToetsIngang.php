<?php

class ArrayOfCtpToetsIngang extends ctpVocabulaireGebondenVeld
{

    /**
     * @var ingang[] $ingang
     */
    protected $ingang = null;

    /**
     * @param string $_
     * @param anyURI $vocabulaire
     * @param anyURI $vocabulairelocatie
     */
    public function __construct($_, $vocabulaire, $vocabulairelocatie)
    {
      parent::__construct($_, $vocabulaire, $vocabulairelocatie);
    }

    /**
     * @return ingang[]
     */
    public function getIngang()
    {
      return $this->ingang;
    }

    /**
     * @param ingang[] $ingang
     * @return ArrayOfCtpToetsIngang
     */
    public function setIngang(array $ingang = null)
    {
      $this->ingang = $ingang;
      return $this;
    }

}
