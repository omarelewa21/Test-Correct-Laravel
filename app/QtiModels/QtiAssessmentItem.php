<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 11/08/2020
 * Time: 14:23
 */

namespace tcCore\QtiModels;


class QtiAssessmentItem
{
    protected $attributes;

    protected $type;
    protected $typeAttributes;
    protected $typeDetails;
    protected $correctResponse;

    protected $outcomeDefaultValue;
    protected $outcomeDefaultValueType;
    protected $stylesheets;

    protected $originalBody;
    protected $parsedBody;

//    protected $responseProcessing;
    protected $responseConditions;

    protected function _init()
    {
        $this->attributes = collect([]);
        $this->stylesheets = collect([]);
        $this->responseConditions = collect([]);
        $this->typeAttributes = collect([]);
    }

    public function __construct(array $attributes)
    {
        $this->_init();

        foreach($attributes as $key => $value){
            $this->attributes->set($key,$value);
        }
    }

    public function getAttributeItem($key)
    {
        return $this->attributes->get($key);
    }

    public function setCorrectResponse($response)
    {
        $this->correctResponse = $response;
        return $this;
    }

    public function setOutcomeDefaultValueAndType($value,$type)
    {
        $this->outcomeDefaultValue = $value;
        $this->outcomeDefaultValueType = $type;
        return $this;
    }

    public function setStylesheets($stylesheets)
    {
        $this->stylesheets = collect($stylesheets);
        return $this;
    }

    public function addStylesheet($stylesheet)
    {
        $this->stylesheets->push($stylesheet);
        return $this;
    }

    public function getStylesheets()
    {
        return $this->stylesheets;
    }

    public function setOriginalBody($body)
    {
        $this->originalBody = $body;
        return $this;
    }

    public function getOriginalBody()
    {
        return $this->originalBody;
    }

    public function setParsedBody($body)
    {
        $this->parsedBody = $body;
        return $this;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function addTypeAttribute($key,$value)
    {
        $this->typeAttributes->set($key,$value);
        return $this;
    }

    public function getTypeAttributes()
    {
        return $this->typeAttributes;
    }

    public function getTypeAttributesItem($key)
    {
        return $this->typeAttributes->get($key);
    }

    public function setTypeDetails($details)
    {
        $this->typeDetails = $details;
        return $this;
    }

    public function getTypeDetails()
    {
        return $this->typeDetails;
    }

    public function addResponseCondition(QtiResponseCondition $condition)
    {
       $this->responseConditions->push($condition);
       return $this;
    }

    public function getResponseConditions(){
        return $this->responseConditions;
    }

}