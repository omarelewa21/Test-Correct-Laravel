<?php

class ctpNormering
{

    /**
     * @var ctpVocabulaireGebondenVeld $toetscategorie
     */
    protected $toetscategorie = null;

    /**
     * @var ctpVocabulaireGebondenVeld $toetsniveau
     */
    protected $toetsniveau = null;

    /**
     * @var float $wegingsfactor
     */
    protected $wegingsfactor = null;

    /**
     * @var norm[] $norm
     */
    protected $norm = null;

    /**
     * @var anyURI $vocabulaire
     */
    protected $vocabulaire = null;

    /**
     * @var anyURI $vocabulairelocatie
     */
    protected $vocabulairelocatie = null;

    /**
     * @param anyURI $vocabulaire
     * @param anyURI $vocabulairelocatie
     */
    public function __construct($vocabulaire, $vocabulairelocatie)
    {
      $this->vocabulaire = $vocabulaire;
      $this->vocabulairelocatie = $vocabulairelocatie;
    }

    /**
     * @return ctpVocabulaireGebondenVeld
     */
    public function getToetscategorie()
    {
      return $this->toetscategorie;
    }

    /**
     * @param ctpVocabulaireGebondenVeld $toetscategorie
     * @return ctpNormering
     */
    public function setToetscategorie($toetscategorie)
    {
      $this->toetscategorie = $toetscategorie;
      return $this;
    }

    /**
     * @return ctpVocabulaireGebondenVeld
     */
    public function getToetsniveau()
    {
      return $this->toetsniveau;
    }

    /**
     * @param ctpVocabulaireGebondenVeld $toetsniveau
     * @return ctpNormering
     */
    public function setToetsniveau($toetsniveau)
    {
      $this->toetsniveau = $toetsniveau;
      return $this;
    }

    /**
     * @return float
     */
    public function getWegingsfactor()
    {
      return $this->wegingsfactor;
    }

    /**
     * @param float $wegingsfactor
     * @return ctpNormering
     */
    public function setWegingsfactor($wegingsfactor)
    {
      $this->wegingsfactor = $wegingsfactor;
      return $this;
    }

    /**
     * @return norm[]
     */
    public function getNorm()
    {
      return $this->norm;
    }

    /**
     * @param norm[] $norm
     * @return ctpNormering
     */
    public function setNorm(array $norm = null)
    {
      $this->norm = $norm;
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
     * @return ctpNormering
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
     * @return ctpNormering
     */
    public function setVocabulairelocatie($vocabulairelocatie)
    {
      $this->vocabulairelocatie = $vocabulairelocatie;
      return $this;
    }

}
