<?php

class ctpSchool
{

    /**
     * @var string $schooljaar
     */
    protected $schooljaar = null;

    /**
     * @var string $brincode
     */
    protected $brincode = null;

    /**
     * @var string $dependancecode
     */
    protected $dependancecode = null;

    /**
     * @var string $schoolkey
     */
    protected $schoolkey = null;

    /**
     * @var \DateTime $aanmaakdatum
     */
    protected $aanmaakdatum = null;

    /**
     * @var string $auteur
     */
    protected $auteur = null;

    /**
     * @var string $xsdversie
     */
    protected $xsdversie = null;

    /**
     * @var string $commentaar
     */
    protected $commentaar = null;

    /**
     * @param \DateTime $aanmaakdatum
     */
    public function __construct(\DateTime $aanmaakdatum)
    {
      $this->aanmaakdatum = $aanmaakdatum->format(\DateTime::ATOM);
    }

    /**
     * @return string
     */
    public function getSchooljaar()
    {
      return $this->schooljaar;
    }

    /**
     * @param string $schooljaar
     * @return ctpSchool
     */
    public function setSchooljaar($schooljaar)
    {
      $this->schooljaar = $schooljaar;
      return $this;
    }

    /**
     * @return string
     */
    public function getBrincode()
    {
      return $this->brincode;
    }

    /**
     * @param string $brincode
     * @return ctpSchool
     */
    public function setBrincode($brincode)
    {
      $this->brincode = $brincode;
      return $this;
    }

    /**
     * @return string
     */
    public function getDependancecode()
    {
      return $this->dependancecode;
    }

    /**
     * @param string $dependancecode
     * @return ctpSchool
     */
    public function setDependancecode($dependancecode)
    {
      $this->dependancecode = $dependancecode;
      return $this;
    }

    /**
     * @return string
     */
    public function getSchoolkey()
    {
      return $this->schoolkey;
    }

    /**
     * @param string $schoolkey
     * @return ctpSchool
     */
    public function setSchoolkey($schoolkey)
    {
      $this->schoolkey = $schoolkey;
      return $this;
    }

    /**
     * @return \DateTime
     */
    public function getAanmaakdatum()
    {
      if ($this->aanmaakdatum == null) {
        return null;
      } else {
        try {
          return new \DateTime($this->aanmaakdatum);
        } catch (\Exception $e) {
          return false;
        }
      }
    }

    /**
     * @param \DateTime $aanmaakdatum
     * @return ctpSchool
     */
    public function setAanmaakdatum(\DateTime $aanmaakdatum)
    {
      $this->aanmaakdatum = $aanmaakdatum->format(\DateTime::ATOM);
      return $this;
    }

    /**
     * @return string
     */
    public function getAuteur()
    {
      return $this->auteur;
    }

    /**
     * @param string $auteur
     * @return ctpSchool
     */
    public function setAuteur($auteur)
    {
      $this->auteur = $auteur;
      return $this;
    }

    /**
     * @return string
     */
    public function getXsdversie()
    {
      return $this->xsdversie;
    }

    /**
     * @param string $xsdversie
     * @return ctpSchool
     */
    public function setXsdversie($xsdversie)
    {
      $this->xsdversie = $xsdversie;
      return $this;
    }

    /**
     * @return string
     */
    public function getCommentaar()
    {
      return $this->commentaar;
    }

    /**
     * @param string $commentaar
     * @return ctpSchool
     */
    public function setCommentaar($commentaar)
    {
      $this->commentaar = $commentaar;
      return $this;
    }

}
