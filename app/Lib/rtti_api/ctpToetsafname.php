<?php

class ctpToetsafname
{

    /**
     * @var string $leerlingid
     */
    protected $leerlingid = null;

    /**
     * @var string $eckid
     */
    protected $eckid = null;

    /**
     * @var string $resultaatverwerkerid
     */
    protected $resultaatverwerkerid = null;

    /**
     * @var ArrayOfCtpResultaat $resultaten
     */
    protected $resultaten = null;

    
    public function __construct()
    {
    
    }

    /**
     * @return string
     */
    public function getLeerlingid()
    {
      return $this->leerlingid;
    }

    /**
     * @param string $leerlingid
     * @return ctpToetsafname
     */
    public function setLeerlingid($leerlingid)
    {
      $this->leerlingid = $leerlingid;
      return $this;
    }

    /**
     * @return string
     */
    public function getEckid()
    {
      return $this->eckid;
    }

    /**
     * @param string $eckid
     * @return ctpToetsafname
     */
    public function setEckid($eckid)
    {
      $this->eckid = $eckid;
      return $this;
    }

    /**
     * @return string
     */
    public function getResultaatverwerkerid()
    {
      return $this->resultaatverwerkerid;
    }

    /**
     * @param string $resultaatverwerkerid
     * @return ctpToetsafname
     */
    public function setResultaatverwerkerid($resultaatverwerkerid)
    {
      $this->resultaatverwerkerid = $resultaatverwerkerid;
      return $this;
    }

    /**
     * @return ArrayOfCtpResultaat
     */
    public function getResultaten()
    {
      return $this->resultaten;
    }

    /**
     * @param ArrayOfCtpResultaat $resultaten
     * @return ctpToetsafname
     */
    public function setResultaten($resultaten)
    {
      $this->resultaten = $resultaten;
      return $this;
    }

}
