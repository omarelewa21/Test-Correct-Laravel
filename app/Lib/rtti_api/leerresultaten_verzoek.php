<?php

class leerresultaten_verzoek
{

    /**
     * @var ctpSchool $school
     */
    protected $school = null;

    /**
     * @var ArrayOfCtpToetsafname $toetsafnames
     */
    protected $toetsafnames = null;

    /**
     * @var ArrayOfCtpToets $toetsen
     */
    protected $toetsen = null;

    /**
     * @param ctpSchool $school
     * @param ArrayOfCtpToetsafname $toetsafnames
     * @param ArrayOfCtpToets $toetsen
     */
    public function __construct($school, $toetsafnames, $toetsen)
    {
      $this->school = $school;
      $this->toetsafnames = $toetsafnames;
      $this->toetsen = $toetsen;
    }

    /**
     * @return ctpSchool
     */
    public function getSchool()
    {
      return $this->school;
    }

    /**
     * @param ctpSchool $school
     * @return leerresultaten_verzoek
     */
    public function setSchool($school)
    {
      $this->school = $school;
      return $this;
    }

    /**
     * @return ArrayOfCtpToetsafname
     */
    public function getToetsafnames()
    {
      return $this->toetsafnames;
    }

    /**
     * @param ArrayOfCtpToetsafname $toetsafnames
     * @return leerresultaten_verzoek
     */
    public function setToetsafnames($toetsafnames)
    {
      $this->toetsafnames = $toetsafnames;
      return $this;
    }

    /**
     * @return ArrayOfCtpToets
     */
    public function getToetsen()
    {
      return $this->toetsen;
    }

    /**
     * @param ArrayOfCtpToets $toetsen
     * @return leerresultaten_verzoek
     */
    public function setToetsen($toetsen)
    {
      $this->toetsen = $toetsen;
      return $this;
    }

}
