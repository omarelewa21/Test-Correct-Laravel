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


    protected function _init()
    {
        $this->metaData = collect([]);
        $this->resources = collect([]);
    }

    public function __construct()
    {
        $this->_init();
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

    public function setOriginalXml($xml)
    {
        $this->originalXml = simplexml_load_string($xml);
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
        foreach (($this->originalXml->resources->resource) as $tag=>$node) {
            if ($tag == 'resource') {
                if ($resource = QtiResource::createWithSimpleXMLArrayIfPossible($node)) {
                    $this->addResource($resource);
                }
            }
        }
    }

}
