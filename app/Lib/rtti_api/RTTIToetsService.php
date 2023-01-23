<?php

class RTTIToetsService extends \SoapClient
{

  public $test;

    /**
     * @var array $classmap The defined classes
     */
    private static $classmap = array (
    'toetsscoreType' => '\\toetsscoreType',
    'betrouwbaarheidsinterval' => '\\betrouwbaarheidsinterval',
    'referentiescoreType' => '\\referentiescoreType',
    'leerresultaten_verzoek' => '\\leerresultaten_verzoek',
    'ctpSchool' => '\\ctpSchool',
    'ArrayOfCtpToetsafname' => '\\ArrayOfCtpToetsafname',
    'ctpToetsafname' => '\\ctpToetsafname',
    'ArrayOfCtpResultaat' => '\\ArrayOfCtpResultaat',
    'ctpResultaat' => '\\ctpResultaat',
    'ctpVocabulaireGebondenVeld' => '\\ctpVocabulaireGebondenVeld',
    'ctpAnderResultaatType' => '\\ctpAnderResultaatType',
    'ctpOsoResultaat' => '\\ctpOsoResultaat',
    'ArrayOfCtpToets' => '\\ArrayOfCtpToets',
    'ctpToets' => '\\ctpToets',
    'ctpNormering' => '\\ctpNormering',
    'norm' => '\\norm',
    'normkleur' => '\\normkleur',
    'ArrayOfCtpToetsIngang' => '\\ArrayOfCtpToetsIngang',
    'ingang' => '\\ingang',
    'ArrayOfCtpToetsOnderdeel' => '\\ArrayOfCtpToetsOnderdeel',
    'ctpToetsOnderdeel' => '\\ctpToetsOnderdeel',
    'ctpLeeg' => '\\ctpLeeg',
    'autorisatie' => '\\autorisatie',
  );

    /**
     * @param array $options A array of config values
     * @param string $wsdl The wsdl file to use
     */
    public function __construct(array $options = array(), $wsdl = null)
    {
    
    
    foreach (self::$classmap as $key => $value) {
      if (!isset($options['classmap'][$key])) {
        $options['classmap'][$key] = $value;
      }
    }

    $options = array_merge(
      array (
        'encoding' => 'utf8',
        'features' => 1,
        "trace" => 1,
        'exceptions' => 1,
        'soap_version'=> SOAP_1_2,
        'location' => 'https://www.rttionline.nl/RTTIToetsService.Test/RTTIToetsService.svc',
        'use' => SOAP_LITERAL,
        'style' => SOAP_DOCUMENT
      ), $options
    );
    
    if (!$wsdl) {
      $wsdl = 'https://www.rttionline.nl/RTTIToetsService.Test/RTTIToetsService.svc?wsdl';
    }
      parent::__construct($wsdl, $options);

      $auth = new autorisatie('Pk77881FG-HJ99777737=','89TY55661==866FFFG','UitgeverX');
      $headers[] = new SoapHeader('http://www.edustandaard.nl/leerresultaten/2/autorisatie','autorisatie',$auth,false);
      $this->test = $headers;
      $this->__setSoapHeaders($headers);
    }
    
    /**
     * @param leerresultaten_verzoek $leerresultaten_verzoek
     * @return ctpLeeg
     */
    public function BrengLeerresultaten(leerresultaten_verzoek $leerresultaten_verzoek)
    {

      // build array
      // $i = 0;
      // foreach($leerresultaten_verzoek->getToetsafnames() as $afname) {
      //   $toetsafnames[$i]['toetsafname'] = array(
      //     'leerlingid' => $afname->getLeerlingid(),
      //     'resultaatverwerkerid' => $afname->getResultaatverwerkerid(),
      //   );

      //   foreach($afname->getResultaten() as $resultaat) {
      //     $resultaten[]['resultaat'] = array(
      //       'key' => $resultaat->getKey(),
      //       'afnamedatum' => $resultaat->getAfnamedatum(),
      //       'toetscode' => $resultaat->getToetscode(),
      //       'toetsonderdeelcode' => $resultaat->getToetsonderdeelcode(),
      //       'score' => $resultaat->getScore(),
      //     );
      //   }

      //   $toetsafnames[$i]['toetsafname']['resultaten'] = $resultaten;
      //   $i++;
      // }
      // return $toetsafnames;

      // $school = $leerresultaten_verzoek->getSchool();
      // $toetsafnames = $leerresultaten_verzoek->getToetsafnames();
      // $toetsen = $leerresultaten_verzoek->getToetsen();

      // $leerresultaten_verzoek_array = array(
      //   'school' => $school,
      //   'toetsafnames' => $toetsafnames,
      //   'toetsen' => $toetsen
      // );

      // return $leerresultaten_verzoek_array;

      try {
        return $this->__call('BrengLeerresultaten', $leerresultaten_verzoek);
      } catch (Exception $e) {
        return $e->getMessage();
      }

      // return $this->__getLastResponse();
    }

    function __doRequest($request, $location, $action, $version, $one_way = 0) 
    {

      $_SESSION['req'] = $request;
      $res = parent::__doRequest($request, $location, $action, $version, $one_way);

      if(isset($this->__soap_fault) && $this->__soap_fault != null){
        $exception = $this->__soap_fault;
        $_SESSION['exc'] = $exception;
      }
      return $res;
    }

    public function getTypes()
    {
      return $this->__getTypes();
    }

    public function getFunctions()
    {
      return $this->__getFunctions();
    }

    public function debug(){
      return $this->__getLastRequest();
     // return $this->test;
    }

}
