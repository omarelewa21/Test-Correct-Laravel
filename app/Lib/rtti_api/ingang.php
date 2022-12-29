<?php

class ingang
{

    /**
     * @var ctpVocabulaireGebondenVeld $_
     */
    protected $_ = null;

    /**
     * @var int $niveau
     */
    protected $niveau = null;

    /**
     * @param ctpVocabulaireGebondenVeld $_
     * @param int $niveau
     */
    public function __construct($_, $niveau)
    {
      $this->_ = $_;
      $this->niveau = $niveau;
    }

    /**
     * @return ctpVocabulaireGebondenVeld
     */
    public function get_()
    {
      return $this->_;
    }

    /**
     * @param ctpVocabulaireGebondenVeld $_
     * @return ingang
     */
    public function set_($_)
    {
      $this->_ = $_;
      return $this;
    }

    /**
     * @return int
     */
    public function getNiveau()
    {
      return $this->niveau;
    }

    /**
     * @param int $niveau
     * @return ingang
     */
    public function setNiveau($niveau)
    {
      $this->niveau = $niveau;
      return $this;
    }

}
