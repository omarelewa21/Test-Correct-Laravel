<?php

class ctpResultaat
{

    /**
     * @var date $afnamedatum
     */
    protected $afnamedatum = null;

    /**
     * @var string $toetscode
     */
    protected $toetscode = null;

    /**
     * @var ctpVocabulaireGebondenVeld $versie
     */
    protected $versie = null;

    /**
     * @var string $toetsonderdeelcode
     */
    protected $toetsonderdeelcode = null;

    /**
     * @var ctpAnderResultaatType $anderresultaat
     */
    protected $anderresultaat = null;

    /**
     * @var ctpOsoResultaat $osoresultaat
     */
    protected $osoresultaat = null;

    /**
     * @var int $score
     */
    protected $score = null;

    /**
     * @var string $infourl
     */
    protected $infourl = null;

    /**
     * @var string $key
     */
    protected $key = null;

    /**
     * @param date $afnamedatum
     * @param string $key
     */
    public function __construct($afnamedatum, $key)
    {
      $this->afnamedatum = $afnamedatum;
      $this->key = $key;
    }

    /**
     * @return date
     */
    public function getAfnamedatum()
    {
      return $this->afnamedatum;
    }

    /**
     * @param date $afnamedatum
     * @return ctpResultaat
     */
    public function setAfnamedatum($afnamedatum)
    {
      $this->afnamedatum = $afnamedatum;
      return $this;
    }

    /**
     * @return string
     */
    public function getToetscode()
    {
      return $this->toetscode;
    }

    /**
     * @param string $toetscode
     * @return ctpResultaat
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
     * @return ctpResultaat
     */
    public function setVersie($versie)
    {
      $this->versie = $versie;
      return $this;
    }

    /**
     * @return string
     */
    public function getToetsonderdeelcode()
    {
      return $this->toetsonderdeelcode;
    }

    /**
     * @param string $toetsonderdeelcode
     * @return ctpResultaat
     */
    public function setToetsonderdeelcode($toetsonderdeelcode)
    {
      $this->toetsonderdeelcode = $toetsonderdeelcode;
      return $this;
    }

    /**
     * @return ctpAnderResultaatType
     */
    public function getAnderresultaat()
    {
      return $this->anderresultaat;
    }

    /**
     * @param ctpAnderResultaatType $anderresultaat
     * @return ctpResultaat
     */
    public function setAnderresultaat($anderresultaat)
    {
      $this->anderresultaat = $anderresultaat;
      return $this;
    }

    /**
     * @return ctpOsoResultaat
     */
    public function getOsoresultaat()
    {
      return $this->osoresultaat;
    }

    /**
     * @param ctpOsoResultaat $osoresultaat
     * @return ctpResultaat
     */
    public function setOsoresultaat($osoresultaat)
    {
      $this->osoresultaat = $osoresultaat;
      return $this;
    }

    /**
     * @return decimal
     */
    public function getScore()
    {
      return $this->score;
    }

    /**
     * @param decimal $score
     * @return ctpResultaat
     */
    public function setScore($score)
    {
      $this->score = $score;
      return $this;
    }

    /**
     * @return string
     */
    public function getInfourl()
    {
      return $this->infourl;
    }

    /**
     * @param string $infourl
     * @return ctpResultaat
     */
    public function setInfourl($infourl)
    {
      $this->infourl = $infourl;
      return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
      return $this->key;
    }

    /**
     * @param string $key
     * @return ctpResultaat
     */
    public function setKey($key)
    {
      $this->key = $key;
      return $this;
    }

}
