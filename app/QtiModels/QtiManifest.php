<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 11/08/2020
 * Time: 13:06
 */

namespace tcCore\QtiModels;


class QtiManifest
{

    protected $metaData;
    protected $resources;
    protected $originalXml;
    public $namespaces = [];
    private $file;
    private $manufacturer = 'CITO';


    protected function _init()
    {
        $this->metaData = collect([]);
        $this->resources = collect([]);
    }

    public function __construct($file = null)
    {
        $this->_init();
        $this->setManufacturer();
        $this->file = $file;
    }

    public function addMetaData($metaDataKey, $metaDataValue)
    {
        $this->metaData->put($metaDataKey, $metaDataValue);
        return $this;
    }

    public function getMetaData()
    {
        return $this->metaData;
    }

    public function getMetaDataItem($key)
    {
        return $this->metaData->get($key);
    }

    public function addResource(QtiResource $resource)
    {
        $this->resources->push($resource);
        return $this;
    }

    public function getResources()
    {
        return $this->resources;
    }

    public function getManufacturer()
    {
        return $this->manufacturer;
    }

    public function setOriginalXml($xml)
    {
        if ($xml instanceof \SimpleXMLElement) {
            $this->originalXml = $xml;
        } else {
            $this->originalXml = simplexml_load_string($xml);
        }

        $this->namespaces = $this->originalXml->getNamespaces(true);
        $this->refreshMetaData();
        $this->refreshResources();
        return $this;
    }

    public function getOriginalXml()
    {
        return $this->originalXml;
    }


    private function refreshMetaData()
    {
        foreach (get_object_vars($this->originalXml->metadata) as $key => $value) {
            $this->addMetaData($key, $value);
        }
    }

    private function refreshResources()
    {
        foreach (($this->originalXml->resources->resource) as $tag => $node) {
            if ($tag == 'resource') {
                if ($resource = QtiResource::createWithSimpleXMLArrayIfPossible($node)) {
                    $this->addResource($resource);
                }
            }
        }
    }

    public function getScope() {
        if ($this->manufacturer === 'CITO') {
            return 'cito';
        }
        return '';
    }

    public function getProperties()
    {
        $meta = $this->originalXml->metadata->children('depcp', true)->metadata;
        return [
            'id'       => $meta->id !== null && $meta->id->__toString() ? $meta->id->__toString() : $this->getDefaultId(),
            'name'     => $meta->name !== null && $meta->name->__toString() ? $meta->name->__toString() : $this->getDefaultName(),
            'version'  => $meta->version !== null && $meta->version->__toString() ? $meta->version->__toString() : $this->getDefaultVersion(),
            'guid'     => $meta->guid !== null && $meta->guid->__toString() ? $meta->guid->__toString() : $this->getDefaultUuid(),
            'testType' => $meta->testType !== null && $meta->testType->__toString() ? $meta->testType->__toString() : $this->getDefaultTestType(),
        ];
    }

    public function getTestResourcesList()
    {
        $list = collect([]);

        $dom = new \DOMDocument();
        $dom->loadXML($this->originalXml->asXML());

        $namespaceURI = 'http://www.imsglobal.org/xsd/imscp_ext_v1p2';

        foreach ($dom->getElementsByTagName('resource') as $resource) {
            if ($resource->getAttribute('href') && $resource->getAttribute('type') == 'imsqti_item_xmlv2p2') {
                $resourceObj = [
                    'href'       => $resource->getAttribute('href'),
                    'identifier' => $resource->getAttribute('identifier'),
                    'guid'       => $resource->getAttribute('guid'),

                ];
                foreach ($resource->getElementsByTagNameNS($namespaceURI, 'property') as $property) {
                    $resourceObj[$property->getElementsByTagNameNS($namespaceURI,
                        'name')->item(0)->nodeValue] = $property->getElementsByTagNameNS($namespaceURI,
                        'value')->item(0)->nodeValue;
                }
                $list->add($resourceObj);
            }
        }

        return $list;
    }

    public function getTestListWithResources()
    {
        $list = [];

        foreach ($this->getTestResourcesList() as $resource) {


            $testName = sprintf('%s - %s - %s', $resource['hoofddomein'], $resource['leerdoel'], $resource['leerweg']);
            if (!array_key_exists($testName, $list)) {
                $list[$testName] = [];
            }

            $list[$testName][] = $resource;

        }
        return $list;
    }

    public function getName()
    {
        $props = $this->getProperties();

        if ($this->manufacturer === 'WOOTS'){
            return $props['name'];
        }

        return sprintf('%s | %s', $props['id'], $props['name']);
    }

    public function getId()
    {
        return $this->getProperties()['id'];
    }

    private function setManufacturer()
    {
        $this->manufacturer = 'WOOTS';
    }

    /**
     * @return string
     */
    private function getDefaultId(): string
    {
        if ($this->manufacturer === 'WOOTS') {
            return '';
        }
        return 'someId';
    }

    /**
     * @return string
     */
    private function getDefaultName(): string
    {
        if ($this->manufacturer === 'WOOTS') {
            $info = pathinfo($this->file);
            if (array_key_exists('dirname', $info)) {
                $arr = explode(DIRECTORY_SEPARATOR, $info['dirname']);
                $var = array_pop($arr);
                if (!empty($var)) {
                    return $var;
                }
            }

        }
        return 'someMeta';
    }

    /**
     * @return string
     */
    private function getDefaultVersion(): string
    {
        return 'someVersion';
    }

    /**
     * @return string
     */
    private function getDefaultUuid(): string
    {
        return 'someGuid';
    }

    /**
     * @return string
     */
    private function getDefaultTestType(): string
    {
        return 'someTestType';
    }

}
