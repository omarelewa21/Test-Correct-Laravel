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
    private $manufacturer = 'CITO';


    protected function _init()
    {
        $this->metaData = collect([]);
        $this->resources = collect([]);
    }

    public function __construct()
    {
        $this->_init();
        $this->setManufacturer();
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

    public function getManufacturer() {
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

    public function getProperties()
    {
        $meta = $this->originalXml->metadata->children('depcp', true)->metadata;

        return [
            'id'       => $meta->id ? $meta->id->__toString() : 'someId',
            'name'     => $meta->meta ? $meta->name->__toString() : 'someMeta',
            'version'  => $meta->version ? $meta->version->__toString() : 'someVersion',
            'guid'     => $meta->guid ? $meta->guid->__toString() : 'someGuid',
            'testType' => $meta->testType ? $meta->testType->__toString() : 'someTestType',
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

}
