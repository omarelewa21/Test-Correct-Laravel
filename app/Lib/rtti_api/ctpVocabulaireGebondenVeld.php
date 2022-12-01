<?php

class ctpVocabulaireGebondenVeld
{

    /**
     * @var string $_
     */
    protected $_ = null;

    /**
     * @var anyURI $vocabulaire
     */
    protected $vocabulaire = null;

    /**
     * @var anyURI $vocabulairelocatie
     */
    protected $vocabulairelocatie = null;

    /**
     * @param string $_
     * @param anyURI $vocabulaire
     * @param anyURI $vocabulairelocatie
     */
    public function __construct($_, $vocabulaire, $vocabulairelocatie)
    {
      $this->_ = $_;
      $this->vocabulaire = $vocabulaire;
      $this->vocabulairelocatie = $vocabulairelocatie;
    }

    /**
     * @return string
     */
    public function get_()
    {
      return $this->_;
    }

    /**
     * @param string $_
     * @return ctpVocabulaireGebondenVeld
     */
    public function set_($_)
    {
      $this->_ = $_;
      return $this;
    }

    /**
     * @return anyURI
     */
    public function getVocabulaire()
    {
      return $this->vocabulaire;
    }

    /**
     * @param anyURI $vocabulaire
     * @return ctpVocabulaireGebondenVeld
     */
    public function setVocabulaire($vocabulaire)
    {
      $this->vocabulaire = $vocabulaire;
      return $this;
    }

    /**
     * @return anyURI
     */
    public function getVocabulairelocatie()
    {
      return $this->vocabulairelocatie;
    }

    /**
     * @param anyURI $vocabulairelocatie
     * @return ctpVocabulaireGebondenVeld
     */
    public function setVocabulairelocatie($vocabulairelocatie)
    {
      $this->vocabulairelocatie = $vocabulairelocatie;
      return $this;
    }

}
