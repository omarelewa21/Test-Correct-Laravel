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
        $this->metaData->put($metaDataKey,$metaDataValue);
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
        $this->originalXml = $xml;
        return $this;
    }

    public function getOriginalXml()
    {
        return $this->originalXml;
    }

}