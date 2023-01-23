<?php

class autorisatie
{

    /**
     * @var string $autorisatiesleutel
     */
    protected $autorisatiesleutel = null;

    /**
     * @var string $klantcode
     */
    protected $klantcode = null;

    /**
     * @var string $klantnaam
     */
    protected $klantnaam = null;

    /**
     * @param string $autorisatiesleutel
     * @param string $klantcode
     * @param string $klantnaam
     */
    public function __construct($autorisatiesleutel, $klantcode, $klantnaam)
    {
      $this->autorisatiesleutel = $autorisatiesleutel;
      $this->klantcode = $klantcode;
      $this->klantnaam = $klantnaam;
    }

    /**
     * @return string
     */
    public function getAutorisatiesleutel()
    {
      return $this->autorisatiesleutel;
    }

    /**
     * @param string $autorisatiesleutel
     * @return autorisatie
     */
    public function setAutorisatiesleutel($autorisatiesleutel)
    {
      $this->autorisatiesleutel = $autorisatiesleutel;
      return $this;
    }

    /**
     * @return string
     */
    public function getKlantcode()
    {
      return $this->klantcode;
    }

    /**
     * @param string $klantcode
     * @return autorisatie
     */
    public function setKlantcode($klantcode)
    {
      $this->klantcode = $klantcode;
      return $this;
    }

    /**
     * @return string
     */
    public function getKlantnaam()
    {
      return $this->klantnaam;
    }

    /**
     * @param string $klantnaam
     * @return autorisatie
     */
    public function setKlantnaam($klantnaam)
    {
      $this->klantnaam = $klantnaam;
      return $this;
    }

}
