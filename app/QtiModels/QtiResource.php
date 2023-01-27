<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 11/08/2020
 * Time: 14:10
 */

namespace tcCore\QtiModels;


use tcCore\Test;

class QtiResource
{
    public $identifier;
    public $type;
    public $href;
    protected $version;
    public $guid;

    protected $metaData;

    protected $assessmentItem;

    protected function _init()
    {
        $this->metaData = collect([]);
    }

    public function __construct($identifier, $type, $href, $version, $guid, $test = false, $manufacturer=false)
    {
        $this->_init();

        $this->identifier = $identifier;
        $this->type = $type;
        $this->href = $href;

        if ($manufacturer === 'WOOTS') {
            // remove /zipdir/ from href;
            $this->href = str_replace('/zipdir', '/zipdir/NBO voorronde 20_21-qti', $this->href);
        }

        $this->version = $version;
        $this->guid = $guid;
        if ($test === false) {
            $test = Test::find(1);
        }
        $this->test = $test;

//        logger(sprintf('import started for resource %s, %s, %s', $this->href, $this->type, $this->identifier));
    }

    public function addMetaData($key, $value)
    {
        $this->metaData->push($key, $value);
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

    public function setAssesmentItem(QtiAssessmentItem $item)
    {
        $this->assessmentItem = $item;
        return $this;
    }

    public function getAssessmentItem()
    {
        return $this->assessmentItem;
    }

    public function getTest()
    {
        return $this->test;
    }

    public static function createWithSimpleXMLArrayIfPossible(\SimpleXMLElement $el)
    {
        $attributes = [];
        $attributesDepcp = [];
        foreach ($el->attributes() as $name => $value) {
            $attributes[$name] = (string) $value;
        }

        foreach ($el->attributes('depcp', true) as $name => $value) {
            $attributesDepcp[$name] = (string) $value;
        }

        try {
            if ($attributes['type'] !== 'imsqti_item_xmlv2p2') {
                throw new \Exception('not correct of type imsqti_item_xmlv2p2');
            }


            return new self(
                $attributes['identifier'],
                $attributes['type'],
                $attributes['href'],
                array_key_exists('version', $attributesDepcp) ? $attributesDepcp['version'] : '',
                array_key_exists('guid', $attributesDepcp) ? $attributesDepcp['guid'] : ''
            );
        } catch (\Exception $e) {
            return false;
        }
    }

}
