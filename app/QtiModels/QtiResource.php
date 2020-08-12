<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 11/08/2020
 * Time: 14:10
 */

namespace tcCore\QtiModels;


class QtiResource
{
     protected $identifier;
     protected $type;
     protected $href;
     protected $version;
     protected $guid;

     protected $metaData;

     protected $assessmentItem;

     protected function _init()
     {
         $this->metaData = collect([]);
     }

     public function __construct($identifier, $type,$href,$version,$guid)
     {
         $this->_init();

         $this->identifier = $identifier;
         $this->type = $type;
         $this->href = $href;
         $this->version = $version;
         $this->guid = $guid;
     }

     public function addMetaData($key, $value)
     {
         $this->metaData->push($key,$value);
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

}